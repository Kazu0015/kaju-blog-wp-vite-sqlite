<?php
/**
 * ドキュメントタイトル（<title> / OGP）
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'document_title_parts', 'kaju_blog_filter_document_title_parts' );

/**
 * 果樹フィルタ付き栽培記録一覧の title を h1 と揃える
 *
 * @param array<string, string> $title
 * @return array<string, string>
 */
function kaju_blog_filter_document_title_parts( array $title ): array {
	if ( ! is_post_type_archive( 'record' ) ) {
		return $title;
	}

	$filter_label = kaju_blog_get_record_fruit_filter_label();
	if ( '' === $filter_label ) {
		return $title;
	}

	$title['title'] = $filter_label;

	return $title;
}
