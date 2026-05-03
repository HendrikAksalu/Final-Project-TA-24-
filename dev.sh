#!/usr/bin/env bash
# Standalone Vite app at repo root — same Windows/npm UNC issue as laravel-backend.
cd "$(dirname "$0")"
export NVM_DIR="${NVM_DIR:-$HOME/.nvm}"
if [[ -s "$NVM_DIR/nvm.sh" ]]; then
  # shellcheck source=/dev/null
  . "$NVM_DIR/nvm.sh"
  if [[ -f .nvmrc ]]; then nvm use; fi
fi
exec npm run dev
