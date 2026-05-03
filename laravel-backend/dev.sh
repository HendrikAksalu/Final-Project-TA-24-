#!/usr/bin/env bash
# Use Linux/WSL Node (nvm) — Windows npm + WSL project path breaks ("vite not recognized").
cd "$(dirname "$0")"
export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
if [[ -s "$NVM_DIR/nvm.sh" ]]; then
  # shellcheck source=/dev/null
  . "$NVM_DIR/nvm.sh"
  nvm use
fi
exec npm run dev
