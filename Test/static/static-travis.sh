#!/bin/bash

# Copyright © Magento, Inc. All rights reserved.
# See COPYING.txt for license details.

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

./vendor/bin/phpstan analyse -c Test/static/phpstan.neon
./vendor/bin/phpcs ./ --standard=Test/static/phpcs-ruleset.xml -p -n
./vendor/bin/phpmd Console xml Test/static/phpmd-ruleset.xml
./vendor/bin/phpunit --configuration Test/Unit