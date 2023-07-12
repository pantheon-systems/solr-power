# Contributing

Since 2.3.3 the default branch is `main`. Please make sure you are working against the correct branch. The `master` branch will no longer be accepting pull requests.

## Workflow

Development and releases are structured around two branches, `develop` and `main`. The `develop` branch is the source and destination for feature branches.

We prefer to squash commits (i.e. avoid merge PRs) from a feature branch into `develop` when merging, and to include the PR # in the commit message. PRs to `develop` should also include any relevent updates to the changelog in readme.txt. For example, if a feature constitutes a minor or major version bump, that version update should be discussed and made as part of approving and merging the feature into `develop`.

`develop` should be stable and usable, though possibly a few commits ahead of the public release on wp.org.

The `main` branch matches the latest stable release deployed to [wp.org](wp.org).

## Testing

You may notice there are two sets of tests running:

* The [PHPUnit](https://phpunit.de/) test suite runs against a Solr instance.
* The [Behat](http://behat.org/) test suite runs against a Pantheon site, to ensure the plugin's compatibility with the Pantheon platform.

Both of these test suites can be run locally, with a varying amount of setup.

PHPUnit requires the [WordPress PHPUnit test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/), and access to a database with name `wordpress_test`. If you haven't already configured the test suite locally, you can run `bash bin/install-wp-tests.sh wordpress_test root '' localhost`. You'll also need access to a running Solr instance, in order to run the unit tests against Solr.

Behat requires a Pantheon site with Solr enabled. Once you've created the site, you'll need [install Terminus](https://github.com/pantheon-systems/terminus#installation), and set the `TERMINUS_TOKEN`, `TERMINUS_SITE`, and `TERMINUS_ENV` environment variables. Then, you can run `./bin/behat-prepare.sh` to prepare the site for the test suite.

Note that dependencies are installed via Composer and the `vendor` directory is not committed to the repository. You will need to run `composer install` locally for the plugin to function. You can read more about Composer [here](https://getcomposer.org)

## Release Process

1. From `develop`, checkout a new branch `release_X.Y.Z`.
1. Make a release commit:
    * Drop the `-dev` from the version number in `package.json`, `README.md`, `readme.txt`, CHANGELOG.md, and `solr-power.php`. For `readme.txt`, the version number must be updated both at the top of the document as well as the changelog.
    * Add the date to the `** X.Y.X **` heading in the changelogs in README.md, readme.txt, and any other appropriate location.
    * Commit these changes with the message `Release X.Y.Z`
    * Push the release branch up.
1. Open a Pull Request to merge `release_X.Y.Z` into `main`. Your PR should consist of all commits to `develop` since the last release, and one commit to update the version number. The PR name should also be `Release X.Y.Z`.
1. After all tests pass and you have received approval from a [CODEOWNER](./CODEOWNERS), merge the PR into `main`. "merge" is preferred in this case, not rebase. _Never_ squash to `main`.
1. Pull `main` locally, create a new tag (based on version number from previous steps), and push up. The tag should _only_ be the version number. It _should not_ be prefixed  `v` (i.e. `X.Y.Z`, not `vX.Y.X`).
1. Confirm that the necessary assets are present in the newly created tag, and test on a WP install if desired.
1. Create a [new release](https://github.com/pantheon-systems/solr-power/releases/new) using the tag created in the previous steps, naming the release with the new version number, and targeting the tag created in the previous step. Paste the release changelog from the `Changelog` section of [the readme](readme.txt) into the body of the release, including the links to the closed issues if applicable.
1. Wait for the [_Release solr-power plugin to wp.org_ action](https://github.com/pantheon-systems/solr-power/actions/workflows/wordpress-plugin-deploy.yml) to finish deploying to the WordPress.org plugin repository. If all goes well, users with SVN commit access for that plugin will receive an emailed diff of changes.
1. Check WordPress.org: Ensure that the changes are live on [the plugin repository](https://wordpress.org/plugins/solr-power/). This may take a few minutes.
1. Following the release, prepare the next dev version with the following steps:
    * `git checkout develop`
    * `git rebase main`
    * Update the version number in all locations, incrementing the version by one patch version, and add the `-dev` flag (e.g. after releasing `1.2.3`, the new verison will be `1.2.4-dev`)
    * Add new `** X.Y.X-dev **` headings to the changelogs
    * `git add -A .`
    * `git commit -m "Prepare X.Y.X-dev"`
    * `git push origin develop`
