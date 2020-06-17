#!/bin/bash

# Copyright © Magento, Inc. All rights reserved.
# See COPYING.txt for license details.

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

function log() {
    printf "\nRunning %s tests:\n" "$1"
}

log "phpstan"
./vendor/bin/phpstan analyse -c tests/static/phpstan.neon
log "phpcs"
./vendor/bin/phpcs ./ --standard=tests/static/phpcs-ruleset.xml -p -n
log "phpmd"
./vendor/bin/phpmd Console xml tests/static/phpmd-ruleset.xml
log "phpunit"
./vendor/bin/phpunit --configuration Test/Unit
