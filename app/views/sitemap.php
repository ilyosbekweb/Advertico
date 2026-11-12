<?php
header('Content-Type: application/xml; charset=utf-8');
$pages = ['home' => '', 'about' => 'about.html', 'services' => 'services.html', 'portfolio' => 'portfolio.html', 'locations' => 'locations.html', 'contact' => 'contact.html'];
$rows = [];
try {
    foreach (db()->query('SELECT page_key, noindex, updated_at FROM seo_pages') as $row) {
        $rows[$row['page_key']] = $row;
    }
} catch (Throwable $e) {
    error_log('Advertico sitemap error: ' . $e->getMessage());
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($pages as $key => $path) {
    if (!empty($rows[$key]['noindex'])) {
        continue;
    }
    echo '  <url><loc>' . e(absolute_url($path)) . '</loc>';
    if (!empty($rows[$key]['updated_at'])) {
        echo '<lastmod>' . e(date('Y-m-d', strtotime($rows[$key]['updated_at']))) . '</lastmod>';
    }
    echo '<priority>' . ($key === 'home' ? '1.0' : '0.8') . '</priority></url>' . "\n";
}
echo '</urlset>' . "\n";
