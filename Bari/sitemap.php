<?php
header('Content-Type: application/xml; charset=UTF-8');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = $scheme . '://' . $host . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc><?= htmlspecialchars($base, ENT_XML1) ?>store.php</loc><changefreq>daily</changefreq><priority>1.0</priority></url>
  <url><loc><?= htmlspecialchars($base, ENT_XML1) ?>login.php</loc><changefreq>monthly</changefreq><priority>0.3</priority></url>
  <url><loc><?= htmlspecialchars($base, ENT_XML1) ?>register.php</loc><changefreq>monthly</changefreq><priority>0.4</priority></url>
</urlset>
