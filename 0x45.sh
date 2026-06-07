#!/usr/bin/env bash
# Fail Fast Fail Often
set -euo pipefail

RED='\x1b[0;31m'
GREEN='\x1b[0;32m'
YELLOW='\x1b[0;33m'
CYAN='\x1b[0;36m'
NC='\x1b[0m'

run_lint() {
  echo -e "${GREEN}==> Running PHPStan${NC}"
  vendor/bin/phpstan analyse
}

run_format() {
  echo -e "${GREEN}==> Running PHP-CS-Fixer${NC}"
  vendor/bin/php-cs-fixer fix
}

run_all() {
  run_format
  run_lint
}

watch() {
  local label="$1"
  local fn="$2"
  echo -e "${GREEN}==> Watching src/ for changes${NC}"
  local last=""
  while true; do
    current=$(find src -type f -name '*.php' -printf '%T@ %p\n' | sort | md5sum)
    if [ "$current" != "$last" ]; then
      if [ -n "$last" ]; then
        echo -e "${YELLOW}==> Change detected, running ${label}${NC}"
        "$fn" || true
      fi
      last="$current"
    fi
    sleep 1
  done
}

case "${1:-}" in
  install)
    echo -e "${GREEN}==> Installing dependencies${NC}"
    composer install --ignore-platform-req=ext-iconv
    ;;
  serve)
    echo -e "${GREEN}==> Serving on ${YELLOW}http://localhost:7539${NC}"
    php -S localhost:7539 -t public
    ;;
  lint)
    run_lint
    ;;
  format)
    run_format
    ;;
  watch-lint)
    watch "PHPStan" run_lint
    ;;
  watch-format)
    watch "PHP-CS-Fixer" run_format
    ;;
  watch-all)
    watch "PHP-CS-Fixer + PHPStan" run_all
    ;;
  *)
    echo ""
    echo -e "${CYAN}0x45.sh${NC} - E pointer helper script"
    echo ""
    echo -e "${YELLOW}Usage:${NC} ./0x45.sh <command>"
    echo ""
    echo -e "${YELLOW}Commands:${NC}"
    echo -e "  ${GREEN}install${NC}        Install composer dependencies"
    echo -e "  ${GREEN}serve${NC}          Serve the page on ${YELLOW}http://localhost:7539${NC}"
    echo -e "  ${GREEN}lint${NC}           Run PHPStan static analysis"
    echo -e "  ${GREEN}format${NC}         Run PHP-CS-Fixer"
    echo -e "  ${GREEN}watch-lint${NC}     Watch ${YELLOW}src/${NC} and re-run PHPStan on change"
    echo -e "  ${GREEN}watch-format${NC}   Watch ${YELLOW}src/${NC} and re-run PHP-CS-Fixer on change"
    echo -e "  ${GREEN}watch-all${NC}      Watch ${YELLOW}src/${NC} and run PHP-CS-Fixer then PHPStan on change"
    echo ""
    echo -e "${RED}Unknown command:${NC} ${1:-}"
    echo ""
    ;;
esac
