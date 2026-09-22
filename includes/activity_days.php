<?php

declare(strict_types=1);

/**
 * Assure la table du calendrier de présence.
 */
function tcf_activity_days_ensure_table(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS user_activity_days (
                user_id INT UNSIGNED NOT NULL,
                activity_date DATE NOT NULL,
                PRIMARY KEY (user_id, activity_date),
                KEY idx_activity_date (activity_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    } catch (Throwable $e) {
        error_log('tcf_activity_days_ensure_table: ' . $e->getMessage());
    }
    $done = true;
}

/**
 * Enregistre une visite « jour calendaire » pour le calendrier de présence du profil.
 * Une seule écriture réussie par session et par jour.
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
    tcf_activity_days_ensure_table($pdo);
    try {
        $st = $pdo->prepare('INSERT IGNORE INTO user_activity_days (user_id, activity_date) VALUES (?, ?)');
        $st->execute([$userId, $today]);
        $_SESSION['tcf_activity_day_marked'] = $today;
    } catch (Throwable $e) {
        // Ne pas marquer la session si l’écriture a échoué (permet un nouvel essai)
        error_log('tcf_maybe_log_daily_activity: ' . $e->getMessage());
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
        } elseif (!empty($datesSet[$ds]) || $ds === $todayStr) {
            // Jour courant toujours « présent » dès qu’on consulte le profil
            $classes[] = 'profile-cal__day--present';
        } else {
            $classes[] = 'profile-cal__day--absent';
        }
        $isToday = ($ds === $todayStr);
        if ($isToday) {
            $classes[] = 'profile-cal__day--today';
        }
        $html .= '<span class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($ds, ENT_QUOTES, 'UTF-8') . '">' . $d . '</span>';
    }

    return $html;
}
