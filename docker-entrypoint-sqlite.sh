#!/usr/bin/env bash
set -euo pipefail

# ./wordpress をバインドするとイメージ内の SQLite 用ファイルが見えなくなるため、起動時に戻す
WP_CONTENT="/var/www/html/wp-content"
PLUGIN_DIR="${WP_CONTENT}/plugins/sqlite-database-integration"

if [[ ! -f "${WP_CONTENT}/db.php" ]] || [[ ! -d "${PLUGIN_DIR}" ]] || [[ -z "$(ls -A "${PLUGIN_DIR}" 2>/dev/null || true)" ]]; then
	mkdir -p "${WP_CONTENT}/plugins"
	rm -rf "${PLUGIN_DIR}"
	cp -a /opt/wp-sqlite/sqlite-database-integration "${WP_CONTENT}/plugins/"
	cp /opt/wp-sqlite/db.php "${WP_CONTENT}/db.php"
fi

exec /usr/local/bin/docker-entrypoint.sh "$@"
