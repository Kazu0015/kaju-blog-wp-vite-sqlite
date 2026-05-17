<?php
/**
 * サイト URL を DB 全体で置換（シリアライズ対応）
 *
 * 用法:
 *   FROM=http://localhost:8080 TO=https://example.com php replace-site-url.php
 *
 * @package kaju-blog
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once dirname( __DIR__, 4 ) . '/wp-load.php';
}

$from = getenv( 'FROM' ) ?: 'http://localhost:8080';
$to   = getenv( 'TO' ) ?: ( defined( 'WP_HOME' ) ? WP_HOME : '' );

if ( '' === $to ) {
	fwrite( STDERR, "TO が未設定です（環境変数 TO または WP_HOME）\n" );
	exit( 1 );
}

if ( $from === $to ) {
	echo "replace-site-url: スキップ（FROM と TO が同じ）\n";
	exit( 0 );
}

/**
 * @param mixed $data Data.
 * @return mixed
 */
function kaju_blog_replace_deep( $data, string $from, string $to ) {
	if ( is_string( $data ) ) {
		if ( is_serialized( $data ) ) {
			$unserialized = @unserialize( $data, array( 'allowed_classes' => false ) );
			if ( false !== $unserialized || 'b:0;' === $data ) {
				return serialize( kaju_blog_replace_deep( $unserialized, $from, $to ) );
			}
		}
		return str_replace( $from, $to, $data );
	}

	if ( is_array( $data ) ) {
		foreach ( $data as $key => $value ) {
			$data[ $key ] = kaju_blog_replace_deep( $value, $from, $to );
		}
		return $data;
	}

	if ( is_object( $data ) ) {
		foreach ( get_object_vars( $data ) as $key => $value ) {
			$data->$key = kaju_blog_replace_deep( $value, $from, $to );
		}
		return $data;
	}

	return $data;
}

global $wpdb;

$targets = array(
	$wpdb->posts    => array( 'post_content', 'post_excerpt', 'guid' ),
	$wpdb->postmeta  => array( 'meta_value' ),
	$wpdb->comments => array( 'comment_content' ),
	$wpdb->options  => array( 'option_value' ),
);

$total = 0;

foreach ( $targets as $table => $columns ) {
	$pk = 'ID';
	if ( $wpdb->postmeta === $table ) {
		$pk = 'meta_id';
	} elseif ( $wpdb->comments === $table ) {
		$pk = 'comment_ID';
	} elseif ( $wpdb->options === $table ) {
		$pk = 'option_id';
	}

	foreach ( $columns as $column ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT `{$pk}`, `{$column}` FROM `{$table}` WHERE `{$column}` LIKE %s",
				'%' . $wpdb->esc_like( $from ) . '%'
			),
			ARRAY_A
		);

		if ( ! $rows ) {
			continue;
		}

		foreach ( $rows as $row ) {
			$new_value = kaju_blog_replace_deep( $row[ $column ], $from, $to );
			if ( $new_value === $row[ $column ] ) {
				continue;
			}
			$wpdb->update(
				$table,
				array( $column => $new_value ),
				array( $pk => $row[ $pk ] ),
				array( '%s' ),
				array( '%s' )
			);
			++$total;
		}
	}
}

update_option( 'home', $to );
update_option( 'siteurl', $to );

echo "replace-site-url: {$from} => {$to} ({$total} cells updated)\n";
