#!/bin/bash

# SPDX-FileCopyrightText: 2026 STRATO AG
# SPDX-License-Identifier: AGPL-3.0-or-later

# Table-driven tests for derive-coordinate.sh.
#
# Runs entirely offline — no pipeline, no registry, no network, no git.
# Usage: .github/scripts/tests/derive-coordinate.test.sh

# Deliberately no `set -e`: every helper below inspects a non-zero exit status
# from the script under test, which `set -e` would turn into an abort.
set -u
set -o pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DERIVE="${SCRIPT_DIR}/../derive-coordinate.sh"

PASSED=0
FAILED=0

# run_derive <ref> <nc-version> <iid> — invoke the script under test, passing the
# IID only when one was given, since its absence is itself a case under test.
# Sets DERIVE_STDOUT, DERIVE_STDERR and DERIVE_STATUS.
run_derive() {
  local ref="$1" version="$2" iid="$3"
  local stderr_file
  stderr_file="$(mktemp)"

  if [ -n "$iid" ]; then
    DERIVE_STDOUT=$("$DERIVE" "$ref" "$version" "$iid" 2>"$stderr_file")
  else
    DERIVE_STDOUT=$("$DERIVE" "$ref" "$version" 2>"$stderr_file")
  fi
  DERIVE_STATUS=$?
  DERIVE_STDERR="$(cat "$stderr_file")"
  rm -f "$stderr_file"
}

# assert_ok <name> <ref> <nc-version> <iid> <expected-key=value[;key=value...]>
#
# Asserts the script exits 0 and that every expected KEY=value appears verbatim
# as its own output line. Only the listed keys are checked, so a case states the
# part of the coordinate it cares about.
assert_ok() {
  local name="$1" ref="$2" version="$3" iid="$4" expectations="$5"
  local output

  run_derive "$ref" "$version" "$iid"
  output="$DERIVE_STDOUT"

  if [ "$DERIVE_STATUS" -ne 0 ]; then
    printf 'FAIL  %s\n      expected exit 0, got %s\n      stderr: %s\n' \
      "$name" "$DERIVE_STATUS" "$DERIVE_STDERR"
    FAILED=$((FAILED + 1))
    return
  fi

  local missing=""
  local expectation
  while IFS= read -r expectation; do
    [ -z "$expectation" ] && continue
    if ! grep -Fxq -- "$expectation" <<<"$output"; then
      missing="${missing} ${expectation}"
    fi
  done <<<"${expectations//;/$'\n'}"

  if [ -n "$missing" ]; then
    printf 'FAIL  %s\n      missing:%s\n      actual output:\n        %s\n' \
      "$name" "$missing" "${output//$'\n'/$'\n'        }"
    FAILED=$((FAILED + 1))
    return
  fi

  printf 'ok    %s\n' "$name"
  PASSED=$((PASSED + 1))
}

# assert_fails <name> <ref> <nc-version> <iid> <expected-stderr-substring>
#
# Asserts the script exits non-zero, says why on stderr, and emits no coordinate.
# The expected reason must be specific enough to identify *which* guard fired: a
# substring shared by two guards' messages lets either one satisfy the case, so
# deleting one guard would leave the table green.
assert_fails() {
  local name="$1" ref="$2" version="$3" iid="$4" expected_reason="$5"

  run_derive "$ref" "$version" "$iid"

  if [ "$DERIVE_STATUS" -eq 0 ]; then
    printf 'FAIL  %s\n      expected non-zero exit, got 0\n' "$name"
    FAILED=$((FAILED + 1))
    return
  fi

  if ! grep -Fq -- "$expected_reason" <<<"$DERIVE_STDERR"; then
    printf 'FAIL  %s\n      expected stderr to mention: %s\n      actual stderr: %s\n' \
      "$name" "$expected_reason" "$DERIVE_STDERR"
    FAILED=$((FAILED + 1))
    return
  fi

  if [ -n "$DERIVE_STDOUT" ]; then
    printf 'FAIL  %s\n      a refused build must emit no coordinate, got: %s\n' \
      "$name" "$DERIVE_STDOUT"
    FAILED=$((FAILED + 1))
    return
  fi

  printf 'ok    %s\n' "$name"
  PASSED=$((PASSED + 1))
}

# assert_absent <name> <ref> <nc-version> <iid> <key>
#
# Asserts the script exits 0 but emits no line for <key> at all.
assert_absent() {
  local name="$1" ref="$2" version="$3" iid="$4" key="$5"

  run_derive "$ref" "$version" "$iid"

  if [ "$DERIVE_STATUS" -ne 0 ]; then
    printf 'FAIL  %s\n      expected exit 0, got %s\n' "$name" "$DERIVE_STATUS"
    FAILED=$((FAILED + 1))
    return
  fi

  if grep -q "^${key}=" <<<"$DERIVE_STDOUT"; then
    printf 'FAIL  %s\n      expected no %s line, got: %s\n' \
      "$name" "$key" "$(grep "^${key}=" <<<"$DERIVE_STDOUT")"
    FAILED=$((FAILED + 1))
    return
  fi

  printf 'ok    %s\n' "$name"
  PASSED=$((PASSED + 1))
}

echo "=== Lane type: dev lane (branch declares a major) ==="

assert_ok 'ionos-dev-v33 is a dev lane routing to dev-v33' \
  'ionos-dev-v33' '33.0.6.2' '' \
  'LANE_TYPE=dev;LANE=dev-v33;TRAIN=;NC_MAJOR=33;DOWNSTREAM_REF=dev-v33'

assert_ok 'a dev lane publishes no chart' \
  'ionos-dev-v33' '33.0.6.2' '' \
  'PUBLISHES_CHART=false;CHART_PATH='

assert_ok 'ionos-dev-v30 routes to its own major, not a default' \
  'ionos-dev-v30' '30.0.7.2' '' \
  'LANE=dev-v30;NC_MAJOR=30;DOWNSTREAM_REF=dev-v30'

assert_ok 'a fully-qualified refs/heads/ ref is accepted' \
  'refs/heads/ionos-dev-v33' '33.0.6.2' '' \
  'LANE_TYPE=dev;LANE=dev-v33'

echo "=== Lane type: dev lane (branch declares no major) ==="

# A */dev/* user branch carries no major, and under the derived scheme it no
# longer needs to — the major comes from the artifact.
assert_ok 'a user dev branch takes its major from the artifact' \
  'kh/dev/add-simplenavigation-v33' '33.0.6.2' '' \
  'LANE_TYPE=dev;LANE=dev-v33;NC_MAJOR=33;DOWNSTREAM_REF=dev-v33'

assert_ok 'a user dev branch on a different major routes elsewhere' \
  'mk/dev/some-fix' '31.0.6.2.1062' '' \
  'LANE_TYPE=dev;LANE=dev-v31;NC_MAJOR=31;DOWNSTREAM_REF=dev-v31'

# The branch name mentioning a version is not a declaration — only the
# ionos-dev-v<major> form declares one. This case would trip an over-eager
# parser into asserting 33 against an artifact built as 31.
assert_ok 'a version-shaped substring in a user branch name is not a declaration' \
  'kh/dev/prepare-v33-theming' '31.0.6.2' '' \
  'LANE=dev-v31;NC_MAJOR=31'

# The unsuffixed dev branch still builds and is retired only later, so failing it
# closed here would break the live dev build before that step arrives.
assert_ok 'unsuffixed ionos-dev is a dev lane taking its major from the artifact' \
  'ionos-dev' '33.0.6.2' '' \
  'LANE_TYPE=dev;LANE=dev-v33;NC_MAJOR=33;DOWNSTREAM_REF=dev-v33'

echo "=== Lane type: train lane, new naming era ==="

assert_ok 'rc/web-v33-1 is a train lane with its own chart line' \
  'rc/web-v33-1' '33.0.6.2' '990' \
  'LANE_TYPE=train;LANE=rc/web-v33-1;TRAIN=web-v33-1;NC_MAJOR=33;DOWNSTREAM_REF=rc/web-v33-1;PUBLISHES_CHART=true;CHART_PATH=helm/hidrive-next-web-v33-1;CHART_VERSION=1.33.990'

assert_ok 'rc/web-v31-1 publishes into the v31 line' \
  'rc/web-v31-1' '31.0.6.2.1062' '42' \
  'TRAIN=web-v31-1;NC_MAJOR=31;CHART_PATH=helm/hidrive-next-web-v31-1;CHART_VERSION=1.31.42'

echo "=== Lane type: train lane, legacy naming era ==="

# rc/web-3.5 is the live v30 lane. The trailing number is a release counter, not
# a major — so the legacy shape declares no major, parses 30 from the artifact,
# routes to the v30 lane, and needs no rename. Indefinitely, not for a window.
assert_ok 'legacy rc/web-3.5 derives v30 from the artifact' \
  'rc/web-3.5' '30.0.7.2' '88' \
  'LANE_TYPE=train;LANE=rc/web-3.5;TRAIN=web-3.5;NC_MAJOR=30;DOWNSTREAM_REF=rc/web-3.5;CHART_PATH=helm/hidrive-next-web-3.5;CHART_VERSION=1.30.88'

assert_ok 'legacy rc/web-5 is a train lane, its 5 is not a major' \
  'rc/web-5' '32.0.6.1.1062' '7' \
  'LANE_TYPE=train;TRAIN=web-5;NC_MAJOR=32;CHART_PATH=helm/hidrive-next-web-5;CHART_VERSION=1.32.7'

echo "=== Lane type: trunk ==="

# main is the integration trunk with a line of its own. It is not a lane —
# the vocabulary has exactly two lane types — so LANE and TRAIN stay empty.
assert_ok 'main publishes the trunk chart line' \
  'main' '33.0.6.2' '512' \
  'LANE_TYPE=trunk;LANE=;TRAIN=;NC_MAJOR=33;DOWNSTREAM_REF=main;PUBLISHES_CHART=true;CHART_PATH=helm/hidrive-next-main;CHART_VERSION=1.33.512'

echo "=== Lane type: merge-request branch ==="

assert_ok 'a feature branch packages but never publishes' \
  'feature/HDNEXT-2144-derive-coordinate' '33.0.6.2' '4' \
  'LANE_TYPE=merge-request;LANE=;TRAIN=;NC_MAJOR=33;DOWNSTREAM_REF=;PUBLISHES_CHART=false;CHART_PATH='

assert_ok 'a renovate branch packages but never publishes' \
  'renovate/npm-vue-3.x' '33.0.6.2' '4' \
  'LANE_TYPE=merge-request;PUBLISHES_CHART=false;CHART_PATH='

# The */dev/* shape would otherwise swallow these and trigger downstream.
assert_ok 'feature/dev/... is a merge request, not a dev lane' \
  'feature/dev/some-experiment' '33.0.6.2' '' \
  'LANE_TYPE=merge-request;LANE=;DOWNSTREAM_REF='

assert_ok 'renovate/dev/... is a merge request, not a dev lane' \
  'renovate/dev/bump-something' '33.0.6.2' '' \
  'LANE_TYPE=merge-request;LANE=;DOWNSTREAM_REF='

echo "=== The major assertion ==="

# The 2026-07-29 regression: nc-server rc/web-5 built NC 32.0.6.1.1062 and
# triggered the v30 lane. Only an unrelated apk pin conflict stopped it. Where a
# branch declares a major, disagreeing with the artifact must fail the build.
assert_fails 'a train branch declaring v33 that built 32.x fails the build' \
  'rc/web-v33-1' '32.0.6.1.1062' '990' \
  'declares major 33'

assert_fails 'a dev branch declaring v33 that built 30.x fails the build' \
  'ionos-dev-v33' '30.0.7.2' '' \
  'declares major 33'

assert_fails 'the assertion is symmetric — declaring v30 while building 33.x also fails' \
  'ionos-dev-v30' '33.0.6.2' '' \
  'declares major 30'

echo "=== Fail closed ==="

assert_fails 'an unrecognised ref stops the pipeline' \
  'some/random/branch' '33.0.6.2' '' \
  'unrecognised ref'

assert_fails 'a bare topic branch is not silently treated as a merge request' \
  'wip-experiment' '33.0.6.2' '' \
  'unrecognised ref'

# The stable axis is retired: no ionos-stable-v* branch has ever existed, and
# BUILD_TYPE is re-derived from the lane. These refs must not resolve to a lane.
assert_fails 'the retired stable axis is not a lane' \
  'ionos-stable-v30' '30.0.7.2' '' \
  'unrecognised ref'

# ionos-dev-v32.0.6 was deleted from origin to keep one dev-lane shape, so the
# longer form is no longer a lane. Only ionos-dev-v<major> declares a major.
assert_fails 'a dotted ionos-dev-v<major>.<minor> ref is not a lane' \
  'ionos-dev-v32.0.6' '32.0.6.1.1062' '' \
  'unrecognised ref'

assert_fails 'the unsuffixed stable branch is not a lane either' \
  'ionos-stable' '30.0.7.2' '' \
  'unrecognised ref'

# nc-server's fork-sync branch is not a delivery lane; the trunk that publishes a
# chart line is `main` in the image and helm repos.
assert_fails 'master is not a lane' \
  'master' '33.0.6.2' '' \
  'unrecognised ref'

assert_fails 'an empty NC_VERSION stops the pipeline' \
  'ionos-dev-v33' '' '' \
  'NC_VERSION is empty'

# jq -r on a missing .ncVersion key prints the string "null", so this is the
# shape a broken version.json actually reaches the trigger as.
assert_fails 'the literal string null is rejected' \
  'ionos-dev-v33' 'null' '' \
  'is not a dotted numeric version'

assert_fails 'a non-numeric NC_VERSION is rejected' \
  'ionos-dev-v33' 'abc' '' \
  'is not a dotted numeric version'

assert_fails 'a v-prefixed NC_VERSION is rejected' \
  'ionos-dev-v33' 'v33.0.6.2' '' \
  'is not a dotted numeric version'

assert_fails 'a single-component NC_VERSION is rejected' \
  'ionos-dev-v33' '33' '' \
  'is not a dotted numeric version'

assert_fails 'a trailing-dot NC_VERSION is rejected' \
  'ionos-dev-v33' '33.' '' \
  'is not a dotted numeric version'

assert_fails 'a leading-dot NC_VERSION is rejected' \
  'ionos-dev-v33' '.33.0' '' \
  'is not a dotted numeric version'

assert_fails 'a non-numeric component is rejected' \
  'ionos-dev-v33' '33.0.x.2' '' \
  'is not a dotted numeric version'

assert_fails 'an empty ref stops the pipeline' \
  '' '33.0.6.2' '' \
  'ref is empty'

# Called with too few or too many arguments, rather than with bad values. Checked
# directly because the helpers above always pass a well-formed argument count.
assert_usage_error() {
  local name="$1"
  shift
  local stderr status
  stderr=$("$DERIVE" "$@" 2>&1 >/dev/null)
  status=$?

  if [ $status -eq 0 ]; then
    printf 'FAIL  %s\n      expected non-zero exit, got 0\n' "$name"
    FAILED=$((FAILED + 1))
    return
  fi
  if ! grep -Fq -- 'usage' <<<"$stderr"; then
    printf 'FAIL  %s\n      expected a usage error, got: %s\n' "$name" "$stderr"
    FAILED=$((FAILED + 1))
    return
  fi
  printf 'ok    %s\n' "$name"
  PASSED=$((PASSED + 1))
}

assert_usage_error 'no arguments at all is a usage error'
assert_usage_error 'a missing NC_VERSION argument is a usage error' 'ionos-dev-v33'
assert_usage_error 'a fourth argument is a usage error' 'ionos-dev-v33' '33.0.6.2' '1' 'extra'

assert_fails 'a non-numeric pipeline IID is rejected' \
  'rc/web-v33-1' '33.0.6.2' 'abc' \
  'IID'

echo "=== Chart version and path composition ==="

assert_ok 'the chart version is 1.<major>.<iid>' \
  'rc/web-v32-1' '32.0.6.1.1062' '1234' \
  'CHART_VERSION=1.32.1234'

# nc-server cannot compose the full version: CI_PIPELINE_IID does not exist until
# the downstream pipeline starts, so it posts the major downstream instead.
assert_absent 'no chart version is invented when no IID is known yet' \
  'rc/web-v32-1' '32.0.6.1.1062' '' \
  'CHART_VERSION'

# A version with no path would not identify one place to publish to.
assert_absent 'a dev lane gets no chart version even when an IID is known' \
  'ionos-dev-v33' '33.0.6.2' '7' \
  'CHART_VERSION'

assert_absent 'a merge-request branch gets no chart version even when an IID is known' \
  'feature/HDNEXT-2144-derive-coordinate' '33.0.6.2' '7' \
  'CHART_VERSION'

# Uniqueness comes from the path, never from the version. Nothing downstream may
# rely on a chart version alone identifying a chart.
assert_ok 'two trains on one major share a version — first train' \
  'rc/web-v31-1' '31.0.6.2' '77' \
  'CHART_VERSION=1.31.77;CHART_PATH=helm/hidrive-next-web-v31-1'

assert_ok 'two trains on one major share a version — second train, different path' \
  'rc/web-v31-2' '31.0.6.2' '77' \
  'CHART_VERSION=1.31.77;CHART_PATH=helm/hidrive-next-web-v31-2'

# The chart name never varies: helm.sh/chart is emitted from it into immutable
# Deployment selectors, so the train goes in the path and the name stays put.
assert_ok 'the chart name is always hidrive-next' \
  'rc/web-v33-1' '33.0.6.2' '990' \
  'CHART_NAME=hidrive-next'

assert_ok 'the legacy train keeps the same chart name' \
  'rc/web-3.5' '30.0.7.2' '990' \
  'CHART_NAME=hidrive-next'

# 1.30.* through 1.33.* all sort above the legacy 1.7.* line, because semver
# compares the minor numerically. The scheme relies on this deliberately.
assert_ok 'a v30 chart version sorts above the legacy 1.7.x line' \
  'rc/web-3.5' '30.0.7.2' '1' \
  'CHART_VERSION=1.30.1'

echo
if [ $FAILED -ne 0 ]; then
  printf '%s passed, %s FAILED\n' "$PASSED" "$FAILED"
  exit 1
fi
printf 'all %s cases passed\n' "$PASSED"
