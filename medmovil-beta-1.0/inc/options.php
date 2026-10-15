<?php
/**
 * Panel "MedMóvil": ajustes del sitio y edición masiva de precios.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lee una opción del tema.
 *
 * @param string $clave  Nombre de la opción.
 * @param mixed  $defecto Valor por omisión.
 * @return mixed
 */
function medmovil_opt( $clave, $defecto = null ) {
	$opts = get_option( 'medmovil_opts', array() );

	if ( isset( $opts[ $clave ] ) && '' !== $opts[ $clave ] ) {
		return $opts[ $clave ];
	}

	// Sin valor guardado, usamos el que declara el propio campo. Así no hay que
	// repetir el texto por defecto en cada plantilla ni queda nada en blanco
	// hasta que alguien entre al panel y presione Guardar.
	static $defectos = null;
	if ( null === $defectos ) {
		$defectos = array();
		foreach ( medmovil_ajustes() as $grupo ) {
			foreach ( $grupo['campos'] as $k => $campo ) {
				$defectos[ $k ] = isset( $campo['def'] ) ? $campo['def'] : '';
			}
		}
	}

	if ( null !== $defecto ) {
		return $defecto;
	}

	return isset( $defectos[ $clave ] ) ? $defectos[ $clave ] : '';
}

/**
 * Definición de los ajustes, agrupados por pestaña.
 *
 * @return array
 */
function medmovil_ajustes() {
	return array(
		'contacto' => array(
			'titulo' => __( 'Contacto y cobertura', 'medmovil' ),
			'campos' => array(
				'telefono'  => array( 'label' => __( 'Teléfono', 'medmovil' ), 'def' => '+56 9 4715 8235' ),
				'whatsapp'  => array( 'label' => __( 'WhatsApp (solo números, con 56)', 'medmovil' ), 'def' => '56947158235' ),
				'email'     => array( 'label' => __( 'Correo', 'medmovil' ), 'def' => 'medmovilspa@gmail.com' ),
				'instagram' => array( 'label' => __( 'Instagram', 'medmovil' ), 'def' => '@Medmovil.cl' ),
				'instagram_url' => array( 'label' => __( 'Enlace de Instagram', 'medmovil' ), 'def' => 'https://instagram.com/medmovil.cl' ),
				'horario'   => array( 'label' => __( 'Horario de atención', 'medmovil' ), 'def' => 'Lunes a sábado, 8:00 a 19:00' ),
				'region'    => array( 'label' => __( 'Zona de cobertura', 'medmovil' ), 'def' => 'Región Metropolitana' ),
				'precio_desde' => array( 'label' => __( 'Precio "desde" que se muestra en portada', 'medmovil' ), 'def' => '80000', 'tipo' => 'number' ),
			),
		),
		'textos' => array(
			'titulo' => __( 'Textos de la portada', 'medmovil' ),
			'campos' => array(
				'promo_texto'  => array( 'label' => __( 'Barra de anuncio (arriba de todo)', 'medmovil' ), 'def' => 'Sin cobro de traslado en toda la Región Metropolitana · Informe en menos de 24 horas', 'ayuda' => __( 'Déjalo vacío para ocultar la barra. Es el lugar para una promoción o un aviso corto.', 'medmovil' ) ),
				'promo_url'    => array( 'label' => __( 'Enlace de la barra de anuncio', 'medmovil' ), 'def' => '' ),
				'hero_eyebrow' => array( 'label' => __( 'Línea superior', 'medmovil' ), 'def' => 'Región Metropolitana · Agenda en 2 minutos' ),
				'hero_titulo'  => array( 'label' => __( 'Titular', 'medmovil' ), 'def' => 'Tu ecografía [en tu casa], con informe el mismo día', 'ayuda' => __( 'Lo que pongas entre corchetes sale en azul y subrayado a mano.', 'medmovil' ) ),
				'hero_bajada'  => array( 'label' => __( 'Bajada', 'medmovil' ), 'tipo' => 'textarea', 'def' => 'Un profesional de la salud llega a tu domicilio con equipo digital portátil y realiza el examen donde estés. Sin traslados, sin salas de espera, con informe validado por médico radiólogo.' ),
				'cta_texto'    => array( 'label' => __( 'Texto del botón principal', 'medmovil' ), 'def' => 'Solicitar hora' ),
			),
		),
		'marca' => array(
			'titulo' => __( 'Colores de marca', 'medmovil' ),
			'campos' => array(
				'color_navy'   => array( 'label' => __( 'Azul profundo', 'medmovil' ), 'def' => '#0B3560', 'tipo' => 'color' ),
				'color_blue'   => array( 'label' => __( 'Azul principal', 'medmovil' ), 'def' => '#2F7FD4', 'tipo' => 'color' ),
				'color_bright' => array( 'label' => __( 'Azul brillante', 'medmovil' ), 'def' => '#4890E0', 'tipo' => 'color' ),
				'color_deep'   => array( 'label' => __( 'Fondo oscuro', 'medmovil' ), 'def' => '#061A3A', 'tipo' => 'color' ),
			),
		),
		'asistente' => array(
			'titulo' => __( 'Asistente del sitio (chatbot)', 'medmovil' ),
			'campos' => array(
				'chat_on'     => array(
					'label'    => __( 'Mostrar el asistente', 'medmovil' ),
					'tipo'     => 'select',
					'def'      => 'si',
					'opciones' => array( 'si' => __( 'Sí', 'medmovil' ), 'no' => __( 'No', 'medmovil' ) ),
					'ayuda'    => __( 'Si lo apagas queda solo el botón de WhatsApp.', 'medmovil' ),
				),
				'gemini_key'  => array(
					'label' => __( 'Clave de la API de Gemini', 'medmovil' ),
					'tipo'  => 'password',
					'def'   => '',
					'ayuda' => __( 'Se saca gratis en aistudio.google.com/apikey. Queda guardada en tu servidor y nunca llega al navegador del paciente. Sin clave, el asistente igual funciona con respuestas guiadas.', 'medmovil' ),
				),
				'chat_saludo' => array(
					'label' => __( 'Primer mensaje del asistente', 'medmovil' ),
					'tipo'  => 'textarea',
					'def'   => 'Hola, soy el asistente de MedMóvil. Dime qué examen necesitas y en qué comuna vives, y te digo el valor y en cuánto llegamos.',
				),
			),
		),
		'legal' => array(
			'titulo' => __( 'Datos de la empresa', 'medmovil' ),
			'campos' => array(
				'razon_social' => array( 'label' => __( 'Razón social', 'medmovil' ), 'def' => 'MedMóvil SpA' ),
				'rut'          => array( 'label' => __( 'RUT', 'medmovil' ), 'def' => '' ),
				'direccion'    => array( 'label' => __( 'Dirección comercial', 'medmovil' ), 'def' => '' ),
				'ga4'          => array( 'label' => __( 'ID de Google Analytics (G-XXXXXXX)', 'medmovil' ), 'def' => '' ),
			),
		),
	);
}

/**
 * Menú del panel.
 */
function medmovil_menu() {
	add_menu_page(
		__( 'MedMóvil', 'medmovil' ),
		__( 'MedMóvil', 'medmovil' ),
		'manage_options',
		'medmovil',
		'medmovil_pagina_ajustes',
		'dashicons-plus-alt',
		3
	);
	add_submenu_page( 'medmovil', __( 'Ajustes', 'medmovil' ), __( 'Ajustes', 'medmovil' ), 'manage_options', 'medmovil', 'medmovil_pagina_ajustes' );
	add_submenu_page( 'medmovil', __( 'Precios', 'medmovil' ), __( 'Precios', 'medmovil' ), 'manage_options', 'medmovil-precios', 'medmovil_pagina_precios' );
}
add_action( 'admin_menu', 'medmovil_menu' );

/**
 * Registra el grupo de ajustes.
 */
function medmovil_registrar_ajustes() {
	register_setting( 'medmovil_grupo', 'medmovil_opts', array(
		'sanitize_callback' => 'medmovil_sanear_opts',
		'default'           => array(),
	) );
}
add_action( 'admin_init', 'medmovil_registrar_ajustes' );

/**
 * Sanea los ajustes antes de guardarlos.
 *
 * @param array $entrada Valores enviados.
 * @return array
 */
function medmovil_sanear_opts( $entrada ) {
	$limpio = array();
	foreach ( medmovil_ajustes() as $grupo ) {
		foreach ( $grupo['campos'] as $clave => $campo ) {
			if ( ! isset( $entrada[ $clave ] ) ) {
				continue;
			}
			$valor = wp_unslash( $entrada[ $clave ] );
			$tipo  = isset( $campo['tipo'] ) ? $campo['tipo'] : 'text';

			if ( 'textarea' === $tipo ) {
				$limpio[ $clave ] = sanitize_textarea_field( $valor );
			} elseif ( 'color' === $tipo ) {
				$limpio[ $clave ] = sanitize_hex_color( $valor );
			} elseif ( 'select' === $tipo ) {
				$opciones         = isset( $campo['opciones'] ) ? array_keys( $campo['opciones'] ) : array();
				$limpio[ $clave ] = in_array( $valor, $opciones, true ) ? $valor : $campo['def'];
			} elseif ( 'password' === $tipo ) {
				$limpio[ $clave ] = trim( preg_replace( '/[^A-Za-z0-9\-_\.]/', '', $valor ) );
			} else {
				$limpio[ $clave ] = sanitize_text_field( $valor );
			}
		}
	}
	return $limpio;
}

/**
 * Página de ajustes.
 */
function medmovil_pagina_ajustes() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'MedMóvil — ajustes del sitio', 'medmovil' ); ?></h1>
		<p class="description" style="max-width:70ch">
			<?php esc_html_e( 'Todo lo que se ve en el sitio y cambia cada cierto tiempo está aquí. Los precios de cada examen se editan en la pestaña Precios.', 'medmovil' ); ?>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'medmovil_grupo' ); ?>
			<?php foreach ( medmovil_ajustes() as $slug => $grupo ) : ?>
				<h2 style="margin-top:32px"><?php echo esc_html( $grupo['titulo'] ); ?></h2>
				<table class="form-table" role="presentation">
					<?php foreach ( $grupo['campos'] as $clave => $campo ) :
						$valor = medmovil_opt( $clave, $campo['def'] );
						$tipo  = isset( $campo['tipo'] ) ? $campo['tipo'] : 'text';
						?>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( $clave ); ?>"><?php echo esc_html( $campo['label'] ); ?></label></th>
							<td>
								<?php if ( 'textarea' === $tipo ) : ?>
									<textarea id="<?php echo esc_attr( $clave ); ?>" name="medmovil_opts[<?php echo esc_attr( $clave ); ?>]" rows="3" class="large-text"><?php echo esc_textarea( $valor ); ?></textarea>
								<?php elseif ( 'select' === $tipo ) : ?>
									<select id="<?php echo esc_attr( $clave ); ?>" name="medmovil_opts[<?php echo esc_attr( $clave ); ?>]">
										<?php foreach ( $campo['opciones'] as $k => $etiqueta ) : ?>
											<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $valor, $k ); ?>><?php echo esc_html( $etiqueta ); ?></option>
										<?php endforeach; ?>
									</select>
								<?php else : ?>
									<input type="<?php echo esc_attr( $tipo ); ?>"
									       id="<?php echo esc_attr( $clave ); ?>"
									       name="medmovil_opts[<?php echo esc_attr( $clave ); ?>]"
									       value="<?php echo esc_attr( $valor ); ?>"
									       class="<?php echo 'color' === $tipo ? '' : 'regular-text'; ?>">
								<?php endif; ?>
								<?php if ( ! empty( $campo['ayuda'] ) ) : ?>
									<p class="description"><?php echo esc_html( $campo['ayuda'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button( __( 'Guardar cambios', 'medmovil' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Página de precios: toda la lista editable en una sola tabla.
 */
function medmovil_pagina_precios() {
	if ( isset( $_POST['medmovil_precios_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['medmovil_precios_nonce'] ), 'medmovil_precios' ) && current_user_can( 'manage_options' ) ) {
		$precios   = isset( $_POST['precio'] ) ? (array) wp_unslash( $_POST['precio'] ) : array();
		$duraciones = isset( $_POST['duracion'] ) ? (array) wp_unslash( $_POST['duracion'] ) : array();
		$cambios   = 0;

		foreach ( $precios as $id => $valor ) {
			$id     = (int) $id;
			$limpio = (int) preg_replace( '/[^0-9]/', '', $valor );
			if ( get_post_meta( $id, '_mm_precio', true ) !== (string) $limpio ) {
				$cambios++;
			}
			update_post_meta( $id, '_mm_precio', $limpio );
		}
		foreach ( $duraciones as $id => $valor ) {
			update_post_meta( (int) $id, '_mm_duracion', (int) preg_replace( '/[^0-9]/', '', $valor ) );
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( sprintf(
				/* translators: %d: número de precios modificados */
				_n( 'Listo. Se actualizó %d precio.', 'Listo. Se actualizaron %d precios.', $cambios, 'medmovil' ),
				$cambios
			) )
		);
	}

	$lineas = get_terms( array( 'taxonomy' => 'mm_linea', 'hide_empty' => false ) );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Precios de las prestaciones', 'medmovil' ); ?></h1>
		<p class="description" style="max-width:70ch">
			<?php esc_html_e( 'Cambia los valores que necesites y guarda una sola vez al final. Los precios se publican al instante en el sitio, en las páginas de cada examen y en el marcado que lee Google.', 'medmovil' ); ?>
		</p>

		<form method="post">
			<?php wp_nonce_field( 'medmovil_precios', 'medmovil_precios_nonce' ); ?>
			<?php foreach ( $lineas as $linea ) :
				$items = get_posts( array(
					'post_type'      => 'mm_prestacion',
					'posts_per_page' => -1,
					'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
					'tax_query'      => array( array( 'taxonomy' => 'mm_linea', 'field' => 'term_id', 'terms' => $linea->term_id ) ),
				) );
				if ( ! $items ) {
					continue;
				}
				?>
				<h2 style="margin-top:30px"><?php echo esc_html( $linea->name ); ?></h2>
				<table class="wp-list-table widefat striped">
					<thead>
						<tr>
							<th style="width:52%"><?php esc_html_e( 'Prestación', 'medmovil' ); ?></th>
							<th style="width:20%"><?php esc_html_e( 'Precio particular', 'medmovil' ); ?></th>
							<th style="width:16%"><?php esc_html_e( 'Duración (min)', 'medmovil' ); ?></th>
							<th style="width:12%"></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $item->post_title ); ?></strong></td>
							<td>
								<input type="number" name="precio[<?php echo (int) $item->ID; ?>]"
								       value="<?php echo esc_attr( get_post_meta( $item->ID, '_mm_precio', true ) ); ?>"
								       step="1000" min="0" style="width:130px">
							</td>
							<td>
								<input type="number" name="duracion[<?php echo (int) $item->ID; ?>]"
								       value="<?php echo esc_attr( get_post_meta( $item->ID, '_mm_duracion', true ) ); ?>"
								       step="5" min="0" style="width:80px">
							</td>
							<td>
								<a href="<?php echo esc_url( get_edit_post_link( $item->ID ) ); ?>"><?php esc_html_e( 'Editar página', 'medmovil' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>
			<?php submit_button( __( 'Guardar todos los precios', 'medmovil' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Aviso en el escritorio con lo que falta por configurar.
 */
function medmovil_aviso_pendientes() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$pantalla = get_current_screen();
	if ( ! $pantalla || 'dashboard' !== $pantalla->id ) {
		return;
	}

	$sin_precio = get_posts( array(
		'post_type'      => 'mm_prestacion',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'OR',
			array( 'key' => '_mm_precio', 'compare' => 'NOT EXISTS' ),
			array( 'key' => '_mm_precio', 'value' => '0', 'compare' => '=' ),
			array( 'key' => '_mm_precio', 'value' => '', 'compare' => '=' ),
		),
	) );

	if ( $sin_precio ) {
		printf(
			'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( 'Hay prestaciones sin precio publicado.', 'medmovil' ),
			esc_url( admin_url( 'admin.php?page=medmovil-precios' ) ),
			esc_html__( 'Completarlos ahora', 'medmovil' )
		);
	}
}
add_action( 'admin_notices', 'medmovil_aviso_pendientes' );
