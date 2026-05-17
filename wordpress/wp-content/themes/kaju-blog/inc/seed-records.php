<?php
/**
 * 栽培記録（record）サンプル投稿 — WP-CLI / 初回 init 用
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

/** サンプル定義を変えたらバージョンを上げる */
const KAJU_BLOG_SEED_RECORDS_VERSION = '2.1.0';

/** ページネーション用サンプルの文面を変えたらバージョンを上げる */
const KAJU_BLOG_SEED_PAGINATION_RECORDS_VERSION = '1.1.0';

add_action( 'init', 'kaju_blog_maybe_seed_sample_records', 30 );
add_action( 'init', 'kaju_blog_maybe_refresh_pagination_records', 31 );

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
 * 初回・文面更新時にページネーション用サンプルを投入・更新
 */
function kaju_blog_maybe_refresh_pagination_records(): void {
	if ( KAJU_BLOG_SEED_PAGINATION_RECORDS_VERSION === get_option( 'kaju_blog_seed_pagination_records_version', '' ) ) {
		return;
	}

	kaju_blog_seed_pagination_records();
	update_option( 'kaju_blog_seed_pagination_records_version', KAJU_BLOG_SEED_PAGINATION_RECORDS_VERSION, false );
}

/**
 * ページネーション確認用の追加投稿（一覧件数を増やすためのサンプル）
 *
 * @return list<array<string, mixed>>
 */
function kaju_blog_pagination_record_definitions(): array {
	return array(
		array(
			'slug'           => 'record-pagination-01',
			'title'          => 'もも、開花がそろってきた',
			'fruit'          => 'peach',
			'date'           => '2023-04-12 10:00:00',
			'excerpt'        => '暖かくなり、ももの花が一斉に咲き始めました。',
			'intro'          => '4月中旬、ももの花が見頃になりました。小さな庭でも香りが広がり、受粉の様子を観察しながら、強風の日は花びらが散らないよう支柱を補強しています。',
			'featured_image' => 'img/top/photo-peach.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '開花時の水やり',
				'section_body'    => '<p>開花中は根が敏感なため、土が乾いたタイミングで少量ずつ与えています。葉面への散水は避け、花の腐敗を防いでいます。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-02',
			'title'          => 'すもも、風の強い日の枝さばき',
			'fruit'          => 'plum',
			'date'           => '2023-05-08 10:00:00',
			'excerpt'        => '春の強風で枝が擦れたため、傷口を整理しました。',
			'intro'          => 'すももの若枝が風で揺れ、隣の枝と擦れ合っていたため、内向きに伸びた枝を整理しました。樹の中心が開くよう、外側に向かう枝を中心に残しています。',
			'featured_image' => 'img/top/photo-plum.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '枝の整理',
				'section_body'    => '<p>切り口は斜めに滑らかに仕上げ、切り口保護剤を薄く塗布しました。剪定後は樹勢を落とさないよう、施肥は控えめにしています。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-03',
			'title'          => 'ぶどう棚の新梢を誘引',
			'fruit'          => 'grape',
			'date'           => '2023-06-03 10:00:00',
			'excerpt'        => '新梢が伸びてきたので、誘引紐に沿って誘導しました。',
			'intro'          => 'ぶどうの新梢が勢いよく伸びてきました。棚の上方向に誘引し、葉と房が重ならないよう間隔を空けています。日当たりと風通しを確保するための作業です。',
			'featured_image' => 'img/top/photo-grape.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '誘引のコツ',
				'section_body'    => '<p>若い梢は折れやすいので、紐に沿わせるようにゆっくり誘導します。房の下に葉が密集しないよう、不要な葉は摘み取りました。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-04',
			'title'          => 'ブルーベリー、マルチを敷き直し',
			'fruit'          => 'blueberry',
			'date'           => '2023-07-18 10:00:00',
			'excerpt'        => '夏の蒸れを防ぐため、マルチチップを補充しました。',
			'intro'          => 'ブルーベリーの株元に敷いていたマルチが薄くなっていたため、新しいチップを重ねました。雑草抑制と土壌水分の安定が目的です。',
			'featured_image' => 'img/top/photo-blueberry.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '株元の手入れ',
				'section_body'    => '<p>マルチは根元に密着させすぎず、幹周りに少し隙間を残しています。酸性用の液肥は、マルチの上からではなく土に直接与えました。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-05',
			'title'          => 'さくらんぼ、実の肥大を確認',
			'fruit'          => 'cherry',
			'date'           => '2023-05-22 10:00:00',
			'excerpt'        => '摘果後のさくらんぼが順調に大きくなっています。',
			'intro'          => 'さくらんぼの摘果から2週間ほど経ち、残した粒が順調に肥大しています。房の先端粒を中心に、日当たりの良い位置に実がついているのを確認しました。',
			'featured_image' => 'img/top/photo-cherry.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '肥大期の管理',
				'section_body'    => '<p>この時期は急激な乾燥に弱いため、朝に土の状態を確認してから水やりしています。鳥の被害が出る前に、ネットの準備も進めています。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-06',
			'title'          => 'プルーンの若木、芯止め剪定',
			'fruit'          => 'prune',
			'date'           => '2023-03-05 10:00:00',
			'excerpt'        => '植え付け2年目のプルーンに、芯止め剪定を行いました。',
			'intro'          => '若木のプルーンが背丈を伸ばしてきたため、芯止め剪定で低めの樹形に整えました。収穫しやすい高さを目指し、主枝を3本程度に誘導しています。',
			'featured_image' => 'img/top/photo-prune.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '若木剪定のポイント',
				'section_body'    => '<p>切り口は芽の上5mmほどで、外側の芽を残すようにしました。剪定後は根張りを促すため、深い施肥は避けています。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-07',
			'title'          => 'もも、病斑葉を取り除く',
			'fruit'          => 'peach',
			'date'           => '2023-08-14 10:00:00',
			'excerpt'        => '葉に斑点が見つかったため、該当葉を除去しました。',
			'intro'          => 'ももの葉に褐色の斑点が出ていたため、早めに該当葉を摘み取りました。風通しを良くし、葉面に水が残らないよう、夕方の散水は控えています。',
			'featured_image' => 'img/top/photo-peach.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '予防の工夫',
				'section_body'    => '<p>取り除いた葉は庭外へ持ち出し、堆肥には入れませんでした。枝の密度が高い部分は、軽く間引きして光が当たるようにしました。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-08',
			'title'          => 'すもも、実の自重で枝が垂れた',
			'fruit'          => 'plum',
			'date'           => '2023-07-25 10:00:00',
			'excerpt'        => '実が重く枝がしなったため、支柱で受けました。',
			'intro'          => 'すももの実が大きくなり、枝が地面に近づいてきました。折れないよう支柱を立て、房全体を支えるように紐で固定しました。',
			'featured_image' => 'img/top/photo-plum.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '収穫前の支え',
				'section_body'    => '<p>支柱の先端が枝に食い込まないよう、布テープで緩衝しています。収穫まであと少しなので、無理な動きを避けて観察を続けます。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-09',
			'title'          => 'ぶどう、着色期の光と風通し',
			'fruit'          => 'grape',
			'date'           => '2023-08-28 10:00:00',
			'excerpt'        => '房の周りの葉を整理し、着色を促しました。',
			'intro'          => 'ぶどうが着色し始めたため、房の周りの葉を摘葉して光を当てました。風通しも確保し、湿気がこもらないようにしています。',
			'featured_image' => 'img/top/photo-grape.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '着色期の管理',
				'section_body'    => '<p>摘葉は朝の涼しい時間帯に行い、房自体には触れないよう注意しました。葉を取りすぎないよう、房の直上だけを中心に整理しています。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-10',
			'title'          => 'ブルーベリー、鳥よけネットを設置',
			'fruit'          => 'blueberry',
			'date'           => '2023-06-20 10:00:00',
			'excerpt'        => '実が色づき始めたので、ネットで囲いました。',
			'intro'          => 'ブルーベリーの実が青紫色に変わり始め、鳥の食害が心配になったため、ネットをかけました。収穫までの短期間ですが、実を守る大事な対策です。',
			'featured_image' => 'img/top/photo-blueberry.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => 'ネットの掛け方',
				'section_body'    => '<p>ネットの端を地面に固定し、隙間から鳥が入らないようにしました。収穫のたびに開閉しやすいよう、クリップで留めています。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-11',
			'title'          => 'さくらんぼ、収穫前の糖度チェック',
			'fruit'          => 'cherry',
			'date'           => '2023-06-12 10:00:00',
			'excerpt'        => '試し収穫で甘さを確認し、本収穫の時期を見極めました。',
			'intro'          => 'さくらんぼの色が濃くなってきたため、数粒だけ試し収穫して味を確認しました。酸味が抜け、甘みがしっかり出ていれば本格的な収穫に入ります。',
			'featured_image' => 'img/top/photo-cherry.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '収穫の目安',
				'section_body'    => '<p>ヘタが緑から黄緑に変わり、果皮にツヤが出てきた粒から順に収穫します。朝の涼しい時間帯に取ると、傷みにくくなります。</p>',
			),
		),
		array(
			'slug'           => 'record-pagination-12',
			'title'          => 'プルーン、落ち葉を片付けて冬支度',
			'fruit'          => 'prune',
			'date'           => '2023-11-10 10:00:00',
			'excerpt'        => '落葉が進んだので、株元の清掃と土の状態を確認しました。',
			'intro'          => 'プルーンの葉が落ち、樹形がはっきり見えるようになりました。株元に落ち葉が残らないよう片付け、来年の芽吹きに備えて土の状態を確認しています。',
			'featured_image' => 'img/top/photo-prune.webp',
			'section_01'     => array(
				'section_enabled' => 1,
				'section_title'   => '冬に向けて',
				'section_body'    => '<p>落ち葉は病害の温床になりやすいため、庭外へ運びました。乾燥した日に、軽く中耕して土の通気性を高めています。</p>',
			),
		),
	);
}

/**
 * @return int 作成・更新件数
 */
function kaju_blog_seed_pagination_records(): int {
	if ( ! post_type_exists( 'record' ) ) {
		return 0;
	}

	$count = 0;
	foreach ( kaju_blog_pagination_record_definitions() as $def ) {
		if ( kaju_blog_upsert_sample_record( $def, true ) ) {
			++$count;
		}
	}

	return $count;
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
		array(
			'slug'           => 'prune-winter-pruning',
			'title'          => 'プルーンの冬剪定',
			'fruit'          => 'prune',
			'date'           => '2024-02-12 10:00:00',
			'excerpt'        => '落葉後のプルーンに冬剪定を行い、来年の着果と風通しを整えました。',
			'intro'          => 'プルーンは落葉して樹形が見えやすい冬が剪定の好適期です。伸びすぎた枝を整理し、実をつけやすい枝を残しました。',
			'featured_image' => 'img/top/photo-prune.webp',
			'section_01'     => array(
				'section_enabled'     => 1,
				'section_title'       => '剪定のポイント',
				'section_body'        => '<p>内向きに交差する枝や、弱い枝を取り除き、外側に向かって伸びる健枝を中心に残します。</p>',
				'section_list'        => "伸びすぎた枝を短截\n内向き・交差枝を除去\n樹の中心が開くように調整",
				'section_image'       => 'img/top/photo-prune.webp',
				'section_image_style' => 'default',
				'section_point_title' => '🌱ポイント',
				'section_point_text'  => '剪定後は切り口を乾燥させ、病害に注意しましょう。',
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
