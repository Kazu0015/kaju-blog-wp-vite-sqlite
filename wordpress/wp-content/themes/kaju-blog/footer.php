<?php
/**
 * サイトフッター
 *
 * @package kaju-blog
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="footer">
	<div class="footer__inner l-container">
		<nav class="footer__nav" aria-label="<?php esc_attr_e( 'フッターナビゲーション', 'kaju-blog' ); ?>">
			<ul class="footer__nav-list">
				<li class="footer__nav-item"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer__nav-link">TOP</a></li>
				<li class="footer__nav-item"><a href="<?php echo esc_url( home_url( '/category/' ) ); ?>" class="footer__nav-link">カテゴリ</a></li>
				<li class="footer__nav-item"><a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>" class="footer__nav-link">プロフィール</a></li>
				<li class="footer__nav-item"><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" class="footer__nav-link">プライバシーポリシー</a></li>
			</ul>
		</nav>
		<p class="footer__copyright">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> 小さな庭の果樹栽培<wbr>ブログ</p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
