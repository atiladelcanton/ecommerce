#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "==> SABA e-commerce starter setup"
echo "==> Laravel 13 / Tinker 3 / Filament 5 / Livewire 4"

echo "==> Reservando portas do projeto SABA: HTTP 8081, MySQL 3307, Redis 6380, Vite 5174"

run_composer() {
    if command -v composer >/dev/null 2>&1; then
        composer "$@"
        return
    fi

    if command -v docker >/dev/null 2>&1; then
        docker run --rm \
            -u "$(id -u):$(id -g)" \
            -v "$ROOT_DIR:/app" \
            -w /app \
            composer:2 "$@"
        return
    fi

    echo "Composer ou Docker é necessário para instalar as dependências." >&2
    exit 1
}

run_php() {
    if command -v php >/dev/null 2>&1; then
        php "$@"
        return
    fi

    if command -v docker >/dev/null 2>&1; then
        docker run --rm \
            -u "$(id -u):$(id -g)" \
            -v "$ROOT_DIR:/app" \
            -w /app \
            php:8.4-cli php "$@"
        return
    fi

    echo "PHP ou Docker é necessário para executar Artisan." >&2
    exit 1
}

set_env() {
    local key="$1"
    local value="$2"
    if grep -qE "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        printf '\n%s=%s\n' "$key" "$value" >> .env
    fi
}

[ -f .env ] || cp .env.example .env

# This starter owns these host ports. Force them even when reusing a directory
# that contains an older .env from a previous SABA starter version.
set_env APP_URL 'http://localhost:8081'
set_env APP_PORT '8081'
set_env FORWARD_DB_PORT '3307'
set_env FORWARD_REDIS_PORT '6380'
set_env VITE_PORT '5174'

run_composer validate --no-check-publish
run_composer install --no-interaction --prefer-dist
run_php artisan key:generate --force
run_php artisan optimize:clear
run_php artisan filament:assets --no-interaction

mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache

if command -v npm >/dev/null 2>&1; then
    npm install
    npm run build
else
    echo "==> npm não encontrado. Após subir o Sail, rode: ./vendor/bin/sail npm install && ./vendor/bin/sail npm run build"
fi

echo
echo "Setup concluído. Próximos passos:"
echo "  ./vendor/bin/sail down"
echo "  ./vendor/bin/sail up -d --build"
echo "  ./vendor/bin/sail artisan migrate"
echo "  ./vendor/bin/sail artisan test"
echo "  ./vendor/bin/sail artisan make:filament-user"
echo "  Storefront: http://localhost:8081"
echo "  Admin:      http://localhost:8081/admin"
