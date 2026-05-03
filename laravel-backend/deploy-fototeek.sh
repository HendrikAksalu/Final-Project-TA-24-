#!/usr/bin/env bash
# One command: build + upload Fototeek (uses Linux Node via nvm — avoids Windows npm issues).
set -euo pipefail
cd "$(dirname "$0")"
export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
if [[ -s "$NVM_DIR/nvm.sh" ]]; then
  # shellcheck source=/dev/null
  . "$NVM_DIR/nvm.sh"
  nvm use
fi
npm run deploy:fototeek
