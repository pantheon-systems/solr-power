#!/bin/bash
set -e

DIRNAME=$(dirname "$0")
bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest 
composer test

# PHPUnit was deeply unhappy with setting the constant as part of the tests,
# so these are run separately.
# ${DIRNAME}/../vendor/bin/phpunit --group should-commit --filter testShouldNotCommitWhenConstNull
# ${DIRNAME}/../vendor/bin/phpunit --group should-commit --filter testShouldNotCommitWhenConstTrue
# ${DIRNAME}/../vendor/bin/phpunit --group should-commit --filter testShouldCommitWhenConstFalse
rm -rf $WP_TESTS_DIR $WP_CORE_DIR

bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest true
WP_MULTISITE=1 composer test
