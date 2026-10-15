<?php
/**
 * Página estándar.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
while ( have_posts() ) :
	the_post();
	?>
	<div class="svc-top">
		<div class="wrap" style="padding-block:34px 40px">
			<?php medmovil_migas( array( array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ), array( get_the_title(), '' ) ) ); ?>
			<h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:18px"><?php the_title(); ?></h1>
		</div>
	</div>
	<section class="band" style="padding-block:44px 70px">
		<div class="wrap">
			<div class="prose"><?php the_content(); ?></div>
		</div>
	</section>
<?php
endwhile;
get_footer();
