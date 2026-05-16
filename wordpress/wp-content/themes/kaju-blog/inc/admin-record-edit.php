<?php
/**
 * 栽培記録 編集画面 — プレビュー・構成ガイド
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

add_action( 'add_meta_boxes', 'kaju_blog_record_edit_meta_boxes', 20 );
add_action( 'edit_form_after_title', 'kaju_blog_record_edit_layout_guide' );
add_action( 'admin_enqueue_scripts', 'kaju_blog_record_edit_assets' );
add_filter( 'acf/prepare_field/name=section_enabled', 'kaju_blog_hide_section_enabled_field' );
add_filter( 'acf/load_field_groups', 'kaju_blog_dedupe_record_field_groups' );
add_filter( 'admin_post_thumbnail_html', 'kaju_blog_featured_image_help', 10, 2 );
add_filter( 'manage_record_posts_columns', 'kaju_blog_record_columns' );
add_action( 'manage_record_posts_custom_column', 'kaju_blog_record_column_content', 10, 2 );

/**
 * プレビュー用 URL
 */
function kaju_blog_record_preview_url( WP_Post $post ): string {
	if ( in_array( $post->post_status, array( 'publish', 'private' ), true ) ) {
		$url = get_permalink( $post );
		return $url ? $url : '';
	}
	$link = get_preview_post_link( $post );
	return $link ? $link : '';
}

/**
 * @param WP_Post $post
 */
function kaju_blog_record_edit_layout_guide( WP_Post $post ): void {
	if ( 'record' !== $post->post_type ) {
		return;
	}
	?>
	<div class="kaju-record-guide" id="kaju-record-guide">
		<p class="kaju-record-guide__lead">
			<strong>この画面の入力は、右の「公開ページプレビュー」と同じ順番で表示されます。</strong>
			タイトル・果樹・日付・アイキャッチは WordPress 標準の欄、本文のブロックは下の ACF 欄です。
		</p>
		<ol class="kaju-record-guide__map" aria-label="記事の表示順">
			<li><span class="kaju-record-guide__zone">① ヘッダー</span> タイトル欄 ＋ 右サイドバー「果樹」＋ 公開日</li>
			<li><span class="kaju-record-guide__zone">② 大きい画像</span> 右サイドバー「アイキャッチ画像」</li>
			<li><span class="kaju-record-guide__zone">③ リード</span> ACF「リード文」タブ</li>
			<li><span class="kaju-record-guide__zone">④ セクション</span> ACF「セクション 1〜」タブ（見出し・本文・箇条書き・画像・🌱ポイント）</li>
		</ol>
	</div>
	<?php
}

/**
 * @param string $post_type
 */
function kaju_blog_record_edit_meta_boxes( string $post_type ): void {
	if ( 'record' !== $post_type ) {
		return;
	}

	add_meta_box(
		'kaju_record_live_preview',
		'公開ページプレビュー',
		'kaju_blog_render_record_preview_metabox',
		'record',
		'normal',
		'high'
	);
}

/**
 * @param WP_Post $post
 */
function kaju_blog_render_record_preview_metabox( WP_Post $post ): void {
	$preview_url = kaju_blog_record_preview_url( $post );
	?>
	<div class="kaju-record-preview" data-kaju-preview>
		<p class="kaju-record-preview__actions">
			<?php if ( $preview_url ) : ?>
				<a href="<?php echo esc_url( $preview_url ); ?>" class="button button-primary" target="_blank" rel="noopener">
					新しいタブで開く
				</a>
				<button type="button" class="button" data-kaju-preview-refresh>プレビューを更新</button>
			<?php else : ?>
				<span class="description">下書きを保存するとプレビューが表示されます。</span>
			<?php endif; ?>
		</p>
		<?php if ( $preview_url ) : ?>
			<iframe
				class="kaju-record-preview__frame"
				title="<?php esc_attr_e( '栽培記録のプレビュー', 'kaju-blog' ); ?>"
				src="<?php echo esc_url( $preview_url ); ?>"
				data-kaju-preview-frame
				loading="lazy"
			></iframe>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * @param string $html
 * @param int    $post_id
 */
function kaju_blog_featured_image_help( string $html, int $post_id ): string {
	if ( 'record' !== get_post_type( $post_id ) ) {
		return $html;
	}

	$note = '<p class="kaju-record-featured-help"><strong>② 記事上部の大きい画像</strong>（一覧カードのサムネイルにも使われます）</p>';

	return $note . $html;
}

/**
 * @param array<string, mixed> $field
 * @return array<string, mixed>|false
 */
function kaju_blog_hide_section_enabled_field( $field ) {
	return false;
}

/**
 * DB に残った旧 JSON 同期グループと PHP 登録が重複するのを防ぐ
 *
 * @param array<int, array<string, mixed>> $groups
 * @return array<int, array<string, mixed>>
 */
function kaju_blog_dedupe_record_field_groups( array $groups ): array {
	$found  = false;
	$filtered = array();

	foreach ( $groups as $group ) {
		if ( ( $group['key'] ?? '' ) !== 'group_kaju_record' ) {
			$filtered[] = $group;
			continue;
		}
		// テーマ PHP 登録（local=php）を優先
		if ( ! empty( $group['local'] ) && 'php' === $group['local'] ) {
			$filtered[] = $group;
			$found        = true;
			continue;
		}
		if ( $found ) {
			continue;
		}
		$filtered[] = $group;
		$found        = true;
	}

	return $filtered;
}

/**
 * @param string $hook
 */
function kaju_blog_record_edit_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'record' !== $screen->post_type ) {
		return;
	}

	$css = '
		.kaju-record-guide {
			margin: 12px 0 8px;
			padding: 14px 16px;
			background: #f6f7f7;
			border: 1px solid #c3c4c7;
			border-left: 4px solid #7a9e4e;
			border-radius: 4px;
		}
		.kaju-record-guide__lead { margin: 0 0 10px; }
		.kaju-record-guide__map {
			margin: 0;
			padding-left: 1.2em;
			display: grid;
			gap: 6px;
		}
		.kaju-record-guide__zone {
			display: inline-block;
			min-width: 6.5em;
			font-weight: 600;
			color: #38501e;
		}
		#kaju_record_live_preview .inside { margin: 0; padding: 0; }
		.kaju-record-preview { padding: 12px; }
		.kaju-record-preview__actions {
			display: flex;
			flex-wrap: wrap;
			gap: 8px;
			align-items: center;
			margin: 0 0 10px;
		}
		.kaju-record-preview__frame {
			width: 100%;
			height: min(72vh, 780px);
			border: 1px solid #c3c4c7;
			border-radius: 4px;
			background: #fff;
		}
		.kaju-record-featured-help {
			margin: 0 0 8px;
			padding: 8px 10px;
			background: #f0f6e8;
			border-radius: 4px;
			font-size: 12px;
		}
	';

	$ver = wp_get_theme()->get( 'Version' ) ?: '1.0.0';
	wp_register_style( 'kaju-record-admin', false, array(), $ver );
	wp_enqueue_style( 'kaju-record-admin' );
	wp_add_inline_style( 'kaju-record-admin', $css );

	$js = <<<'JS'
	(function () {
		const root = document.querySelector('[data-kaju-preview]');
		if (!root) return;
		const frame = root.querySelector('[data-kaju-preview-frame]');
		const btn = root.querySelector('[data-kaju-preview-refresh]');
		if (!frame || !btn) return;
		btn.addEventListener('click', function () {
			try {
				const url = new URL(frame.src);
				url.searchParams.set('_kaju_refresh', String(Date.now()));
				frame.src = url.toString();
			} catch (e) {
				frame.src = frame.src;
			}
		});
	})();
JS;

	wp_register_script( 'kaju-record-admin', false, array(), $ver, true );
	wp_enqueue_script( 'kaju-record-admin' );
	wp_add_inline_script( 'kaju-record-admin', $js );
}

/**
 * @param string[] $columns
 * @return string[]
 */
function kaju_blog_record_columns( array $columns ): array {
	$new = array();
	foreach ( $columns as $key => $label ) {
		if ( 'title' === $key ) {
			$new['kaju_thumb'] = '画像';
		}
		$new[ $key ] = $label;
	}
	return $new;
}

/**
 * @param string $column
 * @param int    $post_id
 */
function kaju_blog_record_column_content( string $column, int $post_id ): void {
	if ( 'kaju_thumb' !== $column ) {
		return;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		echo get_the_post_thumbnail( $post_id, array( 80, 60 ) );
		return;
	}
	echo '<span style="color:#999;font-size:11px;">未設定</span>';
}
