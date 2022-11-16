# Contributing

Since 2.3.3 the default branch is `main`. Please make sure you are working against the correct branch. The `master` branch will no longer be accepting pull requests.

## Testing

You may notice there are two sets of tests running, on two different services:

* Travis CI runs the [PHPUnit](https://phpunit.de/) test suite against a Solr instance.
* Circle CI runs the [Behat](http://behat.org/) test suite against a Pantheon site, to ensure the plugin's compatibility with the Pantheon platform.

Both of these test suites can be run locally, with a varying amount of setup.

PHPUnit requires the [WordPress PHPUnit test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/), and access to a database with name `wordpress_test`. If you haven't already configured the test suite locally, you can run `bash bin/install-wp-tests.sh wordpress_test root '' localhost`. You'll also need access to a running Solr instance, in order to run the unit tests against Solr.

Behat requires a Pantheon site with Solr enabled. Once you've created the site, you'll need [install Terminus](https://github.com/pantheon-systems/terminus#installation), and set the `TERMINUS_TOKEN`, `TERMINUS_SITE`, and `TERMINUS_ENV` environment variables. Then, you can run `./bin/behat-prepare.sh` to prepare the site for the test suite.

Note that dependencies are installed via Composer and the `vendor` directory is not committed to the repository. You will need to run `composer install` locally for the plugin to function. You can read more about Composer [here](https://getcomposer.org)

## Release Process

1. Update plugin version in `package.json`, `README.md`, `readme.txt`, and `solr-power.php`.
2. Create a PR against the `main` branch.
3. After all tests and code reviews pass (including resolving any merge conflicts) and you have received approval from a CODEOWNER, merge the PR into `main`.
4. Wait for CI to build and push a new tag.
5. Confirm that the necessary assets are present in the newly created tag, and test on a WP install if desired.
6. Publish a new release using the latest tag. Publishing a release will kick off `wordpress-plugin-deploy.yml` and release the plugin to wp.org. If you do not want a tag to be publised to wp.org, do not publish a release from it.