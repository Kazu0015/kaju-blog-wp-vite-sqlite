<?php
/**
 * お問い合わせ固定ページ
 *
 * @package kaju-blog
 */

get_header();

$cf7 = kaju_blog_contact_form_shortcode();

while ( have_posts() ) :
	the_post();
	?>

<main>
	<div class="l-container-s">
		<?php
		get_template_part(
			'template-parts/breadcrumb',
			null,
			array(
				'prefix' => 'contact-breadcrumb',
				'items'  => array(
					array(
						'label' => 'ホーム',
						'url'   => home_url( '/' ),
					),
					array( 'label' => 'お問い合わせ' ),
				),
			)
		);
		?>

		<section class="contact-form" aria-labelledby="contact-form-heading">
			<div class="contact-form__card">
				<header class="contact-header">
					<h1 id="contact-form-heading" class="contact-header__title">お問い合わせ</h1>
				</header>

				<p class="contact-form__lead">
					果樹栽培に関するご質問やご感想など、お気軽にお問い合わせください。<br>
					通常3〜5営業日以内に、ご入力いただいたメールアドレス宛にご返信いたします。
				</p>

				<?php if ( $cf7 ) : ?>
					<div class="contact-form__cf7">
						<?php echo do_shortcode( $cf7 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php else : ?>
					<p class="contact-form__note">Contact Form 7 でフォームを作成し、有効化してください。</p>
				<?php endif; ?>

				<div class="contact-form__notes">
					<p class="contact-form__note">※営業目的のお問い合わせはご遠慮ください。</p>
					<p class="contact-form__note">※個人情報の取り扱いについては、プライバシーポリシーをご確認ください。</p>
				</div>
			</div>

			<a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>" class="contact-profile-link">プロフィールを見る &gt;</a>
		</section>
	</div>
</main>

	<?php
endwhile;

get_footer();
