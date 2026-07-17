<?php

declare(strict_types=1);

/**
 * Enregistre une visite « jour calendaire » pour le calendrier de présence du profil.
 * Une seule écriture MySQL par session et par jour (évite le spam).
 */
function tcf_maybe_log_daily_activity(PDO $pdo, int $userId): void
{
    if ($userId <= 0) {
        return;
    }
    $today = date('Y-m-d');
    if (!empty($_SESSION['tcf_activity_day_marked']) && $_SESSION['tcf_activity_day_marked'] === $today) {
        return;
    }
    $_SESSION['tcf_activity_day_marked'] = $today;
    try {
        $pdo->prepare('INSERT IGNORE INTO user_activity_days (user_id, activity_date) VALUES (?, CURDATE())')->execute([$userId]);
    } catch (Throwable $e) {
        // Table absente : importer database/tcf.sql
    }
}

/**
 * Grille HTML du calendrier de présence (profil).
 *
 * @param array<string, true> $datesSet dates Y-m-d => true
 */
function tcf_profile_activity_calendar_cells(int $y, int $m, array $datesSet, string $todayStr, ?string $joinDate): string
{
    $first = new DateTime(sprintf('%04d-%02d-01', $y, $m));
    $daysInMonth = (int) $first->format('t');
    $dow = (int) $first->format('N');
    $pad = $dow - 1;
    $html = '';
    for ($i = 0; $i < $pad; $i++) {
        $html .= '<span class="profile-cal__cell profile-cal__cell--pad" aria-hidden="true"></span>';
    }
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $ds = sprintf('%04d-%02d-%02d', $y, $m, $d);
        $classes = ['profile-cal__cell', 'profile-cal__day'];
        if ($ds > $todayStr) {
            $classes[] = 'profile-cal__day--future';
        } elseif ($joinDate !== null && $joinDate !== '' && $ds < $joinDate) {
            $classes[] = 'profile-cal__day--na';
        } elseif (!empty($datesSet[$ds])) {
            $classes[] = 'profile-cal__day--present';
        } else {
            $classes[] = 'profile-cal__day--absent';
        }
        $html .= '<span class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($ds, ENT_QUOTES, 'UTF-8') . '">' . $d . '</span>';
    }

    return $html;
}
