/**
 * Writes Fototeek static HTML fallback + PHP bootstrap for ZoneOS hosting.
 * Run from laravel-backend/: node scripts/generate-fototeek-shell.mjs <out.html> <out.php>
 */
import { readFileSync, writeFileSync } from 'node:fs';

const [, , outHtml, outPhp] = process.argv;
if (!outHtml || !outPhp) {
  console.error('Usage: node scripts/generate-fototeek-shell.mjs <out.html> <out.php>');
  process.exit(1);
}

const m = JSON.parse(readFileSync(new URL('../public/build/manifest.json', import.meta.url), 'utf8'));
const cssEntry = m['resources/css/app.css']?.file;
const jsEntry = m['resources/js/app.js'];
const jsFile = jsEntry?.file;
const cssFromJs = jsEntry?.css?.[0];
if (!cssEntry || !jsFile || !cssFromJs) {
  throw new Error('Unexpected manifest.json shape');
}

const buildMark = `${Date.now()}-${jsFile.replace(/[^a-zA-Z0-9._-]/g, '')}`;
const staticV = String(Date.now());

const commonHead = `<!DOCTYPE html>
<html lang="et" data-fototeek-build="${buildMark}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <title>Fototeek</title>
  <style>html,body{margin:0;min-height:100vh;background:#f5f2ee}</style>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..1,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="/logo.png">
`;

const commonTail = `
<div id="app"></div>
<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.getRegistrations().then(function (rs) {
    rs.forEach(function (r) { r.unregister(); });
  });
}
window.addEventListener('pageshow', function (e) { if (e.persisted) location.reload(); });
</script>
`;

const htmlStatic =
  commonHead +
  `  <link rel="stylesheet" href="/${cssEntry}?v=${staticV}">
  <link rel="stylesheet" href="/${cssFromJs}?v=${staticV}">
</head>
<body>
` +
  commonTail +
  `  <script type="module" src="/${jsFile}?v=${staticV}"></script>
</body>
</html>
`;

const phpFile = `<?php
declare(strict_types=1);
header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Content-Type: text/html; charset=UTF-8');
header('X-Fototeek-Shell: ' . gmdate('c'));
header('Surrogate-Control: no-store');
$__fototeek_v = (string) time();
?>
${commonHead}
  <link rel="stylesheet" href="/${cssEntry}?v=<?php echo $__fototeek_v; ?>">
  <link rel="stylesheet" href="/${cssFromJs}?v=<?php echo $__fototeek_v; ?>">
</head>
<body>
${commonTail}
  <script type="module" src="/${jsFile}?v=<?php echo $__fototeek_v; ?>"></script>
</body>
</html>
`;

writeFileSync(outHtml, htmlStatic);
writeFileSync(outPhp, phpFile);
console.log('Generated shell:', { cssEntry, cssFromJs, jsFile, buildMark });
