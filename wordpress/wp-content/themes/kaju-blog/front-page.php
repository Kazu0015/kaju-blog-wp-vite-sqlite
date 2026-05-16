<?php
/**
 * トップページ
 *
 * @package kaju-blog
 */

get_header();

$front_id = (int) get_option( 'page_on_front' );
$records  = new WP_Query(
	array(
		'post_type'      => 'record',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
	)
);

$kv_images = array(
	array( 'src' => 'img/top/photo-peach.webp', 'alt' => 'もも' ),
	array( 'src' => 'img/top/photo-cherry.webp', 'alt' => 'さくらんぼ' ),
	array( 'src' => 'img/top/photo-grape.webp', 'alt' => 'ぶどう' ),
	array( 'src' => 'img/top/photo-plum.webp', 'alt' => 'すもも' ),
	array( 'src' => 'img/top/photo-blueberry.webp', 'alt' => 'ブルーベリー' ),
	array( 'src' => 'img/top/photo-prune.webp', 'alt' => 'プルーン' ),
);

$variety_items = array(
	array( 'slug' => 'peach', 'icon' => 'icon-peach.webp', 'title' => 'もも' ),
	array( 'slug' => 'plum', 'icon' => 'icon-plum.webp', 'title' => 'すもも' ),
	array( 'slug' => 'blueberry', 'icon' => 'icon-blueberry.webp', 'title' => 'ブルーベリー' ),
	array( 'slug' => 'grape', 'icon' => 'icon-grape.webp', 'title' => 'ぶどう' ),
	array( 'slug' => 'prune', 'icon' => 'icon-prune.webp', 'title' => 'プルーン' ),
	array( 'slug' => 'cherry', 'icon' => 'icon-cherry.webp', 'title' => 'さくらんぼ' ),
);
?>

<main>
	<div class="top-kv">
		<div class="top-kv__inner">
			<div class="top-kv__image-wrapper">
				<ul class="top-kv__image-list">
					<?php foreach ( $kv_images as $i => $img ) : ?>
						<li class="top-kv__image-item<?php echo 0 === $i ? ' top-kv__image-item--active' : ''; ?>">
							<img class="top-kv__image" src="<?php echo esc_url( kaju_blog_asset_uri( $img['src'] ) ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" width="8256" height="2048"<?php echo 0 === $i ? ' fetchpriority="high"' : ''; ?> decoding="async">
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="top-kv__lead">
				<div class="top-kv__lead-body">
					<div class="top-kv__lead-layer top-kv__lead-layer--visible">
						<p class="top-kv__lead-text">果樹を育てる日常を</p>
						<p class="top-kv__lead-text">楽しく、等身大に。</p>
					</div>
					<div class="top-kv__lead-layer" aria-hidden="true">
						<p class="top-kv__lead-text">果樹を育てる日常を</p>
						<p class="top-kv__lead-text">楽しく、等身大に。</p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<section class="top-log">
		<div class="top-log__inner l-container">
			<h2 class="top-log__heading">最近の栽培記録</h2>
			<?php if ( $records->have_posts() ) : ?>
				<ul class="top-log__card-list">
					<?php
					while ( $records->have_posts() ) :
						$records->the_post();
						?>
						<li class="top-log__card-item">
							<?php
							get_template_part(
								'template-parts/log',
								'card',
								array( 'post' => get_post() )
							);
							?>
						</li>
					<?php endwhile; ?>
				</ul>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</section>

	<section class="top-variety">
		<div class="top-variety__inner l-container">
			<h2 class="top-variety__heading">果樹ごとのお話</h2>
			<ul class="top-variety__list">
				<?php foreach ( $variety_items as $item ) : ?>
					<li class="top-variety__item">
						<a href="<?php echo esc_url( home_url( '/varieties/' . $item['slug'] . '/' ) ); ?>" class="top-variety__link">
							<img class="top-variety__image" src="<?php echo esc_url( kaju_blog_asset_uri( 'img/top/' . $item['icon'] ) ); ?>" alt="" width="800" height="800" loading="lazy" decoding="async">
							<div class="top-variety__body">
								<h3 class="top-variety__title"><?php echo esc_html( $item['title'] ); ?></h3>
								<p class="top-variety__sub-title">成長の記録</p>
							</div>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="top-introduction">
		<div class="top-introduction__inner l-container">
			<h2 class="top-introduction__heading">
				<span class="top-introduction__heading-1st">このブログについて</span>
			</h2>
			<div class="top-introduction__content">
				<div class="top-introduction__text-wrapper">
					<?php
					$intro_texts = function_exists( 'get_field' ) && $front_id
						? get_field( 'intro_texts', $front_id )
						: null;
					if ( is_array( $intro_texts ) && ! empty( $intro_texts ) ) {
						foreach ( $intro_texts as $row ) {
							$text = $row['text'] ?? '';
							if ( '' === trim( (string) $text ) ) {
								continue;
							}
							$class = ! empty( $row['breakable'] ) ? ' top-introduction__text breakable' : ' top-introduction__text';
							echo '<p class="' . esc_attr( trim( $class ) ) . '">' . esc_html( $text ) . '</p>';
						}
					} else {
						?>
						<p class="top-introduction__text">会社員の週末、庭先で始めた果樹栽培。</p>
						<p class="top-introduction__text">失敗もたくさんありますが、収穫の喜びは格別です。</p>
						<p class="top-introduction__text breakable">自分にもできそう、と思っていただけるような、</p>
						<p class="top-introduction__text breakable">等身大の記録を綴っています。</p>
						<?php
					}
					?>
				</div>
				<div class="top-introduction__image-wrapper">
					<?php
					$intro_img = ( function_exists( 'get_field' ) && $front_id )
						? kaju_blog_acf_image_url( get_field( 'intro_image', $front_id ) )
						: '';
					if ( ! $intro_img ) {
						$intro_img = kaju_blog_asset_uri( 'img/top/photo-self.webp' );
					}
					?>
					<img class="top-introduction__image" src="<?php echo esc_url( $intro_img ); ?>" alt="庭で果樹を育てるブログ主" width="612" height="623" loading="lazy" decoding="async">
				</div>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
