<?php
/**
 * Réparation complète de la base (IDs, AUTO_INCREMENT, doublons email, vidéos).
 *
 * Usage (local ou Hostinger, une seule fois) :
 *   https://votredomaine.com/scripts/repair_database.php?key=REPAIR_TCF_2026
 *
 * Puis SUPPRIMER ce fichier (ou le dossier scripts) après exécution.
 */
declare(strict_types=1);

$key = (string) ($_GET['key'] ?? '');
if (PHP_SAPI === 'cli') {
    $key = (string) ($argv[1] ?? getenv('TCF_REPAIR_KEY') ?: '');
}
if ($key !== 'REPAIR_TCF_2026') {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Usage: php repair_database.php REPAIR_TCF_2026\n");
        exit(1);
    }
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Accès refusé. Ajoutez ?key=REPAIR_TCF_2026 à l'URL.\n";
    exit;
}

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>Réparation BDD</title>';
echo '<style>body{font-family:system-ui,sans-serif;max-width:900px;margin:2rem auto;padding:0 1rem;line-height:1.5}';
echo '.ok{color:#0a7} .err{color:#c00} .warn{color:#a60} code{background:#f4f4f4;padding:0 .25rem}</style></head><body>';
echo '<h1>Réparation base de données ELITE TCF CANADA</h1>';

$log = static function (string $msg, string $cls = ''): void {
    $c = $cls !== '' ? ' class="' . htmlspecialchars($cls) . '"' : '';
    echo '<p' . $c . '>' . $msg . '</p>';
    @ob_flush();
    @flush();
};

try {
    $pdo->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'NO_AUTO_VALUE_ON_ZERO', '')");
} catch (Throwable $e) {
    // ignore
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$fixedAi = 0;
$deletedZero = 0;

/* ------------------------------------------------------------------ */
/* 1) Nettoyage id = 0 + restauration AUTO_INCREMENT sur toutes tables */
/* ------------------------------------------------------------------ */
$log('<h2>1. AUTO_INCREMENT &amp; id=0</h2>');

foreach ($tables as $table) {
    $table = (string) $table;
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $log("Table <code>{$table}</code> : lecture impossible — " . htmlspecialchars($e->getMessage()), 'err');
        continue;
    }

    $idCol = null;
    foreach ($cols as $c) {
        if (strcasecmp((string) $c['Field'], 'id') === 0) {
            $idCol = $c;
            break;
        }
    }
    if ($idCol === null) {
        continue;
    }

    try {
        $n = $pdo->exec("DELETE FROM `{$table}` WHERE `id` = 0");
        if ($n > 0) {
            $deletedZero += $n;
            $log("Supprimé {$n} ligne(s) avec <code>id=0</code> dans <code>{$table}</code>", 'warn');
        }
    } catch (Throwable $e) {
        // ignore
    }

    $extra = (string) ($idCol['Extra'] ?? '');
    $key = (string) ($idCol['Key'] ?? '');
    $type = (string) $idCol['Type'];
    $null = (($idCol['Null'] ?? '') === 'NO') ? 'NOT NULL' : 'NULL';

    if (stripos($extra, 'auto_increment') !== false) {
        continue;
    }

    if ($key !== 'PRI') {
        try {
            $pdo->exec("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
            $log("PRIMARY KEY ajoutée sur <code>{$table}.id</code>", 'ok');
        } catch (Throwable $e) {
            // peut déjà exister autrement
        }
    }

    try {
        $pdo->exec("ALTER TABLE `{$table}` MODIFY `id` {$type} {$null} AUTO_INCREMENT");
        $fixedAi++;
        $log("AUTO_INCREMENT ajouté sur <code>{$table}.id</code>", 'ok');
    } catch (Throwable $e) {
        $log("Échec AUTO_INCREMENT <code>{$table}</code> : " . htmlspecialchars($e->getMessage()), 'err');
    }

    try {
        $max = (int) $pdo->query("SELECT COALESCE(MAX(id), 0) FROM `{$table}`")->fetchColumn();
        $next = max(1, $max + 1);
        $pdo->exec("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
    } catch (Throwable $e) {
        // ignore
    }
}

$log("Tables corrigées (AUTO_INCREMENT) : <strong>{$fixedAi}</strong> — lignes id=0 supprimées : <strong>{$deletedZero}</strong>");

/* ------------------------------------------------------------------ */
/* 2) Doublons email + contrainte UNIQUE                                 */
/* ------------------------------------------------------------------ */
$log('<h2>2. Utilisateurs — doublons email &amp; UNIQUE</h2>');

$userCols = [];
try {
    $userCols = $pdo->query('SHOW COLUMNS FROM `users`')->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $log('Table users introuvable.', 'err');
}

if (in_array('email', $userCols, true)) {
    // Trouver emails en double
    $dupEmails = $pdo->query(
        "SELECT LOWER(TRIM(email)) AS e, COUNT(*) AS c
         FROM users
         WHERE email IS NOT NULL AND TRIM(email) <> ''
         GROUP BY LOWER(TRIM(email))
         HAVING c > 1"
    )->fetchAll(PDO::FETCH_ASSOC);

    $fkTables = [];
    foreach ($tables as $t) {
        $t = (string) $t;
        if ($t === 'users') {
            continue;
        }
        try {
            $has = $pdo->query("SHOW COLUMNS FROM `{$t}` LIKE 'user_id'")->fetch();
            if ($has) {
                $fkTables[] = $t;
            }
        } catch (Throwable $e) {
            // ignore
        }
    }
    // Colonnes user-like alternatives
    $altUserCols = [
        'author_user_id' => null,
        'sender_id' => null,
        'created_by' => null,
        'admin_id' => null,
        'joined_by' => null,
    ];

    foreach ($dupEmails as $row) {
        $email = (string) $row['e'];
        $users = $pdo->prepare(
            "SELECT id, email, created_at, last_login, role, status
             FROM users
             WHERE LOWER(TRIM(email)) = ?
             ORDER BY
               CASE WHEN role IN ('super_admin','admin') THEN 0 ELSE 1 END,
               (last_login IS NULL), last_login DESC,
               id ASC"
        );
        $users->execute([$email]);
        $rows = $users->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) < 2) {
            continue;
        }
        $keep = (int) $rows[0]['id'];
        $dups = array_slice($rows, 1);
        foreach ($dups as $dup) {
            $dupId = (int) $dup['id'];
            if ($dupId === $keep || $dupId <= 0) {
                continue;
            }
            foreach ($fkTables as $ft) {
                try {
                    $pdo->prepare("UPDATE `{$ft}` SET user_id = ? WHERE user_id = ?")->execute([$keep, $dupId]);
                } catch (Throwable $e) {
                    // ignore collisions UNIQUE
                }
            }
            foreach (array_keys($altUserCols) as $col) {
                foreach ($tables as $ft) {
                    $ft = (string) $ft;
                    try {
                        $has = $pdo->query("SHOW COLUMNS FROM `{$ft}` LIKE " . $pdo->quote($col))->fetch();
                        if (!$has) {
                            continue;
                        }
                        $pdo->prepare("UPDATE `{$ft}` SET `{$col}` = ? WHERE `{$col}` = ?")->execute([$keep, $dupId]);
                    } catch (Throwable $e) {
                        // ignore
                    }
                }
            }
            try {
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$dupId]);
                $log("Doublon email <code>" . htmlspecialchars($email) . "</code> : gardé id={$keep}, supprimé id={$dupId}", 'warn');
            } catch (Throwable $e) {
                $log("Impossible de supprimer user id={$dupId} : " . htmlspecialchars($e->getMessage()), 'err');
            }
        }
    }

    // UNIQUE email
    try {
        $idx = $pdo->query("SHOW INDEX FROM users WHERE Column_name = 'email' AND Non_unique = 0")->fetch();
        if (!$idx) {
            $pdo->exec('ALTER TABLE users ADD UNIQUE KEY uq_users_email (email)');
            $log('Contrainte UNIQUE ajoutée sur <code>users.email</code>', 'ok');
        } else {
            $log('UNIQUE sur email déjà présent.', 'ok');
        }
    } catch (Throwable $e) {
        $log('UNIQUE email : ' . htmlspecialchars($e->getMessage()), 'err');
    }

    // Vérifier AUTO_INCREMENT users
    try {
        $uCol = $pdo->query("SHOW COLUMNS FROM users LIKE 'id'")->fetch(PDO::FETCH_ASSOC);
        if ($uCol && stripos((string) $uCol['Extra'], 'auto_increment') === false) {
            $pdo->exec('ALTER TABLE users MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
            $log('AUTO_INCREMENT forcé sur <code>users.id</code>', 'ok');
        }
        $maxU = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM users')->fetchColumn();
        $pdo->exec('ALTER TABLE users AUTO_INCREMENT = ' . max(1, $maxU + 1));
    } catch (Throwable $e) {
        $log('users AUTO_INCREMENT : ' . htmlspecialchars($e->getMessage()), 'err');
    }
}

/* ------------------------------------------------------------------ */
/* 3) Vidéos — id valides + visibilité                                 */
/* ------------------------------------------------------------------ */
$log('<h2>3. Vidéos</h2>');

try {
    $vCol = $pdo->query("SHOW COLUMNS FROM videos LIKE 'id'")->fetch(PDO::FETCH_ASSOC);
    if ($vCol && stripos((string) $vCol['Extra'], 'auto_increment') === false) {
        $pdo->exec('ALTER TABLE videos MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
        $log('AUTO_INCREMENT forcé sur <code>videos.id</code>', 'ok');
    }
    $maxV = (int) $pdo->query('SELECT COALESCE(MAX(id), 0) FROM videos')->fetchColumn();
    $pdo->exec('ALTER TABLE videos AUTO_INCREMENT = ' . max(1, $maxV + 1));

    // Visibilités invalides / vides → public
    $fixedVis = $pdo->exec(
        "UPDATE videos
         SET visibility = 'public'
         WHERE visibility IS NULL
            OR TRIM(visibility) = ''
            OR visibility NOT IN ('public','private','premium')"
    );
    if ($fixedVis > 0) {
        $log("Visibilité corrigée sur {$fixedVis} vidéo(s) → public", 'ok');
    }

    $counts = $pdo->query(
        "SELECT visibility, COUNT(*) AS c FROM videos GROUP BY visibility"
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($counts as $c) {
        $log('Vidéos <code>' . htmlspecialchars((string) $c['visibility']) . '</code> : ' . (int) $c['c']);
    }

    $publicCount = (int) $pdo->query(
        "SELECT COUNT(*) FROM videos WHERE visibility IN ('public','premium')"
    )->fetchColumn();
    $log("Vidéos visibles côté utilisateurs (public+premium) : <strong>{$publicCount}</strong>", 'ok');
} catch (Throwable $e) {
    $log('Vidéos : ' . htmlspecialchars($e->getMessage()), 'err');
}

/* ------------------------------------------------------------------ */
/* 4) Test insertion utilisateur (rollback)                            */
/* ------------------------------------------------------------------ */
$log('<h2>4. Test AUTO_INCREMENT users</h2>');
try {
    $pdo->beginTransaction();
    $testEmail = 'repair_test_' . time() . '@example.invalid';
    $pdo->prepare(
        "INSERT INTO users (name, email, password, role, subscription_type, status, created_at)
         VALUES ('Repair Test', ?, ?, 'user', 'free', 'active', NOW())"
    )->execute([$testEmail, password_hash('TmpTest1!', PASSWORD_DEFAULT)]);
    $newId = (int) $pdo->lastInsertId();
    $pdo->rollBack();
    if ($newId > 0) {
        $log("Test OK — prochain id utilisateur serait <strong>{$newId}</strong>", 'ok');
    } else {
        $log('ÉCHEC : lastInsertId = 0. AUTO_INCREMENT users toujours cassé.', 'err');
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $log('Test insert : ' . htmlspecialchars($e->getMessage()), 'err');
}

$log('<h2>Terminé</h2>');
$log('<p class="warn"><strong>Important :</strong> supprimez <code>scripts/repair_database.php</code> après cette opération.</p>');
$log('<p>Ensuite : déconnectez-vous / reconnectez-vous, puis testez inscription + page Vidéos.</p>');
echo '</body></html>';
