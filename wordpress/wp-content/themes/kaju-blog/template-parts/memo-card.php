<?php
/**
 * 作業メモカード（<a class="c-log-card"> のみ。親 <li> は呼び出し側）
 *
 * @package kaju-blog
 * @var WP_Post $post
 */

defined( 'ABSPATH' ) || exit;

$post = $args['post'] ?? null;
if ( ! $post instanceof WP_Post ) {
	return;
}

$post_id   = $post->ID;
$has_thumb = kaju_blog_post_has_thumbnail( $post_id );
$thumb     = kaju_blog_post_thumbnail_url( $post_id, 'large' );
$img_class = 'c-log-card__image' . ( $has_thumb ? '' : ' c-log-card__image--no-image' );
$img_alt   = $has_thumb ? get_the_title( $post_id ) : '';
$excerpt   = get_the_excerpt( $post_id );
if ( '' === $excerpt ) {
	$excerpt = wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 40, '…' );
}
?>
<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="c-log-card">
	<img class="<?php echo esc_attr( $img_class ); ?>" src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" width="800" height="450" loading="lazy" decoding="async"<?php echo $has_thumb ? '' : ' role="presentation"'; ?>>
	<div class="c-log-card__body">
		<p class="c-log-card__date"><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $post_id ) ); ?>"><?php echo esc_html( kaju_blog_format_date( $post_id ) ); ?></time></p>
		<h3 class="c-log-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
		<?php if ( $excerpt ) : ?>
			<p class="c-log-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>
		<div class="c-log-card__meta">
			<img class="c-log-card__meta-like" src="<?php echo esc_url( kaju_blog_asset_uri( 'img/top/icon-thumb-up.svg' ) ); ?>" alt="" width="20" height="20" loading="lazy" decoding="async">
		</div>
	</div>
</a>
