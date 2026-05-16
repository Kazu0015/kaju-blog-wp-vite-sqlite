<?php
/**
 * パンくず
 *
 * @package kaju-blog
 * @var string $prefix  BEM 接頭辞（record-breadcrumb 等）
 * @var array  $items   [ ['label' => '', 'url' => ''], ... ] 最後は url 省略可
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['prefix'] ) || empty( $args['items'] ) || ! is_array( $args['items'] ) ) {
	return;
}

$prefix = preg_replace( '/[^a-z0-9-]/', '', (string) $args['prefix'] );
$items  = $args['items'];
$last   = count( $items ) - 1;
?>
<nav class="<?php echo esc_attr( $prefix ); ?>" aria-label="<?php esc_attr_e( 'パンくずリスト', 'kaju-blog' ); ?>">
	<ol class="<?php echo esc_attr( $prefix ); ?>__list">
		<?php foreach ( $items as $i => $item ) : ?>
			<li class="<?php echo esc_attr( $prefix ); ?>__item"<?php echo ( $i === $last && empty( $item['url'] ) ) ? ' aria-current="page"' : ''; ?>>
				<?php if ( ! empty( $item['url'] ) && $i !== $last ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo esc_attr( $prefix ); ?>__link"><?php echo esc_html( $item['label'] ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $item['label'] ); ?>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
