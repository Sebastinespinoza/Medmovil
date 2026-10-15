<?php
/**
 * Base de los portales: roles, atenciones y archivos privados.
 *
 * Una "atención" es una hora agendada. Es la pieza central del sistema: la crea
 * el paciente (o Sebastián a mano), la toma un profesional, cambia de estado
 * hasta quedar realizada, y de ahí salen el informe, el pago y la comisión.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Roles del sitio.
 *
 * El paciente y el profesional no entran nunca al escritorio de WordPress: ven
 * su portal en el frontend. Por eso ninguno tiene capacidades de administración.
 */
function medmovil_crear_roles() {
	if ( ! get_role( 'mm_paciente' ) ) {
		add_role( 'mm_paciente', __( 'Paciente', 'medmovil' ), array( 'read' => true ) );
	}
	if ( ! get_role( 'mm_profesional' ) ) {
		add_role( 'mm_profesional', __( 'Profesional MedMóvil', 'medmovil' ), array(
			'read' => true,
		) );
	}
	// Los informes usan una ruta privada; no se suben a la biblioteca pública.
	get_role( 'mm_profesional' )->remove_cap( 'upload_files' );
}

/**
 * ¿Qué es este usuario dentro del portal?
 *
 * @param int $user_id ID, o 0 para el usuario actual.
 * @return string 'admin', 'profesional', 'paciente' o ''.
 */
function medmovil_rol( $user_id = 0 ) {
	$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();
	if ( ! $user || ! $user->ID ) {
		return '';
	}
	if ( user_can( $user, 'manage_options' ) ) {
		return 'admin';
	}
	if ( in_array( 'mm_profesional', (array) $user->roles, true ) ) {
		return 'profesional';
	}
	if ( in_array( 'mm_paciente', (array) $user->roles, true ) ) {
		return 'paciente';
	}

	return '';
}

/**
 * Registra el tipo de contenido de las atenciones.
 */
function medmovil_cpt_atencion() {
	register_post_type( 'mm_atencion', array(
		'labels' => array(
			'name'          => __( 'Atenciones', 'medmovil' ),
			'singular_name' => __( 'Atención', 'medmovil' ),
			'add_new_item'  => __( 'Añadir atención', 'medmovil' ),
			'edit_item'     => __( 'Editar atención', 'medmovil' ),
			'not_found'     => __( 'Todavía no hay atenciones', 'medmovil' ),
			'menu_name'     => __( 'Atenciones', 'medmovil' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => 'medmovil',
		'menu_icon'     => 'dashicons-calendar-alt',
		'supports'      => array( 'title' ),
		'map_meta_cap'  => true,
		'capability_type' => 'post',
	) );
}
add_action( 'init', 'medmovil_cpt_atencion' );

/**
 * Estados posibles de una atención.
 *
 * @return array
 */
function medmovil_estados() {
	return array(
		'solicitada' => array( 'n' => __( 'Pendiente de confirmación', 'medmovil' ), 'c' => 'warn' ),
		'agendada'   => array( 'n' => __( 'Agendada', 'medmovil' ), 'c' => 'warn' ),
		'confirmada' => array( 'n' => __( 'Confirmada', 'medmovil' ), 'c' => 'info' ),
		'realizada'  => array( 'n' => __( 'Realizada', 'medmovil' ), 'c' => 'ok' ),
		'informada'  => array( 'n' => __( 'Informe entregado', 'medmovil' ), 'c' => 'ok' ),
		'cancelada'  => array( 'n' => __( 'Cancelada', 'medmovil' ), 'c' => 'bad' ),
	);
}

/**
 * Etiqueta de estado lista para imprimir.
 *
 * @param string $estado Clave.
 */
function medmovil_estado_pill( $estado ) {
	$mapa = medmovil_estados();
	$def  = isset( $mapa[ $estado ] ) ? $mapa[ $estado ] : array( 'n' => $estado, 'c' => 'info' );

	printf(
		'<span class="pill pill-%s">%s</span>',
		esc_attr( $def['c'] ),
		esc_html( $def['n'] )
	);
}

/**
 * Comisión de la plataforma, en porcentaje.
 *
 * @return float
 */
function medmovil_comision_pct() {
	return (float) medmovil_opt( 'comision', '5' );
}

/**
 * Lee una atención con todo lo que hace falta para mostrarla.
 *
 * @param int|WP_Post $post Atención.
 * @return array|null
 */
function medmovil_atencion( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'mm_atencion' !== $post->post_type ) {
		return null;
	}

	$ids = array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $post->ID, '_mm_prestaciones', true ) ) ) );
	$examenes = array();
	foreach ( $ids as $id ) {
		$e = get_post( $id );
		if ( $e ) {
			$examenes[] = array(
				'id' => $id,
				'n'  => $e->post_title,
				'p'  => (int) get_post_meta( $id, '_mm_precio', true ),
				'm'  => (int) get_post_meta( $id, '_mm_duracion', true ),
				'i'  => get_post_meta( $id, '_mm_icono', true ) ?: 'il-eco',
				'u'  => get_permalink( $id ),
			);
		}
	}

	$monto = (int) get_post_meta( $post->ID, '_mm_monto', true );
	if ( ! $monto ) {
		foreach ( $examenes as $e ) {
			$monto += $e['p'];
		}
	}

	$fecha = (string) get_post_meta( $post->ID, '_mm_fecha', true );
	$hora  = (string) get_post_meta( $post->ID, '_mm_hora', true );

	return array(
		'id'          => $post->ID,
		'estado'      => get_post_meta( $post->ID, '_mm_estado', true ) ?: 'agendada',
		'paciente'    => (int) get_post_meta( $post->ID, '_mm_paciente', true ),
		'profesional' => (int) get_post_meta( $post->ID, '_mm_profesional', true ),
		'examenes'    => $examenes,
		'fecha'       => $fecha,
		'hora'        => $hora,
		'ts'          => $fecha ? strtotime( $fecha . ' ' . ( $hora ? $hora : '00:00' ) ) : 0,
		'comuna'      => (string) get_post_meta( $post->ID, '_mm_comuna', true ),
		'direccion'   => (string) get_post_meta( $post->ID, '_mm_direccion', true ),
		'monto'       => $monto,
		'pagado'      => (int) get_post_meta( $post->ID, '_mm_pagado', true ),
		'medio'       => (string) get_post_meta( $post->ID, '_mm_medio', true ),
		'comision'    => round( (int) get_post_meta( $post->ID, '_mm_pagado', true ) * medmovil_comision_pct() / 100 ),
		'informe'     => (string) get_post_meta( $post->ID, '_mm_informe', true ),
		'notas'       => (string) get_post_meta( $post->ID, '_mm_notas', true ),
		'motivo'      => (string) get_post_meta( $post->ID, '_mm_motivo', true ),
	);
}

/**
 * Busca atenciones.
 *
 * @param array $args Filtros: paciente, profesional, estado, desde, hasta, orden, limite.
 * @return array Lista de atenciones ya resueltas.
 */
function medmovil_atenciones( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'paciente'    => 0,
		'profesional' => 0,
		'estado'      => '',
		'desde'       => '',
		'hasta'       => '',
		'orden'       => 'DESC',
		'limite'      => 50,
	) );

	$meta = array( 'relation' => 'AND' );

	if ( $args['paciente'] ) {
		$meta[] = array( 'key' => '_mm_paciente', 'value' => (int) $args['paciente'] );
	}
	if ( $args['profesional'] ) {
		$meta[] = array( 'key' => '_mm_profesional', 'value' => (int) $args['profesional'] );
	}
	if ( $args['estado'] ) {
		$meta[] = array( 'key' => '_mm_estado', 'value' => (array) $args['estado'], 'compare' => 'IN' );
	}
	// Las fechas se guardan como Y-m-d, así que se comparan como texto y ordenan
	// igual de bien. Con type DATE, la capa SQLite no devuelve nada.
	if ( $args['desde'] ) {
		$meta[] = array( 'key' => '_mm_fecha', 'value' => $args['desde'], 'compare' => '>=' );
	}
	if ( $args['hasta'] ) {
		$meta[] = array( 'key' => '_mm_fecha', 'value' => $args['hasta'], 'compare' => '<=' );
	}

	$posts = get_posts( array(
		'post_type'      => 'mm_atencion',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $args['limite'],
		'meta_key'       => '_mm_fecha',
		'orderby'        => 'meta_value',
		'order'          => 'ASC' === $args['orden'] ? 'ASC' : 'DESC',
		'meta_query'     => count( $meta ) > 1 ? $meta : array(),
	) );

	return array_filter( array_map( 'medmovil_atencion', $posts ) );
}

/**
 * Suma los montos y comisiones de una lista de atenciones.
 *
 * @param array $lista Atenciones.
 * @return array
 */
function medmovil_totales( $lista ) {
	$t = array( 'n' => 0, 'monto' => 0, 'pagado' => 0, 'comision' => 0, 'realizadas' => 0, 'canceladas' => 0 );

	foreach ( $lista as $a ) {
		$t['n']++;
		if ( in_array( $a['estado'], array( 'realizada', 'informada' ), true ) ) {
			$t['realizadas']++;
			$t['monto']    += $a['monto'];
			$t['pagado']   += $a['pagado'];
			$t['comision'] += $a['comision'];
		}
		if ( 'cancelada' === $a['estado'] ) {
			$t['canceladas']++;
		}
	}

	return $t;
}

/* ============================================================
   Archivos privados
   ============================================================ */

/**
 * Carpeta donde viven los informes. Nunca se sirve directo desde el navegador.
 *
 * @return string
 */
function medmovil_dir_privado() {
	$subida = wp_upload_dir();
	$dir    = defined( 'MEDMOVIL_PRIVATE_DIR' ) ? (string) MEDMOVIL_PRIVATE_DIR : trailingslashit( $subida['basedir'] ) . 'medmovil-privado';

	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	// Los guardias se revisan cada vez, no solo al crear la carpeta: al mudar
	// de hosting por FTP los archivos que empiezan con punto se pierden muy
	// seguido, y esta carpeta guarda informes médicos.
	if ( ! file_exists( $dir . '/.htaccess' ) ) {
		file_put_contents( $dir . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
	}
	if ( ! file_exists( $dir . '/index.php' ) ) {
		file_put_contents( $dir . '/index.php', "<?php // Silencio.\n" );
	}

	return $dir;
}

/**
 * Guarda un archivo subido en la carpeta privada.
 *
 * @param array $archivo Entrada de $_FILES.
 * @param int   $atencion_id Atención a la que pertenece.
 * @return string|WP_Error Nombre guardado.
 */
function medmovil_guardar_archivo( $archivo, $atencion_id ) {
	$permitidos = array( 'pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png' );
	if ( ! empty( $archivo['error'] ) || empty( $archivo['tmp_name'] ) || ! is_uploaded_file( $archivo['tmp_name'] ) ) {
		return new WP_Error( 'subida', __( 'No se pudo recibir el archivo. Inténtalo de nuevo.', 'medmovil' ) );
	}
	$tipo       = wp_check_filetype( $archivo['name'], $permitidos );

	if ( ! $tipo['ext'] ) {
		return new WP_Error( 'tipo', __( 'Solo se aceptan archivos PDF, JPG o PNG.', 'medmovil' ) );
	}
	if ( filesize( $archivo['tmp_name'] ) > 20 * MB_IN_BYTES ) {
		return new WP_Error( 'peso', __( 'El archivo no puede pesar más de 20 MB.', 'medmovil' ) );
	}

	// Comprobamos que el contenido sea de verdad lo que dice la extensión.
	$finfo = function_exists( 'finfo_open' ) ? finfo_open( FILEINFO_MIME_TYPE ) : false;
	$real = $finfo ? finfo_file( $finfo, $archivo['tmp_name'] ) : $tipo['type'];
	if ( $finfo ) { finfo_close( $finfo ); }
	if ( $real !== $tipo['type'] ) {
		return new WP_Error( 'contenido', __( 'El archivo no parece ser un PDF ni una imagen.', 'medmovil' ) );
	}

	$nombre = sprintf( 'a%d-%s.%s', (int) $atencion_id, wp_generate_password( 20, false, false ), $tipo['ext'] );
	$destino = trailingslashit( medmovil_dir_privado() ) . $nombre;

	if ( ! move_uploaded_file( $archivo['tmp_name'], $destino ) ) {
		return new WP_Error( 'mover', __( 'No se pudo guardar el archivo.', 'medmovil' ) );
	}
	@chmod( $destino, 0640 );

	return $nombre;
}

/**
 * ¿Puede este usuario ver el informe de esta atención?
 *
 * @param array $atencion Atención.
 * @param int   $user_id  Usuario.
 * @return bool
 */
function medmovil_puede_ver( $atencion, $user_id ) {
	if ( ! $atencion || ! $user_id ) {
		return false;
	}
	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}

	return (int) $atencion['paciente'] === (int) $user_id
		|| (int) $atencion['profesional'] === (int) $user_id;
}

/**
 * Entrega un informe, después de comprobar quién lo pide.
 */
function medmovil_servir_archivo() {
	$id = isset( $_GET['a'] ) ? (int) $_GET['a'] : 0;

	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'Tienes que iniciar sesión para ver este documento.', 'medmovil' ), '', array( 'response' => 403 ) );
	}

	$atencion = medmovil_atencion( $id );
	if ( ! $atencion || ! medmovil_puede_ver( $atencion, get_current_user_id() ) ) {
		wp_die( esc_html__( 'Este documento no está disponible para tu cuenta.', 'medmovil' ), '', array( 'response' => 403 ) );
	}
	if ( ! $atencion['informe'] ) {
		wp_die( esc_html__( 'Todavía no hay informe cargado para esta atención.', 'medmovil' ), '', array( 'response' => 404 ) );
	}

	// El nombre viene de la base de datos, pero igual lo limpiamos antes de tocar el disco.
	$nombre = basename( $atencion['informe'] );
	$ruta   = trailingslashit( medmovil_dir_privado() ) . $nombre;

	if ( ! file_exists( $ruta ) ) {
		wp_die( esc_html__( 'El archivo ya no está disponible.', 'medmovil' ), '', array( 'response' => 404 ) );
	}

	$tipos = array( 'pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png' );
	$ext   = strtolower( pathinfo( $nombre, PATHINFO_EXTENSION ) );

	$visible = sprintf(
		'MedMovil-informe-%s-%s.%s',
		$atencion['id'],
		$atencion['fecha'] ? $atencion['fecha'] : gmdate( 'Y-m-d' ),
		$ext
	);

	nocache_headers();
	header( 'Content-Type: ' . ( isset( $tipos[ $ext ] ) ? $tipos[ $ext ] : 'application/octet-stream' ) );
	header( 'Content-Length: ' . filesize( $ruta ) );
	header( 'Content-Disposition: inline; filename="' . $visible . '"' );
	header( 'X-Content-Type-Options: nosniff' );
	readfile( $ruta );
	exit;
}
add_action( 'admin_post_medmovil_archivo', 'medmovil_servir_archivo' );
// Sin la versión nopriv, quien llega sin sesión recibe un 400 pelado de
// WordPress en vez del mensaje que explica que tiene que entrar.
add_action( 'admin_post_nopriv_medmovil_archivo', 'medmovil_servir_archivo' );

/**
 * URL del informe de una atención.
 *
 * @param int $atencion_id ID.
 * @return string
 */
function medmovil_url_informe( $atencion_id ) {
	return add_query_arg(
		array( 'action' => 'medmovil_archivo', 'a' => (int) $atencion_id ),
		admin_url( 'admin-post.php' )
	);
}

/* ============================================================
   Ficha clínica y disponibilidad, guardadas en el usuario
   ============================================================ */

/**
 * Campos de la ficha clínica que completa el paciente.
 *
 * @return array
 */
function medmovil_campos_ficha() {
	return array(
		'edad'         => array( 'l' => __( 'Edad del paciente', 'medmovil' ), 't' => 'number' ),
		'movilidad'    => array(
			'l' => __( '¿Cómo se moviliza?', 'medmovil' ),
			't' => 'select',
			'o' => array(
				''           => __( 'Sin especificar', 'medmovil' ),
				'camina'     => __( 'Camina sin ayuda', 'medmovil' ),
				'apoyo'      => __( 'Camina con bastón o andador', 'medmovil' ),
				'silla'      => __( 'Usa silla de ruedas', 'medmovil' ),
				'postrado'   => __( 'Está postrado', 'medmovil' ),
			),
		),
		'antecedentes' => array( 'l' => __( 'Enfermedades o cirugías anteriores', 'medmovil' ), 't' => 'textarea' ),
		'medicamentos' => array( 'l' => __( 'Medicamentos que toma', 'medmovil' ), 't' => 'textarea' ),
		'alergias'     => array( 'l' => __( 'Alergias', 'medmovil' ), 't' => 'textarea' ),
		'motivo'       => array( 'l' => __( '¿Por qué le pidieron este examen?', 'medmovil' ), 't' => 'textarea' ),
		'acceso'       => array( 'l' => __( 'Cómo llegar: piso, ascensor, portería, estacionamiento', 'medmovil' ), 't' => 'textarea' ),
		'contacto'     => array( 'l' => __( 'Teléfono de quien recibe al profesional', 'medmovil' ), 't' => 'text' ),
	);
}

/**
 * Lee la ficha clínica de un paciente.
 *
 * @param int $user_id Usuario.
 * @return array
 */
function medmovil_ficha( $user_id ) {
	$ficha = get_user_meta( $user_id, '_mm_ficha', true );

	return is_array( $ficha ) ? $ficha : array();
}

/**
 * ¿Qué tan completa está la ficha? Sirve para empujar al paciente a llenarla.
 *
 * @param int $user_id Usuario.
 * @return int Porcentaje.
 */
function medmovil_ficha_pct( $user_id ) {
	$ficha  = medmovil_ficha( $user_id );
	$campos = medmovil_campos_ficha();
	$llenos = 0;

	foreach ( array_keys( $campos ) as $k ) {
		if ( ! empty( $ficha[ $k ] ) ) {
			$llenos++;
		}
	}

	return (int) round( $llenos / max( count( $campos ), 1 ) * 100 );
}

/**
 * Bloques horarios que abre un profesional, por día de la semana.
 *
 * @param int $user_id Profesional.
 * @return array [1..7] => array de horas 'HH:MM'.
 */
function medmovil_disponibilidad( $user_id ) {
	$d = get_user_meta( $user_id, '_mm_semana', true );

	return is_array( $d ) ? $d : array();
}

/**
 * Horas que atiende el servicio, para armar la grilla.
 *
 * @return array
 */
function medmovil_horas() {
	$horas = array();
	for ( $h = 8; $h <= 19; $h++ ) {
		$horas[] = sprintf( '%02d:00', $h );
	}

	return $horas;
}

/**
 * Días de la semana en español, empezando por el lunes.
 *
 * @return array
 */
function medmovil_dias() {
	return array(
		1 => __( 'Lunes', 'medmovil' ),
		2 => __( 'Martes', 'medmovil' ),
		3 => __( 'Miércoles', 'medmovil' ),
		4 => __( 'Jueves', 'medmovil' ),
		5 => __( 'Viernes', 'medmovil' ),
		6 => __( 'Sábado', 'medmovil' ),
		7 => __( 'Domingo', 'medmovil' ),
	);
}

/* ============================================================
   Edición de atenciones desde el escritorio
   ============================================================ */

/**
 * Caja de edición de una atención.
 */
function medmovil_meta_atencion() {
	add_meta_box( 'mm_atencion', __( 'Datos de la atención', 'medmovil' ), 'medmovil_caja_atencion', 'mm_atencion', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'medmovil_meta_atencion' );

/**
 * Dibuja la caja.
 *
 * @param WP_Post $post Atención.
 */
function medmovil_caja_atencion( $post ) {
	wp_nonce_field( 'mm_atencion', 'mm_atencion_nonce' );
	$a = medmovil_atencion( $post );
	$ids = wp_list_pluck( $a['examenes'], 'id' );
	?>
	<style>.mmat td{padding:8px 10px 8px 0}.mmat th{text-align:left;width:190px;padding:8px 10px 8px 0}</style>
	<table class="mmat">
		<tr>
			<th><label for="mm_paciente"><?php esc_html_e( 'Paciente', 'medmovil' ); ?></label></th>
			<td>
				<select id="mm_paciente" name="mm_paciente" style="min-width:320px">
					<option value=""><?php esc_html_e( '— sin cuenta —', 'medmovil' ); ?></option>
					<?php foreach ( get_users( array( 'role__in' => array( 'mm_paciente', 'subscriber' ), 'number' => 500 ) ) as $u ) : ?>
						<option value="<?php echo (int) $u->ID; ?>" <?php selected( $a['paciente'], $u->ID ); ?>>
							<?php echo esc_html( $u->display_name . ' — ' . $u->user_email ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="mm_profesional"><?php esc_html_e( 'Profesional', 'medmovil' ); ?></label></th>
			<td>
				<select id="mm_profesional" name="mm_profesional" style="min-width:320px">
					<option value=""><?php esc_html_e( '— por asignar —', 'medmovil' ); ?></option>
					<?php foreach ( get_users( array( 'role' => 'mm_profesional', 'number' => 200 ) ) as $u ) : ?>
						<option value="<?php echo (int) $u->ID; ?>" <?php selected( $a['profesional'], $u->ID ); ?>>
							<?php echo esc_html( $u->display_name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Comprueba disponibilidad, cobertura y credenciales antes de confirmar. Solo administración asigna operadores.', 'medmovil' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mm_prestaciones"><?php esc_html_e( 'Exámenes', 'medmovil' ); ?></label></th>
			<td>
				<select id="mm_prestaciones" name="mm_prestaciones[]" multiple size="9" style="min-width:320px">
					<?php foreach ( get_posts( array( 'post_type' => 'mm_prestacion', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) : ?>
						<option value="<?php echo (int) $p->ID; ?>" <?php selected( in_array( $p->ID, $ids, true ) ); ?>>
							<?php echo esc_html( $p->post_title . ' — ' . medmovil_precio( get_post_meta( $p->ID, '_mm_precio', true ) ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Ctrl o Cmd para elegir varios. El monto se calcula solo si lo dejas en cero.', 'medmovil' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="mm_fecha"><?php esc_html_e( 'Fecha y hora', 'medmovil' ); ?></label></th>
			<td>
				<input type="date" id="mm_fecha" name="mm_fecha" value="<?php echo esc_attr( $a['fecha'] ); ?>">
				<input type="time" name="mm_hora" value="<?php echo esc_attr( $a['hora'] ); ?>" step="3600">
			</td>
		</tr>
		<tr>
			<th><label for="mm_estado"><?php esc_html_e( 'Estado', 'medmovil' ); ?></label></th>
			<td>
				<select id="mm_estado" name="mm_estado">
					<?php foreach ( medmovil_estados() as $k => $e ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $a['estado'], $k ); ?>><?php echo esc_html( $e['n'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="mm_comuna_a"><?php esc_html_e( 'Comuna y dirección', 'medmovil' ); ?></label></th>
			<td>
				<input type="text" id="mm_comuna_a" name="mm_comuna" value="<?php echo esc_attr( $a['comuna'] ); ?>" placeholder="<?php esc_attr_e( 'Comuna', 'medmovil' ); ?>">
				<input type="text" name="mm_direccion" value="<?php echo esc_attr( $a['direccion'] ); ?>" placeholder="<?php esc_attr_e( 'Dirección', 'medmovil' ); ?>" style="min-width:320px">
			</td>
		</tr>
		<tr>
			<th><label for="mm_monto"><?php esc_html_e( 'Monto y pago', 'medmovil' ); ?></label></th>
			<td>
				<input type="number" id="mm_monto" name="mm_monto" value="<?php echo esc_attr( get_post_meta( $post->ID, '_mm_monto', true ) ); ?>" step="1000" placeholder="<?php esc_attr_e( 'Monto', 'medmovil' ); ?>">
				<input type="number" name="mm_pagado" value="<?php echo esc_attr( $a['pagado'] ); ?>" step="1000" placeholder="<?php esc_attr_e( 'Cobrado', 'medmovil' ); ?>">
				<select name="mm_medio">
					<?php foreach ( array( '' => __( 'Medio de pago', 'medmovil' ), 'efectivo' => __( 'Efectivo', 'medmovil' ), 'transferencia' => __( 'Transferencia', 'medmovil' ), 'tarjeta' => __( 'Tarjeta', 'medmovil' ) ) as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $a['medio'], $k ); ?>><?php echo esc_html( $v ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?php
					printf(
						/* translators: 1: porcentaje, 2: monto */
						esc_html__( 'Comisión al %1$s%%: %2$s', 'medmovil' ),
						esc_html( medmovil_comision_pct() ),
						esc_html( medmovil_precio( $a['comision'] ) )
					);
					?>
				</p>
			</td>
		</tr>
		<tr>
			<th><label for="mm_notas_a"><?php esc_html_e( 'Notas', 'medmovil' ); ?></label></th>
			<td><textarea id="mm_notas_a" name="mm_notas" rows="3" class="large-text"><?php echo esc_textarea( $a['notas'] ); ?></textarea></td>
		</tr>
		<?php if ( $a['informe'] ) : ?>
			<tr>
				<th><?php esc_html_e( 'Informe', 'medmovil' ); ?></th>
				<td><a href="<?php echo esc_url( medmovil_url_informe( $post->ID ) ); ?>" target="_blank"><?php esc_html_e( 'Ver el archivo cargado', 'medmovil' ); ?></a></td>
			</tr>
		<?php endif; ?>
	</table>
	<?php
}

/**
 * Guarda la atención.
 *
 * @param int $post_id ID.
 */
function medmovil_guardar_atencion( $post_id ) {
	if ( ! isset( $_POST['mm_atencion_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mm_atencion_nonce'] ), 'mm_atencion' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$antes = (int) get_post_meta( $post_id, '_mm_profesional', true );
	$estado_antes = (string) get_post_meta( $post_id, '_mm_estado', true );

	foreach ( array( 'paciente', 'profesional', 'monto', 'pagado' ) as $k ) {
		if ( isset( $_POST[ 'mm_' . $k ] ) ) {
			update_post_meta( $post_id, '_mm_' . $k, (int) $_POST[ 'mm_' . $k ] );
		}
	}
	foreach ( array( 'fecha', 'hora', 'estado', 'comuna', 'direccion', 'medio' ) as $k ) {
		if ( isset( $_POST[ 'mm_' . $k ] ) ) {
			update_post_meta( $post_id, '_mm_' . $k, sanitize_text_field( wp_unslash( $_POST[ 'mm_' . $k ] ) ) );
		}
	}
	if ( isset( $_POST['mm_notas'] ) ) {
		update_post_meta( $post_id, '_mm_notas', sanitize_textarea_field( wp_unslash( $_POST['mm_notas'] ) ) );
	}
	if ( isset( $_POST['mm_prestaciones'] ) ) {
		update_post_meta( $post_id, '_mm_prestaciones', implode( ',', array_map( 'intval', (array) $_POST['mm_prestaciones'] ) ) );
	}

	// Un título legible, para no ver "Entrada #128" en el listado.
	$a = medmovil_atencion( $post_id );
	$u = $a['paciente'] ? get_userdata( $a['paciente'] ) : null;
	$titulo = sprintf(
		'%s · %s',
		$a['fecha'] ? $a['fecha'] : __( 'sin fecha', 'medmovil' ),
		$u ? $u->display_name : __( 'sin paciente', 'medmovil' )
	);

	remove_action( 'save_post_mm_atencion', 'medmovil_guardar_atencion' );
	wp_update_post( array( 'ID' => $post_id, 'post_title' => $titulo ) );
	add_action( 'save_post_mm_atencion', 'medmovil_guardar_atencion' );

	// Si recién le asignamos profesional, le avisamos.
	$ahora = (int) get_post_meta( $post_id, '_mm_profesional', true );
	if ( $ahora && $ahora !== $antes ) {
		medmovil_avisar_agenda( $post_id );
	}
	if ( $ahora && 'confirmada' === $a['estado'] && ( 'confirmada' !== $estado_antes || $ahora !== $antes ) && $u ) {
		$profesional = get_userdata( $ahora );
		wp_mail( $u->user_email, __( 'Tu hora fue confirmada · MedMóvil', 'medmovil' ), sprintf(
			__( 'Tu visita del %1$s a las %2$s fue confirmada. Profesional: %3$s. Puedes ver los detalles en tu cuenta: %4$s', 'medmovil' ),
			$a['fecha'], $a['hora'], $profesional ? $profesional->display_name : __( 'Por asignar', 'medmovil' ), medmovil_url_portal( 'horas' )
		) );
	}
}
add_action( 'save_post_mm_atencion', 'medmovil_guardar_atencion' );

/**
 * Columnas del listado de atenciones.
 *
 * @param array $cols Columnas.
 * @return array
 */
function medmovil_cols_atencion( $cols ) {
	return array(
		'cb'        => isset( $cols['cb'] ) ? $cols['cb'] : '',
		'title'     => __( 'Atención', 'medmovil' ),
		'mm_estado' => __( 'Estado', 'medmovil' ),
		'mm_prof'   => __( 'Profesional', 'medmovil' ),
		'mm_monto'  => __( 'Monto', 'medmovil' ),
		'mm_com'    => __( 'Comisión', 'medmovil' ),
	);
}
add_filter( 'manage_mm_atencion_posts_columns', 'medmovil_cols_atencion' );

/**
 * Contenido de las columnas.
 *
 * @param string $col     Columna.
 * @param int    $post_id ID.
 */
function medmovil_col_atencion( $col, $post_id ) {
	$a = medmovil_atencion( $post_id );
	if ( ! $a ) {
		return;
	}
	if ( 'mm_estado' === $col ) {
		$e = medmovil_estados();
		echo esc_html( isset( $e[ $a['estado'] ] ) ? $e[ $a['estado'] ]['n'] : $a['estado'] );
	}
	if ( 'mm_prof' === $col ) {
		$u = $a['profesional'] ? get_userdata( $a['profesional'] ) : null;
		echo esc_html( $u ? $u->display_name : '—' );
	}
	if ( 'mm_monto' === $col ) {
		echo esc_html( medmovil_precio( $a['monto'] ) );
	}
	if ( 'mm_com' === $col ) {
		echo esc_html( in_array( $a['estado'], array( 'realizada', 'informada' ), true ) ? medmovil_precio( $a['comision'] ) : '—' );
	}
}
add_action( 'manage_mm_atencion_posts_custom_column', 'medmovil_col_atencion', 10, 2 );
