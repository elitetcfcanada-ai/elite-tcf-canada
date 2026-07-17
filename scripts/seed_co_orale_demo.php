<?php
/**
 * Crée une épreuve de démo « Compréhension orale 1 » avec les 5 jeux
 * d’images/audios présents dans uploads/co_media/demo1/…
 * Exécuter une fois : php scripts/seed_co_orale_demo.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';

$pdo->exec(
    "CREATE TABLE IF NOT EXISTS tcf_co_exams (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slug VARCHAR(140) NOT NULL UNIQUE,
        title VARCHAR(255) NOT NULL,
        subtitle VARCHAR(255) DEFAULT NULL,
        intro_html TEXT DEFAULT NULL,
        visibility VARCHAR(20) NOT NULL DEFAULT 'gratuit',
        is_published TINYINT(1) NOT NULL DEFAULT 1,
        duration_seconds INT UNSIGNED NOT NULL DEFAULT 1800,
        published_at DATETIME DEFAULT NULL,
        created_by INT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_co_exam_pub (is_published, published_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS tcf_co_questions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        exam_id INT UNSIGNED NOT NULL,
        sort_order INT NOT NULL DEFAULT 0,
        question_text TEXT NOT NULL,
        points INT NOT NULL DEFAULT 1,
        image_src TEXT DEFAULT NULL,
        audio_src TEXT DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_co_q_exam (exam_id),
        CONSTRAINT fk_co_q_exam FOREIGN KEY (exam_id) REFERENCES tcf_co_exams(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
$pdo->exec(
    "CREATE TABLE IF NOT EXISTS tcf_co_answers (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        question_id INT UNSIGNED NOT NULL,
        answer_key VARCHAR(8) NOT NULL,
        answer_text TEXT NOT NULL,
        is_correct TINYINT(1) NOT NULL DEFAULT 0,
        sort_order INT NOT NULL DEFAULT 0,
        KEY idx_co_a_q (question_id),
        CONSTRAINT fk_co_a_q FOREIGN KEY (question_id) REFERENCES tcf_co_questions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

$title = 'Compréhension Orale 1 (démo)';

$st = $pdo->prepare('SELECT id FROM tcf_co_exams WHERE title = ? LIMIT 1');
$st->execute([$title]);
if ($st->fetchColumn()) {
    echo "Déjà présent : $title\n";
    exit(0);
}

$base = 'uploads/co_media/demo1';
$items = [
    [
        'q' => "Que doit faire la personne selon l'annonce ?",
        'img' => $base . '/image/1.jpg',
        'aud' => $base . '/audio/1.mp3',
        'a' => ["Se présenter à l'enregistrement", 'Aller à la douane', 'Prendre un taxi', 'Changer de terminal'],
        'ok' => 0,
    ],
    [
        'q' => 'Quel conseil est donné dans ce message ?',
        'img' => $base . '/image/2.jpg',
        'aud' => $base . '/audio/2.mp3',
        'a' => ['Faire plus d’exercice', 'Manger équilibré', 'Dormir 8 heures', 'Éviter le stress'],
        'ok' => 1,
    ],
    [
        'q' => "Quelle information entend-on dans cette publicité ?",
        'img' => $base . '/image/3.jpg',
        'aud' => $base . '/audio/3.mp3',
        'a' => ['Les horaires d’ouverture', 'Le prix des produits', 'La réduction spéciale', "L'adresse du magasin"],
        'ok' => 2,
    ],
    [
        'q' => 'Quelle est la nature de ce message ?',
        'img' => $base . '/image/4.jpg',
        'aud' => $base . '/audio/4.mp3',
        'a' => ['Une invitation', 'Une réclamation', 'Une publicité', 'Un rappel'],
        'ok' => 0,
    ],
    [
        'q' => 'Que doit-on faire en cas de problème ?',
        'img' => $base . '/image/5.jpg',
        'aud' => $base . '/audio/5.mp3',
        'a' => ['Appeler le service client', 'Envoyer un email', 'Se rendre en agence', 'Consulter le site web'],
        'ok' => 0,
    ],
];

$slug = 'comprehension-orale-1-demo-' . substr(uniqid('', true), -6);
$pdo->prepare(
    'INSERT INTO tcf_co_exams (slug,title,subtitle,intro_html,visibility,is_published,duration_seconds,published_at,created_by) VALUES (?,?,?,?,?,?,?,NOW(),NULL)'
)->execute([
    $slug,
    $title,
    'Démo importée — remplacez par vos contenus',
    null,
    'gratuit',
    1,
    1800,
]);
$examId = (int) $pdo->lastInsertId();

$insQ = $pdo->prepare(
    'INSERT INTO tcf_co_questions (exam_id,sort_order,question_text,points,image_src,audio_src) VALUES (?,?,?,?,?,?)'
);
$insA = $pdo->prepare(
    'INSERT INTO tcf_co_answers (question_id,answer_key,answer_text,is_correct,sort_order) VALUES (?,?,?,?,?)'
);

$ord = 0;
foreach ($items as $it) {
    $ord++;
    $insQ->execute([$examId, $ord, $it['q'], 1, $it['img'], $it['aud']]);
    $qid = (int) $pdo->lastInsertId();
    foreach ($it['a'] as $i => $txt) {
        $key = ['a', 'b', 'c', 'd'][$i % 4];
        $ok = $i === (int) $it['ok'] ? 1 : 0;
        $insA->execute([$qid, $key, $txt, $ok, $i]);
    }
}

echo "OK — exam_id=$examId\n";
