<?php
/**
 * テーマ共通ヘルパー
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/**
 * 一覧アーカイブの1ページあたり件数（3列グリッド向け）
 */
function kaju_blog_archive_posts_per_page(): int {
	return 9;
}

/**
 * テーマ内静的ファイル URL（img/ 等）
 */
function kaju_blog_asset_uri( string $path = '' ): string {
	$path = ltrim( $path, '/' );
	return trailingslashit( get_template_directory_uri() ) . $path;
}

/**
 * 果樹ターム slug => CSS modifier / ラベル
 *
 * @return array<string, array{label: string, modifier: string}>
 */
function kaju_blog_fruit_map(): array {
	return array(
		'peach'     => array(
			'label'    => 'もも',
			'modifier' => 'peach',
		),
		'plum'      => array(
			'label'    => 'すもも',
			'modifier' => 'plum',
		),
		'blueberry' => array(
			'label'    => 'ブルーベリー',
			'modifier' => 'blueberry',
		),
		'grape'     => array(
			'label'    => 'ぶどう',
			'modifier' => 'grape',
		),
		'prune'     => array(
			'label'    => 'プルーン',
			'modifier' => 'prune',
		),
		'cherry'    => array(
			'label'    => 'さくらんぼ',
			'modifier' => 'cherry',
		),
	);
}

/**
 * ナビ項目
 *
 * @return array<int, array{label: string, url: string, slug: string}>
 */
function kaju_blog_nav_items(): array {
	return array(
		array(
			'label' => '栽培記録',
			'url'   => get_post_type_archive_link( 'record' ) ?: home_url( '/records/' ),
			'slug'  => 'records',
		),
		array(
			'label' => '庭の木',
			'url'   => get_post_type_archive_link( 'tree' ) ?: home_url( '/trees/' ),
			'slug'  => 'trees',
		),
		array(
			'label' => '作業メモ',
			'url'   => home_url( '/memo/' ),
			'slug'  => 'memo',
		),
		array(
			'label' => 'プロフィール',
			'url'   => home_url( '/profile/' ),
			'slug'  => 'profile',
		),
		array(
			'label' => 'お問い合わせ',
			'url'   => home_url( '/contact/' ),
			'slug'  => 'contact',
		),
	);
}

/**
 * 現在のナビ slug（current 判定用）
 */
function kaju_blog_current_nav_slug(): string {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_post_type_archive( 'record' ) || is_singular( 'record' ) || is_tax( 'fruit' ) ) {
		return 'records';
	}
	if ( is_post_type_archive( 'tree' ) || is_singular( 'tree' ) ) {
		return 'trees';
	}
	if ( is_page( 'profile' ) ) {
		return 'profile';
	}
	if ( is_page( 'contact' ) ) {
		return 'contact';
	}
	if ( is_page( 'memo' ) || is_singular( 'work_memo' ) ) {
		return 'memo';
	}
	return '';
}

/**
 * 果樹タクソノミーアーカイブ URL（栽培記録の絞り込み）
 */
function kaju_blog_fruit_archive_url( string $slug ): string {
	$term = get_term_by( 'slug', $slug, 'fruit' );
	if ( $term instanceof WP_Term ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return (string) $link;
		}
	}
	return home_url( '/records/fruit/' . rawurlencode( $slug ) . '/' );
}

/**
 * 投稿の果樹バッジ modifier（record / tree 共通）
 */
function kaju_blog_record_fruit_modifier( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'fruit' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$slug = $terms[0]->slug;
	$map  = kaju_blog_fruit_map();
	return $map[ $slug ]['modifier'] ?? $slug;
}

/**
 * 投稿の果樹ラベル（record / tree 共通）
 */
function kaju_blog_record_fruit_label( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'fruit' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$slug = $terms[0]->slug;
	$map  = kaju_blog_fruit_map();
	return $map[ $slug ]['label'] ?? $terms[0]->name;
}

/**
 * 庭の木: 植えた年（ACF date → 4桁年、未設定は null）
 */
function kaju_blog_tree_planted_year( int $post_id ): ?int {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	$value = get_field( 'planted_year', $post_id );
	if ( empty( $value ) ) {
		return null;
	}
	$raw = (string) $value;
	if ( preg_match( '/^\d{8}$/', $raw ) ) {
		return (int) substr( $raw, 0, 4 );
	}
	if ( preg_match( '/^\d{4}$/', $raw ) ) {
		return (int) $raw;
	}
	$timestamp = strtotime( $raw );
	if ( false === $timestamp ) {
		return null;
	}
	return (int) gmdate( 'Y', $timestamp );
}

/**
 * 庭の木: 樹齢表示（植えた年から算出）
 */
function kaju_blog_tree_age_label( int $post_id ): string {
	$year = kaju_blog_tree_planted_year( $post_id );
	if ( null === $year ) {
		return '';
	}
	$age = (int) gmdate( 'Y' ) - $year;
	if ( $age < 0 ) {
		return '';
	}
	return sprintf( '樹齢 %d年', $age );
}

/**
 * 庭の木: 植えた年の表示ラベル
 */
function kaju_blog_tree_planted_year_label( int $post_id ): string {
	$year = kaju_blog_tree_planted_year( $post_id );
	if ( null === $year ) {
		return '';
	}
	return sprintf( '植えた年 %d', $year );
}

/**
 * 日付表示（静的 HTML の YYYY.MM.DD）
 */
function kaju_blog_format_date( int $post_id ): string {
	return get_the_date( 'Y.m.d', $post_id );
}

/**
 * ACF セクション一覧（section_01〜10）
 *
 * @return array<int, array<string, mixed>>
 */
function kaju_blog_get_record_sections( int $post_id ): array {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$sections = array();
	for ( $i = 1; $i <= 10; $i++ ) {
		$key    = sprintf( 'section_%02d', $i );
		$group  = get_field( $key, $post_id );
		if ( ! is_array( $group ) ) {
			continue;
		}
		if ( ! kaju_blog_section_group_has_content( $group ) ) {
			continue;
		}
		// 内容があるセクションは常に表示（「表示する」未チェックの取りこぼしを防ぐ）
		$sections[] = $group;
	}
	return $sections;
}

/**
 * textarea 1行1項目 → 配列
 *
 * @return string[]
 */
function kaju_blog_lines_from_textarea( ?string $text ): array {
	if ( null === $text || '' === trim( $text ) ) {
		return array();
	}
	$lines = preg_split( '/\R/u', $text ) ?: array();
	return array_values(
		array_filter(
			array_map( 'trim', $lines ),
			static fn( $line ) => '' !== $line
		)
	);
}

/**
 * 画像未登録時のプレースホルダー URL
 */
function kaju_blog_no_image_uri(): string {
	return kaju_blog_asset_uri( 'img/common/img-no-image.svg' );
}

/**
 * アイキャッチ URL（未設定時は no-image）
 */
function kaju_blog_post_thumbnail_url( int $post_id, string $size = 'large' ): string {
	$url = get_the_post_thumbnail_url( $post_id, $size );
	return $url ? $url : kaju_blog_no_image_uri();
}

/**
 * アイキャッチが設定されているか
 */
function kaju_blog_post_has_thumbnail( int $post_id ): bool {
	return (bool) get_the_post_thumbnail_url( $post_id, 'thumbnail' );
}

/**
 * @deprecated 互換用。kaju_blog_post_thumbnail_url を使用。
 */
function kaju_blog_record_thumbnail_url( int $post_id, string $size = 'large' ): string {
	return kaju_blog_post_thumbnail_url( $post_id, $size );
}

/**
 * @deprecated 互換用。kaju_blog_post_has_thumbnail を使用。
 */
function kaju_blog_record_has_thumbnail( int $post_id ): bool {
	return kaju_blog_post_has_thumbnail( $post_id );
}

/**
 * ACF セクション Group に表示すべき内容があるか
 *
 * @param array<string, mixed> $group
 */
function kaju_blog_section_group_has_content( array $group ): bool {
	$title = isset( $group['section_title'] ) ? trim( (string) $group['section_title'] ) : '';
	$body  = isset( $group['section_body'] ) ? trim( wp_strip_all_tags( (string) $group['section_body'] ) ) : '';
	$list  = kaju_blog_lines_from_textarea( $group['section_list'] ?? '' );
	$image = kaju_blog_acf_image_url( $group['section_image'] ?? null );
	$point = trim( (string) ( $group['section_point_title'] ?? '' ) . (string) ( $group['section_point_text'] ?? '' ) );

	return '' !== $title || '' !== $body || [] !== $list || '' !== $image || '' !== $point;
}

/**
 * ACF 画像フィールド → URL（$use_placeholder で未設定時 no-image）
 */
function kaju_blog_acf_image_url( $image, string $size = 'large', bool $use_placeholder = false ): string {
	if ( empty( $image ) ) {
		return $use_placeholder ? kaju_blog_no_image_uri() : '';
	}
	if ( is_string( $image ) ) {
		return esc_url( $image );
	}
	if ( is_array( $image ) && ! empty( $image['url'] ) ) {
		return (string) $image['url'];
	}
	if ( is_numeric( $image ) ) {
		$url = wp_get_attachment_image_url( (int) $image, $size );
		return $url ? $url : '';
	}
	return '';
}
