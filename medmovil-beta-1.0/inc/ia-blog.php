<?php
/**
 * Generador de artículos con Gemini.
 *
 * Adaptado del que Sebastián ya tenía funcionando en Italmed, pero apuntado a lo
 * que a MedMóvil le sirve: exámenes a domicilio, Región Metropolitana, y el
 * lector típico —un hijo de 45 a 60 años organizando el examen de su papá o su
 * mamá—. Cada artículo sale enlazado al catálogo, que es donde se convierte.
 *
 * Regla que no se toca: el generador crea BORRADORES. Nunca publica. Esto es
 * contenido de salud y lo firma un tecnólogo médico, así que lo lee una persona
 * antes de que salga.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menú del generador.
 */
function medmovil_menu_ia() {
	add_submenu_page(
		'medmovil',
		__( 'Escribir artículo', 'medmovil' ),
		__( 'Escribir artículo', 'medmovil' ),
		'edit_posts',
		'medmovil-ia',
		'medmovil_pagina_ia'
	);
}
add_action( 'admin_menu', 'medmovil_menu_ia', 20 );

/**
 * Títulos sugeridos a partir del catálogo y las comunas reales.
 *
 * @return array
 */
function medmovil_temas_sugeridos() {
	$datos = medmovil_datos_publicos();
	$temas = array();

	$plantillas = array(
		/* translators: %s: nombre del examen */
		__( '¿Para qué sirve una %s y cuándo la piden?', 'medmovil' ),
		/* translators: %s: nombre del examen */
		__( 'Cómo prepararse para una %s en casa', 'medmovil' ),
		/* translators: %s: nombre del examen */
		__( '%s a domicilio: cuánto dura y qué pasa después', 'medmovil' ),
	);

	$i = 0;
	foreach ( array_slice( $datos['ex'], 0, 12 ) as $e ) {
		$temas[] = array(
			'titulo' => sprintf( $plantillas[ $i % 3 ], $e['n'] ),
			'clave'  => mb_strtolower( $e['n'] ) . ' a domicilio',
			'slug'   => $e['s'],
		);
		$i++;
	}

	$generales = array(
		array(
			'titulo' => __( 'Exámenes a domicilio para adultos mayores postrados: qué se puede hacer en casa', 'medmovil' ),
			'clave'  => 'exámenes a domicilio adulto mayor',
			'slug'   => '',
		),
		array(
			'titulo' => __( 'Cómo leer una orden médica: qué significa cada examen que te pidieron', 'medmovil' ),
			'clave'  => 'orden médica exámenes',
			'slug'   => '',
		),
		array(
			'titulo' => __( 'Chequeo preventivo después de los 60: qué exámenes conviene hacerse y cada cuánto', 'medmovil' ),
			'clave'  => 'chequeo preventivo adulto mayor',
			'slug'   => '',
		),
	);

	return array_merge( $generales, $temas );
}

/**
 * Página del generador.
 */
function medmovil_pagina_ia() {
	$hay_clave = medmovil_chat_con_ia();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Escribir un artículo para el blog', 'medmovil' ); ?></h1>

		<?php if ( ! $hay_clave ) : ?>
			<div class="notice notice-warning">
				<p>
					<?php esc_html_e( 'Falta la clave de Gemini.', 'medmovil' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=medmovil' ) ); ?>"><?php esc_html_e( 'Ponla en Ajustes → Asistente del sitio', 'medmovil' ); ?></a>
					<?php esc_html_e( 'y vuelve acá.', 'medmovil' ); ?>
				</p>
			</div>
		<?php endif; ?>

		<p class="description" style="max-width:78ch">
			<?php esc_html_e( 'Esto escribe un borrador completo con su título, resumen, categoría, etiquetas y enlaces a los exámenes del catálogo. No lo publica: queda como borrador para que lo leas, lo corrijas y lo firmes tú. Es contenido de salud y sale con tu nombre.', 'medmovil' ); ?>
		</p>

		<div id="mmia" style="display:grid;grid-template-columns:1fr 340px;gap:26px;margin-top:22px;align-items:start">
			<div class="card" style="padding:20px;background:#fff;border:1px solid #dcdcde;border-radius:8px">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="mmiaT"><?php esc_html_e( 'Tema o título', 'medmovil' ); ?></label></th>
						<td>
							<input type="text" id="mmiaT" class="large-text" placeholder="<?php esc_attr_e( '¿Para qué sirve una ecografía de rodilla y cuándo la piden?', 'medmovil' ); ?>">
							<p class="description"><?php esc_html_e( 'La IA puede mejorarlo. Escríbelo como lo buscaría un paciente.', 'medmovil' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mmiaK"><?php esc_html_e( 'Frase clave para Google', 'medmovil' ); ?></label></th>
						<td>
							<input type="text" id="mmiaK" class="regular-text" placeholder="<?php esc_attr_e( 'ecografía de rodilla a domicilio', 'medmovil' ); ?>">
							<p class="description"><?php esc_html_e( 'Lo que la persona escribiría en Google. Va al inicio de la meta descripción.', 'medmovil' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mmiaC"><?php esc_html_e( 'Comuna a destacar', 'medmovil' ); ?></label></th>
						<td>
							<select id="mmiaC">
								<option value=""><?php esc_html_e( 'Sin comuna (artículo general)', 'medmovil' ); ?></option>
								<?php foreach ( medmovil_datos_publicos()['co'] as $c ) : ?>
									<option value="<?php echo esc_attr( $c['n'] ); ?>"><?php echo esc_html( $c['n'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Si eliges una, el artículo incluye una sección local. Sirve para posicionar "examen + comuna".', 'medmovil' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mmiaE"><?php esc_html_e( 'Exámenes a enlazar', 'medmovil' ); ?></label></th>
						<td>
							<select id="mmiaE" multiple size="8" style="min-width:340px">
								<?php foreach ( medmovil_datos_publicos()['ex'] as $e ) : ?>
									<option value="<?php echo esc_attr( $e['s'] ); ?>"><?php echo esc_html( $e['n'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Ctrl o Cmd para elegir varios. Si no eliges ninguno, la IA propone los que correspondan.', 'medmovil' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="mmiaL"><?php esc_html_e( 'Largo', 'medmovil' ); ?></label></th>
						<td>
							<select id="mmiaL">
								<option value="900"><?php esc_html_e( 'Corto — 900 palabras', 'medmovil' ); ?></option>
								<option value="1400" selected><?php esc_html_e( 'Medio — 1.400 palabras', 'medmovil' ); ?></option>
								<option value="2200"><?php esc_html_e( 'Largo — 2.200 palabras', 'medmovil' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<p>
					<button class="button button-primary button-hero" id="mmiaGo" <?php disabled( ! $hay_clave ); ?>>
						<?php esc_html_e( 'Escribir el borrador', 'medmovil' ); ?>
					</button>
					<span id="mmiaEstado" style="margin-left:12px"></span>
				</p>
				<div id="mmiaSalida"></div>
			</div>

			<div class="card" style="padding:18px;background:#fff;border:1px solid #dcdcde;border-radius:8px">
				<h2 style="margin-top:0;font-size:14px"><?php esc_html_e( 'Temas sugeridos', 'medmovil' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Salen de tu propio catálogo. Haz clic y se llenan los campos.', 'medmovil' ); ?></p>
				<ul id="mmiaSug" style="margin:12px 0 0">
					<?php foreach ( medmovil_temas_sugeridos() as $t ) : ?>
						<li style="margin-bottom:9px">
							<a href="#" data-t="<?php echo esc_attr( $t['titulo'] ); ?>" data-k="<?php echo esc_attr( $t['clave'] ); ?>" data-s="<?php echo esc_attr( $t['slug'] ); ?>">
								<?php echo esc_html( $t['titulo'] ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>

	<script>
	(function () {
		var $ = function (id) { return document.getElementById(id); };

		document.querySelectorAll('#mmiaSug a').forEach(function (a) {
			a.addEventListener('click', function (e) {
				e.preventDefault();
				$('mmiaT').value = a.dataset.t;
				$('mmiaK').value = a.dataset.k;
				if (a.dataset.s) {
					Array.prototype.forEach.call($('mmiaE').options, function (o) {
						o.selected = (o.value === a.dataset.s);
					});
				}
				$('mmiaT').focus();
			});
		});

		$('mmiaGo').addEventListener('click', function (e) {
			e.preventDefault();
			var titulo = $('mmiaT').value.trim();
			if (!titulo) { $('mmiaT').focus(); return; }

			var boton = e.target;
			boton.disabled = true;
			$('mmiaEstado').textContent = '<?php echo esc_js( __( 'Escribiendo… esto tarda entre 20 y 60 segundos.', 'medmovil' ) ); ?>';
			$('mmiaSalida').innerHTML = '';

			var examenes = Array.prototype.filter.call($('mmiaE').options, function (o) { return o.selected; })
				.map(function (o) { return o.value; });

			var cuerpo = new URLSearchParams({
				action: 'medmovil_ia_articulo',
				_wpnonce: '<?php echo esc_js( wp_create_nonce( 'medmovil_ia' ) ); ?>',
				titulo: titulo,
				clave: $('mmiaK').value.trim(),
				comuna: $('mmiaC').value,
				largo: $('mmiaL').value,
				examenes: examenes.join(',')
			});

			fetch(ajaxurl, { method: 'POST', body: cuerpo, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (r) {
					boton.disabled = false;
					$('mmiaEstado').textContent = '';
					if (r.success) {
						$('mmiaSalida').innerHTML =
							'<div class="notice notice-success" style="margin:14px 0 0"><p><strong>' +
							'<?php echo esc_js( __( 'Borrador listo:', 'medmovil' ) ); ?> ' + r.data.titulo + '</strong><br>' +
							'<?php echo esc_js( __( 'Léelo completo antes de publicar.', 'medmovil' ) ); ?> ' +
							'<a href="' + r.data.editar + '">' + '<?php echo esc_js( __( 'Abrir el borrador', 'medmovil' ) ); ?>' + '</a></p></div>';
					} else {
						$('mmiaSalida').innerHTML =
							'<div class="notice notice-error" style="margin:14px 0 0"><p>' +
							(r.data && r.data.mensaje ? r.data.mensaje : 'Error') + '</p></div>';
					}
				})
				.catch(function () {
					boton.disabled = false;
					$('mmiaEstado').textContent = '';
					$('mmiaSalida').innerHTML = '<div class="notice notice-error" style="margin:14px 0 0"><p><?php echo esc_js( __( 'Se cortó la conexión. Vuelve a intentarlo.', 'medmovil' ) ); ?></p></div>';
				});
		});
	})();
	</script>
	<?php
}

/**
 * Instrucciones del redactor.
 *
 * @param array $opciones Datos del formulario.
 * @return string
 */
function medmovil_ia_instrucciones( $opciones ) {
	$datos  = medmovil_datos_publicos();
	$region = medmovil_opt( 'region', 'Región Metropolitana' );
	$sitio  = home_url( '/' );

	$catalogo = array();
	foreach ( $datos['ex'] as $e ) {
		$catalogo[] = sprintf( '%s — %s — %s — %s', $e['n'], $e['p'] ? medmovil_precio( $e['p'] ) : 'consultar', $e['m'] . ' min', $e['u'] );
	}

	$enlaces = array();
	foreach ( (array) $opciones['examenes'] as $slug ) {
		foreach ( $datos['ex'] as $e ) {
			if ( $e['s'] === $slug ) {
				$enlaces[] = sprintf( '%s → %s', $e['n'], $e['u'] );
			}
		}
	}

	$t  = "Eres redactor de salud para el blog de MedMóvil, un servicio chileno de exámenes médicos a domicilio en la {$region}. Escribes en español de Chile, tratando de \"tú\".\n\n";

	$t .= "QUIÉN TE LEE\n";
	$t .= "Casi siempre un hijo o una hija de entre 45 y 60 años que está organizando el examen de su papá o su mamá, muchas veces con movilidad reducida o postrado. No es médico. Está cansado y quiere respuestas claras. También te lee el propio paciente adulto mayor.\n";
	$t .= "Escribe para esa persona: frases cortas, sin jerga, y cuando uses un término técnico explícalo en la misma frase.\n\n";

	$t .= "QUÉ HACE MEDMÓVIL\n";
	$t .= "Un profesional de la salud llega al domicilio con equipo digital portátil y realiza el examen en la casa del paciente. El informe lo firma un médico radiólogo y llega en menos de 24 horas en PDF. Se reserva en bloques de una hora, con el traslado considerado. El pago es al momento del examen: efectivo, transferencia o tarjeta. No cobramos traslado dentro de la {$region}.\n";
	$t .= "MedMóvil trabaja SOLO con valores particulares. NO menciones Fonasa, isapres, bonos, copagos ni convenios de seguro en ninguna parte del artículo.\n\n";

	$t .= "REGLAS QUE NO PUEDES ROMPER\n";
	$t .= "- No diagnostiques, no interpretes resultados, no sugieras tratamientos ni medicamentos, no des dosis.\n";
	$t .= "- No prometas resultados clínicos ni digas que un examen \"descarta\" o \"confirma\" una enfermedad por sí solo.\n";
	$t .= "- Recuerda al menos una vez que el examen se hace con indicación médica y que el artículo no reemplaza la consulta.\n";
	$t .= "- No inventes precios, plazos ni exámenes: usa solo los del catálogo de abajo.\n";
	$t .= "- No inventes estudios, cifras ni citas. Si mencionas una guía clínica, nómbrala de forma general, sin inventar autores, años ni números de página.\n";
	$t .= "- Nada de alarmismo. No asustes para vender.\n\n";

	$t .= "FORMATO DEL ARTÍCULO (HTML, sin <html> ni <body>)\n";
	$t .= "- Responde la pregunta del título en las primeras 120 palabras. Nada de introducciones de relleno.\n";
	$t .= "- Usa <h2> y <h3>, párrafos <p> cortos, listas <ul>, y <strong> para lo importante.\n";
	$t .= "- Incluye una tabla <table> cuando ayude a comparar (por ejemplo, qué examen sirve para qué).\n";
	$t .= "- Incluye un bloque de preguntas frecuentes al final, con <h3> por pregunta.\n";
	$t .= "- Extensión objetivo: {$opciones['largo']} palabras.\n";
	$t .= "- Enlaza a los exámenes del catálogo con <a href=\"URL\">nombre del examen</a>, usando las URL exactas. Entre 2 y 5 enlaces, integrados en el texto, nunca amontonados al final.\n";
	$t .= "- Termina con un párrafo que invite a agendar a domicilio, enlazando a {$sitio}cotizador/\n\n";

	if ( $opciones['comuna'] ) {
		$t .= "SECCIÓN LOCAL OBLIGATORIA\n";
		$t .= "Incluye un <h2> dedicado a {$opciones['comuna']}: que ahí atendemos a domicilio, qué tipo de paciente solemos ver en esa comuna y cómo se coordina la visita. Escríbelo con naturalidad, sin repetir el nombre de la comuna en cada frase.\n\n";
	}

	if ( $enlaces ) {
		$t .= "EXÁMENES QUE DEBES ENLAZAR SÍ O SÍ\n" . implode( "\n", $enlaces ) . "\n\n";
	}

	$t .= "CATÁLOGO COMPLETO (nombre — valor — duración — URL)\n";
	$t .= implode( "\n", $catalogo ) . "\n\n";

	$t .= "ORDEN DE TU RESPUESTA\n";
	$t .= "1) PRIMERO este bloque exacto, con tus datos:\n\n";
	$t .= "<!-- MM_META\n{\n";
	$t .= "  \"titulo\": \"Título final, atractivo y claro\",\n";
	$t .= "  \"seo_titulo\": \"Título para Google, máximo 60 caracteres\",\n";
	$t .= "  \"slug\": \"slug-en-guiones-sin-tildes\",\n";
	$t .= "  \"meta\": \"Meta descripción con la frase clave al inicio, máximo 155 caracteres\",\n";
	$t .= "  \"categoria\": \"Una sola: Imagenología, Cardiología, Laboratorio, Enfermería, Adulto mayor, Salud preventiva o A domicilio\",\n";
	$t .= "  \"etiquetas\": [\"seis\", \"etiquetas\", \"en\", \"minúscula\", \"sin\", \"tildes\"],\n";
	$t .= "  \"lectura\": 7,\n";
	$t .= "  \"examenes\": [\"slug-del-examen-1\", \"slug-del-examen-2\"]\n";
	$t .= "}\n-->\n\n";
	$t .= "2) DESPUÉS el HTML del artículo, sin envolverlo en bloques de código.\n";

	return $t;
}

/**
 * Genera el borrador.
 */
function medmovil_ia_generar() {
	check_ajax_referer( 'medmovil_ia' );

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_send_json_error( array( 'mensaje' => __( 'No tienes permiso para escribir entradas.', 'medmovil' ) ) );
	}
	if ( ! medmovil_chat_con_ia() ) {
		wp_send_json_error( array( 'mensaje' => __( 'Falta la clave de Gemini en Ajustes → Asistente del sitio.', 'medmovil' ) ) );
	}

	$opciones = array(
		'titulo'   => isset( $_POST['titulo'] ) ? sanitize_text_field( wp_unslash( $_POST['titulo'] ) ) : '',
		'clave'    => isset( $_POST['clave'] ) ? sanitize_text_field( wp_unslash( $_POST['clave'] ) ) : '',
		'comuna'   => isset( $_POST['comuna'] ) ? sanitize_text_field( wp_unslash( $_POST['comuna'] ) ) : '',
		'largo'    => isset( $_POST['largo'] ) ? min( 3000, max( 600, (int) $_POST['largo'] ) ) : 1400,
		'examenes' => isset( $_POST['examenes'] ) ? array_filter( array_map( 'sanitize_title', explode( ',', wp_unslash( $_POST['examenes'] ) ) ) ) : array(),
	);

	if ( '' === $opciones['titulo'] ) {
		wp_send_json_error( array( 'mensaje' => __( 'Falta el tema del artículo.', 'medmovil' ) ) );
	}

	$peticion = sprintf(
		"Título propuesto: %s\nFrase clave: %s\n\nEscribe el artículo ahora.",
		$opciones['titulo'],
		$opciones['clave'] ? $opciones['clave'] : $opciones['titulo']
	);

	$respuesta = wp_remote_post(
		'https://generativelanguage.googleapis.com/v1beta/models/' . MEDMOVIL_CHAT_MODELO . ':generateContent',
		array(
			'timeout' => 120,
			'headers' => array(
				'Content-Type'   => 'application/json',
				'x-goog-api-key' => trim( (string) medmovil_opt( 'gemini_key', '' ) ),
			),
			'body'    => wp_json_encode( array(
				'system_instruction' => array( 'parts' => array( array( 'text' => medmovil_ia_instrucciones( $opciones ) ) ) ),
				'contents'           => array( array( 'role' => 'user', 'parts' => array( array( 'text' => $peticion ) ) ) ),
				'generationConfig'   => array(
					'temperature'     => 0.75,
					'maxOutputTokens' => 8192,
				),
			) ),
		)
	);

	if ( is_wp_error( $respuesta ) ) {
		wp_send_json_error( array( 'mensaje' => __( 'No se pudo hablar con Google: ', 'medmovil' ) . $respuesta->get_error_message() ) );
	}

	$cuerpo = json_decode( wp_remote_retrieve_body( $respuesta ), true );

	if ( 200 !== wp_remote_retrieve_response_code( $respuesta ) ) {
		$detalle = isset( $cuerpo['error']['message'] ) ? $cuerpo['error']['message'] : __( 'respuesta inesperada', 'medmovil' );
		wp_send_json_error( array( 'mensaje' => __( 'Google respondió con un error: ', 'medmovil' ) . $detalle ) );
	}

	$texto = '';
	if ( isset( $cuerpo['candidates'][0]['content']['parts'] ) ) {
		foreach ( $cuerpo['candidates'][0]['content']['parts'] as $parte ) {
			if ( isset( $parte['text'] ) ) {
				$texto .= $parte['text'];
			}
		}
	}

	if ( '' === trim( $texto ) ) {
		wp_send_json_error( array( 'mensaje' => __( 'La respuesta llegó vacía. Prueba con un tema más concreto o un largo menor.', 'medmovil' ) ) );
	}

	$articulo = medmovil_ia_separar( $texto, $opciones );
	$post_id  = medmovil_ia_guardar( $articulo, $opciones );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'mensaje' => $post_id->get_error_message() ) );
	}

	wp_send_json_success( array(
		'titulo' => $articulo['titulo'],
		'editar' => get_edit_post_link( $post_id, 'raw' ),
	) );
}
add_action( 'wp_ajax_medmovil_ia_articulo', 'medmovil_ia_generar' );

/**
 * Separa el bloque de datos del cuerpo del artículo.
 *
 * @param string $texto    Respuesta cruda.
 * @param array  $opciones Datos del formulario.
 * @return array
 */
function medmovil_ia_separar( $texto, $opciones ) {
	$meta = array();

	if ( preg_match( '/<!--\s*MM_META\s*(\{.*?\})\s*-->/s', $texto, $m ) ) {
		$decodificado = json_decode( $m[1], true );
		if ( is_array( $decodificado ) ) {
			$meta = $decodificado;
		}
		$texto = str_replace( $m[0], '', $texto );
	}

	// A veces devuelve el HTML envuelto en un bloque de código.
	$texto = preg_replace( '/^\s*```(?:html)?\s*/i', '', trim( $texto ) );
	$texto = preg_replace( '/```\s*$/', '', $texto );

	return array(
		'titulo'     => ! empty( $meta['titulo'] ) ? $meta['titulo'] : $opciones['titulo'],
		'seo_titulo' => isset( $meta['seo_titulo'] ) ? $meta['seo_titulo'] : '',
		'slug'       => isset( $meta['slug'] ) ? $meta['slug'] : '',
		'meta'       => isset( $meta['meta'] ) ? $meta['meta'] : '',
		'categoria'  => isset( $meta['categoria'] ) ? $meta['categoria'] : '',
		'etiquetas'  => isset( $meta['etiquetas'] ) && is_array( $meta['etiquetas'] ) ? $meta['etiquetas'] : array(),
		'lectura'    => isset( $meta['lectura'] ) ? (int) $meta['lectura'] : 0,
		'examenes'   => isset( $meta['examenes'] ) && is_array( $meta['examenes'] ) ? $meta['examenes'] : $opciones['examenes'],
		'html'       => trim( $texto ),
	);
}

/**
 * Guarda el borrador.
 *
 * @param array $articulo Artículo separado.
 * @param array $opciones Datos del formulario.
 * @return int|WP_Error
 */
function medmovil_ia_guardar( $articulo, $opciones ) {
	$post_id = wp_insert_post( array(
		'post_type'    => 'post',
		'post_status'  => 'draft', // Nunca se publica solo.
		'post_title'   => wp_strip_all_tags( $articulo['titulo'] ),
		'post_content' => wp_kses_post( $articulo['html'] ),
		'post_excerpt' => sanitize_text_field( $articulo['meta'] ),
		'post_name'    => $articulo['slug'] ? sanitize_title( $articulo['slug'] ) : '',
	), true );

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	update_post_meta( $post_id, '_mm_ia', current_time( 'mysql' ) );

	if ( $articulo['lectura'] ) {
		update_post_meta( $post_id, '_mm_lectura', (int) $articulo['lectura'] );
	}
	if ( $articulo['examenes'] ) {
		update_post_meta( $post_id, '_mm_prestaciones', implode( "\n", array_map( 'sanitize_title', $articulo['examenes'] ) ) );
	}

	// Si algún día instala Yoast o Rank Math, los campos ya están puestos.
	if ( $opciones['clave'] ) {
		update_post_meta( $post_id, '_yoast_wpseo_focuskw', $opciones['clave'] );
		update_post_meta( $post_id, 'rank_math_focus_keyword', $opciones['clave'] );
	}
	if ( $articulo['seo_titulo'] ) {
		update_post_meta( $post_id, '_mm_seo_titulo', sanitize_text_field( $articulo['seo_titulo'] ) );
		update_post_meta( $post_id, '_yoast_wpseo_title', sanitize_text_field( $articulo['seo_titulo'] ) );
	}
	if ( $articulo['meta'] ) {
		update_post_meta( $post_id, '_mm_seo_desc', sanitize_text_field( $articulo['meta'] ) );
		update_post_meta( $post_id, '_yoast_wpseo_metadesc', sanitize_text_field( $articulo['meta'] ) );
	}

	if ( $articulo['categoria'] ) {
		$termino = term_exists( $articulo['categoria'], 'category' );
		if ( ! $termino ) {
			$termino = wp_insert_term( $articulo['categoria'], 'category' );
		}
		if ( ! is_wp_error( $termino ) ) {
			wp_set_post_terms( $post_id, array( (int) $termino['term_id'] ), 'category' );
		}
	}

	if ( $articulo['etiquetas'] ) {
		wp_set_post_terms( $post_id, array_map( 'sanitize_text_field', $articulo['etiquetas'] ), 'post_tag' );
	}

	return $post_id;
}
