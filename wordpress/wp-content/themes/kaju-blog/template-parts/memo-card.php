<?php
/**
 * 作業メモカード（c-card-post）
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
$thumb     = kaju_blog_post_thumbnail_url( $post_id, 'medium_large' );
$img_class = 'c-card-post__image' . ( $has_thumb ? '' : ' c-card-post__image--placeholder' );
$excerpt   = get_the_excerpt( $post_id );
if ( '' === $excerpt ) {
	$excerpt = wp_trim_words( wp_strip_all_tags( (string) $post->post_content ), 40, '…' );
}
?>
<article class="c-card-post">
	<a class="c-card-post__link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
		<div class="c-card-post__image-wrapper">
			<img class="<?php echo esc_attr( $img_class ); ?>" src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $has_thumb ? get_the_title( $post_id ) : '' ); ?>" width="800" height="450" loading="lazy" decoding="async"<?php echo $has_thumb ? '' : ' role="presentation"'; ?>>
		</div>
		<div class="c-card-post__body">
			<time class="c-card-post__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $post_id ) ); ?>"><?php echo esc_html( kaju_blog_format_date( $post_id ) ); ?></time>
			<h2 class="c-card-post__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
			<?php if ( $excerpt ) : ?>
				<p class="c-card-post__excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
		</div>
	</a>
</article>
