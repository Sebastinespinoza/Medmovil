<?php
/**
 * Asistente de MedMóvil.
 *
 * La clave de Gemini vive en la base de datos y nunca sale al navegador: el
 * navegador habla con WordPress y WordPress habla con Google. Si no hay clave
 * configurada, el asistente sigue funcionando en modo guiado (botones) para que
 * el sitio nunca quede con un chat muerto.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MEDMOVIL_CHAT_MODELO = 'gemini-2.5-flash';
const MEDMOVIL_CHAT_LIMITE = 40;

/**
 * ¿Hay clave de Gemini guardada?
 *
 * @return bool
 */
function medmovil_chat_con_ia() {
	return '' !== trim( (string) medmovil_opt( 'gemini_key', '' ) );
}

/**
 * ¿Se muestra el asistente?
 *
 * @return bool
 */
function medmovil_chat_activo() {
	return 'no' !== medmovil_opt( 'chat_on', 'si' );
}

/**
 * Instrucciones del asistente: quién es, qué sabe y qué no puede hacer.
 *
 * @return string
 */
function medmovil_chat_instrucciones() {
	$datos  = medmovil_datos_publicos();
	$region = medmovil_opt( 'region', 'Región Metropolitana' );

	$catalogo = array();
	foreach ( $datos['ex'] as $e ) {
		$catalogo[] = sprintf(
			'%s | slug:%s | %s | %s | %d min | %s',
			$e['n'],
			$e['s'],
			$e['l'],
			$e['p'] ? medmovil_precio( $e['p'] ) : 'consultar',
			$e['m'],
			mb_strtolower( $e['ae'] )
		);
	}

	$zonas   = array( 'a' => 'todos los días', 'b' => 'varios días por semana', 'c' => 'a pedido' );
	$comunas = array();
	foreach ( $datos['co'] as $c ) {
		$comunas[] = sprintf(
			'%s (slug:%s, llegada ~%d min, cobertura %s)',
			$c['n'],
			$c['s'],
			$c['t'],
			isset( $zonas[ $c['z'] ] ) ? $zonas[ $c['z'] ] : 'a pedido'
		);
	}

	$texto = "Eres el asistente de MedMóvil, un servicio chileno de exámenes médicos a domicilio en la {$region}.\n";
	$texto .= "Hablas español de Chile, tratando de \"tú\", cercano y breve. Máximo 4 frases por respuesta, salvo que te pidan detalle.\n";
	$texto .= "Quien te escribe suele tener entre 45 y 60 años y está organizando el examen de un padre o una madre que no puede salir de la casa. Trátalo con calma y sin tecnicismos.\n\n";

	$texto .= "TU TRABAJO\n";
	$texto .= "1. Entender qué examen necesita y en qué comuna vive.\n";
	$texto .= "2. Decirle el precio exacto, cuánto dura y si necesita preparación.\n";
	$texto .= "3. Dejarle el examen agregado a su reserva y llevarlo a confirmar por WhatsApp.\n\n";

	$texto .= "REGLAS QUE NO PUEDES ROMPER\n";
	$texto .= "- No diagnosticas, no interpretas resultados, no dices qué puede tener el paciente ni recomiendas tratamientos. Si te lo piden, dilo con amabilidad y ofrece agendar el examen que el médico haya indicado.\n";
	$texto .= "- No inventas precios, plazos, exámenes ni comunas: solo usas los de la lista de abajo. Si algo no está en la lista, di que lo coordinamos por WhatsApp.\n";
	$texto .= "- Si preguntan por un examen que no hacemos (resonancia, scanner, endoscopía), dilo derecho y ofrece la alternativa más cercana de la lista.\n";
	$texto .= "- No pides RUT, dirección exacta, datos de tarjeta ni antecedentes de salud. Eso se ve al agendar.\n";
	$texto .= "- El pago es al momento del examen: efectivo, transferencia o tarjeta. No hay pago en línea todavía.\n";
	$texto .= "- Cada visita se reserva en bloques de una hora, con el traslado considerado.\n";
	$texto .= "- El informe lo firma un médico radiólogo y llega en menos de 24 horas en PDF.\n";
	$texto .= "- Nunca prometas una hora concreta ni un profesional concreto: eso se confirma por WhatsApp.\n\n";

	$texto .= "ACCIONES\n";
	$texto .= "Puedes actuar sobre la página. Cuando corresponda, agrega la etiqueta al final de tu mensaje, en su propia línea, sin explicarla:\n";
	$texto .= "[[EXAMEN:slug]] muestra la tarjeta del examen con su precio y el botón de agregar. Úsala cada vez que nombres un examen concreto.\n";
	$texto .= "[[COMUNA:slug]] guarda la comuna del paciente cuando te la diga.\n";
	$texto .= "[[WHATSAPP]] ofrece el botón para confirmar la reserva. Úsala cuando ya sepas el examen y la comuna, o cuando pidan algo que tú no puedes resolver.\n\n";

	$texto .= "CATÁLOGO (nombre | slug | línea | precio | duración | preparación)\n";
	$texto .= implode( "\n", $catalogo ) . "\n\n";
	$texto .= "COMUNAS CON COBERTURA\n";
	$texto .= implode( '; ', $comunas ) . "\n";
	$texto .= "Si la comuna no está en la lista, di que la coordinamos igual escribiendo por WhatsApp.\n\n";
	$texto .= 'HORARIO: ' . medmovil_opt( 'horario', 'Lunes a sábado, 8:00 a 19:00' ) . "\n";

	return $texto;
}

/**
 * Registra el punto de entrada del chat.
 */
function medmovil_chat_rest() {
	register_rest_route( 'medmovil/v1', '/chat', array(
		'methods'             => 'POST',
		'callback'            => 'medmovil_chat_responder',
		'permission_callback' => '__return_true',
		'args'                => array(
			'mensajes' => array( 'required' => true, 'type' => 'array' ),
		),
	) );
}
add_action( 'rest_api_init', 'medmovil_chat_rest' );

/**
 * Cuenta las consultas por IP para que nadie use la clave como API propia.
 *
 * @return bool True si todavía puede preguntar.
 */
function medmovil_chat_cupo() {
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$clave = 'mm_chat_' . md5( $ip . gmdate( 'YmdH' ) );
	$usos  = (int) get_transient( $clave );

	if ( $usos >= MEDMOVIL_CHAT_LIMITE ) {
		return false;
	}
	set_transient( $clave, $usos + 1, HOUR_IN_SECONDS );

	return true;
}

/**
 * Llama a Gemini y devuelve la respuesta.
 *
 * @param WP_REST_Request $req Petición.
 * @return WP_REST_Response
 */
function medmovil_chat_responder( $req ) {
	if ( ! medmovil_chat_con_ia() ) {
		return new WP_REST_Response( array(
			'ok'    => false,
			'texto' => __( 'El asistente todavía no está conectado. Escríbenos por WhatsApp y te respondemos al tiro.', 'medmovil' ),
		), 200 );
	}

	if ( ! medmovil_chat_cupo() ) {
		return new WP_REST_Response( array(
			'ok'    => false,
			'texto' => __( 'Hemos conversado harto por hoy. Sigamos por WhatsApp para no perder el hilo.', 'medmovil' ),
		), 200 );
	}

	$mensajes = (array) $req->get_param( 'mensajes' );
	$mensajes = array_slice( $mensajes, -14 );
	$contents = array();

	foreach ( $mensajes as $m ) {
		$rol   = ( isset( $m['rol'] ) && 'bot' === $m['rol'] ) ? 'model' : 'user';
		$texto = isset( $m['texto'] ) ? sanitize_textarea_field( $m['texto'] ) : '';
		if ( '' === $texto ) {
			continue;
		}
		$contents[] = array(
			'role'  => $rol,
			'parts' => array( array( 'text' => mb_substr( $texto, 0, 1200 ) ) ),
		);
	}

	if ( ! $contents ) {
		return new WP_REST_Response( array( 'ok' => false, 'texto' => __( 'No alcancé a leer tu mensaje. ¿Me lo repites?', 'medmovil' ) ), 200 );
	}

	$url = sprintf(
		'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
		rawurlencode( MEDMOVIL_CHAT_MODELO )
	);

	$respuesta = wp_remote_post( $url, array(
		'timeout' => 25,
		'headers' => array(
			'Content-Type'   => 'application/json',
			'x-goog-api-key' => trim( (string) medmovil_opt( 'gemini_key', '' ) ),
		),
		'body'    => wp_json_encode( array(
			'system_instruction' => array(
				'parts' => array( array( 'text' => medmovil_chat_instrucciones() ) ),
			),
			'contents'           => $contents,
			'generationConfig'   => array(
				'temperature'     => 0.4,
				'maxOutputTokens' => 800,
				'thinkingConfig'  => array( 'thinkingBudget' => 0 ),
			),
		) ),
	) );

	if ( is_wp_error( $respuesta ) ) {
		return new WP_REST_Response( array(
			'ok'    => false,
			'texto' => __( 'Se me cortó la conexión. ¿Seguimos por WhatsApp?', 'medmovil' ),
		), 200 );
	}

	$codigo = wp_remote_retrieve_response_code( $respuesta );
	$cuerpo = json_decode( wp_remote_retrieve_body( $respuesta ), true );

	if ( 200 !== $codigo || ! is_array( $cuerpo ) ) {
		$detalle = isset( $cuerpo['error']['message'] ) ? $cuerpo['error']['message'] : '';
		if ( $detalle ) {
			// Queda registrado para el administrador, nunca se le muestra al paciente.
			set_transient( 'medmovil_chat_error', $detalle, DAY_IN_SECONDS );
		}
		return new WP_REST_Response( array(
			'ok'    => false,
			'texto' => __( 'No pude responderte en este momento. Escríbenos por WhatsApp y te ayudamos de inmediato.', 'medmovil' ),
		), 200 );
	}

	$texto = '';
	if ( isset( $cuerpo['candidates'][0]['content']['parts'] ) ) {
		foreach ( $cuerpo['candidates'][0]['content']['parts'] as $parte ) {
			if ( isset( $parte['text'] ) ) {
				$texto .= $parte['text'];
			}
		}
	}

	$texto = trim( $texto );
	if ( '' === $texto ) {
		$texto = __( 'Prefiero que esto lo veamos con una persona. ¿Te parece si seguimos por WhatsApp?', 'medmovil' ) . "\n[[WHATSAPP]]";
	}

	return new WP_REST_Response( array( 'ok' => true, 'texto' => $texto ), 200 );
}

/**
 * Widget del asistente y botón de WhatsApp, agrupados en una esquina.
 */
function medmovil_dock() {
	$activo = medmovil_chat_activo();
	?>
	<div class="dock">
		<?php if ( $activo ) : ?>
			<button type="button" class="dock-b chatb" title="Abrir asistente" id="chatBtn" aria-expanded="false" aria-controls="chatw"
			        aria-label="<?php esc_attr_e( 'Abrir el asistente', 'medmovil' ); ?>">
				<?php medmovil_icono( 'i-chat' ); ?>
				<span><?php esc_html_e( '¿Te ayudo a elegir?', 'medmovil' ); ?></span>
			</button>
		<?php endif; ?>
		<a class="dock-b wab" aria-label="Contactar por WhatsApp" title="WhatsApp" href="<?php echo esc_url( medmovil_whatsapp() ); ?>" target="_blank" rel="noopener">
			<?php medmovil_icono( 'i-wa' ); ?><span><?php esc_html_e( 'WhatsApp', 'medmovil' ); ?></span>
		</a>
	</div>

	<?php if ( ! $activo ) : ?>
		<?php return; ?>
	<?php endif; ?>

	<section class="chatw" id="chatw" hidden
	         data-ia="<?php echo medmovil_chat_con_ia() ? '1' : '0'; ?>"
	         data-url="<?php echo esc_url( rest_url( 'medmovil/v1/chat' ) ); ?>"
	         data-wa="<?php echo esc_url( medmovil_whatsapp() ); ?>"
	         data-catalogo="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>"
	         data-cobertura="<?php echo esc_url( home_url( '/cobertura/' ) ); ?>"
	         data-saludo="<?php echo esc_attr( medmovil_opt( 'chat_saludo', __( 'Hola, soy el asistente de MedMóvil. Dime qué examen necesitas y en qué comuna vives, y te digo el valor y en cuánto llegamos.', 'medmovil' ) ) ); ?>"
	         aria-label="<?php esc_attr_e( 'Asistente de MedMóvil', 'medmovil' ); ?>">

		<header class="chat-h">
			<span class="chat-av"><?php medmovil_icono( 'i-chat' ); ?></span>
			<div>
				<b><?php esc_html_e( 'Asistente MedMóvil', 'medmovil' ); ?></b>
				<span><?php echo esc_html( medmovil_opt( 'horario', '' ) ); ?></span>
			</div>
			<button type="button" class="chat-x" id="chatX" aria-label="<?php esc_attr_e( 'Cerrar', 'medmovil' ); ?>">
				<?php medmovil_icono( 'i-x' ); ?>
			</button>
		</header>

		<div class="chat-msgs" id="chatMsgs"></div>

		<div class="chat-chips" id="chatChips"></div>

		<form class="chat-f" id="chatF">
			<input type="text" id="chatIn" autocomplete="off" maxlength="500"
			       placeholder="<?php esc_attr_e( 'Escribe tu pregunta…', 'medmovil' ); ?>"
			       aria-label="<?php esc_attr_e( 'Tu mensaje', 'medmovil' ); ?>">
			<button type="submit" aria-label="<?php esc_attr_e( 'Enviar', 'medmovil' ); ?>"><?php medmovil_icono( 'i-send' ); ?></button>
		</form>

		<p class="chat-legal"><?php esc_html_e( 'Te orienta para agendar. No entrega diagnósticos ni interpreta resultados.', 'medmovil' ); ?></p>
	</section>
	<?php
}

/**
 * Avisa en el panel si Google está rechazando la clave.
 */
function medmovil_chat_aviso() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$error = get_transient( 'medmovil_chat_error' );
	if ( ! $error ) {
		return;
	}
	printf(
		'<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'El asistente no pudo hablar con Google:', 'medmovil' ),
		esc_html( $error )
	);
	delete_transient( 'medmovil_chat_error' );
}
add_action( 'admin_notices', 'medmovil_chat_aviso' );
