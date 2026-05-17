#!/bin/bash

# 本番サーバーへ rsync で差分転送する（SSH での compose 起動は行わない）
#
# 使用方法: プロジェクトルートの .env に DEPLOY_TARGET / DEPLOY_SSH_KEY を設定し、引数なしで実行
#   ./deploy-rsync.sh
#
# .env に記載する変数:
#   DEPLOY_TARGET   … rsync 先（例: user@host:/home/kazu/docker_doc/kaju_blog）
#   DEPLOY_SSH_KEY  … 秘密鍵のパス
#   SKIP_BUILD=1           … frontend の npm run build をスキップ
#   SKIP_THEME_ACTIVATE=1  … 転送後の有効テーマ確認・切替をスキップ
#   DEPLOY_ENV_FILE        … リモートへ .env.prod として送るローカルファイルのパス（任意）
#   DEPLOY_WP_CONTAINER    … WordPress コンテナ名（既定: kaju_blog_wordpress_prod）

REPO_ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$REPO_ROOT"

if [ $# -gt 0 ]; then
	echo "エラー: コマンドライン引数は使用できません。.env の DEPLOY_TARGET のみで転送先を指定してください。"
	exit 1
fi

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
	(cd frontend && npm run build)
else
	echo "==> SKIP_BUILD=1 のためビルドをスキップします"
fi

MANIFEST="wordpress/wp-content/themes/kaju-blog/assets/.vite/manifest.json"
if [ ! -f "$MANIFEST" ]; then
	echo "エラー: ビルド成果物が見つかりません ($MANIFEST)"
	echo "      frontend で npm run build を実行するか、SKIP_BUILD=1 を外してください。"
	exit 1
fi

echo "=========================================="
echo "rsync 転送情報"
echo "=========================================="
echo "転送先: ${DEPLOY_TARGET}/"
echo "秘密鍵: $KEY_PATH"
echo "=========================================="
echo ""

SSH_HOST="${DEPLOY_TARGET%%:*}"
REMOTE_DIR="${DEPLOY_TARGET#*:}"

echo "==> ルートファイルを転送中（サーバー側の .env.prod 等は削除しない）..."
rsync -avz \
	-e "ssh -i $KEY_PATH" \
	docker-compose.prod.yml \
	Dockerfile \
	docker-entrypoint-sqlite.sh \
	"${DEPLOY_TARGET}/"

echo "==> wordpress/ を差分転送中（--delete で不要ファイルを削除）..."
rsync -avz --delete --no-owner --no-group \
	--exclude='wp-content/database/' \
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

WP_CONTAINER="${DEPLOY_WP_CONTAINER:-kaju_blog_wordpress_prod}"
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

echo "完了。続きは ssh_connect.sh 等で SSH 接続し、デプロイ先で以下を実行してください。"
echo "  cd ${REMOTE_DIR}"
echo "  docker compose -f docker-compose.prod.yml --env-file .env.prod up -d --build"
