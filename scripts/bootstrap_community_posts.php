<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/community_posts_helper.php';

tcf_community_posts_ensure_tables($pdo);
tcf_community_drop_channel_tables($pdo);

$ok = (bool) $pdo->query("SHOW TABLES LIKE 'community_posts'")->fetchColumn();
echo $ok ? "community_posts OK\n" : "community_posts MISSING\n";
echo "Channel tables dropped (if present).\n";
