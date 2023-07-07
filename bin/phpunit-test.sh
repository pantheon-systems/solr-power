#!/bin/bash
set -e

DIRNAME=$(dirname "$0")

bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest
# composer phpunit
# rm -rf $WP_TESTS_DIR $WP_CORE_DIR

composer phpunit --group should-commit --filter testShouldCommit::shouldCommitTestValues --filter "/@shouldCommitTestValues\.0$/" || true
composer phpunit --group should-commit --filter testShouldCommit::shouldCommitTestValues --filter "/@shouldCommitTestValues\.1$/" || true
composer phpunit --group should-commit --filter testShouldCommit::shouldCommitTestValues --filter "/@shouldCommitTestValues\.2$/" || true
rm -rf $WP_TESTS_DIR $WP_CORE_DIR

composer phpunit-shouldcommit
rm -rf $WP_TESTS_DIR $WP_CORE_DIR
bash bin/install-wp-tests.sh wordpress_test root root 127.0.0.1 latest true
WP_MULTISITE=1 composer phpunit
rm -rf $WP_TESTS_DIR $WP_CORE_DIR
WP_MULTISITE=1 composer phpunit-shouldcommit
