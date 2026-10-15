<?php
/**
 * Portal del paciente.
 *
 * Quien entra acá casi nunca es el paciente: es su hijo o su hija. Por eso todo
 * está escrito para alguien que está organizando el examen de otra persona y
 * quiere dos cosas rápido: cuándo llegan y dónde está el informe.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dibuja la sección pedida.
 *
 * @param string $seccion Sección.
 */
function medmovil_portal_paciente( $seccion ) {
	$uid = get_current_user_id();

	switch ( $seccion ) {
		case 'horas':
			medmovil_pac_horas( $uid );
			break;
		case 'informes':
			medmovil_pac_informes( $uid );
			break;
		case 'pagos':
			medmovil_pac_pagos( $uid );
			break;
		case 'ficha':
			medmovil_pac_ficha( $uid );
			break;
		case 'perfil':
			medmovil_pac_perfil( $uid );
			break;
		default:
			medmovil_pac_resumen( $uid );
	}
}

/**
 * Resumen del paciente.
 *
 * @param int $uid Usuario.
 */
function medmovil_pac_resumen( $uid ) {
	$todas     = medmovil_atenciones( array( 'paciente' => $uid, 'limite' => 100 ) );
	$proximas  = array();
	$hechas    = array();
	$gastado   = 0;
	$informes  = 0;
	$hoy       = wp_date( 'Y-m-d' );

	foreach ( $todas as $a ) {
		if ( in_array( $a['estado'], array( 'solicitada', 'agendada', 'confirmada' ), true ) && $a['fecha'] >= $hoy ) {
			$proximas[] = $a;
		}
		if ( in_array( $a['estado'], array( 'realizada', 'informada' ), true ) ) {
			$hechas[]  = $a;
			$gastado  += $a['pagado'];
		}
		if ( $a['informe'] ) {
			$informes++;
		}
	}
	usort( $proximas, function ( $x, $y ) { return $x['ts'] <=> $y['ts']; } );
	$pct = medmovil_ficha_pct( $uid );
	?>

	<?php medmovil_kpis_abrir(); ?>
		<?php
		if ( medmovil_kpi_visible( 'paciente', 'proxima' ) ) {
			$p = $proximas ? $proximas[0] : null;
			medmovil_kpi( array(
				'icono'    => 'i-calcheck',
				'etiqueta' => __( 'Tu próxima solicitud u hora', 'medmovil' ),
				'valor'    => $p ? medmovil_fecha( 'j M · H:i', $p['ts'] ) : __( 'Sin horas', 'medmovil' ),
				'nota'     => $p ? ( 'solicitada' === $p['estado'] ? __( 'Pendiente de confirmación', 'medmovil' ) : ( isset( $p['examenes'][0]['n'] ) ? $p['examenes'][0]['n'] : __( 'Visita a domicilio', 'medmovil' ) ) ) : __( 'Solicita cuando quieras', 'medmovil' ),
				'url'      => medmovil_url_portal( 'horas' ),
			) );
		}
		if ( medmovil_kpi_visible( 'paciente', 'total' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-checklist',
				'etiqueta' => __( 'Exámenes realizados', 'medmovil' ),
				'valor'    => count( $hechas ),
				'nota'     => __( 'con MedMóvil', 'medmovil' ),
				'color'    => 'var(--ok)',
			) );
		}
		if ( medmovil_kpi_visible( 'paciente', 'informes' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-filecheck',
				'etiqueta' => __( 'Informes guardados', 'medmovil' ),
				'valor'    => $informes,
				'nota'     => __( 'listos para descargar', 'medmovil' ),
				'color'    => 'var(--c-dop)',
				'url'      => medmovil_url_portal( 'informes' ),
			) );
		}
		if ( medmovil_kpi_visible( 'paciente', 'ficha' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-notebook',
				'etiqueta' => __( 'Ficha clínica', 'medmovil' ),
				'valor'    => $pct . '%',
				'nota'     => $pct >= 80 ? __( 'completa', 'medmovil' ) : __( 'te falta completarla', 'medmovil' ),
				'color'    => $pct >= 80 ? 'var(--ok)' : 'var(--warn)',
				'url'      => medmovil_url_portal( 'ficha' ),
			) );
		}
		if ( medmovil_kpi_visible( 'paciente', 'gastado' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-coin',
				'etiqueta' => __( 'Total pagado', 'medmovil' ),
				'valor'    => medmovil_precio( $gastado ),
				'nota'     => __( 'historial de pagos', 'medmovil' ),
				'color'    => 'var(--c-lab)',
				'url'      => medmovil_url_portal( 'pagos' ),
			) );
		}
		?>
	<?php medmovil_kpis_cerrar(); ?>

	<?php if ( $pct < 80 ) : ?>
		<div class="pn-nota">
			<?php medmovil_icono( 'i-notebook' ); ?>
			<div>
				<b><?php esc_html_e( 'Completa tu ficha clínica', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Con los medicamentos, la movilidad y cómo llegar a tu casa, el profesional prepara la visita antes de salir. Toma dos minutos y se hace una sola vez.', 'medmovil' ); ?></p>
			</div>
			<a class="pbtn pbtn-p" href="<?php echo esc_url( medmovil_url_portal( 'ficha' ) ); ?>">
				<?php esc_html_e( 'Completarla', 'medmovil' ); ?>
			</a>
		</div>
	<?php endif; ?>

	<div class="pgrid pgrid-1-2">
		<?php
		medmovil_caja_abrir(
			__( 'Tus próximas horas', 'medmovil' ),
			__( 'Tus solicitudes y visitas confirmadas.', 'medmovil' )
		);

		if ( $proximas ) {
			echo '<div class="hlist">';
			foreach ( array_slice( $proximas, 0, 4 ) as $a ) {
				medmovil_tarjeta_hora( $a, 'paciente' );
			}
			echo '</div>';
		} else {
			medmovil_vacio(
				__( 'No tienes horas agendadas.', 'medmovil' ),
				'<a class="pbtn pbtn-p" href="' . esc_url( medmovil_url_reserva() ) . '">' . esc_html__( 'Solicitar un examen', 'medmovil' ) . '</a>',
				'i-caladd'
			);
		}
		medmovil_caja_cerrar();

		medmovil_caja_abrir(
			__( 'Tu historial', 'medmovil' ),
			__( 'Todo lo que te has hecho con nosotros.', 'medmovil' ),
			'<a class="pbtn" href="' . esc_url( medmovil_url_portal( 'horas' ) ) . '">' . esc_html__( 'Ver todo', 'medmovil' ) . '</a>'
		);

		if ( $hechas ) {
			echo '<div class="ptabla-wrap"><table class="ptabla">';
			medmovil_thead_atenciones( 'paciente', true );
			echo '<tbody>';
			foreach ( array_slice( $hechas, 0, 6 ) as $a ) {
				medmovil_fila_atencion( $a, 'paciente', true );
			}
			echo '</tbody></table></div>';
		} else {
			medmovil_vacio( __( 'Todavía no tienes exámenes realizados.', 'medmovil' ), '', 'i-filesearch' );
		}
		medmovil_caja_cerrar();
		?>
	</div>
	<?php
}

/**
 * Tarjeta grande de una hora agendada.
 *
 * @param array  $a   Atención.
 * @param string $rol Quién la mira.
 */
function medmovil_tarjeta_hora( $a, $rol ) {
	$otro = 'paciente' === $rol ? $a['profesional'] : $a['paciente'];
	$u    = $otro ? get_userdata( $otro ) : null;
	$nombres = array();
	foreach ( $a['examenes'] as $e ) {
		$nombres[] = $e['n'];
	}
	?>
	<article class="hcard">
		<div class="hcard-f">
			<b><?php echo esc_html( $a['ts'] ? wp_date( 'j', $a['ts'] ) : '—' ); ?></b>
			<span><?php echo esc_html( $a['ts'] ? medmovil_fecha( 'M', $a['ts'] ) : '' ); ?></span>
		</div>
		<div class="hcard-t">
			<b><?php echo esc_html( implode( ' · ', $nombres ) ); ?></b>
			<span>
				<?php medmovil_icono( 'i-clock' ); ?> <?php echo esc_html( $a['hora'] ); ?>
				<?php if ( $a['comuna'] ) : ?>
					· <?php medmovil_icono( 'i-pin' ); ?> <?php echo esc_html( $a['comuna'] ); ?>
				<?php endif; ?>
			</span>
			<span>
				<?php medmovil_icono( 'i-user' ); ?>
				<?php echo esc_html( $u ? $u->display_name : __( 'Profesional por asignar', 'medmovil' ) ); ?>
			</span>
		</div>
		<div class="hcard-r">
			<?php medmovil_estado_pill( $a['estado'] ); ?>
			<b><?php echo esc_html( medmovil_precio( $a['monto'] ) ); ?></b>
		</div>
	</article>
	<?php
}

/**
 * Listado completo de horas.
 *
 * @param int $uid Usuario.
 */
function medmovil_pac_horas( $uid ) {
	$todas = medmovil_atenciones( array( 'paciente' => $uid, 'limite' => 200 ) );

	medmovil_caja_abrir(
		__( 'Solicitudes y horas', 'medmovil' ),
		__( 'Desde la más reciente. Si algo no calza, escríbenos y lo corregimos.', 'medmovil' ),
		'<a class="pbtn pbtn-p" href="' . esc_url( medmovil_url_reserva() ) . '">' . esc_html__( 'Solicitar otro examen', 'medmovil' ) . '</a>'
	);

	if ( $todas ) {
		echo '<div class="ptabla-wrap"><table class="ptabla">';
		medmovil_thead_atenciones( 'paciente' );
		echo '<tbody>';
		foreach ( $todas as $a ) {
			medmovil_fila_atencion( $a, 'paciente' );
		}
		echo '</tbody></table></div>';
	} else {
		medmovil_vacio(
			__( 'Todavía no has agendado con nosotros.', 'medmovil' ),
			'<a class="pbtn pbtn-p" href="' . esc_url( get_post_type_archive_link( 'mm_prestacion' ) ) . '">' . esc_html__( 'Ver los exámenes', 'medmovil' ) . '</a>',
			'i-caladd'
		);
	}
	medmovil_caja_cerrar();
}

/**
 * Informes descargables.
 *
 * @param int $uid Usuario.
 */
function medmovil_pac_informes( $uid ) {
	$todas = medmovil_atenciones( array( 'paciente' => $uid, 'limite' => 200 ) );
	$con   = array_filter( $todas, function ( $a ) { return ! empty( $a['informe'] ); } );

	medmovil_caja_abrir(
		__( 'Tus informes', 'medmovil' ),
		__( 'Necesitas iniciar sesión para abrirlos desde el portal.', 'medmovil' )
	);

	if ( $con ) {
		echo '<div class="ilist">';
		foreach ( $con as $a ) {
			$nombres = array();
			foreach ( $a['examenes'] as $e ) {
				$nombres[] = $e['n'];
			}
			$nuevo = ! get_user_meta( $uid, '_mm_visto_' . $a['id'], true );
			?>
			<a class="icard" href="<?php echo esc_url( medmovil_url_informe( $a['id'] ) ); ?>" target="_blank" rel="noopener">
				<span class="icard-ic"><?php medmovil_icono( 'i-filecheck' ); ?></span>
				<span class="icard-t">
					<b><?php echo esc_html( implode( ' · ', $nombres ) ); ?>
						<?php if ( $nuevo ) : ?><i class="pill pill-ok"><?php esc_html_e( 'Nuevo', 'medmovil' ); ?></i><?php endif; ?>
					</b>
					<span><?php echo esc_html( $a['fecha'] ? medmovil_fecha( 'j \d\e F, Y', $a['ts'] ) : '' ); ?></span>
				</span>
				<span class="icard-b"><?php medmovil_icono( 'i-dl' ); ?> <?php esc_html_e( 'Descargar', 'medmovil' ); ?></span>
			</a>
			<?php
			update_user_meta( $uid, '_mm_visto_' . $a['id'], 1 );
		}
		echo '</div>';
		?>
		<p class="pn-legal">
			<?php medmovil_icono( 'i-info' ); ?>
			<span><?php esc_html_e( 'El informe es un documento para tu médico: él lo interpreta y te indica qué hacer. Si algo te preocupa, llévaselo y consúltale.', 'medmovil' ); ?></span>
		</p>
		<?php
	} else {
		medmovil_vacio( __( 'Todavía no hay informes cargados en tu cuenta.', 'medmovil' ), '', 'i-filesearch' );
	}
	medmovil_caja_cerrar();
}

/**
 * Ficha clínica.
 *
 * @param int $uid Usuario.
 */
function medmovil_pac_ficha( $uid ) {
	$ficha = medmovil_ficha( $uid );

	// Tras crear la cuenta se llega derecho acá, así que el saludo va acá.
	if ( isset( $_GET['bienvenida'] ) ) {
		?>
		<div class="pn-nota pn-nota-ok">
			<?php medmovil_icono( 'i-ok' ); ?>
			<div>
				<b><?php esc_html_e( 'Tu cuenta quedó creada', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Lo más útil que puedes hacer ahora es completar esta ficha: el profesional la lee antes de salir hacia tu casa, y así llega sabiendo qué se va a encontrar.', 'medmovil' ); ?></p>
			</div>
		</div>
		<?php
	}

	medmovil_caja_abrir(
		__( 'Tu ficha clínica', 'medmovil' ),
		__( 'La lee el profesional antes de salir hacia tu casa. Se completa una vez y se puede editar cuando quieras.', 'medmovil' )
	);
	?>
	<form method="post" class="pform">
		<?php wp_nonce_field( 'mm_portal_ficha', 'mm_nonce' ); ?>
		<input type="hidden" name="mm_accion" value="ficha">

		<?php foreach ( medmovil_campos_ficha() as $k => $campo ) :
			$valor = isset( $ficha[ $k ] ) ? $ficha[ $k ] : '';
			?>
			<div class="pf <?php echo 'textarea' === $campo['t'] ? 'pf-ancho' : ''; ?>">
				<label for="f_<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $campo['l'] ); ?></label>
				<?php if ( 'textarea' === $campo['t'] ) : ?>
					<textarea id="f_<?php echo esc_attr( $k ); ?>" name="f_<?php echo esc_attr( $k ); ?>" rows="3"><?php echo esc_textarea( $valor ); ?></textarea>
				<?php elseif ( 'select' === $campo['t'] ) : ?>
					<select id="f_<?php echo esc_attr( $k ); ?>" name="f_<?php echo esc_attr( $k ); ?>">
						<?php foreach ( $campo['o'] as $ov => $ol ) : ?>
							<option value="<?php echo esc_attr( $ov ); ?>" <?php selected( $valor, $ov ); ?>><?php echo esc_html( $ol ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input type="<?php echo esc_attr( $campo['t'] ); ?>" id="f_<?php echo esc_attr( $k ); ?>"
					       name="f_<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $valor ); ?>">
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<div class="pf-acc">
			<button class="pbtn pbtn-p" type="submit"><?php medmovil_icono( 'i-save' ); ?> <?php esc_html_e( 'Guardar mi ficha', 'medmovil' ); ?></button>
		</div>
	</form>

	<p class="pn-legal">
		<?php medmovil_icono( 'i-lock' ); ?>
		<span><?php esc_html_e( 'Esta información la ve solo el profesional que te va a atender. No se comparte con nadie más ni se usa para publicidad.', 'medmovil' ); ?></span>
	</p>
	<?php
	medmovil_caja_cerrar();
}

/**
 * Datos de la cuenta.
 *
 * @param int $uid Usuario.
 */
function medmovil_pac_perfil( $uid ) {
	$u = get_userdata( $uid );

	medmovil_caja_abrir( __( 'Tus datos', 'medmovil' ), __( 'Con esto sabemos a quién llamar y dónde llegar.', 'medmovil' ) );
	?>
	<form method="post" class="pform">
		<?php wp_nonce_field( 'mm_portal_perfil', 'mm_nonce' ); ?>
		<input type="hidden" name="mm_accion" value="perfil">

		<div class="pf">
			<label for="nombre"><?php esc_html_e( 'Nombre', 'medmovil' ); ?></label>
			<input type="text" id="nombre" name="nombre" value="<?php echo esc_attr( $u->first_name ); ?>">
		</div>
		<div class="pf">
			<label for="apellido"><?php esc_html_e( 'Apellido', 'medmovil' ); ?></label>
			<input type="text" id="apellido" name="apellido" value="<?php echo esc_attr( $u->last_name ); ?>">
		</div>
		<div class="pf">
			<label for="fono"><?php esc_html_e( 'Teléfono', 'medmovil' ); ?></label>
			<input type="tel" id="fono" name="fono" value="<?php echo esc_attr( get_user_meta( $uid, '_mm_fono', true ) ); ?>">
		</div>
		<div class="pf">
			<label for="comuna"><?php esc_html_e( 'Comuna', 'medmovil' ); ?></label>
			<select id="comuna" name="comuna">
				<option value=""><?php esc_html_e( 'Elige tu comuna', 'medmovil' ); ?></option>
				<?php
				$actual = get_user_meta( $uid, '_mm_comuna', true );
				foreach ( medmovil_comunas( 60 ) as $c ) :
					?>
					<option value="<?php echo esc_attr( $c->post_title ); ?>" <?php selected( $actual, $c->post_title ); ?>>
						<?php echo esc_html( $c->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="pf pf-ancho">
			<label for="direccion"><?php esc_html_e( 'Dirección', 'medmovil' ); ?></label>
			<input type="text" id="direccion" name="direccion" value="<?php echo esc_attr( get_user_meta( $uid, '_mm_direccion', true ) ); ?>">
		</div>
		<div class="pf pf-ancho">
			<label><?php esc_html_e( 'Correo', 'medmovil' ); ?></label>
			<input type="email" value="<?php echo esc_attr( $u->user_email ); ?>" disabled>
			<span class="pf-ayuda"><?php esc_html_e( 'Para cambiar el correo, escríbenos: es el que identifica tu cuenta y tus informes.', 'medmovil' ); ?></span>
		</div>

		<div class="pf-acc">
			<button class="pbtn pbtn-p" type="submit"><?php medmovil_icono( 'i-save' ); ?> <?php esc_html_e( 'Guardar', 'medmovil' ); ?></button>
			<a class="pbtn" href="<?php echo esc_url( wp_lostpassword_url( medmovil_url_portal() ) ); ?>"><?php esc_html_e( 'Cambiar contraseña', 'medmovil' ); ?></a>
		</div>
	</form>
	<?php
	medmovil_caja_cerrar();
}

/** Historial de importes realmente registrados; una solicitud no cuenta como pago. */
function medmovil_pac_pagos( $uid ) {
	$lista = medmovil_atenciones( array( 'paciente' => $uid, 'limite' => 200 ) );
	$pagadas = array_filter( $lista, function ( $a ) { return $a['pagado'] > 0 && in_array( $a['estado'], array( 'realizada', 'informada' ), true ); } );
	$total = array_sum( array_column( $pagadas, 'pagado' ) );
	medmovil_caja_abrir( __( 'Historial de pagos', 'medmovil' ), __( 'Importes registrados por el equipo después de cada visita. Esto no reemplaza la boleta o factura.', 'medmovil' ) );
	if ( $pagadas ) {
		?><p class="pn-nota"><strong><?php esc_html_e( 'Total registrado:', 'medmovil' ); ?> <?php echo esc_html( medmovil_precio( $total ) ); ?></strong></p>
		<div class="ptabla-wrap"><table class="ptabla"><thead><tr><th><?php esc_html_e( 'Fecha', 'medmovil' ); ?></th><th><?php esc_html_e( 'Examen', 'medmovil' ); ?></th><th><?php esc_html_e( 'Medio', 'medmovil' ); ?></th><th><?php esc_html_e( 'Registrado', 'medmovil' ); ?></th></tr></thead><tbody>
		<?php foreach ( $pagadas as $a ) : ?><tr>
			<td data-th="<?php esc_attr_e( 'Fecha', 'medmovil' ); ?>"><?php echo esc_html( $a['fecha'] ? medmovil_fecha( 'j M Y', $a['ts'] ) : '—' ); ?></td>
			<td data-th="<?php esc_attr_e( 'Examen', 'medmovil' ); ?>"><?php echo esc_html( implode( ' · ', wp_list_pluck( $a['examenes'], 'n' ) ) ); ?></td>
			<td data-th="<?php esc_attr_e( 'Medio', 'medmovil' ); ?>"><?php echo esc_html( $a['medio'] ? ucfirst( $a['medio'] ) : __( 'Sin especificar', 'medmovil' ) ); ?></td>
			<td data-th="<?php esc_attr_e( 'Registrado', 'medmovil' ); ?>"><strong><?php echo esc_html( medmovil_precio( $a['pagado'] ) ); ?></strong></td>
		</tr><?php endforeach; ?>
		</tbody></table></div><?php
	} else {
		medmovil_vacio( __( 'Todavía no hay pagos registrados. Las solicitudes de hora no generan un cobro.', 'medmovil' ), '', 'i-coin' );
	}
	medmovil_caja_cerrar();
}
