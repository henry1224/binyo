#!/bin/sh

set -eu

node bin/check-docs.mjs
git diff --check
git diff --cached --check

(
    cd apps/backoffice
    composer ci:check
    npm run build
)

printf '%s\n' 'OK: quality gate lulus'
