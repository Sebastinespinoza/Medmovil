<?php
/** Solicitudes de atención, sin cobro en línea ni asignación automática de cupos. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function medmovil_url_reserva( $examen = 0 ) {
	$pagina = get_page_by_path( 'reservar' );
	$url = $pagina ? get_permalink( $pagina ) : home_url( '/reservar/' );
	return $examen ? add_query_arg( 'examen', (int) $examen, $url ) : $url;
}

/** Validación en el servidor: nunca se aceptan precios enviados por el navegador. */
function medmovil_prestaciones_reserva( $ids ) {
	$ids = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
	if ( ! $ids || count( $ids ) > 8 ) {
		return new WP_Error( 'examenes', __( 'Elige entre uno y ocho exámenes.', 'medmovil' ) );
	}
	$total = 0;
	foreach ( $ids as $id ) {
		$post = get_post( $id );
		$precio = (int) get_post_meta( $id, '_mm_precio', true );
		if ( ! $post || 'mm_prestacion' !== $post->post_type || 'publish' !== $post->post_status || $precio <= 0 ) {
			return new WP_Error( 'no_disponible', __( 'Uno de los exámenes ya no está disponible para reservar. Revisa el catálogo.', 'medmovil' ) );
		}
		$total += $precio;
	}
	return array( 'ids' => $ids, 'total' => $total );
}

/** La solicitud queda pendiente hasta que administración confirme el cupo. */
function medmovil_procesar_reserva() {
	if ( ! is_page_template( 'template-reserva.php' ) || 'POST' !== ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) || ! isset( $_POST['mm_solicitar'] ) ) {
		return;
	}
	if ( 'paciente' !== medmovil_rol() || ! isset( $_POST['mm_reserva_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mm_reserva_nonce'] ) ), 'mm_solicitar_hora' ) ) {
		$GLOBALS['mm_reserva_error'] = __( 'Inicia sesión como paciente y vuelve a enviar la solicitud.', 'medmovil' );
		return;
	}
	$raw = isset( $_POST['examenes'] ) ? (array) wp_unslash( $_POST['examenes'] ) : array();
	$examenes = medmovil_prestaciones_reserva( $raw );
	if ( is_wp_error( $examenes ) ) {
		$GLOBALS['mm_reserva_error'] = $examenes->get_error_message();
		return;
	}
	$fecha = isset( $_POST['fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['fecha'] ) ) : '';
	$hora = isset( $_POST['hora'] ) ? sanitize_text_field( wp_unslash( $_POST['hora'] ) ) : '';
	$comuna = isset( $_POST['comuna'] ) ? sanitize_text_field( wp_unslash( $_POST['comuna'] ) ) : '';
	$direccion = isset( $_POST['direccion'] ) ? sanitize_text_field( wp_unslash( $_POST['direccion'] ) ) : '';
	$fono = isset( $_POST['fono'] ) ? sanitize_text_field( wp_unslash( $_POST['fono'] ) ) : '';
	$dia = DateTimeImmutable::createFromFormat( '!Y-m-d', $fecha, wp_timezone() );
	$hoy = new DateTimeImmutable( 'today', wp_timezone() );
	$comuna_post = null;
	foreach ( medmovil_comunas( 100 ) as $c ) {
		if ( $c->post_title === $comuna ) { $comuna_post = $c; break; }
	}
	if ( ! $dia || $dia->format( 'Y-m-d' ) !== $fecha || $dia <= $hoy || $dia > $hoy->modify( '+90 days' ) || ! in_array( $hora, medmovil_horas(), true ) ) {
		$GLOBALS['mm_reserva_error'] = __( 'Elige una fecha y hora válidas dentro de los próximos 90 días.', 'medmovil' );
		return;
	}
	if ( ! $comuna_post || 'publish' !== $comuna_post->post_status || ! $direccion || mb_strlen( $direccion ) > 250 || ! $fono || mb_strlen( $fono ) > 40 ) {
		$GLOBALS['mm_reserva_error'] = __( 'Revisa la comuna, dirección y teléfono de contacto.', 'medmovil' );
		return;
	}
	$uid = get_current_user_id();
	$firma = md5( implode( ',', $examenes['ids'] ) . '|' . $fecha . '|' . $hora . '|' . $comuna . '|' . $direccion );
	$antigua = get_transient( 'mm_solicitud_' . $uid . '_' . $firma );
	if ( $antigua && medmovil_atencion( (int) $antigua ) ) {
		wp_safe_redirect( medmovil_url_reserva() . '?solicitud=' . (int) $antigua );
		exit;
	}
	$id = wp_insert_post( array(
		'post_type' => 'mm_atencion',
		'post_status' => 'publish',
		'post_author' => $uid,
		'post_title' => sprintf( __( 'Solicitud %1$s · %2$s', 'medmovil' ), $fecha, wp_get_current_user()->display_name ),
	), true );
	if ( is_wp_error( $id ) || ! $id ) {
		$GLOBALS['mm_reserva_error'] = __( 'No pudimos guardar tu solicitud. Inténtalo de nuevo.', 'medmovil' );
		return;
	}
	foreach ( array(
		'_mm_estado' => 'solicitada', '_mm_paciente' => $uid,
		'_mm_prestaciones' => implode( ',', $examenes['ids'] ), '_mm_monto' => $examenes['total'],
		'_mm_fecha' => $fecha, '_mm_hora' => $hora, '_mm_comuna' => $comuna,
		'_mm_direccion' => $direccion, '_mm_fono_reserva' => $fono,
	) as $clave => $valor ) {
		update_post_meta( $id, $clave, $valor );
	}
	set_transient( 'mm_solicitud_' . $uid . '_' . $firma, $id, 10 * MINUTE_IN_SECONDS );
	update_user_meta( $uid, '_mm_fono', $fono );
	update_user_meta( $uid, '_mm_comuna', $comuna );
	update_user_meta( $uid, '_mm_direccion', $direccion );
	wp_mail( get_option( 'admin_email' ), __( 'Nueva solicitud de hora · MedMóvil', 'medmovil' ), sprintf( __( 'Hay una nueva solicitud #%1$d para el %2$s a las %3$s. Revísala y confirma disponibilidad: %4$s', 'medmovil' ), $id, $fecha, $hora, admin_url( 'post.php?post=' . $id . '&action=edit' ) ) );
	wp_mail( wp_get_current_user()->user_email, __( 'Recibimos tu solicitud · MedMóvil', 'medmovil' ), sprintf( __( 'Recibimos tu solicitud #%1$d para el %2$s a las %3$s. Está pendiente de confirmación; aún no es una hora confirmada. Puedes ver su estado en %4$s', 'medmovil' ), $id, $fecha, $hora, medmovil_url_portal( 'horas' ) ) );
	wp_safe_redirect( add_query_arg( 'solicitud', $id, medmovil_url_reserva() ) );
	exit;
}
add_action( 'template_redirect', 'medmovil_procesar_reserva', 20 );
