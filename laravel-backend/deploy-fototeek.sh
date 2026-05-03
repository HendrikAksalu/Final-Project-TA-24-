#!/usr/bin/env bash
# One command: build + upload Fototeek (uses Linux Node via nvm — avoids Windows npm issues).
set -eo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "=== Fototeek deploy (from $(pwd)) ==="

export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
if [[ -s "$NVM_DIR/nvm.sh" ]]; then
  echo "Loading nvm from $NVM_DIR ..."
  # nvm.sh is not compatible with bash -u (nounset); disable for load + nvm commands.
  set +u
  # shellcheck source=/dev/null
  . "$NVM_DIR/nvm.sh"
  echo "nvm loaded (shell ready)."
  if [[ -f .nvmrc ]]; then
    v="$(tr -d '\r\n' < .nvmrc)"
    echo "Using .nvmrc → Node $v"
    echo "If Node is not installed yet, 'nvm install' downloads it — often 1–3 minutes, looks idle."
    nvm install "$v"
    echo "Switching to Node $v ..."
    nvm use "$v"
  else
    nvm use
  fi
  set -u
else
  echo "No nvm ($NVM_DIR/nvm.sh missing) — using node/npm already on your PATH."
fi

set -euo pipefail

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
