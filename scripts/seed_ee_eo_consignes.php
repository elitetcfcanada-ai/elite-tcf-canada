<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/tcf_consignes_defaults.php';

function seed_ee(PDO $pdo): void
{
    $bodies = tcf_consigne_ee_bodies();
    $titles = [
        'tache1' => 'Tâche 1 — Message court',
        'tache2' => 'Tâche 2 — Narration / article',
        'tache3' => 'Tâche 3 — Argumentation',
    ];
    foreach (['tache1', 'tache2', 'tache3'] as $i => $key) {
        $sort = $i + 1;
        $st = $pdo->prepare('SELECT id, body FROM tcf_ee_consignes WHERE task_key=? ORDER BY id ASC LIMIT 1');
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $pdo->prepare('INSERT INTO tcf_ee_consignes (title, body, task_key, visibility, is_published, sort_order, is_active) VALUES (?,?,?,?,1,?,1)')
                ->execute([$titles[$key], $bodies[$key], $key, 'gratuit', $sort]);
            echo "EE insert $key\n";
            continue;
        }
        if (tcf_consigne_body_needs_refresh((string) $row['body'], 'ee')) {
            $pdo->prepare('UPDATE tcf_ee_consignes SET title=?, body=?, visibility=?, is_published=1, sort_order=?, is_active=1 WHERE id=?')
                ->execute([$titles[$key], $bodies[$key], 'gratuit', $sort, (int) $row['id']]);
            echo "EE refresh $key\n";
        } else {
            echo "EE ok $key\n";
        }
    }
}

function seed_eo(PDO $pdo): void
{
    $bodies = tcf_consigne_eo_bodies();
    $titles = [
        'tache1' => 'Tâche 1 : Présentation (entretien dirigé)',
        'tache2' => 'Tâche 2 : Exercice en interaction',
        'tache3' => 'Tâche 3 : Expression d’un point de vue',
    ];
    foreach (['tache1', 'tache2', 'tache3'] as $i => $key) {
        $sort = $i + 1;
        $st = $pdo->prepare('SELECT id, body FROM tcf_eo_consignes WHERE task_key=? ORDER BY id ASC LIMIT 1');
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $pdo->prepare("INSERT INTO tcf_eo_consignes (title, body, task_key, visibility, is_published, sort_order, is_active) VALUES (?,?,?,'gratuit',1,?,1)")
                ->execute([$titles[$key], $bodies[$key], $key, $sort]);
            echo "EO insert $key\n";
            continue;
        }
        if (tcf_consigne_body_needs_refresh((string) $row['body'], 'eo')) {
            $pdo->prepare('UPDATE tcf_eo_consignes SET title=?, body=?, visibility=?, is_published=1, sort_order=?, is_active=1 WHERE id=?')
                ->execute([$titles[$key], $bodies[$key], 'gratuit', $sort, (int) $row['id']]);
            echo "EO refresh $key\n";
        } else {
            echo "EO ok $key\n";
        }
    }
}

seed_ee($pdo);
seed_eo($pdo);
seed_ce($pdo);
seed_co($pdo);

function seed_ce(PDO $pdo): void
{
    $bodies = tcf_consigne_ce_bodies();
    $titles = [
        'structure' => 'Structure de l’épreuve et stratégie de scoring',
        'techniques' => 'Les 5 techniques essentielles',
        'erreurs' => 'Erreurs courantes à éviter',
    ];
    // Ensure columns
    try {
        $cols = $pdo->query('SHOW COLUMNS FROM tcf_ce_consignes')->fetchAll(PDO::FETCH_ASSOC);
        $names = array_map(static fn ($c) => (string) ($c['Field'] ?? ''), $cols);
        if (!in_array('section_key', $names, true)) {
            $pdo->exec("ALTER TABLE tcf_ce_consignes ADD COLUMN section_key VARCHAR(40) NOT NULL DEFAULT 'structure' AFTER body");
        }
        if (!in_array('sort_order', $names, true)) {
            $pdo->exec('ALTER TABLE tcf_ce_consignes ADD COLUMN sort_order INT NOT NULL DEFAULT 1 AFTER is_published');
        }
    } catch (Throwable $e) {
        echo 'CE migrate: ' . $e->getMessage() . "\n";
    }

    $needsFull = false;
    foreach (['structure', 'techniques', 'erreurs'] as $key) {
        $st = $pdo->prepare('SELECT id, body FROM tcf_ce_consignes WHERE section_key=? ORDER BY id ASC LIMIT 1');
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row || tcf_consigne_body_needs_refresh((string) ($row['body'] ?? ''), 'ce')) {
            $needsFull = true;
            break;
        }
    }
    if ($needsFull) {
        $pdo->exec("DELETE FROM tcf_ce_consignes WHERE section_key IN ('structure','techniques','erreurs') OR section_key='' OR section_key='general'");
        $ins = $pdo->prepare("INSERT INTO tcf_ce_consignes (title, body, section_key, visibility, is_published, sort_order) VALUES (?,?,?,'gratuit',1,?)");
        foreach (['structure', 'techniques', 'erreurs'] as $i => $key) {
            $ins->execute([$titles[$key], $bodies[$key], $key, $i + 1]);
            echo "CE refresh $key\n";
        }
    } else {
        foreach (['structure', 'techniques', 'erreurs'] as $key) {
            echo "CE ok $key\n";
        }
    }
}

function seed_co(PDO $pdo): void
{
    $bodies = tcf_consigne_co_bodies();
    $titles = [
        'structure' => 'Structure de l’épreuve et stratégie de scoring',
        'techniques' => 'Les 5 techniques essentielles',
        'erreurs' => 'Erreurs courantes à éviter',
    ];
    try {
        $cols = $pdo->query('SHOW COLUMNS FROM tcf_co_consignes')->fetchAll(PDO::FETCH_ASSOC);
        $names = array_map(static fn ($c) => (string) ($c['Field'] ?? ''), $cols);
        if (!in_array('section_key', $names, true)) {
            $pdo->exec("ALTER TABLE tcf_co_consignes ADD COLUMN section_key VARCHAR(40) NOT NULL DEFAULT 'structure' AFTER body");
        }
        if (!in_array('sort_order', $names, true)) {
            $pdo->exec('ALTER TABLE tcf_co_consignes ADD COLUMN sort_order INT NOT NULL DEFAULT 1 AFTER is_published');
        }
    } catch (Throwable $e) {
        echo 'CO migrate: ' . $e->getMessage() . "\n";
    }

    $needsFull = false;
    foreach (['structure', 'techniques', 'erreurs'] as $key) {
        $st = $pdo->prepare('SELECT id, body FROM tcf_co_consignes WHERE section_key=? ORDER BY id ASC LIMIT 1');
        $st->execute([$key]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if (!$row || tcf_consigne_body_needs_refresh((string) ($row['body'] ?? ''), 'co')) {
            $needsFull = true;
            break;
        }
    }
    if ($needsFull) {
        $pdo->exec("DELETE FROM tcf_co_consignes WHERE section_key IN ('structure','techniques','erreurs') OR section_key='' OR section_key='general'");
        $ins = $pdo->prepare("INSERT INTO tcf_co_consignes (title, body, section_key, visibility, is_published, sort_order) VALUES (?,?,?,'gratuit',1,?)");
        foreach (['structure', 'techniques', 'erreurs'] as $i => $key) {
            $ins->execute([$titles[$key], $bodies[$key], $key, $i + 1]);
            echo "CO refresh $key\n";
        }
    } else {
        foreach (['structure', 'techniques', 'erreurs'] as $key) {
            echo "CO ok $key\n";
        }
    }
}

// Nettoyer d’anciennes lignes hors tâches 1/2/3
try {
    $pdo->exec("DELETE FROM tcf_ee_consignes WHERE task_key NOT IN ('tache1','tache2','tache3')");
    $pdo->exec("DELETE FROM tcf_eo_consignes WHERE task_key NOT IN ('tache1','tache2','tache3')");
    $pdo->exec("DELETE FROM tcf_ce_consignes WHERE section_key NOT IN ('structure','techniques','erreurs')");
    $pdo->exec("DELETE FROM tcf_co_consignes WHERE section_key NOT IN ('structure','techniques','erreurs')");
    echo "Cleanup non-task rows.\n";
} catch (Throwable $e) {
    echo 'Cleanup skipped: ' . $e->getMessage() . "\n";
}

echo "Done.\n";
