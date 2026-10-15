#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

if ! command -v ngrok >/dev/null 2>&1; then
    echo "Instale o ngrok na máquina host: https://ngrok.com/download/linux" >&2
    exit 1
fi

# Read only the port; never source .env as shell code.
port="${APP_PORT:-}"
if [[ -z "$port" && -f .env ]]; then
    port="$(sed -n 's/^APP_PORT=//p' .env | tail -n 1 | tr -d '\r\"\047')"
fi
port="${port:-8081}"

if [[ ! "$port" =~ ^[0-9]+$ ]] || (( ${#port} > 5 )) || (( 10#$port < 1 || 10#$port > 65535 )); then
    echo "APP_PORT deve ser uma porta válida entre 1 e 65535." >&2
    exit 1
fi

echo "Abrindo túnel para o Sail em http://localhost:$port"
exec ngrok http "http://localhost:$port" "$@"
