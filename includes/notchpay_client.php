<?php

declare(strict_types=1);

require_once __DIR__ . '/payment_config.php';

/**
 * @return array{ok:bool,http:int,data:?array,error?:string}
 */
function tcf_notchpay_request(string $method, string $path, ?array $body = null): array
{
    $pubKey = tcf_notchpay_public_key();
    $secKey = tcf_notchpay_secret_key();
    
    if ($pubKey === '') {
        return ['ok' => false, 'http' => 0, 'data' => null, 'error' => 'Clé Notch Pay non configurée.'];
    }

    $url = tcf_notchpay_api_base() . '/' . ltrim($path, '/');
    $ch = curl_init($url);
    if ($ch === false) {
        return ['ok' => false, 'http' => 0, 'data' => null, 'error' => 'cURL indisponible.'];
    }

    $headers = [
        'Authorization: ' . $pubKey,
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    if ($secKey !== '') {
        $headers[] = 'Grant-Authorization: ' . $secKey;
        $headers[] = 'X-Grant: ' . $secKey;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 45,
        CURLOPT_CONNECTTIMEOUT => 15,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    $raw = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'http' => $http, 'data' => null, 'error' => $err !== '' ? $err : 'Erreur réseau Notch Pay.'];
    }

    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        return ['ok' => false, 'http' => $http, 'data' => null, 'error' => 'Réponse Notch Pay invalide.'];
    }

    if ($http >= 400) {
        $msg = (string) ($data['message'] ?? $data['error'] ?? 'Erreur Notch Pay (' . $http . ').');
        return ['ok' => false, 'http' => $http, 'data' => $data, 'error' => $msg];
    }

    return ['ok' => true, 'http' => $http, 'data' => $data];
}

/**
 * @param array{name:string,email?:string,phone?:string} $customer
 */
function tcf_notchpay_initialize_payment(int $amount, string $reference, string $description, array $customer, string $callbackUrl, string $currency = 'XAF'): array
{
    $customerPayload = [
        'name' => $customer['name'] ?? 'Client TCF',
        'email' => $customer['email'] ?? 'client@elite-tcf.local',
    ];
    $phone = trim((string) ($customer['phone'] ?? ''));
    if ($phone !== '') {
        $customerPayload['phone'] = tcf_notchpay_normalize_phone($phone);
    }

    return tcf_notchpay_request('POST', '/payments', [
        'amount' => $amount,
        'currency' => $currency,
        'customer' => $customerPayload,
        'description' => $description,
        'reference' => $reference,
        'callback' => $callbackUrl,
    ]);
}

function tcf_notchpay_process_mobile(string $paymentReference, string $channel, string $phone): array
{
    $normalized = tcf_notchpay_normalize_phone($phone);
    // Notch Pay Mobile Money : uniquement `phone` au format E.164 (+237…)
    return tcf_notchpay_request('POST', '/payments/' . rawurlencode($paymentReference), [
        'channel' => $channel,
        'data' => [
            'phone' => $normalized,
        ],
    ]);
}

/**
 * Charge MoMo avec repli sur cm.mobile (auto MTN/Orange) si le canal précis échoue.
 *
 * @return array{ok:bool,http?:int,data?:?array,error?:string,channel_used?:string}
 */
function tcf_notchpay_charge_mobile(string $paymentReference, string $channel, string $phone): array
{
    $channel = trim($channel) !== '' ? trim($channel) : 'cm.mobile';
    $attempts = [$channel];
    if ($channel === 'cm.mtn' || $channel === 'cm.orange') {
        $attempts[] = 'cm.mobile';
    } elseif ($channel === 'cm.mobile') {
        $detected = tcf_notchpay_detect_channel($phone);
        if ($detected !== 'cm.mobile') {
            $attempts[] = $detected;
        }
    }

    $last = ['ok' => false, 'http' => 0, 'data' => null, 'error' => 'Charge Mobile Money impossible.', 'channel_used' => $channel];
    foreach (array_values(array_unique($attempts)) as $ch) {
        $res = tcf_notchpay_process_mobile($paymentReference, $ch, $phone);
        $res['channel_used'] = $ch;
        if (!empty($res['ok'])) {
            $status = '';
            if (is_array($res['data'] ?? null)) {
                $status = tcf_notchpay_payment_status_from_response($res['data']);
            }
            if (!tcf_notchpay_is_failure_status($status)) {
                return $res;
            }
        }
        $last = $res;
    }

    return $last;
}

function tcf_notchpay_get_payment(string $paymentReference): array
{
    return tcf_notchpay_request('GET', '/payments/' . rawurlencode($paymentReference));
}

/**
 * @param array<string,mixed> $payload
 */
function tcf_notchpay_payment_status_from_response(array $payload): string
{
    $status = '';
    if (isset($payload['transaction']) && is_array($payload['transaction'])) {
        $status = (string) ($payload['transaction']['status'] ?? '');
    }
    if ($status === '' && isset($payload['payment']) && is_array($payload['payment'])) {
        $status = (string) ($payload['payment']['status'] ?? '');
    }
    if ($status === '' && isset($payload['status'])) {
        $status = (string) $payload['status'];
    }
    return strtolower(trim($status));
}

function tcf_notchpay_extract_reference(array $payload): string
{
    if (isset($payload['transaction']['reference'])) {
        return (string) $payload['transaction']['reference'];
    }
    if (isset($payload['payment']['reference'])) {
        return (string) $payload['payment']['reference'];
    }
    if (isset($payload['reference'])) {
        return (string) $payload['reference'];
    }
    return '';
}

function tcf_notchpay_extract_authorization_url(array $payload): string
{
    if (isset($payload['authorization_url']) && is_string($payload['authorization_url'])) {
        return trim($payload['authorization_url']);
    }
    if (isset($payload['transaction']['authorization_url']) && is_string($payload['transaction']['authorization_url'])) {
        return trim($payload['transaction']['authorization_url']);
    }
    return '';
}

/** @return list<string> */
function tcf_notchpay_success_statuses(): array
{
    return ['complete', 'completed', 'success', 'successful'];
}

/** @return list<string> */
function tcf_notchpay_failure_statuses(): array
{
    return ['failed', 'failure', 'cancelled', 'canceled', 'rejected', 'declined', 'expired', 'timeout', 'abandoned'];
}

function tcf_notchpay_is_success_status(string $status): bool
{
    return in_array(strtolower(trim($status)), tcf_notchpay_success_statuses(), true);
}

function tcf_notchpay_is_failure_status(string $status): bool
{
    return in_array(strtolower(trim($status)), tcf_notchpay_failure_statuses(), true);
}

/**
 * Normalise un numéro Mobile Money au format E.164 (+237XXXXXXXXX pour le Cameroun).
 */
function tcf_notchpay_normalize_phone(string $phone): string
{
    $raw = trim($phone);
    if ($raw === '') {
        return '';
    }

    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    if ($digits === '') {
        return '';
    }

    // 00… → indicatif international
    if (str_starts_with($digits, '00')) {
        $digits = substr($digits, 2);
    }

    // Double indicatif Cameroun : 2372376… → 2376…
    while (str_starts_with($digits, '237237')) {
        $digits = substr($digits, 3);
    }

    // 2370XXXXXXXXX (0 national collé après l'indicatif)
    if (preg_match('/^2370(\d{9})$/', $digits, $m)) {
        $digits = '237' . $m[1];
    }

    // Déjà E.164 Cameroun (237 + 9 chiffres)
    if (preg_match('/^2376\d{8}$/', $digits)) {
        return '+' . $digits;
    }

    // Numéro local Cameroun avec 0 : 06XXXXXXXX
    if (preg_match('/^0(6\d{8})$/', $digits, $m)) {
        return '+237' . $m[1];
    }

    // Numéro local Cameroun 9 chiffres (6XXXXXXXX)
    if (preg_match('/^6\d{8}$/', $digits)) {
        return '+237' . $digits;
    }

    $countryCodes = [
        '237', '225', '221', '226', '223', '227', '228', '229',
        '243', '241', '242', '236', '235', '256', '254', '255',
        '250', '233', '234', '260',
    ];
    foreach ($countryCodes as $code) {
        if (str_starts_with($digits, $code) && strlen($digits) >= strlen($code) + 8) {
            return '+' . $digits;
        }
    }

    // Par défaut : Cameroun
    $local = ltrim($digits, '0');
    if ($local !== '' && !str_starts_with($local, '237')) {
        return '+237' . $local;
    }

    return '+' . $digits;
}

/** True si numéro Cameroun Mobile Money valide (+237 + 9 chiffres commençant par 6). */
function tcf_notchpay_is_valid_cm_phone(string $phone): bool
{
    $n = tcf_notchpay_normalize_phone($phone);
    return (bool) preg_match('/^\+2376\d{8}$/', $n);
}

/**
 * Mappe le choix UI (mtn_momo / orange_money / auto) vers un canal Notch Pay.
 */
function tcf_notchpay_provider_to_channel(string $provider): string
{
    $p = strtolower(trim($provider));
    if (in_array($p, ['mtn_momo', 'mtn', 'cm.mtn', 'momo', 'mtm'], true)) {
        return 'cm.mtn';
    }
    if (in_array($p, ['orange_money', 'orange', 'cm.orange'], true)) {
        return 'cm.orange';
    }
    if (in_array($p, ['auto', 'cm.mobile', 'mobile', ''], true)) {
        return '';
    }

    return '';
}

/**
 * Canal final : priorité au fournisseur choisi, sinon détection préfixe, sinon cm.mobile.
 */
function tcf_notchpay_resolve_channel(string $phone, string $provider = ''): string
{
    $fromProvider = tcf_notchpay_provider_to_channel($provider);
    if ($fromProvider !== '') {
        return $fromProvider;
    }

    return tcf_notchpay_detect_channel($phone);
}

function tcf_notchpay_detect_channel(string $phone): string
{
    $normalized = tcf_notchpay_normalize_phone($phone);
    $digits = preg_replace('/\D+/', '', $normalized) ?? '';

    // Cameroun
    if (str_starts_with($digits, '237') || (strlen($digits) === 9 && str_starts_with($digits, '6'))) {
        $local = strlen($digits) === 9 ? $digits : substr($digits, 3);

        // MTN MoMo CM : 67x, 68x, 650-654
        if (preg_match('/^(67|68|650|651|652|653|654)/', $local)) {
            return 'cm.mtn';
        }
        // Orange Money CM : 69x, 655-659
        if (preg_match('/^(69|655|656|657|658|659)/', $local)) {
            return 'cm.orange';
        }

        // Préfixe ambigu / nouveau → auto MTN/Orange côté Notch
        return 'cm.mobile';
    }

    // Côte d'Ivoire (+225)
    if (str_starts_with($digits, '225')) {
        $local = substr($digits, 3);
        if (str_starts_with($local, '05') || str_starts_with($local, '5')) {
            return 'ci.mtn';
        }
        if (str_starts_with($local, '07') || str_starts_with($local, '7')) {
            return 'ci.orange';
        }
        if (str_starts_with($local, '01') || str_starts_with($local, '1')) {
            return 'ci.moov';
        }
        return 'ci.mtn';
    }

    // Sénégal (+221)
    if (str_starts_with($digits, '221')) {
        $local = substr($digits, 3);
        if (str_starts_with($local, '77') || str_starts_with($local, '78')) {
            return 'sn.orange';
        }
        if (str_starts_with($local, '76')) {
            return 'sn.free';
        }
        return 'sn.wave';
    }

    return 'cm.mobile';
}
