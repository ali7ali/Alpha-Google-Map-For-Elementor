#!/usr/bin/env bash
set -euo pipefail

PLUGIN_SLUG="alpha-google-map-for-elementor"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="${ROOT_DIR}/build"
PACKAGE_DIR="${BUILD_DIR}/${PLUGIN_SLUG}"
ZIP_FILE="${BUILD_DIR}/${PLUGIN_SLUG}.zip"

rm -rf "${PACKAGE_DIR}" "${ZIP_FILE}"
mkdir -p "${PACKAGE_DIR}"

rsync -a "${ROOT_DIR}/" "${PACKAGE_DIR}/" \
	--exclude='.git' \
	--exclude='.github' \
	--exclude='.editorconfig' \
	--exclude='.gitignore' \
	--exclude='build' \
	--exclude='scripts' \
	--exclude='vendor' \
	--exclude='node_modules' \
	--exclude='composer.json' \
	--exclude='composer.lock' \
	--exclude='phpcs.xml.dist'

( cd "${BUILD_DIR}" && zip -qr "${PLUGIN_SLUG}.zip" "${PLUGIN_SLUG}" )

echo "Built ${ZIP_FILE}"
