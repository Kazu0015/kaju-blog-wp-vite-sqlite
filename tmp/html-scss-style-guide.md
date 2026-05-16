# HTML/SCSS Coding Style Guide

このスタイルガイドは、`~/html_doc/tmp/sample/01` から `05` のHTML/SCSSサンプルをもとに、今後AIへHTML/SCSSコーディングを依頼するときの基準として使う。

## サンプル参照時の最重要ルール

サンプル01から05は、要素クラスをシングルハイフンでつなぐ旧来のBEMライト記法を使っている。

```text
header-logo
top-kv-inner
c-post-thumbnail
contact-form-item
```

本ガイドではこの命名を採用しない。新規実装では、サンプルからHTML構造、SCSS分割、余白感、カード・見出し・ボタンの寸法感は参考にしてよいが、class命名は必ずBEM形式へ変換する。

変換ルール:

- `block-element` は `block__element` に変換する。
- `block-element-sub` は `block__element-sub` または `block__sub-element` に変換する。
- `__` は1つのclass内で1回だけ使う。
- `block__element__sub` のような多段elementは禁止する。
- element内の単語はハイフンでつなぐ。
- modifierは `block--modifier` または `block__element--modifier` とする。

変換例:

```text
.header-logo          -> .header__logo
.header-list          -> .header__list
.header-item          -> .header__item
.top-kv-inner         -> .top-kv__inner
.top-kv-copy-text     -> .top-kv__copy-text
.c-post-thumbnail     -> .c-post__thumbnail
.contact-form-item    -> .contact-form__item
.footer-nav-list      -> .footer__nav-list または .footer-nav__list
```

`.footer-nav-list` のようなケースは、`footer` の要素として扱うなら `.footer__nav-list`、`footer-nav` を独立したblockとして扱うなら `.footer-nav__list` とする。構造と再利用性で判断する。

サンプルには `aria-*` 属性、`js-` 接頭辞、`is-` 状態class、`:focus-visible` がほとんど出てこない。これはサンプルが静的デモ中心のためであり、新規実装では本ガイドのAccessibilityとJavaScript Hooksのルールを適用する。

## サンプルから参考にするもの・しないもの

| 観点 | 方針 |
| --- | --- |
| `global/`, `foundation/`, `layout/`, `component/`, `page/`, `utility/` の分割 | 参考にする |
| `style.scss` の `@use` 順序 | 参考にする |
| `mq` mixinとbreakpoints map | 参考にする |
| CSS変数による色・フォント・幅のトークン化 | 参考にする |
| SCSS見出しコメント形式 | 参考にする |
| 余白感、セクション間隔、カード・ボタン・見出しの寸法感 | 参考にする |
| HTML headテンプレート | 参考にする |
| class命名のシングルハイフン要素記法 | 参考にしない。必ずBEMへ変換する |
| `aria-*`, `js-`, `is-`, `:focus-visible` の省略 | 参考にしない。新規実装では必要に応じて採用する |

## 基本方針

- HTMLはセマンティックに書く。
- SCSSはBEM形式を必須とする。
- class名は `block__element--modifier` を基本形にする。
- サンプルのシングルハイフン要素記法はそのまま使わず、必ずBEM形式へ変換する。
- SCSSはコンポーネント単位、レイアウト単位、ページ単位で分ける。
- 余白は8pxベースで設計する。
- 色、フォント、幅、z-indexなどの共通値はCSS変数またはSCSS変数から使う。
- レスポンシブはmobile firstで書く。
- 装飾用のdivは増やしすぎず、意味のあるHTML構造を優先する。
- カード、見出し、ボタンの余白感はサンプルの落ち着いた密度に寄せる。

## ディレクトリ構成

SCSSは次のレイヤーに分ける。

```text
scss/
  style.scss
  global/
    _index.scss
    _color.scss
    _font.scss
    _breakpoints.scss
    _content-width.scss
    _z-index.scss
    _mixin.scss
  foundation/
    _index.scss
    _reset.scss
    _base.scss
  layout/
    _index.scss
    _container.scss
    _header.scss
    _footer.scss
    _section.scss
  component/
    _index.scss
    _button.scss
    _title.scss
    _post.scss
    _posts.scss
    _product.scss
    _products.scss
    form/
      _text.scss
      _textarea.scss
      _checkbox.scss
  page/
    _index.scss
    top/
      _top-kv.scss
      _top-xxx.scss
  utility/
    _index.scss
    _utility.scss
```

`style.scss` は読み込みだけにする。

```scss
@use "foundation";
@use "utility";
@use "component";
@use "layout";
@use "page";
```

`global/_index.scss` は次の `@forward` 順を推奨する。

```scss
@forward "breakpoints";
@forward "color";
@forward "content-width";
@forward "font";
@forward "mixin";
@forward "z-index";
```

任意ファイルを作らない場合は、対応する `@forward` 行も書かない。

条件付きファイル:

| ファイル | 必須/任意 | 追加条件 |
| --- | --- | --- |
| `global/_breakpoints.scss` | 必須 | media query mixinを管理する |
| `global/_color.scss` | 必須 | 色トークンを管理する |
| `global/_content-width.scss` | 必須 | コンテンツ幅を管理する |
| `global/_font.scss` | 必須 | フォントトークンを管理する |
| `global/_mixin.scss` | 任意 | 共通mixinが必要な場合に追加する |
| `global/_z-index.scss` | 任意 | z-indexの競合が複数箇所で起きる場合に追加する |
| `layout/_section.scss` | 任意 | `.l-section` でセクション余白を統一する場合に追加する |
| `layout/_section-body.scss` | 任意 | セクション見出しと本文の間隔を共通化する場合に追加する |
| `layout/header/_header-*.scss` | 任意 | headerが複数領域に分かれて複雑な場合のみ分割する |

## Class Naming

BEM形式を必須にする。block、element、modifierの責務を混ぜない。

```text
.block
.block__element
.block--modifier
.block__element--modifier
```

役割が明確な共通classには接頭辞を付ける。

- `l-`: レイアウト専用。幅、配置、セクション余白など。
- `c-`: 再利用できるコンポーネント。
- `u-`: 単一目的のユーティリティ。
- ページ固有: `top-kv`, `top-feature`, `single-contents` のようにページ名またはセクション名をblockにする。

良い例:

```html
<section class="top-feature l-section">
    <div class="l-container">
        <h2 class="c-title-level2 c-title-level2--center">特徴</h2>

        <div class="l-section-body">
            <article class="top-feature__item">
                <div class="top-feature__thumbnail">
                    <img src="img/pic-feature.jpg" width="640" height="360" alt="" loading="lazy" />
                </div>
                <div class="top-feature__body">
                    <h3 class="top-feature__title">タイトル</h3>
                    <p class="top-feature__text">本文が入ります。</p>
                </div>
            </article>
        </div>
    </div>
</section>
```

避ける例:

```html
<div class="box">
    <div class="box-title red big">タイトル</div>
</div>
```

## HTML

- `header`, `main`, `section`, `article`, `nav`, `footer`, `time`, `picture`, `form`, `label` など、意味に合う要素を使う。
- 見出し階層はページ構造に合わせる。見た目の都合で階層を飛ばさない。
- 画像には `width` と `height` を入れる。
- 遅延読み込みしてよい画像には `loading="lazy"` を付ける。
- altは意味のある画像には具体的に書き、装飾画像は空にする。
- 外部リンクには `target="_blank"` と `rel="noopener noreferrer"` を付ける。
- フォームでは `label` と入力要素を対応させる。
- テキストの改行制御が必要な場合は、サンプル同様に `span` を使って行単位を調整してよい。
- 装飾だけのラッパーを増やす前に、擬似要素や既存要素で表現できるか検討する。

## Head Template

HTMLの`head`は、案件に合わせて文言を調整しつつ、原則として次の構成を入れる。

```html
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>サイト名 - ページ説明 -</title>
    <meta name="description" content="ページの説明文を入れる。" />
    <meta name="format-detection" content="telephone=no" />

    <!-- favicon/webclipicon -->
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="apple-touch-icon" href="webclip.png" />

    <!-- ogp -->
    <meta property="og:site_name" content="サイト名" />
    <meta property="og:url" content="URL(絶対パス)" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="サイト名 - ページ説明 -" />
    <meta property="og:description" content="ページの説明文を入れる。" />
    <meta property="og:image" content="URL(絶対パス)" />
    <meta property="og:locale" content="ja_JP" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:description" content="ページの説明文を入れる。" />
    <meta name="twitter:image:src" content="URL(絶対パス)" />

    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet" />

    <!-- css -->
    <link rel="stylesheet" href="css/style.css" />

    <!-- js -->
    <script src="js/main.js" defer></script>
</head>
```

## Header / Footer

- header内のグローバルナビゲーションは、原則として `nav > ul > li > a` で構成する。
- footer内のナビゲーション、サイトマップ、SNSリンク一覧も、複数リンクの集合であれば `ul > li > a` を使う。
- ロゴは `a > img` で組む。トップページでは `<h1 class="header__logo">` で囲み、下層ページでは `<div class="header__logo">` を使う。下層ページの`h1`はページ主見出しに使う。
- 単独のCTAボタン、カートリンク、オンラインショップリンクなど、リストではない単体リンクは無理に `ul/li` にしなくてよい。
- ページ内に `nav` が2つ以上ある場合は、`aria-label` を必須にする。1つだけの場合も、役割が曖昧なら付ける。
- class名はBEM形式にし、headerなら `.header__nav`, `.header__list`, `.header__item`, `.header__link`、footerなら `.footer__nav`, `.footer__list`, `.footer__item`, `.footer__link` を基本にする。
- サンプルでは `.header-logo` などの旧記法が使われているが、新規実装では `.header__logo` のようにBEM形式へ変換する。

headerの例:

```html
<header class="header">
    <h1 class="header__logo">
        <a href="index.html">
            <img src="img/logo.svg" width="120" height="32" alt="サイト名" />
        </a>
    </h1>

    <nav class="header__nav" aria-label="グローバルナビゲーション">
        <ul class="header__list">
            <li class="header__item">
                <a href="about.html" class="header__link">about</a>
            </li>
            <li class="header__item">
                <a href="service.html" class="header__link">service</a>
            </li>
        </ul>
    </nav>
</header>
```

footerの例:

```html
<footer class="footer">
    <nav class="footer__nav" aria-label="フッターナビゲーション">
        <ul class="footer__list">
            <li class="footer__item">
                <a href="privacy.html" class="footer__link">privacy policy</a>
            </li>
            <li class="footer__item">
                <a href="contact.html" class="footer__link">contact</a>
            </li>
        </ul>
    </nav>

    <small class="footer__copyright">&copy; サイト名</small>
</footer>
```

## File And Asset Organization

ファイルは用途ごとに配置場所を分ける。新規作成時は、既存プロジェクトの構成を優先しつつ、基本は以下の構成に寄せる。

```text
css/
  style.css
img/
  bg-top-kv.jpg
  bg-top-kv-sp.jpg
  pic-top-feature.jpg
  icon-cart.svg
  logo.svg
  ogp.png
  webclip.png
  favicon.ico
js/
  main.js
scss/
  global/
  foundation/
  layout/
  component/
  page/
  utility/
```

- 画像は原則として `img/` 直下にフラットに配置する。サンプル01から05はこの方式を採用している。
- 画像の用途は `bg-`, `pic-`, `icon-`, `logo`, `thumb-` などの接頭辞で区別する。
- 画像点数が多い大規模案件のみ、`img/common/`, `img/top/`, `img/{page-name}/`, `img/icon/`, `img/logo/` のサブディレクトリ分割を任意で採用してよい。
- OGP画像、favicon、webclipは既存構成に合わせる。新規作成時はルート直下または `img/` 直下に置く。
- SCSSは `scss/` 配下で `global`, `foundation`, `layout`, `component`, `page`, `utility` に分ける。
- コンパイル後のCSSは `css/style.css` に出力する。
- JavaScriptは原則 `js/main.js` にまとめる。規模が大きくなった場合のみ機能別に分割する。
- HTMLからのパスは、既存サンプルに合わせて相対パスで書く。
- 既存プロジェクトにすでに配置ルールがある場合は、既存ルールを優先する。

## Asset Naming

画像やアイコンのファイル名は、用途が分かる接頭辞を付ける。

- 写真画像は `pic-` から始める。
  - `pic-top-feature.jpg`
  - `pic-menu-coffee.jpg`
- 背景画像は `bg-` から始める。
  - `bg-top-kv.jpg`
  - `bg-page-head.png`
- アイコンは `icon-` から始める。
  - `icon-cart.svg`
  - `icon-sns-twitter.svg`
- ロゴは `logo.svg`, `logo-white.svg`, `logo-sp.png` のように用途が分かる名前にする。
- SP専用画像は `-sp` を付ける。
  - `bg-top-kv-sp.jpg`
- サムネイルは `thumb-` または `thumbnail` を使う。
  - `thumb-post.jpg`
  - `thumbnail.png`
- 画像名には原則として英小文字、数字、ハイフンを使う。
- 内容が分かりにくい連番だけのファイル名は避ける。

## Prohibited Patterns

以下の書き方は原則として避ける。

- インラインスタイル `style=""` を使わない。
- CSSでIDセレクタを使わない。
- `!important` を乱用しない。アクセシビリティ用utilityなど、必要性が明確な場合に限定する。
- `div` だけでページ構造を組まない。
- class名に見た目だけの名前を付けない。
  - 避ける例: `.red`, `.big`, `.mt20`, `.left-box`
- `block__element__sub` のような多段elementを書かない。
- JavaScript操作用classに見た目のCSSを書かない。
- 同じUIをページごとに別classで重複実装しない。
- 既存のcomponentで表現できるUIを、ページ固有SCSSで作り直さない。
- 色、フォント、z-index、共通幅を各ファイルに直接書き散らさない。
- `--color-border` のような曖昧な色トークン名を使わない。

## Images

- 画像には原則として `width`, `height`, `alt` を指定する。
- ファーストビュー外の画像には `loading="lazy"` を付ける。
- 意味のある画像の `alt` は、画像の内容が伝わるように具体的に書く。
- 装飾目的の画像は `alt=""` にする。
- PC/SPで画像を切り替える場合は `picture` と `source` を使う。

```html
<picture>
    <source media="(max-width: 767px)" srcset="img/bg-top-kv-sp.jpg" />
    <img src="img/bg-top-kv.jpg" width="1440" height="720" alt="キービジュアルの説明" />
</picture>
```

- サムネイルやカード画像で比率を固定する場合は、SCSSで `aspect-ratio` と `object-fit: cover;` を使う。
- 背景として扱う必要がある装飾画像はCSSの `background-image` を使ってよい。ただし、意味のある画像はHTMLの `img` として配置する。
- 画像の角丸や影はcomponent側に寄せ、ページごとにばらつかせない。

## Forms

- 入力項目には必ず `label` を用意する。
- `label` と `input`, `textarea`, `select` は `for` と `id` で紐付けるか、`label` の中に入力要素を含める。
- `input` には内容に合う `type` を指定する。
  - 例: `text`, `email`, `tel`, `password`, `search`
- 入力補助が必要な項目には `autocomplete` を指定する。
- 必須項目には `required` を付ける。
- `button` には必ず `type` を指定する。
- フォーム部品の共通スタイルは `component/form/` に置く。
- エラー表示のclassはBEMまたは状態classで表す。
  - 例: `.contact-form__error`, `.is-error`
- placeholderだけをlabel代わりにしない。

```html
<div class="contact-form__item">
    <label for="email" class="contact-form__label">メールアドレス</label>
    <input type="email" name="email" id="email" class="c-input-text" autocomplete="email" required />
    <p class="contact-form__error" id="email-error">メールアドレスを入力してください。</p>
</div>
```

サンプルでは `.contact-form-item` のような旧記法が出る場合があるが、新規実装では `.contact-form__item` のようにBEM形式へ変換する。

## Accessibility

- リンクはページ遷移やURL移動に使う。
- `button` はフォーム送信、メニュー開閉、モーダル操作などのUI操作に使う。
- `button` には必ず `type="button"` または `type="submit"` を指定する。
- アイコンだけのリンクやボタンには、見た目のテキストまたは `aria-label` を付ける。
- `nav` が複数ある場合は `aria-label` で役割を区別する。サンプルにはない場合でも、新規実装では必要に応じて付ける。
- 開閉UIでは `aria-expanded` と `aria-controls` を必要に応じて使う。
- hoverスタイルを書く場合は、キーボード操作のために `:focus-visible` も考慮する。
- フォーカスリングを理由なく消さない。
- 見出し階層を保ち、視覚上のサイズだけを理由に `h1` から `h3` へ飛ばさない。
- 視覚的に隠すテキストには `.u-visually-hidden` を使う。

```html
<button type="button" class="header__menu-button js-menu-button" aria-label="メニューを開く" aria-expanded="false" aria-controls="global-nav">
    <span class="header__menu-line"></span>
</button>
```

## JavaScript Hooks

- JavaScriptで取得・操作する要素には `js-` 接頭辞のclassを付ける。
  - 例: `.js-menu-button`, `.js-header-nav`, `.js-accordion-trigger`
- `js-` classにはCSSを書かない。
- 見た目のスタイルはBEM classに書き、JS操作は `js-` classで行う。
- 状態を表すclassは `is-` 接頭辞にする。
  - 例: `.is-active`, `.is-open`, `.is-fixed`, `.is-error`
- `is-` classは状態変更に限定し、通常の見た目を作るためのclassとして乱用しない。
- data属性は、値をJSへ渡す必要がある場合に使う。
  - 例: `data-target`, `data-modal-id`

```html
<button type="button" class="accordion__button js-accordion-trigger" aria-expanded="false">
    質問タイトル
</button>
<div class="accordion__body js-accordion-body">
    回答本文
</div>
```

```scss
.accordion__body {
    display: none;

    &.is-open {
        display: block;
    }
}
```

## Component Split Rules

SCSSの置き場所は、役割で判断する。

- 2箇所以上で使うUIは `component/` に置く。
- 1ページだけで使うUIは `page/` に置く。
- 幅、余白、配置などの外枠は `layout/` に置く。
- 色、フォント、ブレイクポイント、mixin、z-indexなどの共通値は `global/` に置く。
- resetやbodyの基本設定は `foundation/` に置く。
- 単一目的で再利用性が高い補助classは `utility/` に置く。
- ページ固有SCSSからcomponentの内部構造を強く上書きしない。必要ならmodifierを追加する。
- 似たUIが増えたら、新しいページ固有classを作る前に既存component化できるか検討する。

## Mixins

- プロジェクト共通のmixinは `global/_mixin.scss` に集約する。
- 矢印、三角形、hover演出など複数箇所で使う処理はmixin化してよい。
- mixin名は `arrow`, `triangle`, `hover-fade` のように用途が分かる名前にする。
- 引数が複数あるmixinには、引数の意味が分かる短いコメントと使用例を併記する。
- ページやcomponent固有のmixinは、必要であれば該当SCSSファイルの冒頭にローカル定義してよい。

## SCSS

- 各SCSSファイルの先頭で必要なglobalを読み込む。

```scss
@use "../global" as g;
```

- ページ配下など階層が深い場合は相対パスを合わせる。

```scss
@use "../../global" as g;
```

- セレクタはBEMのblockを起点に書く。
- ネストは深くしすぎない。原則2階層程度までにする。
- modifierは `&--modifier` で書く。
- elementは `&__element` で書く。
- 状態を表す場合は `is-active` のような状態classを使ってよい。
- 色は直接hexを書かず、原則 `var(--color-xxx)` を使う。
- フォントは `var(--font-family-xxx)` を使う。
- 共通幅は `var(--width-content)` などの変数を使う。

```scss
.c-button {
    display: grid;
    place-items: center;
    min-height: 48px;
    padding: 8px 24px;
    color: var(--color-font-white);
    background-color: var(--color-bg-primary);

    &--center {
        margin-inline: auto;
    }

    &--white {
        color: var(--color-font-base);
        background-color: var(--color-bg-white);
    }
}
```

ボタン内に追加要素が不要な場合は、`.c-button__inner` を作らず `.c-button` 自体に配置と余白を書く。アイコンや複雑な内包構造が必要な場合のみelementを追加する。

## Breakpoints

レスポンシブはmobile firstで書く。標準は `min-width` のmixinを使う。

```scss
$breakpoints: (
    "sm": 500px,
    "md": 768px,
    "lg": 1080px,
    "xl": 1200px,
);

@mixin mq($breakpoint: md) {
    @media screen and (min-width: #{map-get($breakpoints, $breakpoint)}) {
        @content;
    }
}
```

SPのスタイルを通常記述し、PC差分だけを `@include g.mq()` に書く。

media queryは原則として `@include g.mq()` を使う。SCSS内で直接 `@media screen and ...` は書かない。例外は、印刷用CSS、外部ライブラリ調整、`prefers-reduced-motion`、`(hover: hover)` など特殊なmedia queryが必要な場合に限定する。

例外の特殊media queryも、可能であれば `global/_mixin.scss` にmixinとして定義し、`@include` で呼び出す。直接 `@media` を書くのは最後の手段とする。

```scss
// global/_mixin.scss
@mixin hover {
  @media (hover: hover) {
    &:hover {
      @content;
    }
  }
}
```

```scss
// 悪い例
@media (hover: hover) {
  &:hover {
    opacity: 0.6;
  }
}

// 良い例
@include g.hover {
  opacity: 0.6;
}
```

```scss
.top-feature__list {
    display: grid;
    gap: 48px;

    @include g.mq(sm) {
        grid-template-columns: repeat(2, 1fr);
    }

    @include g.mq(lg) {
        grid-template-columns: repeat(3, 1fr);
        gap: 80px 64px;
    }
}
```

引数を省略した `g.mq()` は `md`、つまり `768px` を使い、SPからPCへの標準切り替えに使う。グリッド段組みやレイアウト幅の中間調整が必要な場合のみ `g.mq(sm)`, `g.mq(lg)`, `g.mq(xl)` を明示する。同じblock内では、ブレイクポイントを小さい順に並べる。

## Spacing

余白は8pxベースで設計する。

- 小さい余白: `8px`, `16px`, `24px`
- 中くらいの余白: `32px`, `40px`, `48px`, `56px`
- セクション余白: SPは `56px` から `96px`、PCは `80px` から `160px` を目安にする。
- 見出し直下の本文やカード内の近い要素は `8px` から `16px` を基本にする。
- セクション見出しと本文ブロックの間はSPで `32px` から `48px`、PCで `40px` から `80px` を目安にする。
- グリッドのgapは `16px`, `24px`, `32px`, `40px`, `48px`, `56px`, `64px`, `80px` の範囲から選ぶ。

共通化できる余白はlayoutまたはutilityに寄せる。

```scss
.l-section {
    padding: 96px 0;

    @include g.mq() {
        padding: 160px 0;
    }
}

.l-section-body {
    margin-top: 48px;

    @include g.mq() {
        margin-top: 80px;
    }
}
```

セクション縦余白は `.l-section` を基本とする。セクション以外で同じ縦paddingを使い回したい場合に限り `.u-ptb` を併用してよい。`.l-section` と `.u-ptb` を同じ要素に重ねがけしない。

## Colors And Tokens

色は必ずCSS変数として定義し、各コンポーネントでは変数を参照する。

```scss
:root {
    --color-bg-primary: #baa8a2;
    --color-bg-primary-light: rgba(186, 168, 162, 0.15);
    --color-bg-secondary: #f7f4f2;
    --color-bg-base: #fafafa;
    --color-bg-white: #ffffff;
    --color-bg-black: #333333;
    --color-bg-accent: #f4b400;
    --color-border-gray: #eeeeee;
    --color-border-primary: #baa8a2;
    --color-border-black: #333333;
    --color-border-white: #ffffff;
    --color-font-primary: #baa8a2;
    --color-font-base: #5d5d5d;
    --color-font-black: #333333;
    --color-font-white: #ffffff;
    --color-font-link: #0066cc;
    --color-font-attention: #d93025;
    --color-font-gray: #999999;
}
```

新しい色が必要な場合は、先に `global/_color.scss` に追加してから使う。

上記の色値は例であり、案件のトンマナに合わせて差し替える。`--color-bg-base` はbodyなどページ全体の背景、`--color-bg-white` はカードなど「常に白」を意図する箇所に使う。`--color-bg-primary-light` はprimary色の薄いバリエーション、`--color-bg-secondary` はprimaryとは別系統の補助背景色として使う。

色トークンは用途別に命名する。

```text
--color-bg-{primary|secondary|base|white|black|accent|primary-light}
--color-font-{base|primary|white|black|link|attention|gray}
--color-border-{gray|primary|black|white}
```

`--color-border` のような曖昧な単体名は避け、`--color-border-gray` のように役割が分かる名前にする。

## Typography

- baseの文字サイズはSPで `14px`、PCで `16px` を基準にする。
- bodyの `line-height` は `1.75` から `2.0` を目安にする。
- 日本語フォントは `Noto Sans JP` または案件に合わせたbase fontを使う。
- 英字見出しには英字用font tokenを使い、`text-transform: uppercase` と広めの `letter-spacing` を使ってよい。
- `letter-spacing` はbaseへ一律指定せず、必要なblockごとに指定する。
- 日本語の文字間は必要に応じて `0.08em` から `0.12em`、英字ラベルや見出しでは `0.1em` から `0.4em` を使う。

## Layout

- 横幅制御は `l-container` と `l-container-s` を使う。
- `l-container` は `width: 90%; margin: 0 auto; max-width: var(--width-content);` を基本にする。
- `l-container-s` は狭い本文やフォーム、記事一覧などに使う。
- 広いレイアウトが必要な案件のみ、`l-container-l` と `--width-content-l` を追加してよい。
- セクション全体の縦余白は `l-section` または `u-ptb` のような共通classに寄せる。
- `.l-section` を基本とし、汎用的な上下paddingを小さな範囲で使い回す場合のみ `.u-ptb` を使う。
- レイアウトclassは見た目の外枠だけを担当し、コンポーネント固有の装飾を入れない。

containerは共通placeholderを使う。

```scss
%container {
    width: 90%;
    margin: 0 auto;
}

.l-container {
    @extend %container;
    max-width: var(--width-content);
}

.l-container-s {
    @extend %container;
    max-width: var(--width-content-s);
}

.l-container-l {
    @extend %container;
    max-width: var(--width-content-l);
}
```

`l-container-l` が不要な案件では作らない。

## Components

再利用できるUIは `component/` に置く。

代表例:

- `c-button`
- `c-large-button`
- `c-title-level2`
- `c-title-level3`
- `c-page-kv`
- `c-cta`
- `c-post`
- `c-posts`
- `c-product`
- `c-products`
- `c-meta`
- `c-label`
- `c-date`
- `c-sns-icon`
- `c-pagination`
- `c-instagram`
- `c-input-text`
- `c-input-checkbox`

ページネーションの命名は `c-pagination` を正とし、`c-pagenation` は使わない。

コンポーネントは単体で使えるようにし、ページ固有の余白や配置はページSCSS側に書く。

カード系は以下を基準にする。

- 画像、メタ情報、タイトル、本文、ボタンの順序を自然に並べる。
- `article` を使える場合は使う。
- サムネイルは `display: block;` にする。
- 画像比率が必要なら `aspect-ratio` と `object-fit: cover;` を使う。
- カード間のgapはSPで `32px` から `40px`、PCで `40px` から `48px` を目安にする。

ボタンは以下を基準にする。

- `c-button` をblockにする。
- サイズ違いだけなら `c-button--size-medium`, `c-button--size-large` のようなmodifierで表す。装飾や構造が大きく異なる特殊ボタンのみ、`c-large-button` のように別blockにしてよい。
- 中央寄せは `c-button--center` で表す。
- 背景色違いは `c-button--white`, `c-button--accent`, `c-button--black` のようなmodifierにする。
- paddingは8pxベースにする。

見出しは以下を基準にする。

- 共通見出しは `c-title-level1`, `c-title-level2`, `c-title-level3` として管理する。
- 中央寄せは `--center` modifierにする。
- 白文字など色違いは `--white` modifierにする。
- 英字ラベル見出しが必要なら `c-title-level2-english` のように分ける。

## Page Styles

ページ固有のスタイルは `page/{page-name}/_xxx.scss` に置く。

ページ内のセクションごとにファイルを分割する。ファイル名は `_{ページ名}-{セクション名}.scss` とする。

```text
page/
  _index.scss
  top/
    _top-kv.scss
    _top-log.scss
    _top-variety.scss
    _top-introduction.scss
  single/
    _single-breadcrumb.scss
    _single-header.scss
    _single-featured.scss
    _single-intro.scss
    _single-section.scss
    _single-nav.scss
  contact/
    _contact-form.scss
    _contact-thanks.scss
```

`page/_index.scss` で各ファイルを `@use` で読み込む。ページディレクトリ内に `_index.scss` は作らない。

```scss
// page/_index.scss
@use "top/top-kv";
@use "top/top-log";
@use "single/single-breadcrumb";
@use "single/single-header";
@use "single/single-section";
@use "single/single-nav";
```

ページ固有blockは `top-feature`, `single-contents`, `contact-form` のように命名する。

ページ固有blockのクラス名は、**HTMLファイル名をプレフィックスにする**。

| HTMLファイル | プレフィックス | 例 |
| --- | --- | --- |
| `index.html` | `top-` （例外） | `top-kv`, `top-feature__item` |
| `single.html` | `single-` | `single-header`, `single-section__title` |
| `contact.html` | `contact-` | `contact-form`, `contact-form__item` |
| `about.html` | `about-` | `about-hero`, `about-hero__title` |

`index.html` だけは `index-` ではなく `top-` を使う。それ以外はファイル名をそのままプレフィックスにする。

`l-`, `c-`, `u-` の共通クラスはこのルールの対象外とする。

ページ固有blockでもBEM形式を必須にする。

```scss
.top-feature {
    background-color: var(--color-bg-primary-light);
}

.top-feature__list {
    display: grid;
    gap: 48px;
}

.top-feature__item {
    display: grid;
    gap: 24px;
}

.top-feature__title {
    font-size: 18px;
}
```

## Utility

utilityは最小限にする。サンプルにあるようなアクセシビリティ補助や、汎用的な余白classに限定する。

```scss
.u-visually-hidden {
    position: absolute !important;
    white-space: nowrap !important;
    width: 1px !important;
    height: 1px !important;
    overflow: hidden !important;
    border: 0 !important;
    padding: 0 !important;
    clip: rect(0 0 0 0) !important;
    clip-path: inset(50%) !important;
    margin: -1px !important;
}
```

utilityを増やしすぎてHTMLがutilityだらけにならないようにする。

## Comments

SCSSファイルには、サンプルと同じ形式で見出しコメントを入れる。

```scss
/*!
component > button
------------------------------
*/
```

大きな区切りや意図が伝わりにくいmixinには短いコメントを書いてよい。HTMLコメントは主要セクションの開始と終了に限定する。

```html
<!-- top-feature -->
<section class="top-feature l-section">
    ...
</section>
<!-- end top-feature -->
```

## AIに実装を依頼するときの指示

AIは実装前にこのスタイルガイドを読み、以下を必ず守る。

- このスタイルガイドを最優先する。必要に応じて `~/html_doc/tmp/sample/01` から `05` のHTML/SCSSを参照し、SCSS構成、余白感、コンポーネントの寸法感、画像命名、head構成を確認してよい。
- ただし、サンプルのclass命名は旧来のBEMライト記法であり、本ガイドでは採用しない。サンプルのclass名をそのまま使わず、必ず本ガイドのBEM形式へ変換する。
- BEM形式のSCSSを必須にする。
- `__` は1つのclass内で1回だけ使い、`block__element__sub` は書かない。
- HTMLはセマンティックに書く。
- headにはmeta、favicon/webclip、OGP、Twitter Card、Google Fonts、CSS、defer付きJSを適切に入れる。
- `l-`, `c-`, `u-`, ページ固有blockの責務を分ける。
- ページ固有blockのクラス名はHTMLファイル名をプレフィックスにする。`index.html` のみ例外で `top-` を使う。
- 色はCSS変数から使い、直接hexを書かない。
- 色トークンは `--color-bg-*`, `--color-font-*`, `--color-border-*` のように用途別に命名する。
- 余白は8pxベースで設計する。
- mobile firstで書き、PC差分は `@include g.mq()` にまとめる。
- 通常のレスポンシブ対応で、直接 `@media screen and ...` は書かない。
- コンポーネントSCSSとページSCSSを混ぜない。
- header/footer内の複数リンクは `nav > ul > li > a` を基本にする。
- ページ内に `nav` が2つ以上ある場合は `aria-label` を付ける。
- 画像は原則 `img/` 直下にフラット配置し、大規模案件のみサブディレクトリを使う。
- 画像ファイル名は `pic-`, `bg-`, `icon-`, `logo`, `thumb-` など用途が分かる接頭辞を付ける。
- JS操作用classは `js-`、状態classは `is-` を使う。
- `js-` classにはCSSを書かない。
- リンクとボタンを役割で使い分ける。
- フォームでは `label`, 適切な `type`, `autocomplete`, `required` を必要に応じて指定する。
- ページネーションは `c-pagination` と命名し、`c-pagenation` は使わない。
- containerは `%container` と `@extend` を使って共通化する。
- サイズ違いだけのボタンは `c-button--size-*` で表し、構造が大きく異なる場合のみ `c-large-button` を使う。
- 装飾用divを増やしすぎない。
- カード、見出し、ボタンの余白感はサンプル01から05の雰囲気に寄せる。
- 実装後、BEM命名、セマンティックHTML、変数使用、レスポンシブ、余白の一貫性をセルフチェックする。

## Implementation Checklist

実装後は以下を確認する。

- HTMLがセマンティックに書かれている。
- headにmeta、favicon/webclip、OGP、Twitter Card、CSS、defer付きJSが入っている。
- 見出し階層が正しい。
- SCSSがBEM形式になっている。
- サンプル由来のシングルハイフン要素記法をBEMへ変換している。
- `block__element__sub` のような多段elementを書いていない。
- `l-`, `c-`, `u-`, ページ固有blockの責務が分かれている。
- header/footer内の複数リンクが `nav > ul > li > a` になっている。
- ページ内に `nav` が2つ以上ある場合に `aria-label` が付いている。
- 画像に `width`, `height`, `alt` がある。
- 必要な画像に `loading="lazy"` がある。
- PC/SP画像の切り替えが必要な箇所で `picture` を使っている。
- 色、フォント、共通幅が変数から使われている。
- 色トークンが `--color-bg-*`, `--color-font-*`, `--color-border-*` の用途別命名になっている。
- 余白が8pxベースになっている。
- mobile firstで書かれている。
- `@include g.mq()` の使い方が既存サンプルに合っている。
- 通常のレスポンシブ対応で直接 `@media screen and ...` を書いていない。
- `style=""`, IDセレクタ、不要な `!important` を使っていない。
- `js-` classにCSSを書いていない。
- 状態classが `is-active`, `is-open`, `is-error` などの形になっている。
- リンクとボタンを役割で使い分けている。
- フォームの `label`, `type`, `autocomplete`, `required` が適切である。
- hoverだけでなく `:focus-visible` も必要に応じて考慮している。
- 既存componentで表現できるUIを重複実装していない。
- `c-pagination` を使い、`c-pagenation` と書いていない。
- containerが `%container` と `@extend` で共通化されている。
- ファイル配置と画像命名がこのガイドに沿っている。
