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

node scripts/generate-fototeek-shell.mjs "$TMP_HTML" "$TMP_PHP"

SCP_OPTS=(-i "$KEY" -o StrictHostKeyChecking=accept-new)

# Vite/Laravel emits imported files (e.g. logos) as /build/assets/<hash>.png — shell CSS/JS use /assets/.
# Upload the same files to BOTH trees so hashed URLs and manifest paths both resolve as real files (not SPA HTML).
echo "Ensuring remote asset directories exist ..."
ssh "${SCP_OPTS[@]}" "$HOST" "mkdir -p ${REMOTE_BASE}/assets ${REMOTE_BASE}/build/assets ${REMOTE_BASE}/current/public/assets ${REMOTE_BASE}/current/public/build/assets"

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/assets/"

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/build/assets/ (for Vue-imported /build/assets/* URLs) ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/build/assets/"

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/current/public/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/current/public/assets/"

echo "Uploading assets → ${HOST}:${REMOTE_BASE}/current/public/build/assets/ ..."
scp "${SCP_OPTS[@]}" public/build/assets/* "${HOST}:${REMOTE_BASE}/current/public/build/assets/"

echo "Uploading public/logo.png (favicon /logo.png) ..."
scp "${SCP_OPTS[@]}" public/logo.png "${HOST}:${REMOTE_BASE}/logo.png"
scp "${SCP_OPTS[@]}" public/logo.png "${HOST}:${REMOTE_BASE}/current/public/logo.png"

echo "Uploading index.php (PHP shell — bypasses static HTML cache) ..."
scp "${SCP_OPTS[@]}" "$TMP_PHP" "${HOST}:${REMOTE_BASE}/current/public/index.php"
scp "${SCP_OPTS[@]}" "$TMP_PHP" "${HOST}:${REMOTE_BASE}/index.php"

echo "Uploading index.html fallback (same markup, static backup) ..."
scp "${SCP_OPTS[@]}" "$TMP_HTML" "${HOST}:${REMOTE_BASE}/current/public/index.html"
scp "${SCP_OPTS[@]}" "$TMP_HTML" "${HOST}:${REMOTE_BASE}/index.html"

echo "Done. Homepage uses PHP index.php — browsers/CDNs stop pinning stale HTML shells."
