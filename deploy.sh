#!/usr/bin/env bash
# deploy.sh — trigger the GitHub Actions deploy workflow for haunted-tech-wptheme
# Requires: gh CLI (https://cli.github.com) — run `gh auth login` once first.

set -e

REPO="codemonkeyarts/haunted-tech-wptheme"
WORKFLOW="deploy.yml"
BRANCH="main"

echo "Triggering deploy of ${BRANCH} → codalanguez.com..."
gh workflow run "$WORKFLOW" --repo "$REPO" --ref "$BRANCH"

echo "Waiting for run to start..."
sleep 4

# Tail the latest run so you can see progress
RUN_ID=$(gh run list --repo "$REPO" --workflow "$WORKFLOW" --limit 1 --json databaseId -q '.[0].databaseId')
echo "Run #${RUN_ID} — streaming logs:"
gh run watch "$RUN_ID" --repo "$REPO"
