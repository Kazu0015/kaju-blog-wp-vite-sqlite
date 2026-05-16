<?php
/**
 * 固定ページ（プライバシーポリシー等）
 *
 * @package kaju-blog
 */

get_header();

	while ( have_posts() ) :
	the_post();
	?>

<main>
	<div class="l-container-s">
		<article>
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
		</article>
	</div>
</main>

	<?php
endwhile;

get_footer();
