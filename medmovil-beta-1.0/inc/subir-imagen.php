<?php
/**
 * Cambiar la imagen de una prestación desde la misma página.
 *
 * Con la sesión de administrador abierta, cada tarjeta del catálogo y la
 * ficha de la prestación muestran un botón «Cambiar imagen». Se elige la foto
 * en el computador o el celular, se sube a la biblioteca de medios y queda
 * como «Imagen destacada» de esa prestación, sin entrar al escritorio de
 * WordPress. Los visitantes no ven el botón ni cargan su script.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ¿El usuario actual puede cambiar la imagen de esta prestación?
 *
 * @param int $post_id Prestación.
 * @return bool
 */
function medmovil_puede_subir_imagen( $post_id ) {
	return $post_id
		&& 'mm_prestacion' === get_post_type( $post_id )
		&& current_user_can( 'upload_files' )
		&& current_user_can( 'edit_post', $post_id );
}

/**
 * Botón «Cambiar imagen» sobre la foto de una prestación.
 *
 * @param int $post_id Prestación.
 */
function medmovil_boton_subir_imagen( $post_id ) {
	if ( ! medmovil_puede_subir_imagen( $post_id ) ) {
		return;
	}

	medmovil_subir_imagen_script();
	$tiene = has_post_thumbnail( $post_id );
	?>
	<button type="button" class="mm-subir" data-mm-subir="<?php echo esc_attr( $post_id ); ?>"
	        title="<?php echo esc_attr( sprintf( __( 'Cambiar la imagen de «%s»', 'medmovil' ), get_the_title( $post_id ) ) ); ?>">
		<?php medmovil_icono( 'i-plus' ); ?>
		<span><?php echo esc_html( $tiene ? __( 'Cambiar imagen', 'medmovil' ) : __( 'Subir imagen', 'medmovil' ) ); ?></span>
	</button>
	<?php
}

/**
 * Carga el script de subida una sola vez, y solo para quien puede usarlo.
 */
function medmovil_subir_imagen_script() {
	if ( wp_script_is( 'medmovil-subir-imagen', 'enqueued' ) ) {
		return;
	}
	wp_enqueue_script( 'medmovil-subir-imagen', MEDMOVIL_URI . '/assets/js/subir-imagen.js', array(), MEDMOVIL_VERSION, true );
	wp_localize_script( 'medmovil-subir-imagen', 'mmSubirImagen', array(
		'url'    => admin_url( 'admin-ajax.php' ),
		'nonce'  => wp_create_nonce( 'medmovil_subir_imagen' ),
		'maxMb'  => (int) floor( wp_max_upload_size() / MB_IN_BYTES ),
		'textos' => array(
			'subiendo' => __( 'Subiendo…', 'medmovil' ),
			'listo'    => __( 'Imagen guardada', 'medmovil' ),
			'tipo'     => __( 'Elige una imagen JPG, PNG o WebP.', 'medmovil' ),
			'grande'   => __( 'La imagen pesa más de lo que permite el servidor (%s MB).', 'medmovil' ),
			'error'    => __( 'No se pudo subir la imagen. Intenta de nuevo.', 'medmovil' ),
		),
	) );
}

/**
 * Recibe la imagen, la guarda en la biblioteca y la deja como destacada.
 */
function medmovil_subir_imagen_ajax() {
	check_ajax_referer( 'medmovil_subir_imagen', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	if ( ! medmovil_puede_subir_imagen( $post_id ) ) {
		wp_send_json_error( array( 'mensaje' => __( 'No tienes permiso para cambiar esta imagen.', 'medmovil' ) ), 403 );
	}

	if ( empty( $_FILES['imagen'] ) || ! empty( $_FILES['imagen']['error'] ) ) {
		wp_send_json_error( array( 'mensaje' => __( 'No llegó ninguna imagen.', 'medmovil' ) ), 400 );
	}

	// Solo fotos: nada de SVG ni PDF, aunque el sitio permita subirlos.
	$permitidos = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
	);
	$archivo = $_FILES['imagen']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- lo valida wp_check_filetype_and_ext.
	$tipo    = wp_check_filetype_and_ext( $archivo['tmp_name'], $archivo['name'], $permitidos );
	if ( empty( $tipo['type'] ) ) {
		wp_send_json_error( array( 'mensaje' => __( 'Elige una imagen JPG, PNG o WebP.', 'medmovil' ) ), 400 );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$adjunto = media_handle_upload(
		'imagen',
		$post_id,
		array( 'post_title' => get_the_title( $post_id ) ),
		array( 'test_form' => false, 'mimes' => $permitidos )
	);
	if ( is_wp_error( $adjunto ) ) {
		wp_send_json_error( array( 'mensaje' => $adjunto->get_error_message() ), 400 );
	}

	update_post_meta( $adjunto, '_wp_attachment_image_alt', get_the_title( $post_id ) );
	set_post_thumbnail( $post_id, $adjunto );

	wp_send_json_success( array(
		'url' => wp_get_attachment_image_url( $adjunto, 'medium_large' ),
	) );
}
add_action( 'wp_ajax_medmovil_subir_imagen', 'medmovil_subir_imagen_ajax' );
