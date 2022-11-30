# Contributing

Since 2.3.3 the default branch is `main`. Please make sure you are working against the correct branch. The `master` branch will no longer be accepting pull requests.

## Workflow

The `develop` branch is the development branch which means it contains the next version to be released. `main` contains the corresponding stable development version. Always work on the `develop` branch and open up PRs against `develop`.

## Testing

You may notice there are two sets of tests running:

* The [PHPUnit](https://phpunit.de/) test suite runs against a Solr instance.
* The [Behat](http://behat.org/) test suite runs against a Pantheon site, to ensure the plugin's compatibility with the Pantheon platform.

Both of these test suites can be run locally, with a varying amount of setup.

PHPUnit requires the [WordPress PHPUnit test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/), and access to a database with name `wordpress_test`. If you haven't already configured the test suite locally, you can run `bash bin/install-wp-tests.sh wordpress_test root '' localhost`. You'll also need access to a running Solr instance, in order to run the unit tests against Solr.

Behat requires a Pantheon site with Solr enabled. Once you've created the site, you'll need [install Terminus](https://github.com/pantheon-systems/terminus#installation), and set the `TERMINUS_TOKEN`, `TERMINUS_SITE`, and `TERMINUS_ENV` environment variables. Then, you can run `./bin/behat-prepare.sh` to prepare the site for the test suite.

Note that dependencies are installed via Composer and the `vendor` directory is not committed to the repository. You will need to run `composer install` locally for the plugin to function. You can read more about Composer [here](https://getcomposer.org)

## Release Process

1. Starting from `develop`, cut a release branch named `release_X.Y.Z` containing your changes.
1. Update plugin version in `package.json`, `README.md`, `readme.txt`, and `solr-power.php`.
1. Update the Changelog with the latest changes.
1. Create a PR against the `main` branch.
1. After all tests pass and you have received approval from a CODEOWNER (including resolving any merge conflicts), merge the PR into `main`.
1. [Check the _Build and Tag_ action](https://github.com/pantheon-systems/solr-power/actions/workflows/build-tag.yml): a new tag named with the version number should've been created. It should contain all the built assets.
1. Create a [new release](https://github.com/pantheon-systems/solr-power/releases/new), naming the release with the new version number, and targeting the tag created in the previous step. Paste the release changelog from `CHANGELOG.md` into the body of the release and include a link to the closed issues if applicable.
1. Wait for the [_Release solr-power plugin to wp.org_ action](https://github.com/pantheon-systems/solr-power/actions/workflows/wordpress-plugin-deploy.yml) to finish deploying to the WordPress.org repository. If all goes well, users with SVN commit access for that plugin will receive an emailed diff of changes.
1. Check WordPress.org: Ensure that the changes are live on https://wordpress.org/plugins/solr-power/. This may take a few minutes.