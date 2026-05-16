# kaju-blog テーマ — セットアップ

静的サイト `kaju_blog_vite/app/` から移植済み。

## 管理画面でやること

1. **外観 → テーマ** で Kaju Blog が有効か確認
2. **設定 → パーマリンク** →「投稿名」→ **変更を保存**（CPT `/records/` 用。未実施だと下層がすべて 404）
3. 固定ページ `front` / `profile` / `contact` 等は **テーマ初回読み込みで自動作成** される（`inc/setup.php`）。無い場合はテーマを一度無効化→再有効化
4. **栽培記録** で記事を追加（果樹タクソノミーを1つ付与）
5. **作業メモ** で記事を追加（一覧は固定ページ `/memo/`、詳細は `/memo/{slug}/`）。サンプル4件はテーマ初回読み込みで自動投入
6. **ACF** — 栽培記録のフィールドはテーマ `inc/acf-record-fields.php` で自動登録（セクション1〜10・タブ付き）。旧 JSON を DB に残している場合は ACF 画面で重複グループを無効化
7. 栽培記録の編集画面に **公開ページプレビュー**（iframe）と **入力ガイド** が表示されます
8. **Contact Form 7** でフォーム作成（お名前・メール・種別・内容・URL任意・同意チェック）

## 開発

```bash
./up_dev.sh
# WordPress http://localhost:8080
# Vite http://localhost:5173
```

SCSS/JS は `frontend/src/` を編集。

### CSS が当たらないとき

`wp-config.php` の `KAJU_BLOG_VITE_STRATEGY` を確認する。

| 値 | 動作 |
|----|------|
| **auto**（推奨） | `assets/.vite/manifest.json` があればビルド済み CSS を読む。**Vite を止めていても表示される** |
| **dev** | 常に `localhost:5173` の Vite から読む。**Vite コンテナ必須**。止めると CSS なし |
| **prod** | 常にビルド済み assets |

SCSS を変えたら:

```bash
docker compose run --rm vite npm run build
```

HMR で即反映したいときだけ `KAJU_BLOG_VITE_STRATEGY` を `dev` にし、`vite` コンテナを起動する。

## URL

| 種別 | URL |
|------|-----|
| トップ | `/` |
| 栽培記録一覧 | `/records/` |
| 記事 | `/records/{slug}/` |
| 果樹絞り込み | `/records/fruit/{slug}/` |
| プロフィール | `/profile/` |
| お問い合わせ | `/contact/` |
| 作業メモ一覧 | `/memo/` |
| 作業メモ | `/memo/{slug}/` |

## 画像

`themes/kaju-blog/img/` に静的 `app/img/` をコピー済み。PHP では `kaju_blog_asset_uri( 'img/...' )` を使用。
