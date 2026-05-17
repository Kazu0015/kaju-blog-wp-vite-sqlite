<?php
/**
 * 果樹フィルタ（栽培記録一覧上で絞り込み）
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

$map     = kaju_blog_fruit_map();
$current = kaju_blog_get_record_fruit_filter_slug();
?>
<nav class="record-filter" aria-label="<?php esc_attr_e( 'カテゴリで絞り込む', 'kaju-blog' ); ?>">
	<ul class="record-filter__list">
		<li class="record-filter__item">
			<a
				href="<?php echo esc_url( kaju_blog_record_filter_url() ); ?>"
				class="record-filter__link c-log-card__meta-badge c-log-card__meta-badge--all<?php echo '' === $current ? ' is-active' : ''; ?>"
				<?php echo '' === $current ? ' aria-current="true"' : ''; ?>
			><?php esc_html_e( 'すべて', 'kaju-blog' ); ?></a>
		</li>
		<?php foreach ( $terms as $term ) : ?>
			<?php
			$modifier   = $map[ $term->slug ]['modifier'] ?? $term->slug;
			$label      = $map[ $term->slug ]['label'] ?? $term->name;
			$is_active  = $current === $term->slug;
			$link_class = 'record-filter__link c-log-card__meta-badge c-log-card__meta-badge--' . esc_attr( $modifier );
			if ( $is_active ) {
				$link_class .= ' is-active';
			}
			?>
			<li class="record-filter__item">
				<a
					href="<?php echo esc_url( kaju_blog_record_filter_url( $term->slug ) ); ?>"
					class="<?php echo esc_attr( $link_class ); ?>"
					<?php echo $is_active ? ' aria-current="true"' : ''; ?>
				><?php echo esc_html( $label ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
