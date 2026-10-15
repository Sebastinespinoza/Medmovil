<?php
/**
 * Lo que hace personalizables a los portales.
 *
 * Todo lo que cambia entre un MedMóvil y otro —qué secciones ve cada rol, qué
 * indicadores se muestran, el porcentaje de comisión, si el profesional ve o no
 * sus ingresos— se decide acá, sin tocar código.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lee una opción del portal.
 *
 * @param string $clave   Nombre.
 * @param mixed  $defecto Valor por omisión.
 * @return mixed
 */
function medmovil_portal_opt( $clave, $defecto = '' ) {
	$opts = get_option( 'medmovil_portal', array() );

	return isset( $opts[ $clave ] ) ? $opts[ $clave ] : $defecto;
}

/**
 * Indicadores disponibles por rol. El dueño elige cuáles se muestran.
 *
 * @return array
 */
function medmovil_kpis_disponibles() {
	return array(
		'paciente' => array(
			'proxima'  => __( 'Próxima solicitud u hora', 'medmovil' ),
			'total'    => __( 'Exámenes realizados', 'medmovil' ),
			'informes' => __( 'Informes disponibles', 'medmovil' ),
			'gastado'  => __( 'Total pagado', 'medmovil' ),
			'ficha'    => __( 'Ficha clínica completa', 'medmovil' ),
		),
		'profesional' => array(
			'hoy'       => __( 'Atenciones de hoy', 'medmovil' ),
			'semana'    => __( 'Atenciones de la semana', 'medmovil' ),
			'pacientes' => __( 'Pacientes atendidos', 'medmovil' ),
			'ingresos'  => __( 'Ingresos del mes', 'medmovil' ),
			'pendiente' => __( 'Informes pendientes', 'medmovil' ),
			'comision'  => __( 'Comisión a pagar', 'medmovil' ),
		),
		'admin' => array(
			'ventas'     => __( 'Monto agendado del mes', 'medmovil' ),
			'comision'   => __( 'Mi comisión del mes', 'medmovil' ),
			'atenciones' => __( 'Atenciones del mes', 'medmovil' ),
			'pacientes'  => __( 'Pacientes activos', 'medmovil' ),
			'ticket'     => __( 'Ticket promedio', 'medmovil' ),
			'canceladas' => __( 'Canceladas del mes', 'medmovil' ),
		),
	);
}

/**
 * ¿Se muestra este indicador?
 *
 * @param string $rol  Rol.
 * @param string $kpi  Clave.
 * @return bool
 */
function medmovil_kpi_visible( $rol, $kpi ) {
	$ocultos = (array) medmovil_portal_opt( 'kpis_' . $rol, array() );

	return ! in_array( $kpi, $ocultos, true );
}

/**
 * Cosas pendientes por sección, para las insignias del menú.
 *
 * @param string $rol Rol.
 * @return array
 */
function medmovil_avisos( $rol ) {
	$uid = get_current_user_id();

	if ( 'paciente' === $rol ) {
		$proximas = medmovil_atenciones( array( 'paciente' => $uid, 'estado' => array( 'solicitada', 'agendada', 'confirmada' ), 'desde' => wp_date( 'Y-m-d' ) ) );
		$nuevos   = 0;
		foreach ( medmovil_atenciones( array( 'paciente' => $uid, 'estado' => 'informada', 'limite' => 20 ) ) as $a ) {
			if ( ! get_user_meta( $uid, '_mm_visto_' . $a['id'], true ) ) {
				$nuevos++;
			}
		}

		return array( 'horas' => count( $proximas ), 'informes' => $nuevos );
	}

	if ( 'profesional' === $rol ) {
		$hoy = medmovil_atenciones( array( 'profesional' => $uid, 'desde' => wp_date( 'Y-m-d' ), 'hasta' => wp_date( 'Y-m-d' ) ) );
		$sin = medmovil_atenciones( array( 'profesional' => $uid, 'estado' => 'realizada', 'limite' => 60 ) );
		$pendientes = 0;
		foreach ( $sin as $a ) {
			if ( ! $a['informe'] ) {
				$pendientes++;
			}
		}

		return array( 'agenda' => count( $hoy ), 'informes' => $pendientes );
	}

	if ( 'admin' === $rol ) {
		$nuevas = medmovil_atenciones( array( 'estado' => 'solicitada', 'limite' => 60 ) );

		return array( 'atenciones' => count( $nuevas ) );
	}

	return array();
}

/**
 * Página de ajustes de los portales.
 */
function medmovil_menu_portal() {
	add_submenu_page(
		'medmovil',
		__( 'Portales', 'medmovil' ),
		__( 'Portales', 'medmovil' ),
		'manage_options',
		'medmovil-portal',
		'medmovil_pagina_portal'
	);
}
add_action( 'admin_menu', 'medmovil_menu_portal', 15 );

/**
 * Dibuja y guarda la página de ajustes de los portales.
 */
function medmovil_pagina_portal() {
	if ( isset( $_POST['mm_portal_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mm_portal_nonce'] ), 'mm_portal' ) && current_user_can( 'manage_options' ) ) {
		$nuevo = array(
			'nombre'       => isset( $_POST['nombre'] ) ? sanitize_text_field( wp_unslash( $_POST['nombre'] ) ) : '',
			'bienvenida'   => isset( $_POST['bienvenida'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bienvenida'] ) ) : '',
			'registro'     => isset( $_POST['registro'] ) ? 'si' : 'no',
			'ver_ingresos' => isset( $_POST['ver_ingresos'] ) ? 'si' : 'no',
			'ver_comision' => isset( $_POST['ver_comision'] ) ? 'si' : 'no',
		);

		foreach ( array( 'paciente', 'profesional', 'admin' ) as $rol ) {
			$nuevo[ 'kpis_' . $rol ]    = isset( $_POST[ 'kpis_' . $rol ] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST[ 'kpis_' . $rol ] ) ) : array();
			$nuevo[ 'ocultar_' . $rol ] = isset( $_POST[ 'ocultar_' . $rol ] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST[ 'ocultar_' . $rol ] ) ) : array();
		}

		update_option( 'medmovil_portal', $nuevo );

		$opts = get_option( 'medmovil_opts', array() );
		$opts['comision'] = isset( $_POST['comision'] ) ? (float) str_replace( ',', '.', wp_unslash( $_POST['comision'] ) ) : 5;
		update_option( 'medmovil_opts', $opts );

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Listo, los portales quedaron como los dejaste.', 'medmovil' ) . '</p></div>';
	}

	$kpis     = medmovil_kpis_disponibles();
	$secciones = array(
		'paciente'    => medmovil_secciones( 'paciente', false ),
		'profesional' => medmovil_secciones( 'profesional', false ),
		'admin'       => medmovil_secciones( 'admin', false ),
	);
	$titulos = array(
		'paciente'    => __( 'Portal del paciente', 'medmovil' ),
		'profesional' => __( 'Portal del profesional', 'medmovil' ),
		'admin'       => __( 'Tu panel de control', 'medmovil' ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Portales', 'medmovil' ); ?></h1>
		<p class="description" style="max-width:80ch">
			<?php esc_html_e( 'Acá decides qué ve cada quien cuando entra a su cuenta. Todo lo que desmarques desaparece de su menú y de su resumen; no se borra nada, solo deja de mostrarse.', 'medmovil' ); ?>
		</p>

		<form method="post">
			<?php wp_nonce_field( 'mm_portal', 'mm_portal_nonce' ); ?>

			<h2><?php esc_html_e( 'General', 'medmovil' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="nombre"><?php esc_html_e( 'Cómo se llama el portal', 'medmovil' ); ?></label></th>
					<td><input type="text" id="nombre" name="nombre" class="regular-text"
					           value="<?php echo esc_attr( medmovil_portal_opt( 'nombre', 'Mi cuenta MedMóvil' ) ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="bienvenida"><?php esc_html_e( 'Mensaje de bienvenida', 'medmovil' ); ?></label></th>
					<td>
						<textarea id="bienvenida" name="bienvenida" rows="2" class="large-text"><?php echo esc_textarea( medmovil_portal_opt( 'bienvenida', 'Acá quedan guardados tus exámenes, tus informes en PDF y tus próximas horas.' ) ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="comision"><?php esc_html_e( 'Tu comisión por examen', 'medmovil' ); ?></label></th>
					<td>
						<input type="number" id="comision" name="comision" step="0.1" min="0" max="100" style="width:90px"
						       value="<?php echo esc_attr( medmovil_comision_pct() ); ?>"> %
						<p class="description"><?php esc_html_e( 'Se calcula sobre el monto de las atenciones realizadas. Aparece en tu panel y, si lo permites, en el del profesional.', 'medmovil' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Permisos', 'medmovil' ); ?></th>
					<td>
						<label><input type="checkbox" name="registro" <?php checked( medmovil_portal_opt( 'registro', 'si' ), 'si' ); ?>>
							<?php esc_html_e( 'Los pacientes pueden crearse una cuenta solos', 'medmovil' ); ?></label><br>
						<label><input type="checkbox" name="ver_ingresos" <?php checked( medmovil_portal_opt( 'ver_ingresos', 'si' ), 'si' ); ?>>
							<?php esc_html_e( 'El profesional ve cuánto lleva facturado', 'medmovil' ); ?></label><br>
						<label><input type="checkbox" name="ver_comision" <?php checked( medmovil_portal_opt( 'ver_comision', 'si' ), 'si' ); ?>>
							<?php esc_html_e( 'El profesional ve cuánta comisión te debe', 'medmovil' ); ?></label>
						<p class="description"><?php esc_html_e( 'Mostrarle la comisión evita discusiones al final del mes: los dos ven el mismo número.', 'medmovil' ); ?></p>
					</td>
				</tr>
			</table>

			<?php foreach ( $titulos as $rol => $titulo ) : ?>
				<h2 style="margin-top:34px"><?php echo esc_html( $titulo ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Secciones del menú', 'medmovil' ); ?></th>
						<td>
							<?php
							$ocultas = (array) medmovil_portal_opt( 'ocultar_' . $rol, array() );
							foreach ( $secciones[ $rol ] as $clave => $item ) :
								?>
								<label style="display:inline-block;min-width:230px;margin-bottom:6px">
									<input type="checkbox" name="ocultar_<?php echo esc_attr( $rol ); ?>[]"
									       value="<?php echo esc_attr( $clave ); ?>"
									       <?php checked( in_array( $clave, $ocultas, true ) ); ?>>
									<?php
									printf(
										/* translators: %s: nombre de la sección */
										esc_html__( 'Ocultar «%s»', 'medmovil' ),
										esc_html( $item[1] )
									);
									?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Indicadores del resumen', 'medmovil' ); ?></th>
						<td>
							<?php
							$ocultos = (array) medmovil_portal_opt( 'kpis_' . $rol, array() );
							foreach ( $kpis[ $rol ] as $clave => $etiqueta ) :
								?>
								<label style="display:inline-block;min-width:230px;margin-bottom:6px">
									<input type="checkbox" name="kpis_<?php echo esc_attr( $rol ); ?>[]"
									       value="<?php echo esc_attr( $clave ); ?>"
									       <?php checked( in_array( $clave, $ocultos, true ) ); ?>>
									<?php
									printf(
										/* translators: %s: nombre del indicador */
										esc_html__( 'Ocultar «%s»', 'medmovil' ),
										esc_html( $etiqueta )
									);
									?>
								</label>
							<?php endforeach; ?>
						</td>
					</tr>
				</table>
			<?php endforeach; ?>

			<?php submit_button( __( 'Guardar los portales', 'medmovil' ) ); ?>
		</form>
	</div>
	<?php
}
