<?php
/**
 * Campos personalizados. Sin plugins: cajas nativas de WordPress.
 *
 * Los campos de lista usan una línea por elemento y "|" para separar partes.
 * Es feo de mirar, pero cualquiera puede editarlo sin aprender nada nuevo.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convierte un textarea de líneas "a | b | c" en un array de arrays.
 *
 * @param string $texto  Contenido del campo.
 * @param int    $partes Número de columnas esperadas.
 * @return array
 */
function medmovil_lineas( $texto, $partes = 1 ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $texto ) as $linea ) {
		$linea = trim( $linea );
		if ( '' === $linea ) {
			continue;
		}
		$cols = array_map( 'trim', explode( '|', $linea ) );
		while ( count( $cols ) < $partes ) {
			$cols[] = '';
		}
		$out[] = $cols;
	}
	return $out;
}

/**
 * Definición de los campos por tipo de contenido.
 *
 * @return array
 */
function medmovil_campos() {
	return array(
		'mm_prestacion' => array(
			'titulo' => __( 'Datos de la prestación', 'medmovil' ),
			'campos' => array(
				'_mm_codigo'       => array( 'tipo' => 'text', 'label' => __( 'Código de la prestación', 'medmovil' ), 'ayuda' => __( 'El código que trae impresa la orden médica. No mostramos valores Fonasa; el código sirve solo para que el paciente encuentre su examen escribiéndolo en el buscador. Verifícalo contra el arancel vigente antes de publicar.', 'medmovil' ) ),
				'_mm_precio'       => array( 'tipo' => 'number', 'label' => __( 'Precio particular (solo números)', 'medmovil' ), 'ayuda' => __( 'Ejemplo: 80000. Se muestra como $80.000.', 'medmovil' ) ),
				'_mm_precio_antes' => array( 'tipo' => 'number', 'label' => __( 'Precio anterior (opcional)', 'medmovil' ), 'ayuda' => __( 'Si lo llenas, aparece tachado al lado del precio.', 'medmovil' ) ),
				'_mm_duracion'     => array( 'tipo' => 'number', 'label' => __( 'Duración en minutos', 'medmovil' ) ),
				'_mm_edad'         => array( 'tipo' => 'text', 'label' => __( 'Edad mínima', 'medmovil' ), 'ayuda' => __( 'Ejemplo: Desde los 5 años.', 'medmovil' ) ),
				'_mm_icono'        => array(
					'tipo'    => 'select',
					'label'   => __( 'Ilustración', 'medmovil' ),
					'opciones' => array(
						'il-eco'    => __( 'Transductor / ecografía general', 'medmovil' ),
						'il-msk'    => __( 'Articulación / musculoesquelética', 'medmovil' ),
						'il-dop'    => __( 'Flujo sanguíneo / Doppler', 'medmovil' ),
						'il-cardio' => __( 'Corazón / cardiología', 'medmovil' ),
						'il-lab'    => __( 'Tubos / laboratorio', 'medmovil' ),
						'il-rx'     => __( 'Placa / radiografía', 'medmovil' ),
						'il-nurse'  => __( 'Casa con cruz / enfermería', 'medmovil' ),
						'il-clockx' => __( 'Reloj / monitoreo', 'medmovil' ),
					),
				),
				'_mm_resumen'      => array( 'tipo' => 'textarea', 'filas' => 3, 'label' => __( 'Resumen de portada', 'medmovil' ), 'ayuda' => __( 'Dos líneas que resumen el examen. Aparece bajo el título.', 'medmovil' ) ),
				'_mm_incluye'      => array( 'tipo' => 'textarea', 'filas' => 6, 'label' => __( 'Qué incluye el valor', 'medmovil' ), 'ayuda' => __( 'Un ítem por línea.', 'medmovil' ) ),
				'_mm_ventajas'     => array( 'tipo' => 'textarea', 'filas' => 5, 'label' => __( 'Por qué conviene a domicilio', 'medmovil' ), 'ayuda' => __( 'Tres líneas, formato: Título | Texto explicativo', 'medmovil' ) ),
				'_mm_condiciones'  => array( 'tipo' => 'textarea', 'filas' => 8, 'label' => __( 'Cuándo se solicita', 'medmovil' ), 'ayuda' => __( 'Formato: Etiqueta | Título | Texto. Una condición por línea.', 'medmovil' ) ),
				'_mm_preparacion'  => array( 'tipo' => 'textarea', 'filas' => 4, 'label' => __( 'Preparación', 'medmovil' ), 'ayuda' => __( 'Qué tiene que hacer el paciente antes. Si no requiere nada, dilo igual.', 'medmovil' ) ),
				'_mm_faq'          => array( 'tipo' => 'textarea', 'filas' => 8, 'label' => __( 'Preguntas frecuentes', 'medmovil' ), 'ayuda' => __( 'Formato: Pregunta | Respuesta. Una por línea. Google las muestra en los resultados.', 'medmovil' ) ),
			),
		),
		'mm_comuna' => array(
			'titulo' => __( 'Datos de la comuna', 'medmovil' ),
			'campos' => array(
				'_mm_tiempo'      => array( 'tipo' => 'number', 'label' => __( 'Tiempo de llegada estimado (minutos)', 'medmovil' ) ),
				'_mm_zona'        => array(
					'tipo'     => 'select',
					'label'    => __( 'Nivel de cobertura', 'medmovil' ),
					'opciones' => array(
						'a' => __( 'Zona de mayor demanda — todos los días', 'medmovil' ),
						'b' => __( 'Cobertura frecuente — varios días por semana', 'medmovil' ),
						'c' => __( 'Cobertura a pedido', 'medmovil' ),
					),
				),
				'_mm_sectores'    => array( 'tipo' => 'textarea', 'filas' => 3, 'label' => __( 'Sectores y referencias', 'medmovil' ), 'ayuda' => __( 'Barrios, avenidas o hitos de la comuna, separados por coma. Ayudan al posicionamiento local.', 'medmovil' ) ),
			),
		),
		'mm_pack' => array(
			'titulo' => __( 'Datos del pack', 'medmovil' ),
			'campos' => array(
				'_mm_etiqueta'     => array( 'tipo' => 'text', 'label' => __( 'Etiqueta', 'medmovil' ), 'ayuda' => __( 'Ejemplo: Adulto mayor, Más solicitado.', 'medmovil' ) ),
				'_mm_precio'       => array( 'tipo' => 'number', 'label' => __( 'Precio del pack', 'medmovil' ) ),
				'_mm_precio_antes' => array( 'tipo' => 'number', 'label' => __( 'Precio sumando cada examen por separado', 'medmovil' ) ),
				'_mm_incluye'      => array( 'tipo' => 'textarea', 'filas' => 6, 'label' => __( 'Qué incluye', 'medmovil' ), 'ayuda' => __( 'Un examen por línea.', 'medmovil' ) ),
				'_mm_destacado'    => array( 'tipo' => 'check', 'label' => __( 'Destacar este pack', 'medmovil' ) ),
			),
		),
		'mm_aliado' => array(
			'titulo' => __( 'Datos de la alianza', 'medmovil' ),
			'campos' => array(
				'_mm_url' => array( 'tipo' => 'text', 'label' => __( 'Sitio web o Instagram', 'medmovil' ) ),
			),
		),
		'mm_testimonio' => array(
			'titulo' => __( 'Datos del testimonio', 'medmovil' ),
			'campos' => array(
				'_mm_autor'  => array( 'tipo' => 'text', 'label' => __( 'Nombre', 'medmovil' ), 'ayuda' => __( 'Ejemplo: Carolina M. Usa solo la inicial del apellido.', 'medmovil' ) ),
				'_mm_lugar'  => array( 'tipo' => 'text', 'label' => __( 'Comuna y examen', 'medmovil' ), 'ayuda' => __( 'Ejemplo: La Florida · Ecografía abdominal', 'medmovil' ) ),
			),
		),
	);
}

/**
 * Registra las cajas.
 */
function medmovil_meta_boxes() {
	foreach ( medmovil_campos() as $tipo => $def ) {
		add_meta_box( 'medmovil_' . $tipo, $def['titulo'], 'medmovil_pintar_caja', $tipo, 'normal', 'high' );
	}

	add_meta_box(
		'medmovil_seo',
		__( 'SEO — cómo se ve en Google', 'medmovil' ),
		'medmovil_pintar_seo',
		array( 'mm_prestacion', 'mm_comuna', 'page', 'post', 'mm_pack' ),
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'medmovil_meta_boxes' );

/** Ayuda a encontrar la foto que ya alimenta el catálogo y la ficha de producto. */
function medmovil_aviso_imagen_prestacion() {
	$screen = get_current_screen();
	if ( ! $screen || 'mm_prestacion' !== $screen->post_type || ! current_user_can( 'edit_posts' ) ) { return; }
	printf( '<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>',
		esc_html__( 'La imagen de esta ecografía se cambia en «Imagen destacada» de la barra lateral. Aparecerá en el catálogo y en su página. Usa imágenes comerciales sin datos identificables de pacientes.', 'medmovil' ),
		esc_url( admin_url( 'admin.php?page=medmovil-iconos' ) ),
		esc_html__( 'Gestionar iconos de la biblioteca', 'medmovil' )
	);
}
add_action( 'admin_notices', 'medmovil_aviso_imagen_prestacion' );

/**
 * Pinta una caja de campos.
 *
 * @param WP_Post $post Entrada actual.
 */
function medmovil_pintar_caja( $post ) {
	$defs = medmovil_campos();
	if ( empty( $defs[ $post->post_type ] ) ) {
		return;
	}
	wp_nonce_field( 'medmovil_guardar', 'medmovil_nonce' );
	echo '<div class="medmovil-campos" style="display:flex;flex-direction:column;gap:18px;padding:6px 0">';

	foreach ( $defs[ $post->post_type ]['campos'] as $clave => $campo ) {
		$valor = get_post_meta( $post->ID, $clave, true );
		$id    = esc_attr( $clave );

		echo '<p style="margin:0"><label for="' . $id . '" style="display:block;font-weight:600;margin-bottom:5px">' . esc_html( $campo['label'] ) . '</label>';

		switch ( $campo['tipo'] ) {
			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%1$s" rows="%2$d" class="widefat" style="font-family:ui-monospace,Menlo,Consolas,monospace;font-size:13px">%3$s</textarea>',
					$id,
					isset( $campo['filas'] ) ? (int) $campo['filas'] : 4,
					esc_textarea( $valor )
				);
				break;

			case 'select':
				echo '<select id="' . $id . '" name="' . $id . '" class="widefat">';
				foreach ( $campo['opciones'] as $v => $etiqueta ) {
					printf( '<option value="%s"%s>%s</option>', esc_attr( $v ), selected( $valor, $v, false ), esc_html( $etiqueta ) );
				}
				echo '</select>';
				break;

			case 'check':
				printf(
					'<label><input type="checkbox" id="%1$s" name="%1$s" value="1"%2$s> %3$s</label>',
					$id,
					checked( $valor, '1', false ),
					esc_html__( 'Sí', 'medmovil' )
				);
				break;

			default:
				printf(
					'<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" class="widefat">',
					esc_attr( $campo['tipo'] ),
					$id,
					esc_attr( $valor )
				);
		}

		if ( ! empty( $campo['ayuda'] ) ) {
			echo '<span class="description" style="display:block;margin-top:4px">' . esc_html( $campo['ayuda'] ) . '</span>';
		}
		echo '</p>';
	}
	echo '</div>';
}

/**
 * Caja de SEO.
 *
 * @param WP_Post $post Entrada actual.
 */
function medmovil_pintar_seo( $post ) {
	wp_nonce_field( 'medmovil_guardar', 'medmovil_nonce' );
	$titulo = get_post_meta( $post->ID, '_mm_seo_title', true );
	$desc   = get_post_meta( $post->ID, '_mm_seo_desc', true );
	?>
	<p style="margin:0 0 16px">
		<label for="_mm_seo_title" style="display:block;font-weight:600;margin-bottom:5px"><?php esc_html_e( 'Título en Google', 'medmovil' ); ?></label>
		<input type="text" id="_mm_seo_title" name="_mm_seo_title" value="<?php echo esc_attr( $titulo ); ?>" class="widefat" maxlength="70">
		<span class="description"><?php esc_html_e( 'Hasta 60 caracteres. Si lo dejas vacío se genera solo con el nombre de la prestación y la marca.', 'medmovil' ); ?></span>
	</p>
	<p style="margin:0">
		<label for="_mm_seo_desc" style="display:block;font-weight:600;margin-bottom:5px"><?php esc_html_e( 'Descripción en Google', 'medmovil' ); ?></label>
		<textarea id="_mm_seo_desc" name="_mm_seo_desc" rows="3" class="widefat" maxlength="180"><?php echo esc_textarea( $desc ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Hasta 155 caracteres. Es el texto que la gente lee antes de hacer clic: nombra el examen, el domicilio y el precio.', 'medmovil' ); ?></span>
	</p>
	<?php
}

/**
 * Guarda los campos.
 *
 * @param int $post_id ID de la entrada.
 */
function medmovil_guardar_meta( $post_id ) {
	if ( ! isset( $_POST['medmovil_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['medmovil_nonce'] ), 'medmovil_guardar' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$tipo = get_post_type( $post_id );
	$defs = medmovil_campos();

	if ( ! empty( $defs[ $tipo ] ) ) {
		foreach ( $defs[ $tipo ]['campos'] as $clave => $campo ) {
			if ( 'check' === $campo['tipo'] ) {
				update_post_meta( $post_id, $clave, isset( $_POST[ $clave ] ) ? '1' : '' );
				continue;
			}
			if ( ! isset( $_POST[ $clave ] ) ) {
				continue;
			}
			$valor = wp_unslash( $_POST[ $clave ] );
			$valor = ( 'textarea' === $campo['tipo'] )
				? sanitize_textarea_field( $valor )
				: sanitize_text_field( $valor );
			update_post_meta( $post_id, $clave, $valor );
		}
	}

	foreach ( array( '_mm_seo_title', '_mm_seo_desc' ) as $clave ) {
		if ( isset( $_POST[ $clave ] ) ) {
			update_post_meta( $post_id, $clave, sanitize_text_field( wp_unslash( $_POST[ $clave ] ) ) );
		}
	}
}
add_action( 'save_post', 'medmovil_guardar_meta' );
