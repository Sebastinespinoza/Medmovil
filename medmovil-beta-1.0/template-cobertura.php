<?php
/**
 * Template Name: Cobertura por comuna
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
$zonas = array(
	'a' => array( __( 'Zona de mayor demanda', 'medmovil' ), __( 'Atendemos todos los días, con la agenda siempre abierta.', 'medmovil' ) ),
	'b' => array( __( 'Cobertura frecuente', 'medmovil' ), __( 'Varios días por semana, según la disponibilidad del equipo.', 'medmovil' ) ),
	'c' => array( __( 'Cobertura a pedido', 'medmovil' ), __( 'Coordinamos la visita según el examen y la fecha que necesites.', 'medmovil' ) ),
);
?>
<div class="svc-top">
	<div class="wrap" style="padding-block:34px 40px">
		<?php medmovil_migas( array( array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ), array( get_the_title(), '' ) ) ); ?>
		<p class="eyebrow" style="margin-top:20px"><?php echo esc_html( medmovil_opt( 'region', '' ) ); ?></p>
		<h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:14px"><?php esc_html_e( 'Dónde llegamos', 'medmovil' ); ?></h1>
		<div class="prose" style="margin-top:18px"><?php while ( have_posts() ) { the_post(); the_content(); } ?></div>
	</div>
</div>

<?php foreach ( $zonas as $clave => $zona ) :
	$comunas = get_posts( array(
		'post_type'      => 'mm_comuna',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC' ),
		'meta_query'     => array( array( 'key' => '_mm_zona', 'value' => $clave ) ),
	) );
	if ( ! $comunas ) {
		continue;
	}
	?>
	<section class="band" style="padding-block:40px">
		<div class="wrap">
			<div class="sec-head" style="margin-bottom:22px" data-rv>
				<div>
					<p class="eyebrow"><?php echo esc_html( $zona[0] ); ?></p>
					<h2 style="margin-top:12px;font-size:clamp(1.3rem,2.2vw,1.7rem)"><?php echo esc_html( $zona[1] ); ?></h2>
				</div>
			</div>
			<div class="rel" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
				<?php foreach ( $comunas as $c ) : ?>
					<a href="<?php echo esc_url( get_permalink( $c ) ); ?>">
						<b><?php echo esc_html( $c->post_title ); ?></b>
						<span><?php echo esc_html( sprintf( __( 'Llegada ~%d min', 'medmovil' ), (int) get_post_meta( $c->ID, '_mm_tiempo', true ) ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endforeach; ?>

<?php get_template_part( 'partials/convenios' ); ?>
<?php get_footer(); ?>
