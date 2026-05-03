#!/usr/bin/env bash
# Deploy Laravel+Vite build to Fototeek static hosting (ZoneOS / subdomain docroot).
#
# Uses PHP for index.php so Cache-Control is applied per-request (static index.html is often cached forever).
#
# Prerequisites: npm run build (from this directory), SSH key access to host.
#
# Usage:
#   ./deploy-fototeek-static.sh virt137753@ta24aksalu.itmajakas.ee
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

HOST="${DEPLOY_HOST:-${1:-}}"
KEY="${DEPLOY_SSH_KEY:-$HOME/.ssh/id_ed25519}"
REMOTE_BASE="${DEPLOY_REMOTE_BASE:-~/domeenid/www.ta24aksalu.itmajakas.ee/fototeek}"

if [[ -z "$HOST" ]]; then
  echo "Usage: DEPLOY_HOST=user@host $0   OR   $0 user@host" >&2
  echo "Easiest: cd laravel-backend && bash ./deploy-fototeek.sh" >&2
  exit 1
fi

if [[ ! -f public/build/manifest.json ]]; then
  echo "Missing public/build/manifest.json — run: npm run build" >&2
  exit 1
fi

TMP_HTML="$(mktemp)"
TMP_PHP="$(mktemp)"
trap 'rm -f "$TMP_HTML" "$TMP_PHP"' EXIT

node --input-type=module -e "
import { readFileSync, writeFileSync } from 'node:fs';
const m = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));
const cssEntry = m['resources/css/app.css']?.file;
const jsEntry = m['resources/js/app.js'];
const jsFile = jsEntry?.file;
const cssFromJs = jsEntry?.css?.[0];
if (!cssEntry || !jsFile || !cssFromJs) {
  throw new Error('Unexpected manifest shape — check resources/css/app.css and resources/js/app.js entries');
}
const buildMark = String(Date.now()) + '-' + jsFile.replace(/[^a-zA-Z0-9._-]/g, '');
const htmlInner = \`<!DOCTYPE html>
<html lang=\"et\" data-fototeek-build=\"\${buildMark}\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <meta http-equiv=\"Cache-Control\" content=\"no-cache, no-store, must-revalidate\" />
  <meta http-equiv=\"Pragma\" content=\"no-cache\" />
  <meta http-equiv=\"Expires\" content=\"0\" />
  <title>Fototeek</title>
  <style>html,body{margin:0;min-height:100vh;background:#f5f2ee}</style>
  <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
  <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
  <link href=\"https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..1,800&family=Inter:wght@400;500;600;700&display=swap\" rel=\"stylesheet\">
  <link rel=\"icon\" type=\"image/png\" href=\"/logo.png\">
  <link rel=\"stylesheet\" href=\"/\${cssEntry}\">
  <link rel=\"stylesheet\" href=\"/\${cssFromJs}\">
</head>
<body>
  <div id=\"app\"></div>
  <script>window.addEventListener('pageshow',function(e){if(e.persisted)location.reload();});</script>
  <script type=\"module\" src=\"/\${jsFile}\"></script>
</body>
</html>\`;

const phpOut = \`<?php
declare(strict_types=1);
header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Content-Type: text/html; charset=UTF-8');
header('X-Fototeek-Shell: ' . gmdate('c'));
echo <<<'FOTOTEEK_SHELL_EOF'
\${htmlInner}
FOTOTEEK_SHELL_EOF
;
\`;

writeFileSync(process.argv[1], htmlInner);
writeFileSync(process.argv[2], phpOut);
console.log('Generated shell:', { cssEntry, cssFromJs, jsFile });
" "$TMP_HTML" "$TMP_PHP"

SCP_OPTS=(-i "$KEY" -o StrictHostKeyChecking=accept-new)

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/assets/"

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/current/public/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/current/public/assets/"

echo "Uploading index.php (PHP shell — bypasses static HTML cache) ..."
scp "${SCP_OPTS[@]}" "$TMP_PHP" "${HOST}:${REMOTE_BASE}/current/public/index.php"
scp "${SCP_OPTS[@]}" "$TMP_PHP" "${HOST}:${REMOTE_BASE}/index.php"

echo "Uploading index.html fallback (same markup, static backup) ..."
scp "${SCP_OPTS[@]}" "$TMP_HTML" "${HOST}:${REMOTE_BASE}/current/public/index.html"
scp "${SCP_OPTS[@]}" "$TMP_HTML" "${HOST}:${REMOTE_BASE}/index.html"

echo "Done. Homepage uses PHP index.php — browsers/CDNs stop pinning stale HTML shells."
