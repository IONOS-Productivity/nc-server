#!/bin/bash

# SPDX-FileCopyrightText: 2026 STRATO AG
# SPDX-License-Identifier: AGPL-3.0-or-later

# Derive the publish coordinate for one build.
#
# Given a git ref and the NC_VERSION that was actually built, emit the tuple that
# says where this build belongs: its lane, its train, its Nextcloud major, the
# downstream branch to trigger, and the chart path and version to publish under.
# Emit nothing and exit non-zero if the inputs do not describe exactly one place.
#
# Usage:
#   derive-coordinate.sh <ref> <nc-version> [pipeline-iid]
#
# Output is KEY=value lines on stdout, suitable for appending to $GITHUB_OUTPUT,
# $GITHUB_ENV or a GitLab dotenv artifact. Reasons for refusing go to stderr.
#
#   LANE_TYPE             dev | train | trunk | merge-request
#   LANE                  the lane, empty for trunk and merge-request
#   TRAIN                 the release train, empty unless LANE_TYPE=train
#   NC_MAJOR              Nextcloud major, parsed from NC_VERSION
#   DOWNSTREAM_REF        branch to trigger in the image and helm repos
#   PUBLISHES_CHART       true | false
#   CHART_NAME            always hidrive-next
#   CHART_PATH            OCI repository path, empty when PUBLISHES_CHART=false
#   CHART_VERSION         1.<major>.<iid>, only when publishing and an IID is known
#
# Whether a lane *packages* a chart without pushing it is policy that keys off
# LANE_TYPE, and belongs to the helm pipeline rather than here.
#
# Two properties this script exists to hold:
#
#   The major comes from the artifact, never from the branch name. Where a branch
#   name *declares* a major, that declaration is an assertion checked against the
#   artifact and the build fails on disagreement. This is why legacy rc/web-3.5
#   needs no rename: it builds 30.x, parses to v30, and routes to the v30 lane.
#
#   Every guard fails closed. An unrecognised ref, an empty or malformed
#   NC_VERSION, or a declared major that disagrees with the artifact stops the
#   pipeline. There is no default lane to fall back to.
#
# The pipeline IID is optional because CI_PIPELINE_IID does not exist until the
# downstream pipeline starts: nc-server calls this without one and posts the lane,
# train and major downstream as trigger variables, and the chart version is
# composed where the IID is known. This script is the authority for the
# derivation itself, not a file shared between the two repos.
#
# Ref vocabulary note: `main` is the trunk as it exists in the image and helm
# repos. nc-server has no `main` branch — its refs are the ionos-dev* and rc/*
# lanes below — so the trunk arm is reached only when the derivation runs
# downstream.

set -e
set -u
set -o pipefail

# The chart name may never vary. Its common labels emit helm.sh/chart, which is
# included in the spec.selector.matchLabels of Deployments, and Deployment
# selectors are immutable in Kubernetes — renaming the chart makes helm upgrade
# fail and forces delete-and-recreate of every Deployment. The train identifier
# goes in the path instead.
readonly CHART_NAME="hidrive-next"

# The nextcloud-dev/helm namespace is shared — the chart pulls its imaginary
# dependency from the same path it pushes to, and the namespace also hosts the
# nextcloud-workspace charts — so path segments are product-qualified.
readonly CHART_PATH_PREFIX="helm/${CHART_NAME}"

die() {
  echo "derive-coordinate: $1" >&2
  exit 1
}

if [ "$#" -lt 2 ] || [ "$#" -gt 3 ]; then
  die "usage: derive-coordinate.sh <ref> <nc-version> [pipeline-iid]"
fi

REF="$1"
NC_VERSION="$2"
PIPELINE_IID="${3:-}"

[ -n "$REF" ] || die "ref is empty; refusing to guess a lane"

# Accept both github.ref_name (bare) and github.ref (fully qualified).
REF="${REF#refs/heads/}"

# --- The major comes from the artifact -------------------------------------

# jq -r on a missing .ncVersion key prints the string "null", which is the shape
# a broken version.json actually reaches the trigger as. Require at least
# <major>.<minor>: a real NC_VERSION always carries dots, so a bare number is
# more likely a truncated read than a version.
if [ -z "$NC_VERSION" ]; then
  die "NC_VERSION is empty; refusing to guess a major"
fi

if ! [[ "$NC_VERSION" =~ ^[0-9]+\.[0-9]+(\.[0-9]+)*$ ]]; then
  die "NC_VERSION '${NC_VERSION}' is not a dotted numeric version; refusing to guess a major"
fi

NC_MAJOR="${NC_VERSION%%.*}"

if [ -n "$PIPELINE_IID" ] && ! [[ "$PIPELINE_IID" =~ ^[0-9]+$ ]]; then
  die "pipeline IID '${PIPELINE_IID}' is not numeric"
fi

# --- The ref says which lane, and may declare a major ----------------------

# DECLARED_MAJOR stays empty when the ref shape carries no major. Those refs need
# no assertion and, under the derived scheme, no rename: */dev/* user branches
# and legacy rc/web-<n> trains both fall here. A version-shaped substring
# elsewhere in a branch name is not a declaration.
DECLARED_MAJOR=""
LANE_TYPE=""
LANE=""
TRAIN=""

if [ "$REF" = "main" ]; then
  # The integration trunk. Not a lane — the vocabulary has exactly two lane
  # types — but it publishes a chart line of its own so the trunk stays
  # continuously exercised rather than being packaged only once a change
  # reaches a lane.
  LANE_TYPE="trunk"

elif [[ "$REF" =~ ^ionos-dev-v([0-9]+)$ ]]; then
  LANE_TYPE="dev"
  DECLARED_MAJOR="${BASH_REMATCH[1]}"

elif [ "$REF" = "ionos-dev" ]; then
  # The unsuffixed dev branch predates the per-major lanes and still builds. It
  # declares no major, so it takes one from the artifact like any user dev
  # branch. Retiring it is a later step; failing it closed here would break the
  # live dev build before that step arrives.
  LANE_TYPE="dev"

elif [[ "$REF" =~ ^(feature|renovate)/.+$ ]]; then
  # Checked but never published: these lint, template and package only.
  # Matched before the */dev/* shape below, which would otherwise swallow
  # feature/dev/... and renovate/dev/... into a dev lane and trigger downstream.
  LANE_TYPE="merge-request"

elif [[ "$REF" =~ ^[^/]+/dev/.+$ ]]; then
  # A user dev branch. Carries no major and no longer needs to.
  LANE_TYPE="dev"

elif [[ "$REF" =~ ^rc/(web-v([0-9]+)-[0-9]+)$ ]]; then
  LANE_TYPE="train"
  TRAIN="${BASH_REMATCH[1]}"
  DECLARED_MAJOR="${BASH_REMATCH[2]}"

elif [[ "$REF" =~ ^rc/(web-[0-9]+(\.[0-9]+)?)$ ]]; then
  # Legacy naming era — rc/web-3.5, rc/web-5. The trailing number is a release
  # counter, not a major, so this shape declares nothing. Both eras are accepted
  # indefinitely; this is not a transition window.
  LANE_TYPE="train"
  TRAIN="${BASH_REMATCH[1]}"

else
  # No default lane. The retired stable axis (ionos-stable*, ionos-dev with no
  # major) and anything else unrecognised stop here.
  die "unrecognised ref '${REF}'; refusing to fall back to a default lane"
fi

# --- The declaration is an assertion, not a source of truth ----------------

if [ -n "$DECLARED_MAJOR" ] && [ "$DECLARED_MAJOR" != "$NC_MAJOR" ]; then
  die "ref '${REF}' declares major ${DECLARED_MAJOR} but NC_VERSION '${NC_VERSION}' is major ${NC_MAJOR}"
fi

# --- Compose the rest ------------------------------------------------------

PUBLISHES_CHART="false"
CHART_PATH=""
DOWNSTREAM_REF=""

case "$LANE_TYPE" in
  dev)
    # Dev installs from the working tree with helm upgrade --install, so it has a
    # downstream branch — chart source, values and image tag all match the major
    # under test — but no chart path. It publishes nothing, by design.
    LANE="dev-v${NC_MAJOR}"
    DOWNSTREAM_REF="$LANE"
    ;;
  train)
    LANE="rc/${TRAIN}"
    DOWNSTREAM_REF="$LANE"
    PUBLISHES_CHART="true"
    # Uniqueness of a published chart comes from this path, never from the
    # version: two trains on the same major emit the same version.
    CHART_PATH="${CHART_PATH_PREFIX}-${TRAIN}"
    ;;
  trunk)
    DOWNSTREAM_REF="main"
    PUBLISHES_CHART="true"
    CHART_PATH="${CHART_PATH_PREFIX}-main"
    ;;
  merge-request)
    # Nothing to trigger: a merge request is checked, never delivered.
    ;;
  *)
    # Unreachable unless a lane type is added above without a composition arm.
    # Fail closed rather than emit a half-built coordinate.
    die "no coordinate composition for lane type '${LANE_TYPE}'"
    ;;
esac

echo "LANE_TYPE=${LANE_TYPE}"
echo "LANE=${LANE}"
echo "TRAIN=${TRAIN}"
echo "NC_MAJOR=${NC_MAJOR}"
echo "DOWNSTREAM_REF=${DOWNSTREAM_REF}"
echo "PUBLISHES_CHART=${PUBLISHES_CHART}"
echo "CHART_NAME=${CHART_NAME}"
echo "CHART_PATH=${CHART_PATH}"

# Only a lane that publishes gets a version, and only once the IID is known. A
# version without a path would not identify one place to publish to.
#
# 1.<major>.<iid> sorts above the legacy 1.7.* line for every major from 30 up,
# because semver compares the minor numerically. That is relied on deliberately.
if [ "$PUBLISHES_CHART" = "true" ] && [ -n "$PIPELINE_IID" ]; then
  echo "CHART_VERSION=1.${NC_MAJOR}.${PIPELINE_IID}"
fi
