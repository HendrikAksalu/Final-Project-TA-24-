#!/usr/bin/env bash
# Deploy Laravel+Vite build to Fototeek static hosting (ZoneOS / subdomain docroot).
#
# Prerequisites: npm run build (from this directory), SSH key access to host.
#
# Usage:
#   ./deploy-fototeek-static.sh virt137753@ta24aksalu.itmajakas.ee
# Or:
#   DEPLOY_HOST=virt137753@ta24aksalu.itmajakas.ee ./deploy-fototeek-static.sh
#
# Optional:
#   DEPLOY_SSH_KEY=~/.ssh/id_ed25519
#   DEPLOY_REMOTE_BASE='~/domeenid/www.ta24aksalu.itmajakas.ee/fototeek'
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

HOST="${DEPLOY_HOST:-${1:-}}"
KEY="${DEPLOY_SSH_KEY:-$HOME/.ssh/id_ed25519}"
# Important: use ~/... in the remote path so ~ expands on the SERVER (not your local $HOME).
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

TMP_INDEX="$(mktemp)"
trap 'rm -f "$TMP_INDEX"' EXIT

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
const html = \`<!DOCTYPE html>
<html lang=\"et\">
<head>
  <meta charset=\"UTF-8\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <title>Fototeek</title>
  <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
  <link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
  <link href=\"https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..1,800&family=Inter:wght@400;500;600;700&display=swap\" rel=\"stylesheet\">
  <link rel=\"icon\" type=\"image/png\" href=\"/logo.png\">
  <link rel=\"stylesheet\" crossorigin href=\"/\${cssEntry}\">
  <link rel=\"stylesheet\" crossorigin href=\"/\${cssFromJs}\">
</head>
<body>
  <div id=\"app\"></div>
  <script type=\"module\" crossorigin src=\"/\${jsFile}\"></script>
</body>
</html>
\`;
writeFileSync(process.argv[1], html);
console.log('Generated index.html using:', { cssEntry, cssFromJs, jsFile });
" "$TMP_INDEX"

SCP_OPTS=(-i "$KEY" -o StrictHostKeyChecking=accept-new)

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/assets/"

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/current/public/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/current/public/assets/"

echo "Uploading index.html (both shells) ..."
scp "${SCP_OPTS[@]}" "$TMP_INDEX" "${HOST}:${REMOTE_BASE}/current/public/index.html"
scp "${SCP_OPTS[@]}" "$TMP_INDEX" "${HOST}:${REMOTE_BASE}/index.html"

echo "Done. Hard-refresh the site once (Ctrl+Shift+R / Cmd+Shift+R)."
