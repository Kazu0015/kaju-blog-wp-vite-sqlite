<?php
/**
 * 栽培記録（record）サンプル投稿 — WP-CLI / 初回 init 用
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/** サンプル定義を変えたらバージョンを上げる */
const KAJU_BLOG_SEED_RECORDS_VERSION = '2.0.0';

add_action( 'init', 'kaju_blog_maybe_seed_sample_records', 30 );

/**
 * 初回のみサンプル投稿を投入（既存スラッグはスキップ）
 */
function kaju_blog_maybe_seed_sample_records(): void {
	if ( KAJU_BLOG_SEED_RECORDS_VERSION === get_option( 'kaju_blog_seed_records_version', '' ) ) {
		return;
	}

	kaju_blog_seed_sample_records();
	update_option( 'kaju_blog_seed_records_version', KAJU_BLOG_SEED_RECORDS_VERSION, false );
}

/**
 * サンプル栽培記録を作成・更新
 *
 * @param bool $force_version  true のときバージョンに関係なく実行（WP-CLI 用）
 * @return int 作成・更新件数
 */
function kaju_blog_seed_sample_records( bool $force_version = false ): int {
	if ( ! post_type_exists( 'record' ) ) {
		return 0;
	}

	$count = 0;

	foreach ( kaju_blog_sample_record_definitions() as $def ) {
		if ( kaju_blog_upsert_sample_record( $def, true ) ) {
			++$count;
		}
	}

	if ( $count > 0 && ! $force_version ) {
		update_option( 'kaju_blog_seed_records_version', KAJU_BLOG_SEED_RECORDS_VERSION, false );
	}

	return $count;
}

/**
 * テーマ内画像をメディアライブラリへ取り込み（同一パスは再利用）
 *
 * @param string $theme_relative 例: img/top/photo-grape-fukuro.webp
 */
function kaju_blog_import_theme_image( string $theme_relative ): int {
	$theme_relative = ltrim( $theme_relative, '/' );
	$cache_key      = 'kaju_blog_media_' . md5( $theme_relative );
	$cached         = (int) get_option( $cache_key, 0 );

	if ( $cached > 0 && wp_attachment_is_image( $cached ) ) {
		return $cached;
	}

	$file = get_template_directory() . '/' . $theme_relative;
	if ( ! is_readable( $file ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$filename = basename( $file );
	$contents = file_get_contents( $file );
	if ( false === $contents ) {
		return 0;
	}

	$upload = wp_upload_bits( $filename, null, $contents );
	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$wp_filetype = wp_check_filetype( $filename, null );
	$attachment  = array(
		'post_mime_type' => $wp_filetype['type'] ?: 'image/webp',
		'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
	if ( is_wp_error( $attach_id ) || ! $attach_id ) {
		return 0;
	}

	$metadata = wp_generate_attachment_metadata( (int) $attach_id, $upload['file'] );
	wp_update_attachment_metadata( (int) $attach_id, $metadata );
	update_option( $cache_key, (int) $attach_id, false );

	return (int) $attach_id;
}

/**
 * ページネーション確認用の追加投稿（既存スラッグはスキップ）
 *
 * @return list<array<string, mixed>>
 */
function kaju_blog_pagination_record_definitions(): array {
	$fruits  = array( 'peach', 'plum', 'grape', 'blueberry', 'cherry', 'prune' );
	$images  = array(
		'img/top/photo-peach.webp',
		'img/top/photo-plum.webp',
		'img/top/photo-grape.webp',
		'img/top/photo-blueberry.webp',
		'img/top/photo-cherry.webp',
		'img/top/photo-prune.webp',
	);
	$records = array();

	for ( $i = 1; $i <= 12; $i++ ) {
		$n      = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
		$fruit  = $fruits[ ( $i - 1 ) % count( $fruits ) ];
		$image  = $images[ ( $i - 1 ) % count( $images ) ];
		$month  = str_pad( (string) ( ( $i % 12 ) + 1 ), 2, '0', STR_PAD_LEFT );

		$records[] = array(
			'slug'           => 'record-pagination-' . $n,
			'title'          => sprintf( '栽培記録（ページネーション確認 %s）', $n ),
			'fruit'          => $fruit,
			'date'           => sprintf( '2023-%s-15 10:00:00', $month ),
			'excerpt'        => '一覧のページ送り動作を確認するためのサンプル投稿です。',
			'intro'          => sprintf( 'これはページネーション確認用の栽培記録 %d 件目です。文面・画像は仮の内容です。', $i ),
			'featured_image' => $image,
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => 'メモ',
				'section_body'    => '<p>2ページ目以降にこのカードが表示されれば、ページネーションは正常に動作しています。</p>',
			),
		);
	}

	return $records;
}

/**
 * @return int 新規作成件数
 */
function kaju_blog_seed_pagination_records(): int {
	if ( ! post_type_exists( 'record' ) ) {
		return 0;
	}

	$created = 0;
	foreach ( kaju_blog_pagination_record_definitions() as $def ) {
		if ( kaju_blog_upsert_sample_record( $def, false ) ) {
			++$created;
		}
	}

	return $created;
}

/**
 * @return list<array<string, mixed>>
 */
function kaju_blog_sample_record_definitions(): array {
	return array(
		array(
			'slug'           => 'grape-bag',
			'title'          => 'ぶどうの袋掛作業、コツを共有',
			'fruit'          => 'grape',
			'date'           => '2024-06-15 10:00:00',
			'excerpt'        => 'ぶどうの実が少しずつ大きくなってきたので、今年も袋がけ作業を行いました。',
			'intro'          => 'ぶどうの実が少しずつ大きくなってきたので、今年も袋がけ作業を行いました。袋掛けは病害虫や果実の保護、見た目の美しさにもつながる大切な作業です。',
			'featured_image' => 'img/top/photo-grape-fukuro.webp',
			'section_01'     => array(
				'section_enabled'     => 1,
				'section_title'       => '袋がけの目的',
				'section_body'        => '<p>袋をかけることで、雨や病害虫から果実を守り、きれいな実を育てることができます。</p>',
				'section_list'        => "病気や害虫の予防\n果皮の汚れや傷の防止\n見た目の向上\n収穫・販売時の価値アップ",
				'section_image'       => 'img/top/photo-grape-fukuro.webp',
				'section_image_style' => 'circle',
			),
			'section_02'     => array(
				'section_enabled'     => 1,
				'section_title'       => '作業のタイミング',
				'section_body'        => '<p>満開後20〜30日後、身の粒が大きくなり始めたタイミングが適季です。</p>',
				'section_image'       => 'img/top/photo-grape.webp',
				'section_image_style' => 'default',
				'section_point_title' => '🌱ポイント',
				'section_point_text'  => '晴れた日の午前中に作業をすると、袋の中が蒸れにくくなります。',
			),
		),
		array(
			'slug'           => 'peach-thinning',
			'title'          => 'ももの摘果、粒をそろえる作業',
			'fruit'          => 'peach',
			'date'           => '2024-05-20 10:00:00',
			'excerpt'        => '小さな庭でも、摘果で実の品質を保つことができます。',
			'intro'          => 'ももがひとまわり大きくなったタイミングで、摘果を行いました。1枝に残す粒数を決めて、実がぶつかり合わないようにしています。',
			'featured_image' => 'img/top/photo-peach.webp',
			'section_01'     => array(
				'section_enabled'     => 1,
				'section_title'       => '摘果のポイント',
				'section_body'        => '<p>向きの悪い果や、傷ついた果を先に取り除き、日当たりの良い果を残します。</p>',
				'section_list'        => "1枝あたり2〜3粒を目安に\n手で優しくひねって外す\n摘果後は水やりを控えめに",
				'section_image'       => 'img/top/photo-peach.webp',
				'section_image_style' => 'default',
				'section_point_title' => '🌱ポイント',
				'section_point_text'  => '摘果直後は樹が弱っているので、無理な施肥は避けましょう。',
			),
		),
		array(
			'slug'           => 'plum-harvest',
			'title'          => 'すももの初収穫',
			'fruit'          => 'plum',
			'date'           => '2024-07-10 10:00:00',
			'excerpt'        => '庭先のすももが色づき、初めての収穫を楽しみました。',
			'intro'          => '梅雨明け頃、すももが紫に色づいてきました。軽く押して柔らかければ収穫OK。朝の涼しい時間に収穫しました。',
			'featured_image' => 'img/top/photo-plum.webp',
			'section_01'     => array(
				'section_enabled'     => 1,
				'section_title'       => '収穫の目安',
				'section_body'        => '<p>果皮に白い粉がのっているものは、触らずに収穫すると長持ちします。</p>',
				'section_list'        => "色が均一に濃くなる\n香りが立ってくる\nへた周りが少し柔らかい",
				'section_image'       => 'img/top/photo-plum-shukaku.webp',
				'section_image_style' => 'circle',
			),
		),
		array(
			'slug'           => 'blueberry-autumn',
			'title'          => 'ブルーベリーの葉が紅葉してきた',
			'fruit'          => 'blueberry',
			'date'           => '2024-11-08 10:00:00',
			'excerpt'        => '秋口、ブルーベリーの葉色が美しく変化してきました。',
			'intro'          => '収穫シーズンを終えたあとのブルーベリー。葉が赤やオレンジに色づき、庭のアクセントになっています。',
			'featured_image' => 'img/top/photo-blueberry-koyo.webp',
			'section_01'     => array(
				'section_enabled'     => 1,
				'section_title'       => '紅葉を楽しむ',
				'section_body'        => '<p>日当たりの良い場所では色づきが鮮やかになります。落葉前のこの時期だけの楽しみです。</p>',
				'section_image'       => 'img/top/photo-blueberry-koyo.webp',
				'section_image_style' => 'default',
			),
		),
		array(
			'slug'           => 'cherry-thinning',
			'title'          => 'さくらんぼの摘果を実施',
			'fruit'          => 'cherry',
			'date'           => '2024-04-25 10:00:00',
			'excerpt'        => '房をそろえ、大きく甘い実を目指して摘果しました。',
			'intro'          => 'さくらんぼは1房に実がつきすぎると粒が小さくなります。早めの摘果で品質を確保します。',
			'featured_image' => 'img/top/photo-cherry.webp',
			'section_01'     => array(
				'section_enabled'     => 1,
				'section_title'       => '摘果のコツ',
				'section_body'        => '<p>房の先端付近に残す粒を選び、向きの悪い果は外します。</p>',
				'section_list'        => "1房あたり5〜8粒程度\n日当たりの良い粒を残す\n摘果後は軽く水やり",
				'section_image'       => 'img/top/photo-cherry.webp',
				'section_image_style' => 'circle',
				'section_point_title' => '🌱ポイント',
				'section_point_text'  => '摘果は晴れた日の午前中が作業しやすいです。',
			),
		),
	);
}

/**
 * セクション定義内のテーマ画像パスを添付IDに変換
 *
 * @param array<string, mixed> $section
 * @return array<string, mixed>
 */
function kaju_blog_prepare_section_for_acf( array $section ): array {
	if ( ! empty( $section['section_image'] ) && is_string( $section['section_image'] ) ) {
		$attach_id = kaju_blog_import_theme_image( $section['section_image'] );
		if ( $attach_id > 0 ) {
			$section['section_image'] = $attach_id;
		} else {
			unset( $section['section_image'] );
		}
	}
	return $section;
}

/**
 * @param array<string, mixed> $def
 */
function kaju_blog_upsert_sample_record( array $def, bool $update_existing = false ): bool {
	$slug = (string) ( $def['slug'] ?? '' );
	if ( '' === $slug ) {
		return false;
	}

	$existing = get_posts(
		array(
			'post_type'              => 'record',
			'name'                   => $slug,
			'posts_per_page'         => 1,
			'post_status'            => 'any',
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$is_update = ! empty( $existing );

	if ( $is_update && ! $update_existing ) {
		return false;
	}

	if ( $is_update ) {
		$post_id = (int) $existing[0];
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_title'   => (string) ( $def['title'] ?? $slug ),
				'post_excerpt' => (string) ( $def['excerpt'] ?? '' ),
				'post_date'    => (string) ( $def['date'] ?? current_time( 'mysql' ) ),
			)
		);
	} else {
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'record',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => (string) ( $def['title'] ?? $slug ),
				'post_excerpt' => (string) ( $def['excerpt'] ?? '' ),
				'post_date'    => (string) ( $def['date'] ?? current_time( 'mysql' ) ),
			),
			true
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			return false;
		}
		$post_id = (int) $post_id;
	}

	$fruit = (string) ( $def['fruit'] ?? '' );
	if ( $fruit && taxonomy_exists( 'fruit' ) ) {
		wp_set_object_terms( $post_id, $fruit, 'fruit' );
	}

	if ( ! empty( $def['featured_image'] ) && is_string( $def['featured_image'] ) ) {
		$featured_id = kaju_blog_import_theme_image( $def['featured_image'] );
		if ( $featured_id > 0 ) {
			set_post_thumbnail( $post_id, $featured_id );
		}
	}

	if ( function_exists( 'update_field' ) ) {
		if ( ! empty( $def['intro'] ) ) {
			update_field( 'intro', (string) $def['intro'], $post_id );
		}
		for ( $i = 1; $i <= 10; $i++ ) {
			$key = sprintf( 'section_%02d', $i );
			if ( empty( $def[ $key ] ) || ! is_array( $def[ $key ] ) ) {
				continue;
			}
			update_field( $key, kaju_blog_prepare_section_for_acf( $def[ $key ] ), $post_id );
		}
	} else {
		if ( ! empty( $def['intro'] ) ) {
			update_post_meta( $post_id, 'intro', (string) $def['intro'] );
		}
	}

	return true;
}
