#!/usr/bin/env bash
# One command: build + upload Fototeek (uses Linux Node via nvm — avoids Windows npm issues).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "=== Fototeek deploy (from $(pwd)) ==="

export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
if [[ -s "$NVM_DIR/nvm.sh" ]]; then
  # shellcheck source=/dev/null
  . "$NVM_DIR/nvm.sh"
  if [[ -f .nvmrc ]]; then
    v="$(tr -d '\r\n' < .nvmrc)"
    echo "Using .nvmrc → Node $v"
    nvm install "$v" >/dev/null 2>&1 || true
    nvm use "$v" || {
      echo "ERROR: nvm could not switch to Node $v (from .nvmrc)." >&2
      echo "Install it with: nvm install $v" >&2
      exit 1
    }
  else
    nvm use || {
      echo "ERROR: nvm use failed. Install Node from laravel-backend/.nvmrc or fix nvm." >&2
      exit 1
    }
  fi
fi

command -v node >/dev/null || {
  echo "ERROR: node not found in PATH. Install Node 20.19+ or use nvm." >&2
  exit 1
}
command -v npm >/dev/null || {
  echo "ERROR: npm not found in PATH." >&2
  exit 1
}

echo "node $(node -v) | npm $(npm -v)"
npm run deploy:fototeek

echo "=== Finished OK ==="
