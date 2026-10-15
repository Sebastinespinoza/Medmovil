<?php
/**
 * Banda de convenios institucionales.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="band" id="convenios">
	<div class="wrap">
		<div class="b2b" data-rv>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Convenios institucionales', 'medmovil' ); ?></p>
				<h2 style="margin:14px 0 14px"><?php esc_html_e( 'Para ELEAM, centros de reposo, consultorios y empresas', 'medmovil' ); ?></h2>
				<p class="lede"><?php esc_html_e( 'Coordinamos visitas programadas para varios pacientes en una misma jornada, con tarifas por volumen, informes centralizados y un contacto directo para la institución. Sin que ningún residente tenga que salir del recinto.', 'medmovil' ); ?></p>
				<div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:24px">
					<a class="btn btn-p" href="<?php echo esc_url( medmovil_whatsapp( __( 'Hola, represento a una institución y quiero conversar un convenio.', 'medmovil' ) ) ); ?>" target="_blank" rel="noopener">
						<?php medmovil_icono( 'i-users' ); ?> <?php esc_html_e( 'Solicitar convenio', 'medmovil' ); ?>
					</a>
					<a class="btn btn-o" href="<?php echo esc_url( get_permalink( get_page_by_path( 'convenios' ) ) ); ?>">
						<?php medmovil_icono( 'i-doc' ); ?> <?php esc_html_e( 'Ver cómo funciona', 'medmovil' ); ?>
					</a>
				</div>
			</div>
			<div class="b2b-stats">
				<div class="stat"><b><?php esc_html_e( '1 jornada', 'medmovil' ); ?></b><span><?php esc_html_e( 'Hasta 10 pacientes atendidos en la misma visita', 'medmovil' ); ?></span></div>
				<div class="stat"><b><?php esc_html_e( '24 h', 'medmovil' ); ?></b><span><?php esc_html_e( 'Informes cargados para todo el grupo', 'medmovil' ); ?></span></div>
				<div class="stat"><b>0</b><span><?php esc_html_e( 'Traslados de residentes, ambulancias y acompañantes', 'medmovil' ); ?></span></div>
				<div class="stat"><b><?php echo esc_html( count( medmovil_comunas( -1 ) ) ); ?></b><span><?php esc_html_e( 'Comunas con cobertura publicada', 'medmovil' ); ?></span></div>
			</div>
		</div>
	</div>
</section>
