<?php
/**
 * ページネーション（records.html と同じマークアップ）
 *
 * @package kaju-blog
 * @var array<string, mixed> $args
 */

defined( 'ABSPATH' ) || exit;

$paginate_args = $args['paginate_args'] ?? array();
$links         = kaju_blog_get_pagination_links( $paginate_args );

if ( empty( $links ) ) {
	return;
}
?>
<nav class="c-pagination" aria-label="<?php esc_attr_e( 'ページナビゲーション', 'kaju-blog' ); ?>">
	<ul class="c-pagination__list">
		<?php foreach ( $links as $link ) : ?>
			<li class="c-pagination__item"><?php echo $link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
		<?php endforeach; ?>
	</ul>
</nav>
