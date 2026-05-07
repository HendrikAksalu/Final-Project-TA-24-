#!/bin/bash
# Fototeek — ühekordne SQLite jagatud kausta seadistus Zone.ee serveris.
#
# KASUTUS: SSH-i serverisse ja käivita see skript:
#   ssh virt137753@ta24aksalu.itmajakas.ee
#   bash <(curl -s https://raw.githubusercontent.com/HendrikAksalu/Final-Project-TA-24-/main/scripts/setup-shared-sqlite.sh)
#
# VÕI kopeeri skript kohapeal ja käivita:
#   bash setup-shared-sqlite.sh

set -euo pipefail

BASE="$HOME/domeenid/www.ta24aksalu.itmajakas.ee/fototeek"
SHARED_DB_DIR="$BASE/shared/laravel-backend/database"
SHARED_DB_FILE="$SHARED_DB_DIR/database.sqlite"
SHARED_ENV="$BASE/shared/laravel-backend/.env"
CURRENT_DB="$BASE/current/laravel-backend/database/database.sqlite"
ABSOLUTE_DB_PATH="/home/virt137753/domeenid/www.ta24aksalu.itmajakas.ee/fototeek/shared/laravel-backend/database/database.sqlite"

echo "=== Fototeek SQLite jagatud kausta seadistus ==="
echo

# 1. Kontrolli, kas baas-kaust eksisteerib
if [ ! -d "$BASE" ]; then
  echo "VIGA: $BASE ei eksisteeri. Kas projekt on üldse deployitud?"
  exit 1
fi

# 2. Loo jagatud andmebaasi kaust
echo "[1/5] Loon jagatud kausta: $SHARED_DB_DIR"
mkdir -p "$SHARED_DB_DIR"

# 3. Kopeeri olemasolev DB või loo tühi
if [ -f "$SHARED_DB_FILE" ]; then
  echo "[2/5] Jagatud DB juba eksisteerib: $SHARED_DB_FILE — jätan alles."
elif [ -f "$CURRENT_DB" ] && [ -s "$CURRENT_DB" ]; then
  echo "[2/5] Kopeerin olemasoleva andmebaasi: $CURRENT_DB -> $SHARED_DB_FILE"
  cp "$CURRENT_DB" "$SHARED_DB_FILE"
else
  echo "[2/5] Loon uue tühja andmebaasi faili: $SHARED_DB_FILE"
  touch "$SHARED_DB_FILE"
fi

# 4. Sea õigused
echo "[3/5] Sean õigused"
chmod 775 "$SHARED_DB_DIR"
chmod 664 "$SHARED_DB_FILE"

# 5. Kontrolli/uuenda .env faili
echo "[4/5] Kontrollin .env faili: $SHARED_ENV"
if [ ! -f "$SHARED_ENV" ]; then
  echo "  HOIATUS: $SHARED_ENV ei eksisteeri."
  echo "  See peaks olema loodud esimese deploy ajal. Kontrolli käsitsi."
else
  # Eemalda olemasolevad DB_CONNECTION ja DB_DATABASE read (kui on)
  TMP_ENV="$(mktemp)"
  rg -v '^(DB_CONNECTION|DB_DATABASE)=' "$SHARED_ENV" > "$TMP_ENV" || true

  # Lisa uued read
  {
    cat "$TMP_ENV"
    echo ""
    echo "DB_CONNECTION=sqlite"
    echo "DB_DATABASE=$ABSOLUTE_DB_PATH"
  } > "$SHARED_ENV"

  rm -f "$TMP_ENV"
  echo "  .env uuendatud."
fi

# 6. Näita lõppolukord
echo "[5/5] Lõppolukord:"
echo
echo "  Jagatud DB fail:"
ls -la "$SHARED_DB_FILE"
echo
echo "  .env asjakohased read:"
rg '^(DB_CONNECTION|DB_DATABASE)=' "$SHARED_ENV" || echo "  (DB ridu ei leitud — kontrolli .env faili käsitsi!)"
echo
echo "=== Valmis! ==="
echo
echo "Järgmine samm: käivita Production deploy GitHub Actions UI-st."
echo "Pärast deploy't tee testkasutaja, lisa pilt, deploy uuesti — andmed peavad alles jääma."
