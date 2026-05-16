<?php
/**
 * 404
 *
 * @package kaju-blog
 */

get_header();
?>

<main>
	<div class="l-container">
		<section class="notfound" aria-labelledby="notfound-heading">
			<div class="notfound__panel">
				<div class="notfound__layout">
					<div class="notfound__content">
						<h1 id="notfound-heading" class="notfound__heading">
							<span class="notfound__code">404</span>
							<span class="notfound__heading-text">ページが見つかりません</span>
						</h1>
						<p class="notfound__text">お探しのページは見つからないか、移動または削除された可能性があります。</p>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="notfound__button">
							<svg class="notfound__button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
								<path fill="currentColor" d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
							</svg>
							トップページへ戻る
						</a>
					</div>
					<figure class="notfound__figure">
						<img class="notfound__image" src="<?php echo esc_url( kaju_blog_asset_uri( 'img/header/img-logo.png' ) ); ?>" alt="かごに入ったもも・ぶどう・すもものイラスト" width="657" height="648" loading="lazy" decoding="async">
					</figure>
				</div>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
