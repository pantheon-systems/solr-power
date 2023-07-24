#!/bin/bash
set -e

DIRNAME=$(dirname "$0")

reset_test_environment() {
  rm -rf $WP_TESTS_DIR $WP_CORE_DIR
}

echo "Testing on WordPress single site..."
bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 latest
composer test
reset_test_environment

echo "Testing on WordPress multisite..."
bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 latest true
WP_MULTISITE=1 composer test
reset_test_environment

echo "Testing with latest WordPress nightly version..."
bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 nightly true
composer test