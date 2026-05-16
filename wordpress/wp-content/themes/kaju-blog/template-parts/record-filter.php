<?php
/**
 * 果樹フィルタ（タクソノミーアーカイブリンク）
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;

$terms = get_terms(
	array(
		'taxonomy'   => 'fruit',
		'hide_empty' => false,
	)
);

if ( empty( $terms ) || is_wp_error( $terms ) ) {
	return;
}

$map = kaju_blog_fruit_map();
?>
<nav class="record-filter" aria-label="<?php esc_attr_e( 'カテゴリで絞り込む', 'kaju-blog' ); ?>">
	<ul class="record-filter__list">
		<?php foreach ( $terms as $term ) : ?>
			<?php
			$modifier = $map[ $term->slug ]['modifier'] ?? $term->slug;
			$label    = $map[ $term->slug ]['label'] ?? $term->name;
			$link     = get_term_link( $term );
			if ( is_wp_error( $link ) ) {
				continue;
			}
			?>
			<li class="record-filter__item">
				<a href="<?php echo esc_url( $link ); ?>" class="record-filter__link c-log-card__meta-badge c-log-card__meta-badge--<?php echo esc_attr( $modifier ); ?>"><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
