<?php
/**
 * Contact Form 7 — 静的 HTML と同じコンポーネントクラスを付与
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'wpcf7_autop_or_not', '__return_false' );
add_filter( 'wpcf7_form_elements', 'kaju_blog_cf7_form_elements' );
add_filter( 'wpcf7_form_elements', 'kaju_blog_cf7_disable_submit_button', 25 );
add_action( 'wp_enqueue_scripts', 'kaju_blog_dequeue_cf7_default_styles', 20 );

/**
 * CF7 標準 CSS はフォントサイズ・余白がデザインと食い違うため無効化
 */
function kaju_blog_dequeue_cf7_default_styles(): void {
	if ( ! is_page( 'contact' ) ) {
		return;
	}

	wp_dequeue_style( 'contact-form-7' );
	wp_dequeue_style( 'contact-form-7-rtl' );
}

/**
 * CF7 出力 HTML に c-input-text 等を付与（reset の border: none 対策）
 */
function kaju_blog_cf7_form_elements( string $content ): string {
	$map = array(
		'wpcf7-form-control wpcf7-text'       => 'wpcf7-form-control wpcf7-text c-input-text',
		'wpcf7-form-control wpcf7-email'      => 'wpcf7-form-control wpcf7-email c-input-text',
		'wpcf7-form-control wpcf7-url'        => 'wpcf7-form-control wpcf7-url c-input-text',
		'wpcf7-form-control wpcf7-tel'        => 'wpcf7-form-control wpcf7-tel c-input-text',
		'wpcf7-form-control wpcf7-number'     => 'wpcf7-form-control wpcf7-number c-input-text',
		'wpcf7-form-control wpcf7-textarea'    => 'wpcf7-form-control wpcf7-textarea c-textarea',
		'wpcf7-form-control wpcf7-select'     => 'wpcf7-form-control wpcf7-select c-select',
		'wpcf7-form-control wpcf7-acceptance' => 'wpcf7-form-control wpcf7-acceptance c-input-checkbox',
		'wpcf7-form-control wpcf7-submit'     => 'wpcf7-form-control wpcf7-submit contact-form__submit',
	);

	$content = str_replace( array_keys( $map ), array_values( $map ), $content );

	if ( str_contains( $content, 'contact-form__fields' ) ) {
		$content = preg_replace( '#</?p(?:\s[^>]*)?>#i', '', $content );
		$content = preg_replace( '#<br\s*/?>#i', '', $content );
	}

	return $content;
}

/**
 * お問い合わせページの送信ボタンを無効化（送信不可）
 */
function kaju_blog_cf7_disable_submit_button( string $content ): string {
	if ( ! is_page( 'contact' ) ) {
		return $content;
	}

	$replaced = preg_replace(
		'/(<input\b[^>]*\bclass="[^"]*\bcontact-form__submit\b[^"]*"[^>]*)(>)/i',
		'$1 disabled aria-disabled="true"$2',
		$content,
		1,
		$count
	);

	if ( 1 === $count && is_string( $replaced ) ) {
		return $replaced;
	}

	return $content;
}
