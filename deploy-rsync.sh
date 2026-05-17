#!/bin/bash

# 本番サーバーへ rsync で差分転送する（SSH での compose 起動は行わない）
#
# 使用方法:
#   ./deploy-rsync.sh
#   ./deploy-rsync.sh --overwrite_database_sync
#
# .env（初回のみ）: DEPLOY_TARGET / DEPLOY_SSH_KEY
# 環境変数（任意）: SKIP_BUILD=1 / SKIP_THEME_ACTIVATE=1 / DEPLOY_ENV_FILE など

REPO_ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$REPO_ROOT"

SYNC_DATABASE=0
for arg in "$@"; do
	case "$arg" in
		--overwrite_database_sync)
			SYNC_DATABASE=1
			;;
		-h | --help)
			echo "使い方: $0 [--overwrite_database_sync]"
			echo "  --overwrite_database_sync  ローカル SQLite を本番に上書き（uploads 含む）し URL を置換"
			exit 0
			;;
		*)
			echo "エラー: 不明なオプション: $arg"
			echo "使い方: $0 [--overwrite_database_sync]"
			exit 1
			;;
	esac
done

if [ ! -f .env ]; then
	echo "エラー: プロジェクトルートに .env がありません。cp .env.sample .env して設定してください。"
	exit 1
fi

set -a
# shellcheck disable=SC1091
source .env
set +a

if [ -z "${DEPLOY_TARGET:-}" ]; then
	echo "エラー: .env に DEPLOY_TARGET=user@host:/home/kazu/docker_doc/kaju_blog を設定してください。"
	exit 1
fi

if [ -z "${DEPLOY_SSH_KEY:-}" ]; then
	echo "エラー: .env に DEPLOY_SSH_KEY=/path/to/private_key を設定してください。"
	exit 1
fi

KEY_PATH="${DEPLOY_SSH_KEY}"

if [ ! -f "$KEY_PATH" ]; then
	echo "エラー: 秘密鍵 $KEY_PATH が見つかりません"
	exit 1
fi

if [ "$(stat -c %a "$KEY_PATH" 2>/dev/null || stat -f %A "$KEY_PATH" 2>/dev/null)" != "600" ]; then
	echo "警告: 秘密鍵のパーミッションが600ではありません。セキュリティのため、600に設定することを推奨します。"
	echo "実行: chmod 600 $KEY_PATH"
	echo ""
fi

if [ "${SKIP_BUILD:-0}" != "1" ]; then
	echo "==> frontend: npm run build"
	if [ ! -x frontend/node_modules/.bin/vite ]; then
		echo "    （node_modules 未構築のため npm ci を実行）"
		(cd frontend && npm ci)
	fi
	if ! (cd frontend && npm run build); then
		echo "エラー: npm run build に失敗しました。本番へ古い CSS を送らないため中止します。"
		exit 1
	fi
else
	echo "==> SKIP_BUILD=1 のためビルドをスキップします"
fi

MANIFEST="wordpress/wp-content/themes/kaju-blog/assets/.vite/manifest.json"
if [ ! -f "$MANIFEST" ]; then
	echo "エラー: ビルド成果物が見つかりません ($MANIFEST)"
	echo "      frontend で npm run build を実行するか、SKIP_BUILD=1 を外してください。"
	exit 1
fi

# manifest が指す CSS が実在すること（PHP だけ更新され CSS が古い状態を防ぐ）
MANIFEST_CSS_REL="$(MANIFEST="$MANIFEST" node -e '
const fs = require("fs");
const m = JSON.parse(fs.readFileSync(process.env.MANIFEST, "utf8"));
const entry = m["src/js/main.js"];
if (!entry?.css?.[0]) process.exit(2);
process.stdout.write(entry.css[0]);
' 2>/dev/null || true)"
if [ -z "$MANIFEST_CSS_REL" ]; then
	echo "エラー: manifest に CSS エントリがありません ($MANIFEST)"
	exit 1
fi
MANIFEST_CSS_PATH="wordpress/wp-content/themes/kaju-blog/assets/${MANIFEST_CSS_REL}"
if [ ! -f "$MANIFEST_CSS_PATH" ]; then
	echo "エラー: manifest の CSS がディスク上にありません ($MANIFEST_CSS_PATH)"
	echo "      npm run build が正常完了しているか確認してください。"
	exit 1
fi
echo "==> ビルド CSS: ${MANIFEST_CSS_REL}"

echo "=========================================="
echo "rsync 転送情報"
echo "=========================================="
echo "転送先: ${DEPLOY_TARGET}/"
echo "秘密鍵: $KEY_PATH"
echo "=========================================="
echo ""

SSH_HOST="${DEPLOY_TARGET%%:*}"
REMOTE_DIR="${DEPLOY_TARGET#*:}"

LOCAL_DB="wordpress/wp-content/database/.ht.sqlite"
if [ "$SYNC_DATABASE" = "1" ]; then
	if [ ! -f "$LOCAL_DB" ]; then
		echo "エラー: ローカル DB が見つかりません ($LOCAL_DB)"
		exit 1
	fi
	echo "==> 本番 DB をローカルで上書きします（SQLite + uploads）"
fi

WP_HOME_PROD=""
if [ -f .env.prod ]; then
	# shellcheck disable=SC1091
	WP_HOME_PROD="$(grep -E '^WP_HOME=' .env.prod | head -1 | cut -d= -f2- | tr -d '"' | tr -d "'")"
fi
URL_FROM="http://localhost:8080"

if [ "$SYNC_DATABASE" = "1" ]; then
	echo "==> 本番 WordPress コンテナを一時停止（DB 整合性のため）..."
	ssh -i "$KEY_PATH" "$SSH_HOST" "cd ${REMOTE_DIR} && docker compose -f docker-compose.prod.yml --env-file .env.prod stop wordpress 2>/dev/null || true"
fi

echo "==> ルートファイルを転送中（サーバー側の .env.prod 等は削除しない）..."
rsync -avz \
	-e "ssh -i $KEY_PATH" \
	docker-compose.prod.yml \
	Dockerfile \
	docker-entrypoint-sqlite.sh \
	"${DEPLOY_TARGET}/"

echo "==> wordpress/ を差分転送中（--delete で不要ファイルを削除）..."
RSYNC_EXCLUDE=( )
if [ "$SYNC_DATABASE" != "1" ]; then
	RSYNC_EXCLUDE=( --exclude='wp-content/database/' )
	echo "    （database/ は除外。DB も送る場合は --overwrite_database_sync）"
else
	echo "    （database/ を含めて同期）"
fi
# shellcheck disable=SC2068
rsync -avz --delete --no-owner --no-group \
	"${RSYNC_EXCLUDE[@]}" \
	-e "ssh -i $KEY_PATH" \
	wordpress/ \
	"${DEPLOY_TARGET}/wordpress/"

if [ -n "${DEPLOY_ENV_FILE:-}" ]; then
	if [ ! -f "$DEPLOY_ENV_FILE" ]; then
		echo "エラー: DEPLOY_ENV_FILE=$DEPLOY_ENV_FILE が存在しません"
		exit 1
	fi
	echo "==> 環境ファイルを .env.prod として転送"
	rsync -avz -e "ssh -i $KEY_PATH" "$DEPLOY_ENV_FILE" "${DEPLOY_TARGET}/.env.prod"
fi

echo "==> wordpress/ の所有者を www-data に修正..."
ssh -i "$KEY_PATH" "$SSH_HOST" "chown -R www-data:www-data ${REMOTE_DIR}/wordpress"

echo "==> 本番コンテナを起動・更新（docker compose up -d --build）..."
ssh -i "$KEY_PATH" "$SSH_HOST" "cd ${REMOTE_DIR} && docker compose -f docker-compose.prod.yml --env-file .env.prod up -d --build"
sleep 5

WP_CONTAINER="${DEPLOY_WP_CONTAINER:-kaju_blog_wordpress_prod}"

if [ "$SYNC_DATABASE" = "1" ] && [ -n "$WP_HOME_PROD" ]; then
	echo "==> DB 内 URL を置換: ${URL_FROM} => ${WP_HOME_PROD}"
	ssh -i "$KEY_PATH" "$SSH_HOST" bash -s -- "$WP_CONTAINER" "$URL_FROM" "$WP_HOME_PROD" <<'REMOTE_URL'
set -euo pipefail
CONTAINER="$1"
FROM="$2"
TO="$3"
docker exec -e FROM="$FROM" -e TO="$TO" "$CONTAINER" php /var/www/html/wp-content/themes/kaju-blog/bin/replace-site-url.php
REMOTE_URL
elif [ "$SYNC_DATABASE" = "1" ]; then
	echo "警告: WP_HOME が未設定のため URL 置換をスキップしました（.env.prod の WP_HOME を確認）"
fi
WP_THEME_SLUG="${DEPLOY_WP_THEME:-kaju-blog}"

if [ "${SKIP_THEME_ACTIVATE:-0}" != "1" ]; then
	echo "==> 有効テーマを ${WP_THEME_SLUG} に揃える（コンテナ稼働時のみ）..."
	ssh -i "$KEY_PATH" "$SSH_HOST" bash -s -- "$WP_CONTAINER" "$WP_THEME_SLUG" <<'REMOTE_THEME'
set -euo pipefail
CONTAINER="$1"
THEME="$2"
if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
	echo "スキップ: コンテナ ${CONTAINER} が起動していません（compose up 後に再実行するか手動で有効化してください）"
	exit 0
fi
docker exec -e WP_THEME_SLUG="$THEME" "$CONTAINER" php -r '
require "/var/www/html/wp-load.php";
$theme = getenv( "WP_THEME_SLUG" ) ?: "kaju-blog";
if ( get_option( "stylesheet" ) === $theme ) {
	echo "theme already {$theme}\n";
	exit( 0 );
}
if ( ! wp_get_theme( $theme )->exists() ) {
	fwrite( STDERR, "theme not found: {$theme}\n" );
	exit( 1 );
}
switch_theme( $theme );
echo "activated {$theme}\n";
'
REMOTE_THEME
fi

echo "完了。"
