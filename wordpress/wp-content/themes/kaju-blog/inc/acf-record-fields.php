<?php
/**
 * 栽培記録 ACF フィールド（タブ・表示順ガイド付き）
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', 'kaju_blog_register_record_acf_fields' );

/**
 * @return list<array<string, mixed>>
 */
function kaju_blog_acf_record_section_sub_fields( int $index ): array {
	$n   = str_pad( (string) $index, 2, '0', STR_PAD_LEFT );
	$pre = 'field_record_s' . $n . '_';

	return array(
		array(
			'key'           => $pre . 'enabled',
			'label'         => '表示する',
			'name'          => 'section_enabled',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 1,
			'instructions'  => '非表示にしたい場合のみ OFF（通常は ON のまま）',
		),
		array(
			'key'          => $pre . 'title',
			'label'        => '見出し（h2）',
			'name'         => 'section_title',
			'type'         => 'text',
			'instructions' => 'フロントの番号付き見出しになります',
		),
		array(
			'key'          => $pre . 'body',
			'label'        => '本文',
			'name'         => 'section_body',
			'type'         => 'wysiwyg',
			'tabs'         => 'visual',
			'toolbar'      => 'basic',
			'media_upload' => 0,
			'instructions' => 'セクションのメインテキスト（画像は下の「セクション画像」を使うとレイアウトが崩れません）',
		),
		array(
			'key'          => $pre . 'list',
			'label'        => '箇条書き',
			'name'         => 'section_list',
			'type'         => 'textarea',
			'rows'         => 5,
			'instructions' => '1行に1項目。任意',
		),
		array(
			'key'           => $pre . 'image',
			'label'         => 'セクション画像',
			'name'          => 'section_image',
			'type'          => 'image',
			'return_format' => 'array',
			'preview_size'  => 'medium',
			'instructions'  => '本文横・ポイント横に表示される写真',
		),
		array(
			'key'           => $pre . 'image_style',
			'label'         => 'セクション画像の形',
			'name'          => 'section_image_style',
			'type'          => 'select',
			'choices'       => array(
				'default' => '通常（横長）',
				'circle'  => '円形（箇条書きの横）',
			),
			'default_value' => 'default',
		),
		array(
			'key'          => $pre . 'point_title',
			'label'        => '🌱 ポイント見出し',
			'name'         => 'section_point_title',
			'type'         => 'text',
			'instructions' => '任意',
		),
		array(
			'key'          => $pre . 'point_text',
			'label'        => '🌱 ポイント本文',
			'name'         => 'section_point_text',
			'type'         => 'textarea',
			'rows'         => 3,
			'instructions' => '任意',
		),
	);
}

function kaju_blog_register_record_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$fields = array(
		array(
			'key'     => 'field_record_tab_guide',
			'label'   => '入力ガイド',
			'name'    => '',
			'type'    => 'tab',
			'placement' => 'top',
		),
		array(
			'key'     => 'field_record_layout_message',
			'label'   => '',
			'name'    => '',
			'type'    => 'message',
			'message' => '<p><strong>ページの上から順に入力します。</strong></p>'
				. '<ol style="margin:0;padding-left:1.2em;">'
				. '<li>タイトル・果樹（右）・公開日 → ヘッダー</li>'
				. '<li>アイキャッチ（右）→ 大きい写真</li>'
				. '<li>「リード文」タブ → 冒頭の1段落</li>'
				. '<li>「セクション」タブ → 見出し＋本文＋画像など（複数可）</li>'
				. '</ol>'
				. '<p style="margin:8px 0 0;">上の <strong>公開ページプレビュー</strong> で実際の見え方を確認できます。</p>',
			'new_lines' => '',
			'esc_html'  => 0,
		),
		array(
			'key'       => 'field_record_tab_intro',
			'label'     => 'リード文',
			'name'      => '',
			'type'      => 'tab',
			'placement' => 'top',
		),
		array(
			'key'          => 'field_record_intro',
			'label'        => 'リード文（記事冒頭）',
			'name'         => 'intro',
			'type'         => 'textarea',
			'rows'         => 4,
			'instructions' => 'アイキャッチの直下に表示される導入文',
		),
	);

	for ( $i = 1; $i <= 10; $i++ ) {
		$n = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );

		$fields[] = array(
			'key'       => 'field_record_tab_section_' . $n,
			'label'     => 'セクション ' . $i,
			'name'      => '',
			'type'      => 'tab',
			'placement' => 'top',
		);
		$fields[] = array(
			'key'        => 'field_record_section_' . $n,
			'label'      => 'セクション ' . $i . ' の内容',
			'name'       => 'section_' . $n,
			'type'       => 'group',
			'layout'     => 'block',
			'sub_fields' => kaju_blog_acf_record_section_sub_fields( $i ),
		);
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_kaju_record',
			'title'                 => '記事本文（サイト表示エリア）',
			'fields'                => $fields,
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'record',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
