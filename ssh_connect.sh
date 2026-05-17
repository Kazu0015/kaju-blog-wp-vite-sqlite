#!/bin/bash

# SSH接続スクリプト
# 使用方法: ./ssh_connect.sh [秘密鍵のパス] [ユーザー名] [リモートパス]

# デフォルト値（deploy.sh と揃える）
DEFAULT_KEY_PATH="$HOME/.ssh/kagoya_20251117144716.key"
DEFAULT_USER="root"
DEFAULT_REMOTE_PATH="/home/kazu/docker_doc/kaju_blog"
REMOTE_HOST="133.18.121.105"

# 引数の処理
KEY_PATH="${1:-$DEFAULT_KEY_PATH}"
USER="${2:-$DEFAULT_USER}"
REMOTE_PATH="${3:-$DEFAULT_REMOTE_PATH}"

# 秘密鍵の存在確認
if [ ! -f "$KEY_PATH" ]; then
    echo "エラー: 秘密鍵 $KEY_PATH が見つかりません"
    exit 1
fi

# 秘密鍵のパーミッション確認
if [ "$(stat -c %a "$KEY_PATH" 2>/dev/null || stat -f %A "$KEY_PATH" 2>/dev/null)" != "600" ]; then
    echo "警告: 秘密鍵のパーミッションが600ではありません。セキュリティのため、600に設定することを推奨します。"
    echo "実行: chmod 600 $KEY_PATH"
    echo ""
fi

echo "=========================================="
echo "SSH接続情報"
echo "=========================================="
echo "ホスト: $USER@$REMOTE_HOST"
echo "リモートパス: $REMOTE_PATH"
echo "秘密鍵: $KEY_PATH"
echo "=========================================="
echo ""

if [ -n "$REMOTE_PATH" ]; then
	ssh -t -i "$KEY_PATH" "$USER@$REMOTE_HOST" "cd $REMOTE_PATH && exec \$SHELL -l"
else
	ssh -i "$KEY_PATH" "$USER@$REMOTE_HOST"
fi
