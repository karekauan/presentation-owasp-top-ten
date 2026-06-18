#!/usr/bin/env bash
#
# Apaga o banco e recria do zero (estado limpo para reapresentar).
# Uso:  bash reset.sh
#
set -euo pipefail
cd "$(dirname "$0")"

rm -f db/app.sqlite
echo "[*] Banco removido."
php seed.php
echo "[*] Pronto. Banco no estado inicial."
