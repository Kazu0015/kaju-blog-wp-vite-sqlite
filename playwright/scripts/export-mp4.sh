#!/usr/bin/env bash
# test-results 内の最新 video.webm を MP4（H.264）に変換する
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

WEBM="$(find test-results -name video.webm -type f 2>/dev/null | while read -r f; do
  stat -f '%m %N' "$f" 2>/dev/null || stat -c '%Y %n' "$f"
done | sort -rn | head -1 | cut -d' ' -f2-)"

if [[ -z "${WEBM}" || ! -f "${WEBM}" ]]; then
  echo "video.webm が見つかりません。先に npm test を実行してください。" >&2
  exit 1
fi

FFMPEG="${FFMPEG:-}"
if [[ -z "${FFMPEG}" ]] && command -v ffmpeg >/dev/null 2>&1; then
  if ffmpeg -hide_banner -encoders 2>/dev/null | grep -q libx264; then
    FFMPEG="ffmpeg"
  fi
fi

if [[ -z "${FFMPEG}" ]]; then
  echo "MP4 変換には H.264 対応の ffmpeg が必要です。" >&2
  echo "  brew install ffmpeg" >&2
  echo "または FFMPEG=/path/to/ffmpeg npm run export:mp4" >&2
  exit 1
fi

mkdir -p output
OUT="${ROOT}/output/site-tour.mp4"

"${FFMPEG}" -y -i "${WEBM}" \
  -c:v libx264 \
  -pix_fmt yuv420p \
  -crf 18 \
  -preset slow \
  -movflags +faststart \
  "${OUT}"

echo "MP4: ${OUT}"
