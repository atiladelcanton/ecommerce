#!/usr/bin/env bash
set -euo pipefail

# Executar na raiz do Laravel: bash /caminho/INSTALAR-ATUALIZACAO.sh /caminho/ZIP
if [[ ! -f artisan || ! -f composer.json || ! -d database/migrations ]]; then
  echo 'ERRO: execute este script na raiz do projeto SABA (onde está o artisan).' >&2
  exit 1
fi
if [[ $# -ne 1 || ! -f "$1" ]]; then
  echo 'Uso: bash INSTALAR-ATUALIZACAO.sh /caminho/saba-ecommerce-atualizacao.zip' >&2
  exit 1
fi
package="$(realpath "$1")"
command -v unzip >/dev/null || { echo 'É necessário ter unzip instalado.' >&2; exit 1; }
command -v tar >/dev/null || { echo 'É necessário ter tar instalado.' >&2; exit 1; }
backup="$(pwd)/storage/app/saba-cupons-backup-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$backup/arquivos"
oldlist="$(mktemp)"
trap 'rm -f "$oldlist"' EXIT
while IFS= read -r relative; do
  [[ "$relative" == */ ]] && continue
  # O ZIP é gerado exclusivamente com caminhos do projeto, mas limitamos a extração.
  case "$relative" in
    app/*|routes/*|docs/*|tests/*|INSTALACAO-CUPONS.md|LEIA-ME-CUPONS.txt|ATUALIZACAO-CUPONS-28-10.md|INSTALAR-ATUALIZACAO.sh) ;;
    *) echo "Arquivo inesperado no pacote: $relative" >&2; exit 1 ;;
  esac
  [[ "$relative" != *'..'* ]] || { echo 'Caminho inválido no ZIP.' >&2; exit 1; }
  if [[ -f "$relative" ]]; then
    echo "$relative" >> "$oldlist"
  fi
done < <(unzip -Z1 "$package")
if [[ -s "$oldlist" ]]; then
  tar -czf "$backup/arquivos-anteriores.tar.gz" -T "$oldlist"
fi
# Arquivar os arquivos duplicados antes de extração.
for old in \
  database/migrations/2026_10_27_000001_create_coupons_and_redemptions.php \
  database/migrations/duplicadas/2026_10_27_000001_create_coupons_and_redemptions.php \
  app/Actions/Coupon/RecordCouponRedemptionAction.php \
  app/Actions/Coupon/ValidateCouponAction.php \
  app/Domain/Coupon/Models/CouponRedemption.php; do
  if [[ -f "$old" ]]; then
    mkdir -p "$backup/removidos/$(dirname "$old")"
    mv "$old" "$backup/removidos/$old"
  fi
done
unzip -oq "$package" -d .
echo
printf 'Correção de cupons aplicada. Backup: %s\n' "$backup"
echo 'Nenhuma migration foi executada; .env, composer.lock e public/build não foram modificados.'
echo 'A seguir execute: php artisan optimize:clear && php artisan migrate:status'
echo 'Teste primeiro em ambiente de homologação; não rode migrate:fresh.'
