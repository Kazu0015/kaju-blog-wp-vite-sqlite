# WordPress × SQLite × Vite 開発テンプレート

MySQL 不要・Vite の HMR（ホットリロード）付きで WordPress 開発を始めるための Docker テンプレートです。

---

## はじめに — テーマ `kaju-blog`

このプロジェクトではテーマスラッグ **`kaju-blog`** を使っています。

| 種類 | 名前 |
|------|------|
| テーマディレクトリ | `wordpress/wp-content/themes/kaju-blog/` |
| PHP 関数プレフィックス | `kaju_blog_`（ハイフンは PHP 識別子に使えないため） |
| wp-config 定数 | `KAJU_BLOG_VITE_*` など |

---

## 概要・仕様

| 項目 | 内容 |
|---|---|
| WordPress | 最新版（`wordpress:latest`） |
| データベース | SQLite（MySQL 不要） |
| フロントエンドビルド | Vite 6 + Sass |
| 実行環境 | Docker（Node.js のローカルインストール不要） |
| WordPress ポート | `http://localhost:8080` |
| Vite ポート | `http://localhost:5173` |

### SQLite を採用する理由

通常の WordPress は MySQL が必要ですが、このテンプレートは SQLite（ファイル型データベース）を使用します。  
コンテナが1つ少なくて済み、セットアップが簡単です。個人ブログや小規模サイトの開発に適しています。

> ⚠️ SQLite は大規模・高トラフィックな本番環境には向きません。その場合は MySQL に切り替えてください。

### Vite を採用する理由

SCSS や JavaScript のファイルを保存するたびに、**ページリロードなしで**ブラウザへ即座に反映されます（HMR：Hot Module Replacement）。

---

## ディレクトリ構成

```
.
├── docker-compose.dev.yml      # ローカル開発用 Compose
├── docker-compose.prod.yml     # 本番（Traefik）用 Compose
├── .env.sample                 # 本番・デプロイ用環境変数の雛形
├── deploy-rsync.sh             # VPS へ rsync デプロイ
├── Dockerfile                  # WordPress + SQLite イメージ
├── docker-entrypoint-sqlite.sh # コンテナ起動時の SQLite セットアップ
├── up_dev.sh / down_dev.sh     # 開発の起動・停止
├── up_prod.sh / down_prod.sh   # 本番の起動・停止（VPS 上）
│
├── frontend/                   # Vite フロントエンド（編集するのはここ）
│   ├── src/
│   │   ├── js/
│   │   │   └── main.js         # JS エントリポイント
│   │   └── scss/
│   │       ├── main.scss       # SCSS エントリポイント
│   │       ├── base/           # 変数など基礎スタイル
│   │       ├── components/     # コンポーネント単位のスタイル
│   │       └── pages/          # ページ固有のスタイル
│   ├── vite.config.js
│   └── package.json
│
└── wordpress/                  # WordPress 本体・テーマ
    ├── wp-config.php           # 設定ファイル（Vite・SQLite の定数あり）
    └── wp-content/
        └── themes/
            └── kaju-blog/        # カスタムテーマ（編集するのはここ）
                ├── functions.php
                ├── index.php
                └── style.css
```

---

## 必要なもの

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) がインストールされていること

Node.js のローカルインストールは**不要**です。npm は Docker コンテナ内で実行されます。

---

## セットアップ・起動手順

### 1. リポジトリをクローン

```bash
git clone <リポジトリURL>
cd <ディレクトリ名>
```

### 2. コンテナを起動

```bash
./up_dev.sh
```

初回は Docker イメージのビルドと npm パッケージのインストールが走るため、数分かかります。

### 3. WordPress の初期設定

ブラウザで `http://localhost:8080` を開き、WordPressのインストール画面を進めます。

- サイトのタイトル、ユーザー名、パスワードを設定するだけです
- データベースの設定は**不要**です（SQLite が自動で使われます）

### 4. テーマを有効化

WordPress 管理画面（`http://localhost:8080/wp-admin`）→「外観」→「テーマ」から **Kaju Blog** を有効化してください。

### 5. 開発開始

`frontend/src/` 以下のファイルを編集すると、ブラウザに即座に反映されます。

```
frontend/src/js/main.js       ← JavaScript を書く
frontend/src/scss/main.scss   ← SCSS を書く（@use でパーシャルを読み込む）
```

---

## 停止・再起動

```bash
# 停止
./down_dev.sh

# 再起動
./up_dev.sh
```

---

## 本番ビルド

開発が完了したら、JS・CSS をビルドしてサーバに配置します。

### アセットをビルド

```bash
docker compose -f docker-compose.dev.yml run --rm vite npm run build
```

`wordpress/wp-content/themes/kaju-blog/assets/` にビルド済みファイルが生成されます。

本番コンテナでは `docker-compose.prod.yml` の `WORDPRESS_CONFIG_EXTRA` により `KAJU_BLOG_VITE_STRATEGY` が `prod` になり、ビルド済みアセットのみを読み込みます。

| 値 | 動作 |
|---|---|
| `'auto'`（ローカル既定） | manifest があれば本番、なければ dev サーバ |
| `'prod'`（本番コンテナ） | 常にビルド済みファイルを使用 |

---

## VPS 本番デプロイ（Traefik）

本番 URL: **https://kaju-blog.webcerto.net/**

### 1. 環境ファイルを用意（初回のみ）

```bash
cp .env.sample .env          # デプロイ用（DEPLOY_TARGET 等）
cp .env.sample .env.prod     # VPS 上の compose 用（DOMAIN / WP_HOME）
```

`.env` の `DEPLOY_TARGET` と `DEPLOY_SSH_KEY` を編集してください。  
`.env.prod` の `DOMAIN` / `WP_HOME` は `.env.sample` の既定値（`kaju-blog.webcerto.net`）のままで問題ありません。

VPS へ `.env.prod` も送る場合:

```bash
# .env に追記
DEPLOY_ENV_FILE=.env.prod
```

### 2. ローカルから rsync デプロイ

```bash
./deploy-rsync.sh
```

ビルド → Docker 関連ファイルと `wordpress/` を転送します（`wp-content/database/` は除外し、サーバー上の DB を保持）。  
コンテナが起動中なら、有効テーマを `kaju-blog` に自動で揃えます（旧 DB で `kaju` のまま残っていると真っ白画面になるため）。

### 3. VPS でコンテナ起動

```bash
./ssh_connect.sh
cd /home/kazu/docker_doc/kaju_blog
docker compose -f docker-compose.prod.yml --env-file .env.prod up -d --build
```

停止する場合: `docker compose -f docker-compose.prod.yml --env-file .env.prod down`

> 同一ドメインで旧 `kaju_blog_vite_wp` が稼働中の場合は、先に旧コンテナを停止してから起動してください。

本番コンテナ（`KAJU_BLOG_VITE_STRATEGY=prod`）では、mu-plugin により次が自動で無効になります。

- **Show Current Template**（管理バーの「テンプレート: …」）
- **SQLite** 管理バーの「Database: SQLite」表示

---


## テーマのカスタマイズ

### SCSS の追加

ファイルを作って `main.scss` から `@use` で読み込むだけです。

```scss
/* frontend/src/scss/main.scss */
@use 'base/variables' as *;
@use 'components/vite-ping';
@use 'components/your-new-component'; /* ← 追加 */
```

### テンプレートファイルの追加

`wordpress/wp-content/themes/kaju-blog/` に WordPress テンプレートファイルを追加していきます。

```
themes/kaju-blog/
├── index.php       ← 全ページ共通（既存）
├── single.php      ← 投稿ページ（追加例）
├── archive.php     ← 一覧ページ（追加例）
└── page.php        ← 固定ページ（追加例）
```

---

## 注意点

### Secret Keys を再生成すること

`wordpress/wp-config.php` の 78〜85 行目にある Secret Keys はテンプレートのデフォルト値です。  
本番環境では必ず以下のサービスで新しいキーを生成して差し替えてください。

[https://api.wordpress.org/secret-key/1.1/salt/](https://api.wordpress.org/secret-key/1.1/salt/)

### SQLite のデータは `wordpress/wp-content/database/` に保存される

`.gitignore` でバージョン管理から除外されています。データのバックアップは手動で行ってください。

### アップロードファイルも除外されている

`wordpress/wp-content/uploads/` も `.gitignore` 対象です。画像などは別途バックアップしてください。

### ビルド済みアセットは除外されている

`wordpress/wp-content/themes/kaju-blog/assets/` も `.gitignore` 対象です。  
本番サーバへのデプロイ時は `npm run build` を CI/CD で実行するか、手動でファイルをアップロードしてください。
