<?php
header('Content-Type: text/plain; charset=utf-8');
$base = url();
echo "User-agent: *\n";
echo 'Disallow: ' . $base . "admin/\n";
echo 'Disallow: ' . $base . "api/\n";
echo "\nSitemap: " . absolute_url('sitemap.xml') . "\n";
