<?php
/**
 * Prestaciones de una línea de atención.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$linea = get_queried_object();
?>
<div class="svc-top">
	<div class="wrap" style="padding-block:34px 44px">
		<?php
		medmovil_migas( array(
			array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ),
			array( __( 'Prestaciones', 'medmovil' ), get_post_type_archive_link( 'mm_prestacion' ) ),
			array( $linea->name, '' ),
		) );
		?>
		<p class="eyebrow" style="margin-top:20px;color:<?php echo esc_attr( medmovil_color_linea( $linea ) ); ?>"><?php esc_html_e( 'Línea de atención', 'medmovil' ); ?></p>
		<h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:14px"><?php echo esc_html( $linea->name ); ?></h1>
		<p class="lede" style="margin-top:18px"><?php echo esc_html( $linea->description ); ?></p>
	</div>
</div>

<section class="band">
	<div class="wrap">
		<div class="cats">
			<?php while ( have_posts() ) { the_post(); medmovil_tarjeta_prestacion( get_the_ID() ); } ?>
		</div>
	</div>
</section>

<?php get_template_part( 'partials/convenios' ); ?>
<?php get_footer(); ?>
