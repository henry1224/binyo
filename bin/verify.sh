#!/bin/sh

set -eu

node bin/check-docs.mjs
git diff --check
git diff --cached --check

printf '%s\n' 'OK: quality gate lulus'
