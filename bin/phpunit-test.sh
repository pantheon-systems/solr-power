#!/bin/bash
set -e

DIRNAME=$(dirname "$0")
bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 latest
composer test
rm -rf $WP_TESTS_DIR $WP_CORE_DIR

bash "${DIRNAME}/install-wp-tests.sh" wordpress_test root root 127.0.0.1 latest true
WP_MULTISITE=1 composer test
