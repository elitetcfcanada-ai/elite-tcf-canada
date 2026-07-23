<?php
declare(strict_types=1);

/**
 * Sitemap XML — ELITE TCF CANADA
 * https://elitetcfcanada.online/sitemap.php
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/tcf_seo.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');

$entries = tcf_seo_sitemap_entries();
$lastmod = date('Y-m-d');

// Vidéos publiques
try {
    $vids = $pdo->query(
        "SELECT id, COALESCE(updated_at, created_at) AS lm
         FROM videos
         WHERE visibility IN ('public', 'premium')
         ORDER BY created_at DESC
         LIMIT 200"
    )->fetchAll(PDO::FETCH_ASSOC);
    foreach ($vids as $v) {
        $entries[] = [
            'loc' => site_url('watch.php?v=' . (int) $v['id']),
            'priority' => '0.7',
            'changefreq' => 'weekly',
            'lastmod' => !empty($v['lm']) ? date('Y-m-d', strtotime((string) $v['lm'])) : $lastmod,
        ];
    }
} catch (Throwable $e) {
    // ignore
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($entries as $e): ?>
  <url>
    <loc><?php echo htmlspecialchars($e['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></loc>
    <lastmod><?php echo htmlspecialchars($e['lastmod'] ?? $lastmod, ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></lastmod>
    <changefreq><?php echo htmlspecialchars($e['changefreq'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></changefreq>
    <priority><?php echo htmlspecialchars($e['priority'], ENT_XML1 | ENT_QUOTES, 'UTF-8'); ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
