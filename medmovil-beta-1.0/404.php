<?php
/**
 * Página no encontrada.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<section class="band" style="padding-block:70px">
	<div class="wrap" style="max-width:720px;text-align:center">
		<p class="eyebrow" style="justify-content:center"><?php esc_html_e( 'Error 404', 'medmovil' ); ?></p>
		<h1 style="font-size:clamp(2rem,4vw,3rem);margin:18px 0"><?php esc_html_e( 'Esta página no existe', 'medmovil' ); ?></h1>
		<p class="lede" style="margin-inline:auto"><?php esc_html_e( 'Puede que el enlace esté mal escrito o que hayamos movido el contenido. Prueba con el catálogo de exámenes o escríbenos por WhatsApp.', 'medmovil' ); ?></p>
		<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:28px">
			<a class="btn btn-p" href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>"><?php esc_html_e( 'Ver todos los exámenes', 'medmovil' ); ?></a>
			<a class="btn btn-o" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Ir al inicio', 'medmovil' ); ?></a>
		</div>
	</div>
</section>
<?php get_footer(); ?>
