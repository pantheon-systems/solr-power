#!/bin/bash
set -eou pipefail

DIRNAME=$(dirname "$0")

CURRENT_HASH=$(git rev-parse --short HEAD)

cd "$DIRNAME/.."

npm ci 
npm run build 
composer install --no-dev -o

VERSION=$(jq -r .version ./package.json)
PRERELEASE_VERSION="${VERSION}-${CURRENT_HASH}"

git checkout -b "prerelease-${PRERELEASE_VERSION}"
git add -f assets/* vendor/
git commit -m "Release ${PRERELEASE_VERSION}"
git tag "${PRERELEASE_VERSION}"
git push --tags

git checkout -