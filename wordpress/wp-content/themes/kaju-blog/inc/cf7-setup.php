<?php
/**
 * Contact Form 7 — 静的 contact.html と同じフォームを自動配置
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/** フォーム定義を変えたらバージョンを上げる（既存フォームを上書き更新） */
const KAJU_BLOG_CF7_FORM_VERSION = '1.0.1';

add_action( 'init', 'kaju_blog_maybe_install_cf7_form', 25 );

/**
 * CF7 有効時にテーマ用フォームを作成・更新
 */
function kaju_blog_maybe_install_cf7_form(): void {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return;
	}

	if ( KAJU_BLOG_CF7_FORM_VERSION === get_option( 'kaju_blog_cf7_form_version', '' ) ) {
		return;
	}

	kaju_blog_install_cf7_contact_form();
	update_option( 'kaju_blog_cf7_form_version', KAJU_BLOG_CF7_FORM_VERSION, false );
}

/**
 * テーマ用 CF7 フォームを保存（ID は kaju_blog_cf7_form_id に記録）
 */
function kaju_blog_install_cf7_contact_form(): void {
	$form_body = kaju_blog_cf7_default_form_markup( kaju_blog_privacy_policy_url() );
	$mail      = kaju_blog_cf7_default_mail();
	$messages  = kaju_blog_cf7_default_messages();

	$form_id = (int) get_option( 'kaju_blog_cf7_form_id', 0 );

	if ( $form_id <= 0 ) {
		$existing_forms = get_posts(
			array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'ASC',
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $existing_forms ) ) {
			$form_id = (int) $existing_forms[0];
		}
	}

	if ( $form_id > 0 ) {
		$existing = WPCF7_ContactForm::get_instance( $form_id );
		if ( $existing ) {
			$props                        = $existing->get_properties();
			$props['form']                = $form_body;
			$props['mail']                = $mail;
			$props['messages']            = $messages;
			$props['additional_settings'] = "autop: off\n";
			$existing->set_properties( $props );
			$existing->save();
			update_option( 'kaju_blog_cf7_form_id', $form_id, false );
			return;
		}
	}

	$contact_form = WPCF7_ContactForm::get_template(
		array(
			'title' => 'お問い合わせ',
		)
	);

	$props                        = $contact_form->get_properties();
	$props['form']                = $form_body;
	$props['mail']                = $mail;
	$props['messages']            = $messages;
	$props['additional_settings'] = "autop: off\n";
	$contact_form->set_properties( $props );

	$new_id = $contact_form->save();
	if ( $new_id ) {
		update_option( 'kaju_blog_cf7_form_id', (int) $new_id, false );
	}
}

/**
 * 静的 contact.html と同じマークアップ（CF7 タグ埋め込み）
 */
function kaju_blog_cf7_default_form_markup( string $privacy_url ): string {
	$privacy_url = esc_url( $privacy_url );

	$rows = array(
		'<motion class="contact-form__fields">',
		'',
		'<motion class="contact-form__row">',
		'<motion class="contact-form__label-group">',
		'<label for="contact-name" class="contact-form__label">お名前</label>',
		'<span class="c-required-badge">必須</span>',
		'</motion>',
		'<motion class="contact-form__field">',
		'[text* your-name id:contact-name autocomplete:name class:c-input-text placeholder "山田 太郎"]',
		'</motion>',
		'</motion>',
		'',
		'<motion class="contact-form__row">',
		'<motion class="contact-form__label-group">',
		'<label for="contact-email" class="contact-form__label">メールアドレス</label>',
		'<span class="c-required-badge">必須</span>',
		'</motion>',
		'<motion class="contact-form__field">',
		'[email* your-email id:contact-email autocomplete:email class:c-input-text placeholder "example@email.com"]',
		'</motion>',
		'</motion>',
		'',
		'<motion class="contact-form__row">',
		'<motion class="contact-form__label-group">',
		'<span class="contact-form__label" id="contact-type-label">お問い合わせ種別</span>',
		'<span class="c-required-badge">必須</span>',
		'</motion>',
		'<motion class="contact-form__field">',
		'<label for="contact-type" class="u-visually-hidden">お問い合わせ種別</label>',
		'[select* inquiry-type id:contact-type class:c-select default:1 "ご質問|question" "ご感想|feedback" "リンク・掲載について|link" "その他|other"]',
		'</motion>',
		'</motion>',
		'',
		'<motion class="contact-form__row">',
		'<motion class="contact-form__label-group">',
		'<label for="contact-message" class="contact-form__label">お問い合わせ内容</label>',
		'<span class="c-required-badge">必須</span>',
		'</motion>',
		'<motion class="contact-form__field">',
		'[textarea* your-message id:contact-message class:c-textarea placeholder "ご質問内容をご記入ください"]',
		'</motion>',
		'</motion>',
		'',
		'<motion class="contact-form__row">',
		'<motion class="contact-form__label-group">',
		'<label for="contact-url" class="contact-form__label">ウェブサイトURL</label>',
		'<span class="contact-form__label-note">（任意）</span>',
		'</motion>',
		'<motion class="contact-form__field">',
		'[url your-url id:contact-url autocomplete:url class:c-input-text placeholder "https://example.com"]',
		'</motion>',
		'</motion>',
		'',
		'<motion class="contact-form__row contact-form__row--checkbox">',
		'<motion class="contact-form__field">',
		'<label class="contact-form__checkbox-label">',
		'[acceptance privacy class:c-input-checkbox]',
		'<span><a href="' . $privacy_url . '" class="contact-form__privacy-link">プライバシーポリシー</a>に同意する</span>',
		'</label>',
		'</motion>',
		'</motion>',
		'',
		'<motion class="contact-form__row contact-form__row--submit">',
		'<motion class="contact-form__field">',
		'[submit class:contact-form__submit "送信する"]',
		'</motion>',
		'</motion>',
		'',
		'</motion>',
	);

	return str_replace( array( '<motion', '</motion>' ), array( '<div', '</div>' ), implode( "\n", $rows ) );
}

/**
 * 管理者宛メール
 *
 * @return array<string, mixed>
 */
function kaju_blog_cf7_default_mail(): array {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	$host = is_string( $host ) ? $host : 'localhost';

	return array(
		'active'             => true,
		'subject'            => '【お問い合わせ】[your-name] 様より',
		'sender'             => '[_site_title] <wordpress@' . $host . '>',
		'recipient'          => '[_site_admin_email]',
		'body'               => "以下の内容でお問い合わせがありました。\n\n"
			. "お名前: [your-name]\n"
			. "メールアドレス: [your-email]\n"
			. "お問い合わせ種別: [inquiry-type]\n"
			. "お問い合わせ内容:\n[your-message]\n\n"
			. "ウェブサイトURL: [your-url]\n",
		'additional_headers' => 'Reply-To: [your-email]',
		'attachments'        => '',
		'use_html'           => 0,
		'exclude_blank'      => 0,
	);
}

/**
 * 送信結果メッセージ
 *
 * @return array<string, string>
 */
function kaju_blog_cf7_default_messages(): array {
	return array(
		'mail_sent_ok'     => 'お問い合わせありがとうございます。内容を確認のうえ、ご返信いたします。',
		'mail_sent_ng'     => '送信に失敗しました。時間をおいて再度お試しください。',
		'validation_error' => '入力内容に誤りがあります。確認のうえ、再度お試しください。',
		'spam'             => '送信できませんでした。',
		'accept_terms'     => 'プライバシーポリシーへの同意が必要です。',
		'invalid_email'    => 'メールアドレスの形式が正しくありません。',
		'invalid_required' => '必須項目を入力してください。',
	);
}
