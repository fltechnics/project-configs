#!/usr/bin/env bash

RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}╔═══════════════════════════════════╗${NC}\n"
echo -e "${CYAN}║ -==RUNNING PHP DOCS FOR MODELS==- ║${NC}\n"
echo -e "${CYAN}╚═══════════════════════════════════╝${NC}\n"

php artisan ide-helper:generate
php artisan ide-helper:models --write
php artisan ide-helper:meta


echo -e "${CYAN}╔══════════════════════════════╗${NC}\n"
echo -e "${CYAN}║ -== RUNNING PHP CS FIXER ==- ║${NC}\n"
echo -e "${CYAN}╚══════════════════════════════╝${NC}\n"
php -n vendor/bin/php-cs-fixer --verbose --config=.php_cs fix


echo -e "${CYAN}╔═══════════════════════════════╗${NC}\n"
echo -e "${CYAN}║ -== RUNNING ES LINT FIXER ==- ║${NC}\n"
echo -e "${CYAN}╚═══════════════════════════════╝${NC}\n"
yarn run js:lint


echo -e "${CYAN}╔═════════════════════════════════╗${NC}\n"
echo -e "${CYAN}║ -== RUNNING SASS LINT FIXER ==- ║${NC}\n"
echo -e "${CYAN}╚═════════════════════════════════╝${NC}\n"
yarn run sass:lint


echo -e "${CYAN}╔══════════════════════════╗${NC}\n"
echo -e "${CYAN}║ -== RUNNING PHP UNIT ==- ║${NC}\n"
echo -e "${CYAN}╚══════════════════════════╝${NC}\n"
vendor/bin/phpunit --no-coverage
