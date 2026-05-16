<?php
/**
 * プロフィール固定ページ
 *
 * Template Name: プロフィール（自動）
 *
 * @package kaju-blog
 */

get_header();

while ( have_posts() ) :
	the_post();
	$page_id = get_the_ID();

	$hero_name     = function_exists( 'get_field' ) ? (string) get_field( 'profile_name', $page_id ) : '';
	$hero_tagline  = function_exists( 'get_field' ) ? (string) get_field( 'profile_tagline', $page_id ) : '';
	$hero_text     = function_exists( 'get_field' ) ? (string) get_field( 'profile_text', $page_id ) : '';
	$hero_role     = function_exists( 'get_field' ) ? (string) get_field( 'profile_role', $page_id ) : '';
	$hero_image    = function_exists( 'get_field' ) ? kaju_blog_acf_image_url( get_field( 'profile_image', $page_id ) ) : '';
	$about_text    = function_exists( 'get_field' ) ? (string) get_field( 'profile_about', $page_id ) : '';
	$motivation    = function_exists( 'get_field' ) ? (string) get_field( 'profile_motivation', $page_id ) : '';

	if ( '' === $hero_name ) {
		$hero_name = 'ゆう';
	}
	if ( '' === $hero_role ) {
		$hero_role = 'ブログ主';
	}
	if ( '' === $hero_tagline ) {
		$hero_tagline = '週末ひとり、小さな庭で果樹と暮らしています。';
	}
	if ( '' === $hero_text ) {
		$hero_text = '会社員の平日はデスクワーク。休日は庭先の果樹と向き合う、ゆるやかな暮らしを送っています。';
	}
	if ( '' === $hero_image ) {
		$hero_image = kaju_blog_asset_uri( 'img/top/photo-self.webp' );
	}
	if ( '' === $about_text ) {
		$about_text = '家庭菜園で果樹を育てる過程で感じた、収穫の喜びや失敗を等身大にお伝えしています。これから果樹栽培を始められる方のヒントになれば幸いです。';
	}
	if ( '' === $motivation ) {
		$motivation = '自分の学びの記録として始めたブログですが、同じ趣味を持つ方とつながれる場にしたいと思っています。日々の作業や気づきを、これからもゆるく綴っていきます。';
	}

	$fruit_icons = array(
		'peach'     => 'icon-peach.webp',
		'plum'      => 'icon-plum.webp',
		'blueberry' => 'icon-blueberry.webp',
		'grape'     => 'icon-grape.webp',
		'prune'     => 'icon-prune.webp',
		'cherry'    => 'icon-cherry.webp',
	);
	$fruit_map   = kaju_blog_fruit_map();
	?>

<main>
	<div class="l-container-s">
		<?php
		get_template_part(
			'template-parts/breadcrumb',
			null,
			array(
				'prefix' => 'profile-breadcrumb',
				'items'  => array(
					array(
						'label' => 'ホーム',
						'url'   => home_url( '/' ),
					),
					array( 'label' => 'プロフィール' ),
				),
			)
		);
		?>

		<header class="profile-header">
			<h1 class="profile-header__title">プロフィール</h1>
		</header>

		<div class="profile-sections">
			<section class="profile-hero" aria-labelledby="profile-hero-heading">
				<h2 id="profile-hero-heading" class="u-visually-hidden">ブログ主プロフィール</h2>
				<div class="profile-hero__inner">
					<div class="profile-hero__image-wrapper">
						<img class="profile-hero__image" src="<?php echo esc_url( $hero_image ); ?>" alt="庭で果樹を育てるブログ主・<?php echo esc_attr( $hero_name ); ?>" width="612" height="623" loading="lazy" decoding="async">
					</div>
					<div class="profile-hero__body">
						<p class="profile-hero__role">
							<span class="profile-hero__role-icon" aria-hidden="true">🌱</span>
							<?php echo esc_html( $hero_role ); ?>
						</p>
						<p class="profile-hero__name"><?php echo esc_html( $hero_name ); ?></p>
						<p class="profile-hero__tagline"><?php echo esc_html( $hero_tagline ); ?></p>
						<p class="profile-hero__text"><?php echo esc_html( $hero_text ); ?></p>
					</div>
				</div>
			</section>

			<section class="profile-section" aria-labelledby="profile-about-heading">
				<h2 id="profile-about-heading" class="profile-section__heading">
					<span class="profile-section__heading-icon" aria-hidden="true">🍃</span>
					このブログについて
				</h2>
				<p class="profile-section__text"><?php echo esc_html( $about_text ); ?></p>
			</section>

			<section class="profile-fruits" aria-labelledby="profile-fruits-heading">
				<h2 id="profile-fruits-heading" class="profile-section__heading">
					<span class="profile-section__heading-icon" aria-hidden="true">🍃</span>
					育てている果樹
				</h2>
				<ul class="profile-fruits__list">
					<?php
					$terms = get_terms(
						array(
							'taxonomy'   => 'fruit',
							'hide_empty' => false,
						)
					);
					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
						foreach ( $terms as $term ) :
							$icon = $fruit_icons[ $term->slug ] ?? 'icon-peach.webp';
							$label = $fruit_map[ $term->slug ]['label'] ?? $term->name;
							?>
							<li class="profile-fruits__item">
								<img class="profile-fruits__image" src="<?php echo esc_url( kaju_blog_asset_uri( 'img/top/' . $icon ) ); ?>" alt="" width="800" height="800" loading="lazy" decoding="async">
								<span class="profile-fruits__label"><?php echo esc_html( $label ); ?></span>
							</li>
							<?php
						endforeach;
					endif;
					?>
				</ul>
			</section>

			<section class="profile-section" aria-labelledby="profile-motivation-heading">
				<h2 id="profile-motivation-heading" class="profile-section__heading">
					<span class="profile-section__heading-icon" aria-hidden="true">🍃</span>
					ブログを始めたきっかけ
				</h2>
				<p class="profile-section__text"><?php echo esc_html( $motivation ); ?></p>
			</section>

			<section class="profile-contact" aria-labelledby="profile-contact-heading">
				<div class="profile-contact__inner">
					<div class="profile-contact__content">
						<h2 id="profile-contact-heading" class="profile-section__heading">
							<span class="profile-section__heading-icon" aria-hidden="true">🍃</span>
							お問い合わせ
						</h2>
						<p class="profile-section__text">ご質問やご感想は、お問い合わせフォームよりお気軽にどうぞ。</p>
					</div>
					<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="profile-contact__button">お問い合わせはこちら &gt;</a>
				</div>
			</section>
		</div>
	</div>
</main>

	<?php
endwhile;

get_footer();
