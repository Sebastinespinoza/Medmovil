<?php
/**
 * Entrada al portal: iniciar sesión, crear cuenta y guardar formularios.
 *
 * Todo pasa por el frontend. Ni el paciente ni el profesional ven jamás el
 * escritorio de WordPress: sería confuso y les mostraría cosas que no les
 * corresponden.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Nadie que no sea administrador entra al escritorio.
 */
function medmovil_fuera_del_escritorio() {
	if ( ! is_admin() || wp_doing_ajax() || current_user_can( 'manage_options' ) ) {
		return;
	}

	// admin-post.php vive dentro de wp-admin, pero no es el escritorio: es por
	// donde se sirven los informes privados. Si lo redirigimos, el paciente
	// pide su PDF y recibe la portada del portal.
	global $pagenow;
	if ( in_array( $pagenow, array( 'admin-post.php', 'admin-ajax.php' ), true ) ) {
		return;
	}

	if ( in_array( medmovil_rol(), array( 'paciente', 'profesional' ), true ) ) {
		wp_safe_redirect( medmovil_url_portal() );
		exit;
	}
}
add_action( 'admin_init', 'medmovil_fuera_del_escritorio' );

/**
 * Y tampoco ven la barra gris de arriba.
 */
function medmovil_sin_barra( $mostrar ) {
	return current_user_can( 'manage_options' ) ? $mostrar : false;
}
add_filter( 'show_admin_bar', 'medmovil_sin_barra' );

/**
 * Después de iniciar sesión, cada quien a su portal.
 *
 * @param string  $url  Destino propuesto.
 * @param string  $pedido Destino pedido.
 * @param WP_User $user Usuario.
 * @return string
 */
function medmovil_destino_login( $url, $pedido, $user ) {
	if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
		return $url;
	}
	if ( user_can( $user, 'manage_options' ) ) {
		return $url;
	}
	// La cuenta del paciente puede retomar el carrito después de identificarse.
	if ( medmovil_rol( $user->ID ) === 'paciente' && $pedido && wp_validate_redirect( $pedido, false ) &&
		untrailingslashit( (string) wp_parse_url( $pedido, PHP_URL_PATH ) ) === untrailingslashit( (string) wp_parse_url( medmovil_url_reserva(), PHP_URL_PATH ) ) ) {
		return $pedido;
	}

	return medmovil_url_portal();
}
add_filter( 'login_redirect', 'medmovil_destino_login', 10, 3 );

/**
 * Formulario de acceso y de registro.
 *
 * @param string $aviso Mensaje de error a mostrar.
 */
function medmovil_pantalla_acceso( $aviso = '' ) {
	$registro = 'no' !== medmovil_portal_opt( 'registro', 'si' );
	$modo     = isset( $_GET['crear'] ) ? 'crear' : 'entrar';
	$operador = 'crear' !== $modo && isset( $_GET['acceso'] ) && 'operador' === sanitize_key( wp_unslash( $_GET['acceso'] ) );
	$volver = isset( $_GET['volver'] ) ? esc_url_raw( wp_unslash( $_GET['volver'] ) ) : ( isset( $_POST['volver'] ) ? esc_url_raw( wp_unslash( $_POST['volver'] ) ) : '' );
	$volver = $volver && wp_validate_redirect( $volver, false ) && untrailingslashit( (string) wp_parse_url( $volver, PHP_URL_PATH ) ) === untrailingslashit( (string) wp_parse_url( medmovil_url_reserva(), PHP_URL_PATH ) ) ? $volver : '';
	?>
	<div class="acc">
		<div class="acc-caja">
			<a class="acc-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( MEDMOVIL_URI . '/assets/img/logo.png' ); ?>"
				     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="480" height="134">
			</a>

			<h1><?php echo esc_html( 'crear' === $modo ? __( 'Crea tu cuenta de paciente', 'medmovil' ) : ( $operador ? __( 'Acceso de operadores', 'medmovil' ) : __( 'Entra a tu cuenta de paciente', 'medmovil' ) ) ); ?></h1>
			<p class="acc-sub"><?php echo esc_html( medmovil_portal_opt( 'bienvenida', __( 'Acá quedan guardados tus exámenes, tus informes en PDF y tus próximas horas.', 'medmovil' ) ) ); ?></p>

			<?php if ( $aviso ) : ?>
				<p class="acc-error"><?php medmovil_icono( 'i-warn' ); ?> <span><?php echo esc_html( $aviso ); ?></span></p>
			<?php endif; ?>

			<?php if ( 'crear' === $modo && $registro ) : ?>
				<form method="post" class="acc-form">
					<?php wp_nonce_field( 'mm_registro', 'mm_nonce' ); ?>
					<input type="hidden" name="volver" value="<?php echo esc_attr( $volver ); ?>">
					<input type="hidden" name="mm_accion" value="registro">

					<label for="rnombre"><?php esc_html_e( 'Tu nombre', 'medmovil' ); ?></label>
					<input type="text" id="rnombre" name="nombre" required autocomplete="name"
					       value="<?php echo isset( $_POST['nombre'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) ) : ''; ?>">

					<label for="rmail"><?php esc_html_e( 'Tu correo', 'medmovil' ); ?></label>
					<input type="email" id="rmail" name="email" required autocomplete="email"
					       value="<?php echo isset( $_POST['email'] ) ? esc_attr( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : ''; ?>">

					<label for="rfono"><?php esc_html_e( 'Tu teléfono', 'medmovil' ); ?></label>
					<input type="tel" id="rfono" name="fono" autocomplete="tel" placeholder="+56 9 ...">

					<label for="rclave"><?php esc_html_e( 'Una contraseña', 'medmovil' ); ?></label>
					<input type="password" id="rclave" name="clave" required minlength="8" autocomplete="new-password">
					<span class="acc-ayuda"><?php esc_html_e( 'Mínimo 8 caracteres.', 'medmovil' ); ?></span>

					<label class="acc-check">
						<input type="checkbox" name="acepto" required>
						<span>
							<?php
							printf(
								/* translators: %s: enlace a la política */
								wp_kses_post( __( 'Acepto que MedMóvil guarde mis datos de salud para prestarme el servicio, según su <a href="%s" target="_blank">política de privacidad</a>.', 'medmovil' ) ),
								esc_url( home_url( '/privacidad/' ) )
							);
							?>
						</span>
					</label>

					<button class="btn btn-p" type="submit"><?php esc_html_e( 'Crear mi cuenta', 'medmovil' ); ?></button>
					<p class="acc-pie">
						<?php esc_html_e( '¿Ya tienes cuenta?', 'medmovil' ); ?>
						<a href="<?php echo esc_url( medmovil_url_portal() ); ?>"><?php esc_html_e( 'Entra acá', 'medmovil' ); ?></a>
					</p>
				</form>
			<?php else : ?>
				<form method="post" class="acc-form" action="<?php echo esc_url( $operador ? add_query_arg( 'acceso', 'operador', medmovil_url_portal() ) : medmovil_url_portal() ); ?>">
					<?php wp_nonce_field( 'mm_login', 'mm_nonce' ); ?>
					<input type="hidden" name="mm_accion" value="login">
					<input type="hidden" name="modo" value="<?php echo $operador ? 'operador' : 'paciente'; ?>">
					<input type="hidden" name="volver" value="<?php echo esc_attr( $volver ); ?>">
					<label for="user_login"><?php esc_html_e( 'Correo o usuario', 'medmovil' ); ?></label>
					<input type="text" id="user_login" name="log" required autocomplete="username">

					<label for="user_pass"><?php esc_html_e( 'Contraseña', 'medmovil' ); ?></label>
					<input type="password" id="user_pass" name="pwd" required autocomplete="current-password">

					<label class="acc-check">
						<input type="checkbox" name="rememberme" value="forever" checked>
						<span><?php esc_html_e( 'Mantener la sesión abierta', 'medmovil' ); ?></span>
					</label>

					<button class="btn btn-p" type="submit"><?php esc_html_e( 'Entrar', 'medmovil' ); ?></button>

					<p class="acc-pie">
						<a href="<?php echo esc_url( wp_lostpassword_url( medmovil_url_portal() ) ); ?>"><?php esc_html_e( 'Olvidé mi contraseña', 'medmovil' ); ?></a>
						<?php if ( $registro && ! $operador ) : ?>
							· <a href="<?php echo esc_url( add_query_arg( array( 'crear' => '1', 'volver' => $volver ), medmovil_url_portal() ) ); ?>"><?php esc_html_e( 'Crear una cuenta', 'medmovil' ); ?></a>
						<?php endif; ?>
					</p>
				</form>
			<?php endif; ?>
			<p class="acc-pie"><?php if ( $operador ) : ?><a href="<?php echo esc_url( medmovil_url_portal() ); ?>"><?php esc_html_e( 'Soy paciente', 'medmovil' ); ?></a><?php else : ?><a href="<?php echo esc_url( add_query_arg( 'acceso', 'operador', medmovil_url_portal() ) ); ?>"><?php esc_html_e( 'Soy operador de MedMóvil', 'medmovil' ); ?></a><?php endif; ?></p>

			<p class="acc-nota">
				<?php medmovil_icono( 'i-lock' ); ?>
				<span><?php esc_html_e( 'Para abrir tus informes necesitas iniciar sesión en tu cuenta.', 'medmovil' ); ?></span>
			</p>
		</div>

		<aside class="acc-lado">
			<?php if ( $operador ) : ?>
				<h2><?php esc_html_e( 'Tu espacio de trabajo', 'medmovil' ); ?></h2>
				<ul>
					<li><?php medmovil_icono( 'i-calcheck' ); ?> <span><?php esc_html_e( 'Consultar la agenda y las visitas que te asignó el equipo', 'medmovil' ); ?></span></li>
					<li><?php medmovil_icono( 'i-usercheck' ); ?> <span><?php esc_html_e( 'Ver la información necesaria de tus pacientes asignados', 'medmovil' ); ?></span></li>
					<li><?php medmovil_icono( 'i-filecheck' ); ?> <span><?php esc_html_e( 'Registrar la atención y subir el informe cuando corresponda', 'medmovil' ); ?></span></li>
				</ul>
				<p><?php esc_html_e( 'El acceso de operadores lo crea y habilita la administración de MedMóvil.', 'medmovil' ); ?></p>
			<?php else : ?>
				<h2><?php esc_html_e( 'Con tu cuenta puedes', 'medmovil' ); ?></h2>
				<ul>
					<li><?php medmovil_icono( 'i-filecheck' ); ?> <span><?php esc_html_e( 'Descargar tus informes disponibles desde el portal', 'medmovil' ); ?></span></li>
					<li><?php medmovil_icono( 'i-calcheck' ); ?> <span><?php esc_html_e( 'Ver tus solicitudes, horas confirmadas y el profesional asignado', 'medmovil' ); ?></span></li>
					<li><?php medmovil_icono( 'i-notebook' ); ?> <span><?php esc_html_e( 'Completar tu ficha antes de la visita', 'medmovil' ); ?></span></li>
					<li><?php medmovil_icono( 'i-coin' ); ?> <span><?php esc_html_e( 'Consultar los pagos registrados por el equipo', 'medmovil' ); ?></span></li>
				</ul>
				<p><?php esc_html_e( 'Para solicitar una hora y seguir su confirmación necesitas una cuenta de paciente.', 'medmovil' ); ?></p>
			<?php endif; ?>
		</aside>
	</div>
	<?php
}

/**
 * Procesa los formularios del portal antes de imprimir nada.
 *
 * @return string Mensaje de error, si hubo.
 */
function medmovil_portal_acciones() {
	if ( ! isset( $_POST['mm_accion'] ) ) {
		return '';
	}

	$accion = sanitize_key( wp_unslash( $_POST['mm_accion'] ) );
	$nonce  = isset( $_POST['mm_nonce'] ) ? sanitize_key( $_POST['mm_nonce'] ) : '';
	if ( 'login' === $accion ) {
		return medmovil_hacer_login( $nonce );
	}

	if ( 'registro' === $accion ) {
		return medmovil_hacer_registro( $nonce );
	}

	if ( ! is_user_logged_in() || ! wp_verify_nonce( $nonce, 'mm_portal_' . $accion ) ) {
		return __( 'La sesión expiró. Vuelve a intentarlo.', 'medmovil' );
	}

	$uid = get_current_user_id();
	$rol = medmovil_rol();

	switch ( $accion ) {
		case 'ficha':
			if ( 'paciente' !== $rol ) { break; }
			$ficha = array();
			foreach ( medmovil_campos_ficha() as $k => $campo ) {
				if ( ! isset( $_POST[ 'f_' . $k ] ) ) {
					continue;
				}
				$v = wp_unslash( $_POST[ 'f_' . $k ] );
				$ficha[ $k ] = 'textarea' === $campo['t'] ? sanitize_textarea_field( $v ) : sanitize_text_field( $v );
			}
			update_user_meta( $uid, '_mm_ficha', $ficha );
			medmovil_volver( 'ficha', 'guardado' );
			break;

		case 'perfil':
			if ( ! in_array( $rol, array( 'paciente', 'profesional', 'admin' ), true ) ) { break; }
			$datos = array( 'ID' => $uid );
			if ( isset( $_POST['nombre'] ) ) {
				$datos['first_name']   = sanitize_text_field( wp_unslash( $_POST['nombre'] ) );
				$datos['display_name'] = $datos['first_name'];
			}
			if ( isset( $_POST['apellido'] ) ) {
				$datos['last_name'] = sanitize_text_field( wp_unslash( $_POST['apellido'] ) );
			}
			wp_update_user( $datos );
			foreach ( array( 'fono', 'comuna', 'direccion' ) as $k ) {
				if ( isset( $_POST[ $k ] ) ) {
					update_user_meta( $uid, '_mm_' . $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
				}
			}
			if ( 'profesional' === $rol ) {
				foreach ( array( 'rol_prof', 'registro', 'bio' ) as $k ) {
					if ( isset( $_POST[ $k ] ) ) {
						update_user_meta( $uid, '_mm_' . $k, sanitize_textarea_field( wp_unslash( $_POST[ $k ] ) ) );
					}
				}
			}
			medmovil_volver( 'perfil', 'guardado' );
			break;

		case 'disponibilidad':
            $GLOBALS['mm_availability_input'] = wp_unslash($_POST);
            $result = medmovil_save_availability($uid, $GLOBALS['mm_availability_input']);
            if (is_wp_error($result)) { return $result->get_error_message(); }
            unset($GLOBALS['mm_availability_input']);
            medmovil_volver('horarios', 'guardado');
            break;
		case 'horarios':
			if ( 'profesional' !== $rol ) {
				break;
			}
			$semana = array();
			foreach ( array_keys( medmovil_dias() ) as $dia ) {
				$horas = isset( $_POST[ 'd' . $dia ] ) ? (array) wp_unslash( $_POST[ 'd' . $dia ] ) : array();
				$limpio = array();
				foreach ( $horas as $h ) {
					$h = sanitize_text_field( $h );
					if ( in_array( $h, medmovil_horas(), true ) ) {
						$limpio[] = $h;
					}
				}
				if ( $limpio ) {
					$semana[ $dia ] = $limpio;
				}
			}
			update_user_meta( $uid, '_mm_semana', $semana );
			medmovil_volver( 'horarios', 'guardado' );
			break;

		case 'estado':
			if ( ! in_array( $rol, array( 'profesional', 'admin' ), true ) ) {
				break;
			}
			$id  = isset( $_POST['atencion'] ) ? (int) $_POST['atencion'] : 0;
			$a   = medmovil_atencion( $id );
			$new = isset( $_POST['estado'] ) ? sanitize_key( wp_unslash( $_POST['estado'] ) ) : '';

			if ( ! $a || ! array_key_exists( $new, medmovil_estados() ) ) {
				break;
			}
			if ( 'admin' !== $rol && (int) $a['profesional'] !== $uid ) {
				break;
			}
			// Una solicitud sin asignar no se puede completar; "informada" requiere informe.
			if ( 'profesional' === $rol && ( ! in_array( $a['estado'], array( 'agendada', 'confirmada', 'realizada', 'informada' ), true ) || ! in_array( $new, array( 'confirmada', 'realizada', 'cancelada' ), true ) ) ) {
				break;
			}
			if ( 'informada' === $new && ! $a['informe'] ) { break; }

			update_post_meta( $id, '_mm_estado', $new );
			if ( in_array( $new, array( 'realizada', 'informada' ), true ) && isset( $_POST['pagado'] ) ) {
				update_post_meta( $id, '_mm_pagado', min( $a['monto'], absint( $_POST['pagado'] ) ) );
			}
			if ( in_array( $new, array( 'realizada', 'informada' ), true ) && isset( $_POST['medio'] ) ) {
				$medio = sanitize_key( wp_unslash( $_POST['medio'] ) );
				if ( in_array( $medio, array( '', 'efectivo', 'transferencia', 'tarjeta' ), true ) ) { update_post_meta( $id, '_mm_medio', $medio ); }
			}
			if ( isset( $_POST['notas'] ) ) {
				update_post_meta( $id, '_mm_notas', sanitize_textarea_field( wp_unslash( $_POST['notas'] ) ) );
			}
			medmovil_volver( isset( $_POST['volver'] ) ? sanitize_key( wp_unslash( $_POST['volver'] ) ) : 'agenda', 'guardado' );
			break;

		case 'informe':
			if ( ! in_array( $rol, array( 'profesional', 'admin' ), true ) ) {
				break;
			}
			$id = isset( $_POST['atencion'] ) ? (int) $_POST['atencion'] : 0;
			$a  = medmovil_atencion( $id );

			if ( ! $a || ( 'admin' !== $rol && (int) $a['profesional'] !== $uid ) || ! in_array( $a['estado'], array( 'realizada', 'informada' ), true ) ) {
				break;
			}
			if ( empty( $_FILES['archivo']['name'] ) ) {
				return __( 'Elige el archivo del informe antes de subirlo.', 'medmovil' );
			}

			$guardado = medmovil_guardar_archivo( $_FILES['archivo'], $id );
			if ( is_wp_error( $guardado ) ) {
				return $guardado->get_error_message();
			}

			update_post_meta( $id, '_mm_informe', $guardado );
			update_post_meta( $id, '_mm_estado', 'informada' );
			medmovil_avisar_informe( $a );
			medmovil_volver( 'informes', 'subido' );
			break;
	}

	return '';
}

/** Acceso dentro de la página del portal, con error legible y retorno al carrito. */
function medmovil_hacer_login( $nonce ) {
	if ( ! wp_verify_nonce( $nonce, 'mm_login' ) ) {
		return __( 'El formulario expiró. Recarga la página.', 'medmovil' );
	}
	$nombre = isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '';
	$clave = isset( $_POST['pwd'] ) ? (string) wp_unslash( $_POST['pwd'] ) : '';
	if ( ! $nombre || ! $clave ) { return __( 'Ingresa tu correo y contraseña.', 'medmovil' ); }
	$user = wp_signon( array(
		'user_login' => $nombre,
		'user_password' => $clave,
		'remember' => ! empty( $_POST['rememberme'] ),
	), is_ssl() );
	if ( is_wp_error( $user ) ) { return __( 'No pudimos ingresar. Revisa tus datos o recupera tu contraseña.', 'medmovil' ); }
	$rol = medmovil_rol( $user->ID );
	$operador = isset( $_POST['modo'] ) && 'operador' === sanitize_key( wp_unslash( $_POST['modo'] ) );
	if ( $operador && ! in_array( $rol, array( 'profesional', 'admin' ), true ) ) {
		wp_clear_auth_cookie();
		wp_set_current_user( 0 );
		return __( 'Esta cuenta es de paciente. Usa el acceso para pacientes.', 'medmovil' );
	}
	wp_set_current_user( $user->ID );
	$volver = isset( $_POST['volver'] ) ? esc_url_raw( wp_unslash( $_POST['volver'] ) ) : '';
	if ( 'paciente' === $rol && $volver && wp_validate_redirect( $volver, false ) && untrailingslashit( (string) wp_parse_url( $volver, PHP_URL_PATH ) ) === untrailingslashit( (string) wp_parse_url( medmovil_url_reserva(), PHP_URL_PATH ) ) ) {
		wp_safe_redirect( $volver );
	} else {
		wp_safe_redirect( medmovil_url_portal() );
	}
	exit;
}

/**
 * Crea la cuenta de un paciente.
 *
 * @param string $nonce Nonce recibido.
 * @return string Error, si hubo.
 */
function medmovil_hacer_registro( $nonce ) {
	if ( ! wp_verify_nonce( $nonce, 'mm_registro' ) ) {
		return __( 'El formulario expiró. Vuelve a enviarlo.', 'medmovil' );
	}
	if ( 'no' === medmovil_portal_opt( 'registro', 'si' ) ) {
		return __( 'El registro está cerrado por ahora. Escríbenos por WhatsApp.', 'medmovil' );
	}

	$nombre = isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '';
	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$clave  = isset( $_POST['clave'] ) ? (string) wp_unslash( $_POST['clave'] ) : '';
	$fono   = isset( $_POST['fono'] ) ? sanitize_text_field( wp_unslash( $_POST['fono'] ) ) : '';

	if ( ! $nombre || ! is_email( $email ) ) {
		return __( 'Revisa tu nombre y tu correo.', 'medmovil' );
	}
	if ( strlen( $clave ) < 8 ) {
		return __( 'La contraseña tiene que tener al menos 8 caracteres.', 'medmovil' );
	}
	if ( empty( $_POST['acepto'] ) ) {
		return __( 'Necesitamos que aceptes la política de privacidad para crear la cuenta.', 'medmovil' );
	}
	if ( email_exists( $email ) ) {
		return __( 'Ya hay una cuenta con ese correo. Entra con tu contraseña o recupérala.', 'medmovil' );
	}

	$uid = wp_insert_user( array(
		'user_login'   => $email,
		'user_email'   => $email,
		'user_pass'    => $clave,
		'first_name'   => $nombre,
		'display_name' => $nombre,
		'role'         => 'mm_paciente',
	) );

	if ( is_wp_error( $uid ) ) {
		return $uid->get_error_message();
	}

	update_user_meta( $uid, '_mm_fono', $fono );
	update_user_meta( $uid, '_mm_acepto', current_time( 'mysql' ) );

	wp_set_current_user( $uid );
	wp_set_auth_cookie( $uid, true );

	$volver = isset( $_POST['volver'] ) ? esc_url_raw( wp_unslash( $_POST['volver'] ) ) : '';
	if ( $volver && wp_validate_redirect( $volver, false ) && untrailingslashit( (string) wp_parse_url( $volver, PHP_URL_PATH ) ) === untrailingslashit( (string) wp_parse_url( medmovil_url_reserva(), PHP_URL_PATH ) ) ) {
		wp_safe_redirect( $volver );
	} else {
		wp_safe_redirect( medmovil_url_portal( 'ficha', array( 'bienvenida' => 1 ) ) );
	}
	exit;
}

/**
 * Vuelve a una sección con un aviso.
 *
 * @param string $seccion Sección.
 * @param string $ok      Clave del mensaje.
 */
function medmovil_volver( $seccion, $ok ) {
	wp_safe_redirect( medmovil_url_portal( $seccion, array( 'ok' => $ok ) ) );
	exit;
}

/**
 * Avisa al paciente que su informe está listo.
 *
 * @param array $atencion Atención.
 */
function medmovil_avisar_informe( $atencion ) {
	$paciente = get_userdata( $atencion['paciente'] );
	if ( ! $paciente || ! $paciente->user_email ) {
		return;
	}

	$nombres = array();
	foreach ( $atencion['examenes'] as $e ) {
		$nombres[] = $e['n'];
	}

	$cuerpo  = sprintf( __( 'Hola %s,', 'medmovil' ), $paciente->first_name ? $paciente->first_name : $paciente->display_name ) . "\n\n";
	$cuerpo .= __( 'Tu informe ya está disponible en tu cuenta de MedMóvil.', 'medmovil' ) . "\n\n";
	$cuerpo .= sprintf( __( 'Examen: %s', 'medmovil' ), implode( ', ', $nombres ) ) . "\n";
	$cuerpo .= sprintf( __( 'Fecha de la atención: %s', 'medmovil' ), $atencion['fecha'] ) . "\n\n";
	$cuerpo .= sprintf( __( 'Puedes descargarlo acá: %s', 'medmovil' ), medmovil_url_portal( 'informes' ) ) . "\n\n";
	$cuerpo .= __( 'El informe es para tu médico: él es quien lo interpreta y te indica los pasos a seguir.', 'medmovil' ) . "\n\n";
	$cuerpo .= get_bloginfo( 'name' );

	wp_mail(
		$paciente->user_email,
		__( 'Tu informe de MedMóvil ya está listo', 'medmovil' ),
		$cuerpo
	);
}

/**
 * Avisa al profesional que le tomaron una hora.
 *
 * @param int $atencion_id Atención.
 */
function medmovil_avisar_agenda( $atencion_id ) {
	$a = medmovil_atencion( $atencion_id );
	if ( ! $a || ! $a['profesional'] ) {
		return;
	}
	$prof = get_userdata( $a['profesional'] );
	if ( ! $prof || ! $prof->user_email ) {
		return;
	}

	$paciente = $a['paciente'] ? get_userdata( $a['paciente'] ) : null;
	$nombres  = array();
	foreach ( $a['examenes'] as $e ) {
		$nombres[] = $e['n'];
	}

	$cuerpo  = sprintf( __( 'Hola %s,', 'medmovil' ), $prof->display_name ) . "\n\n";
	$cuerpo .= __( 'Te tomaron una hora:', 'medmovil' ) . "\n\n";
	$cuerpo .= sprintf( __( 'Fecha: %1$s a las %2$s', 'medmovil' ), $a['fecha'], $a['hora'] ) . "\n";
	$cuerpo .= sprintf( __( 'Examen: %s', 'medmovil' ), implode( ', ', $nombres ) ) . "\n";
	$cuerpo .= sprintf( __( 'Comuna: %s', 'medmovil' ), $a['comuna'] ) . "\n";
	$cuerpo .= sprintf( __( 'Paciente: %s', 'medmovil' ), $paciente ? $paciente->display_name : '—' ) . "\n\n";
	$cuerpo .= sprintf( __( 'La ficha clínica y la dirección están en tu agenda: %s', 'medmovil' ), medmovil_url_portal( 'agenda' ) ) . "\n\n";
	$cuerpo .= get_bloginfo( 'name' );

	wp_mail( $prof->user_email, __( 'Nueva hora agendada en MedMóvil', 'medmovil' ), $cuerpo );
}
