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
 * テーマ内静的ファイルの絶対パス（img/ 等）
 */
function kaju_blog_asset_path( string $path = '' ): string {
	$path = ltrim( $path, '/' );
	return trailingslashit( get_template_directory() ) . $path;
}

/**
 * 拡張子なしベースパスから WebP とフォールバック（jpg/png）の URL を解決する。
 *
 * @param string $base_path 例: img/top/photo-peach
 * @return array{webp: ?string, fallback: ?string}
 */
function kaju_blog_resolve_image_sources( string $base_path ): array {
	$base_path = ltrim( $base_path, '/' );
	$dir       = dirname( $base_path );
	$name      = basename( $base_path );
	if ( '.' === $dir ) {
		$dir = '';
	}

	$prefixes = array();
	if ( '' !== $dir ) {
		$prefixes[] = $dir . '/' . $name;
		$prefixes[] = $dir . '/org/' . $name;
	} else {
		$prefixes[] = $name;
	}

	$webp     = null;
	$fallback = null;

	foreach ( $prefixes as $prefix ) {
		$webp_rel = $prefix . '.webp';
		if ( is_readable( kaju_blog_asset_path( $webp_rel ) ) ) {
			$webp = kaju_blog_asset_uri( $webp_rel );
			break;
		}
	}

	foreach ( $prefixes as $prefix ) {
		foreach ( array( 'jpg', 'jpeg', 'png' ) as $ext ) {
			$rel = $prefix . '.' . $ext;
			if ( is_readable( kaju_blog_asset_path( $rel ) ) ) {
				$fallback = kaju_blog_asset_uri( $rel );
				break 2;
			}
		}
	}

	if ( ! $fallback ) {
		$fallback = $webp;
	}

	return array(
		'webp'     => $webp,
		'fallback' => $fallback,
	);
}

/**
 * WebP を優先する picture 要素を出力する。
 *
 * @param string               $base_path 拡張子なし、例: img/top/photo-peach
 * @param array<string, mixed> $img_attrs img に付与する属性（class, alt, width, height, loading, decoding, fetchpriority）
 */
function kaju_blog_the_picture_webp( string $base_path, array $img_attrs = array() ): void {
	$sources = kaju_blog_resolve_image_sources( $base_path );
	if ( ! $sources['webp'] && ! $sources['fallback'] ) {
		return;
	}

	$img_src = $sources['fallback'] ?: $sources['webp'];

	echo '<picture>';
	if ( $sources['webp'] ) {
		printf(
			'<source type="%1$s" srcset="%2$s" />',
			esc_attr( 'image/webp' ),
			esc_url( $sources['webp'] )
		);
	}

	printf( '<img src="%s"', esc_url( $img_src ) );

	$allowed = array( 'class', 'alt', 'width', 'height', 'loading', 'decoding', 'fetchpriority' );
	foreach ( $allowed as $name ) {
		if ( ! array_key_exists( $name, $img_attrs ) ) {
			continue;
		}
		$value = $img_attrs[ $name ];
		if ( '' === $value || null === $value ) {
			continue;
		}
		if ( in_array( $name, array( 'width', 'height' ), true ) ) {
			printf( ' %1$s="%2$d"', esc_attr( $name ), (int) $value );
		} else {
			printf( ' %1$s="%2$s"', esc_attr( $name ), esc_attr( (string) $value ) );
		}
	}

	echo ' /></picture>';
}

/**
 * 固定ページ URL（未作成時はスラッグパスへフォールバック）
 */
function kaju_blog_page_url( string $slug ): string {
	$slug = trim( $slug, '/' );
	$page = get_page_by_path( $slug, OBJECT, 'page' );

	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}

	return home_url( '/' . $slug . '/' );
}

/**
 * プライバシーポリシー URL（WP 設定 → 固定ページがあれば優先）
 */
function kaju_blog_privacy_policy_url(): string {
	$url = get_privacy_policy_url();

	if ( is_string( $url ) && '' !== $url ) {
		return $url;
	}

	return kaju_blog_page_url( 'privacy-policy' );
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
 * 栽培記録一覧 URL
 */
function kaju_blog_record_archive_url(): string {
	$link = get_post_type_archive_link( 'record' );
	return $link ? (string) $link : home_url( '/records/' );
}

/**
 * 栽培記録一覧の果樹絞り込み URL（/records/?record_fruit=slug）
 *
 * @param string|null $fruit_slug 省略時はすべて表示
 */
function kaju_blog_record_filter_url( ?string $fruit_slug = null ): string {
	$base = kaju_blog_record_archive_url();
	if ( null === $fruit_slug || '' === $fruit_slug ) {
		return $base;
	}

	return add_query_arg( 'record_fruit', sanitize_title( $fruit_slug ), $base );
}

/**
 * 現在の果樹絞り込み slug（未選択は空文字）
 */
function kaju_blog_get_record_fruit_filter_slug(): string {
	$slug = get_query_var( 'record_fruit' );
	if ( is_string( $slug ) && '' !== $slug ) {
		return sanitize_title( $slug );
	}

	if ( is_tax( 'fruit' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return $term->slug;
		}
	}

	return '';
}

/**
 * 果樹絞り込みの表示ラベル
 */
function kaju_blog_get_record_fruit_filter_label(): string {
	$slug = kaju_blog_get_record_fruit_filter_slug();
	if ( '' === $slug ) {
		return '';
	}

	$map = kaju_blog_fruit_map();
	return $map[ $slug ]['label'] ?? $slug;
}

/**
 * 果樹タクソノミーアーカイブ URL（栽培記録の絞り込み）
 */
function kaju_blog_fruit_archive_url( string $slug ): string {
	return kaju_blog_record_filter_url( $slug );
}

/**
 * 投稿に付いた果樹タームの slug
 */
function kaju_blog_get_post_fruit_slug( int $post_id ): string {
	$terms = get_the_terms( $post_id, 'fruit' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}

	return $terms[0]->slug;
}

/**
 * 投稿の果樹バッジ modifier（record / tree 共通）
 */
function kaju_blog_record_fruit_modifier( int $post_id ): string {
	$slug = kaju_blog_get_post_fruit_slug( $post_id );
	if ( '' === $slug ) {
		return '';
	}
	$map = kaju_blog_fruit_map();
	return $map[ $slug ]['modifier'] ?? $slug;
}

/**
 * 投稿の果樹ラベル（record / tree 共通）
 */
function kaju_blog_record_fruit_label( int $post_id ): string {
	$slug = kaju_blog_get_post_fruit_slug( $post_id );
	if ( '' === $slug ) {
		return '';
	}
	$terms = get_the_terms( $post_id, 'fruit' );
	$map   = kaju_blog_fruit_map();
	return $map[ $slug ]['label'] ?? ( $terms[0]->name ?? $slug );
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
 * 庭の木: プロフィール表示行（ACF、値がある項目のみ）
 *
 * @return list<array{label: string, value: string}>
 */
function kaju_blog_tree_profile_rows( int $post_id ): array {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$fields = array(
		'rootstock'      => '台木',
		'pollinator'     => '受粉樹',
		'bloom_season'   => '開花期',
		'harvest_season' => '収穫期',
		'status_note'    => '現在の状態',
	);

	$rows = array();
	foreach ( $fields as $key => $label ) {
		$value = trim( (string) get_field( $key, $post_id ) );
		if ( '' === $value ) {
			continue;
		}
		$rows[] = array(
			'label' => $label,
			'value' => $value,
		);
	}

	return $rows;
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
 * 同一投稿タイプの前後投稿を取得（get_*_post の第3引数は taxonomy のため post type を渡さない）
 *
 * @param bool        $previous  true=前の投稿, false=次の投稿.
 * @param int|null    $post_id   対象投稿 ID（省略時はループ中の投稿）.
 * @param string|null $taxonomy  同一タームに限定する taxonomy（例: fruit）.
 * @return WP_Post|null
 */
function kaju_blog_get_adjacent_post( bool $previous, ?int $post_id = null, ?string $taxonomy = null ): ?WP_Post {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return null;
	}

	$in_same_term = null !== $taxonomy && '' !== $taxonomy;
	$tax          = $in_same_term ? $taxonomy : 'category';

	$adjacent = $previous
		? get_previous_post( $in_same_term, '', $tax, $post )
		: get_next_post( $in_same_term, '', $tax, $post );

	if ( $adjacent instanceof WP_Post && $adjacent->post_type === $post->post_type ) {
		return $adjacent;
	}

	if ( $in_same_term ) {
		return kaju_blog_get_adjacent_post( $previous, $post_id, null );
	}

	return null;
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
