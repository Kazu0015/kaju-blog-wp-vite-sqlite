<?php
/**
 * 庭の木カード（<a class="c-log-card c-tree-card"> のみ。親 <li> は呼び出し側）
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
$modifier  = kaju_blog_record_fruit_modifier( $post_id );
$label     = kaju_blog_record_fruit_label( $post_id );
$has_thumb = kaju_blog_post_has_thumbnail( $post_id );
$thumb     = kaju_blog_post_thumbnail_url( $post_id, 'large' );
$img_class = 'c-log-card__image' . ( $has_thumb ? '' : ' c-log-card__image--no-image' );
$img_alt   = $has_thumb ? get_the_title( $post_id ) : '';

$planted_label = kaju_blog_tree_planted_year_label( $post_id );
$age_label     = kaju_blog_tree_age_label( $post_id );
$meta_parts    = array_filter( array( $planted_label, $age_label ) );

$bloom_season   = function_exists( 'get_field' ) ? trim( (string) get_field( 'bloom_season', $post_id ) ) : '';
$harvest_season = function_exists( 'get_field' ) ? trim( (string) get_field( 'harvest_season', $post_id ) ) : '';
$season_parts   = array();
if ( $bloom_season ) {
	$season_parts[] = '開花期: ' . $bloom_season;
}
if ( $harvest_season ) {
	$season_parts[] = '収穫期: ' . $harvest_season;
}
?>
<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="c-log-card c-tree-card">
	<img class="<?php echo esc_attr( $img_class ); ?>" src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" width="800" height="450" loading="lazy" decoding="async"<?php echo $has_thumb ? '' : ' role="presentation"'; ?>>
	<div class="c-log-card__body">
		<?php if ( ! empty( $meta_parts ) ) : ?>
			<p class="c-log-card__date c-tree-card__planted"><?php echo esc_html( implode( ' / ', $meta_parts ) ); ?></p>
		<?php endif; ?>
		<h3 class="c-log-card__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
		<?php if ( ! empty( $season_parts ) ) : ?>
			<p class="c-tree-card__seasons"><?php echo esc_html( implode( ' / ', $season_parts ) ); ?></p>
		<?php endif; ?>
		<?php if ( $modifier && $label ) : ?>
			<div class="c-log-card__meta">
				<span class="c-log-card__meta-badge c-log-card__meta-badge--<?php echo esc_attr( $modifier ); ?>"><?php echo esc_html( $label ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</a>
