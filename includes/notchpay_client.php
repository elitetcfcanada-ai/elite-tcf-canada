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
    return tcf_notchpay_request('POST', '/payments/' . rawurlencode($paymentReference), [
        'channel' => $channel,
        'data' => [
            'phone' => $normalized,
            'account_number' => $normalized,
        ],
    ]);
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

function tcf_notchpay_normalize_phone(string $phone): string
{
    $p = preg_replace('/[^\d+]/', '', trim($phone)) ?? '';
    if ($p === '') {
        return '';
    }
    
    // Codes de pays internationaux
    $countryCodes = [
        '237' => 'CM', // Cameroun
        '225' => 'CI', // Côte d'Ivoire
        '221' => 'SN', // Sénégal
        '226' => 'BF', // Burkina Faso
        '223' => 'ML', // Mali
        '227' => 'NE', // Niger
        '228' => 'TG', // Togo
        '229' => 'BJ', // Bénin
        '243' => 'CD', // RD Congo
        '241' => 'GA', // Gabon
        '242' => 'CG', // Congo
        '236' => 'CF', // Centrafrique
        '235' => 'TD', // Tchad
        '256' => 'UG', // Ouganda
        '254' => 'KE', // Kenya
        '255' => 'TZ', // Tanzanie
        '250' => 'RW', // Rwanda
        '233' => 'GH', // Ghana
        '234' => 'NG', // Nigeria
        '260' => 'ZM', // Zambie
    ];
    
    // Si le numéro commence déjà par +
    if ($p[0] === '+') {
        return $p;
    }
    
    // Si le numéro commence par 0, essayer de détecter le pays
    if (str_starts_with($p, '0')) {
        // Par défaut, on ne peut pas deviner le pays sans contexte
        // On retourne le numéro tel quel pour que l'utilisateur le corrige
        return $p;
    }
    
    // Vérifier si le numéro commence par un code de pays
    foreach ($countryCodes as $code => $country) {
        if (str_starts_with($p, (string) $code)) {
            return '+' . $p;
        }
    }
    
    // Si aucun code de pays n'est détecté, par défaut Cameroun
    return '+237' . $p;
}

function tcf_notchpay_detect_channel(string $phone): string
{
    $normalized = tcf_notchpay_normalize_phone($phone);
    $digits = preg_replace('/[^\d]/', '', $normalized) ?? '';
    
    // Cameroun (+237 ou numéro local de 9 chiffres)
    if (str_starts_with($digits, '237') || strlen($digits) === 9) {
        $local = strlen($digits) === 9 ? $digits : substr($digits, 3);
        
        // MTN préfixes : 67x, 68x, 650, 651, 652, 653, 654
        if (preg_match('/^(67|68|650|651|652|653|654)/', $local)) {
            return 'cm.mtn';
        }
        // Orange préfixes : 69x, 655, 656, 657, 658, 659
        if (preg_match('/^(69|655|656|657|658|659)/', $local)) {
            return 'cm.orange';
        }
        return 'cm.mtn';
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
    
    return 'cm.mtn';
}
