<?php
/**
 * Bloque de cobertura con el mapa de la Región Metropolitana.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once MEDMOVIL_DIR . '/inc/mapa.php';

$comunas = medmovil_comunas( 14 );
$total   = wp_count_posts( 'mm_comuna' );
$restan  = max( 0, (int) $total->publish - count( $comunas ) );
?>
<section class="band" id="cobertura" style="background:var(--surface); border-block:1px solid var(--line)">
	<div class="wrap cover">
		<div data-rv>
			<p class="eyebrow"><?php esc_html_e( 'Cobertura', 'medmovil' ); ?></p>
			<h2 style="margin:14px 0 16px"><?php esc_html_e( 'Llegamos a donde nos necesites', 'medmovil' ); ?></h2>
			<p class="lede" style="margin-bottom:22px">
				<?php
				printf(
					/* translators: %s: región */
					esc_html__( 'Cubrimos toda la %s. Busca tu comuna y te decimos el tiempo estimado de llegada y qué prestaciones tenemos disponibles ahí.', 'medmovil' ),
					esc_html( medmovil_opt( 'region', 'Región Metropolitana' ) )
				);
				?>
			</p>
			<div class="comunas">
				<?php foreach ( $comunas as $c ) : ?>
					<a class="comuna" href="<?php echo esc_url( get_permalink( $c ) ); ?>">
						<?php medmovil_icono( 'i-pin' ); ?> <?php echo esc_html( $c->post_title ); ?>
					</a>
				<?php endforeach; ?>
				<?php if ( $restan ) : ?>
					<a class="comuna" style="border-style:dashed" href="<?php echo esc_url( get_permalink( get_page_by_path( 'cobertura' ) ) ); ?>">
						<?php
						printf(
							/* translators: %d: comunas restantes */
							esc_html__( '+ %d comunas más', 'medmovil' ),
							(int) $restan
						);
						?>
					</a>
				<?php endif; ?>
			</div>
			<p style="font-size:.87rem; color:var(--ink-3); margin-top:20px; display:flex; gap:9px; align-items:flex-start">
				<?php medmovil_icono( 'i-target', 'ico', 'width:15px;height:15px;color:var(--blue);margin-top:3px;flex:none' ); ?>
				<?php esc_html_e( 'Cada comuna tiene su propia página optimizada para Google. Pasa el mouse por el mapa: las comunas de mayor demanda se atienden todos los días y el resto se coordina a pedido.', 'medmovil' ); ?>
			</p>
		</div>

		<div class="mapcard" data-rv>
			<?php medmovil_mapa(); ?>
			<div class="mapout" id="mapout">
				<?php medmovil_icono( 'i-pin' ); ?>
				<span><b id="mapoutN"></b> · <span id="mapoutT"></span></span>
			</div>
			<div class="maplg">
				<span><i style="background:#4890E0"></i> <?php esc_html_e( 'Zona de mayor demanda', 'medmovil' ); ?></span>
				<span><i style="background:#2360A0"></i> <?php esc_html_e( 'Cobertura frecuente', 'medmovil' ); ?></span>
				<span><i style="background:#17456F"></i> <?php esc_html_e( 'Cobertura a pedido', 'medmovil' ); ?></span>
			</div>
		</div>
	</div>

	<script type="application/json" id="mm-comunas">
		<?php
		$datos = array();
		foreach ( medmovil_comunas( -1 ) as $c ) {
			$datos[ $c->post_title ] = array(
				'min'  => (int) get_post_meta( $c->ID, '_mm_tiempo', true ),
				'zona' => get_post_meta( $c->ID, '_mm_zona', true ),
				'url'  => get_permalink( $c ),
			);
		}
		echo wp_json_encode( $datos, JSON_UNESCAPED_UNICODE );
		?>
	</script>
</section>
