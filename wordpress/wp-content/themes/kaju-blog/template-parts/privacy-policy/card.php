<?php
/**
 * プライバシーポリシー — カード内コンテンツ（静的 privacy-policy.html 準拠）
 *
 * @package kaju-blog
 * @var string $title
 * @var string $contact_url
 * @var string $home_url
 */

defined( 'ABSPATH' ) || exit;

$title       = isset( $args['title'] ) ? (string) $args['title'] : 'プライバシーポリシー';
$contact_url = isset( $args['contact_url'] ) ? (string) $args['contact_url'] : home_url( '/contact/' );
$home_url    = isset( $args['home_url'] ) ? (string) $args['home_url'] : home_url( '/' );
?>

<header class="privacy-policy-header">
	<h1 class="privacy-policy-header__title"><?php echo esc_html( $title ); ?></h1>
	<p class="privacy-policy-header__dates">制定日：2026年5月1日 / 最終更新日：2026年5月1日</p>
</header>

<p class="privacy-policy-content__intro">当サイトは、お客様の個人情報保護の重要性について認識し、個人情報の保護に関する法律を遵守するとともに、以下のプライバシーポリシーに従って、適切に取り扱うものとします。</p>

<div class="privacy-policy-sections js-privacy-policy-sections">

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">1. 個人情報の定義</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">「個人情報」とは、個人情報保護法にいう「個人情報」を指し、生存する個人に関する情報であって、当該情報に含まれる氏名、生年月日、住所、電話番号、連絡先その他の記述等により特定の個人を識別できる情報を指します。</p>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">2. 個人情報の収集方法</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">当サイトでは、お問い合わせフォームやコメント機能のご利用時などに、氏名・メールアドレス等の個人情報をご入力いただく場合があります。</p>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">3. 個人情報を収集・利用する目的</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">当サイトが個人情報を収集・利用する目的は、以下のとおりです。</p>
			<ul class="privacy-policy-section__list">
				<li class="privacy-policy-section__list-item">お問い合わせへの回答のため</li>
				<li class="privacy-policy-section__list-item">ご意見・ご感想への対応のため</li>
				<li class="privacy-policy-section__list-item">当サイトのサービス向上のため</li>
				<li class="privacy-policy-section__list-item">上記の利用目的に付随する目的のため</li>
			</ul>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">4. 利用目的の変更</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">当サイトは、利用目的が変更前と関連性を有すると合理的に認められる場合に限り、個人情報の利用目的を変更するものとします。利用目的の変更を行った場合には、当ページにて公表します。</p>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">5. 個人情報の第三者提供</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">当サイトは、次に掲げる場合を除いて、あらかじめユーザーの同意を得ることなく、第三者に個人情報を提供することはありません。</p>
			<ul class="privacy-policy-section__list">
				<li class="privacy-policy-section__list-item">法令に基づく場合</li>
				<li class="privacy-policy-section__list-item">人の生命、身体または財産の保護のために必要がある場合であって、本人の同意を得ることが困難であるとき</li>
				<li class="privacy-policy-section__list-item">公衆衛生の向上または児童の健全な育成の推進のために特に必要がある場合であって、本人の同意を得ることが困難であるとき</li>
				<li class="privacy-policy-section__list-item">国の機関もしくは地方公共団体またはその委託を受けた者が法令の定める事務を遂行することに対して協力する必要がある場合であって、本人の同意を得ることにより当該事務の遂行に支障を及ぼすおそれがあるとき</li>
			</ul>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">6. 個人情報の開示・訂正・削除</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">ユーザーは、当サイトの保有する自己の個人情報について、開示・訂正・追加・削除・利用停止を求めることができます。ご希望の場合は、お問い合わせフォームよりご連絡ください。本人確認のうえ、合理的な期間内に対応いたします。</p>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">7. セキュリティ</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">当サイトは、個人情報の漏えい、滅失またはき損の防止その他個人情報の安全管理のために、必要かつ適切な措置を講じます。</p>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">8. Cookie（クッキー）について</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">当サイトでは、サイトの利便性向上やアクセス状況の分析のため、Cookieを使用する場合があります。Cookieにより個人を特定する情報を取得することはありません。ブラウザの設定によりCookieを無効にすることも可能です。</p>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">9. アクセス解析ツールについて</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">当サイトでは、Googleによるアクセス解析ツール「Googleアナリティクス」を使用している場合があります。GoogleアナリティクスはCookieを使用してトラフィックデータを収集します。詳細は<a href="https://policies.google.com/privacy" class="privacy-policy-section__link" target="_blank" rel="noopener noreferrer">Googleのプライバシーポリシー</a>をご確認ください。</p>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">10. お問い合わせ窓口</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">本ポリシーに関するお問い合わせは、<a href="<?php echo esc_url( $contact_url ); ?>" class="privacy-policy-section__link">お問い合わせフォーム</a>よりご連絡ください。</p>
		</div>
	</details>

	<details class="privacy-policy-section">
		<summary class="privacy-policy-section__summary">
			<span class="privacy-policy-section__title">11. プライバシーポリシーの変更</span>
			<span class="privacy-policy-section__icon" aria-hidden="true"></span>
		</summary>
		<div class="privacy-policy-section__body">
			<p class="privacy-policy-section__text">当サイトは、必要に応じて本ポリシーの内容を変更することがあります。変更後のプライバシーポリシーは、本ページに掲載した時点から効力を生じるものとします。</p>
		</div>
	</details>

</div>

<footer class="privacy-policy-card-footer">
	<p class="privacy-policy-card-footer__operator">運営者：小さな庭の果樹栽培ブログ</p>
	<a href="<?php echo esc_url( $home_url ); ?>" class="privacy-policy-card-footer__button">← トップへ戻る</a>
</footer>
