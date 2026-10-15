<?php
/**
 * Template Name: Portal
 *
 * Una sola página que enruta según quién entra: paciente, profesional o el
 * dueño. Si no hay sesión, muestra el acceso.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Los formularios se procesan antes de imprimir nada, para poder redirigir.
$mm_error = medmovil_portal_acciones();

get_header();

if ( ! is_user_logged_in() ) {
	medmovil_pantalla_acceso( $mm_error );
	get_footer();
	return;
}

$mm_rol = medmovil_rol();

if ( ! $mm_rol ) {
	?>
	<section class="band" style="padding-block:70px">
		<div class="wrap">
			<div class="fl-vacio">
				<?php medmovil_icono( 'i-lock', 'ico' ); ?>
				<b><?php esc_html_e( 'Tu cuenta todavía no tiene un portal asignado', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Escríbenos y lo activamos: toma un minuto.', 'medmovil' ); ?></p>
				<a class="btn btn-p" href="<?php echo esc_url( medmovil_whatsapp() ); ?>" target="_blank" rel="noopener">
					<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Escribir por WhatsApp', 'medmovil' ); ?>
				</a>
			</div>
		</div>
	</section>
	<?php
	get_footer();
	return;
}

$mm_menu    = medmovil_secciones( $mm_rol );
$mm_seccion = isset( $_GET['p'] ) ? sanitize_key( wp_unslash( $_GET['p'] ) ) : 'resumen';
if ( ! isset( $mm_menu[ $mm_seccion ] ) ) {
	$mm_seccion = key( $mm_menu );
}
$mm_titulo = isset( $mm_menu[ $mm_seccion ] ) ? $mm_menu[ $mm_seccion ][1] : __( 'Mi cuenta', 'medmovil' );

medmovil_panel_abrir( $mm_rol, $mm_seccion, $mm_titulo );

if ( $mm_error ) {
	printf(
		'<div class="pn-nota pn-nota-bad"><div><b>%s</b><p>%s</p></div></div>',
		esc_html__( 'No se pudo guardar', 'medmovil' ),
		esc_html( $mm_error )
	);
}

if ( isset( $_GET['ok'] ) ) {
	$mm_ok = array(
		'guardado' => __( 'Listo, se guardó.', 'medmovil' ),
		'subido'   => __( 'Informe subido. Al paciente ya le llegó el aviso por correo.', 'medmovil' ),
	);
	$mm_clave = sanitize_key( wp_unslash( $_GET['ok'] ) );
	if ( isset( $mm_ok[ $mm_clave ] ) ) {
		printf(
			'<div class="pn-nota pn-nota-ok"><div><b>%s</b></div></div>',
			esc_html( $mm_ok[ $mm_clave ] )
		);
	}
}

switch ( $mm_rol ) {
	case 'profesional':
		medmovil_portal_profesional( $mm_seccion );
		break;
	case 'admin':
		medmovil_portal_admin( $mm_seccion );
		break;
	default:
		medmovil_portal_paciente( $mm_seccion );
}

medmovil_panel_cerrar();

get_footer();
