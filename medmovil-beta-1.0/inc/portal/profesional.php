<?php
/**
 * Portal del profesional.
 *
 * El tecnólogo entra a esto entre visita y visita, muchas veces desde el
 * teléfono y en la calle. Prioridad: qué me toca hoy, dónde queda, qué tiene el
 * paciente, y subir el informe rápido.
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
function medmovil_portal_profesional( $seccion ) {
	$uid = get_current_user_id();

	switch ( $seccion ) {
		case 'agenda':
			medmovil_pro_agenda( $uid );
			break;
		case 'horarios':
			medmovil_pro_horarios( $uid );
			break;
		case 'pacientes':
			medmovil_pro_pacientes( $uid );
			break;
		case 'informes':
			medmovil_pro_informes( $uid );
			break;
		case 'ingresos':
			medmovil_pro_ingresos( $uid );
			break;
		case 'perfil':
			medmovil_pro_perfil( $uid );
			break;
		default:
			medmovil_pro_resumen( $uid );
	}
}

/**
 * Resumen del profesional.
 *
 * @param int $uid Usuario.
 */
function medmovil_pro_resumen( $uid ) {
	$hoy      = wp_date( 'Y-m-d' );
	$mes      = wp_date( 'Y-m-01' );
	$del_mes  = medmovil_atenciones( array( 'profesional' => $uid, 'desde' => $mes, 'limite' => 200 ) );
	$de_hoy   = medmovil_atenciones( array( 'profesional' => $uid, 'desde' => $hoy, 'hasta' => $hoy, 'orden' => 'ASC' ) );
	$semana   = medmovil_atenciones( array( 'profesional' => $uid, 'desde' => wp_date( 'Y-m-d', strtotime( 'monday this week' ) ), 'hasta' => wp_date( 'Y-m-d', strtotime( 'sunday this week' ) ) ) );
	$t        = medmovil_totales( $del_mes );

	$pacientes = array();
	$pendientes = 0;
	foreach ( medmovil_atenciones( array( 'profesional' => $uid, 'limite' => 400 ) ) as $a ) {
		if ( $a['paciente'] ) {
			$pacientes[ $a['paciente'] ] = 1;
		}
		if ( 'realizada' === $a['estado'] && ! $a['informe'] ) {
			$pendientes++;
		}
	}

	$ver_ingresos = 'no' !== medmovil_portal_opt( 'ver_ingresos', 'si' );
	$ver_comision = 'no' !== medmovil_portal_opt( 'ver_comision', 'si' );
	?>
	<?php medmovil_kpis_abrir(); ?>
		<?php
		if ( medmovil_kpi_visible( 'profesional', 'hoy' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-caldays',
				'etiqueta' => __( 'Visitas de hoy', 'medmovil' ),
				'valor'    => count( $de_hoy ),
				'nota'     => $de_hoy ? sprintf( __( 'la primera a las %s', 'medmovil' ), $de_hoy[0]['hora'] ) : __( 'día libre', 'medmovil' ),
				'url'      => medmovil_url_portal( 'agenda' ),
			) );
		}
		if ( medmovil_kpi_visible( 'profesional', 'semana' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-calcheck',
				'etiqueta' => __( 'Esta semana', 'medmovil' ),
				'valor'    => count( $semana ),
				'nota'     => __( 'visitas agendadas', 'medmovil' ),
				'color'    => 'var(--c-dop)',
			) );
		}
		if ( medmovil_kpi_visible( 'profesional', 'pacientes' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-users',
				'etiqueta' => __( 'Pacientes atendidos', 'medmovil' ),
				'valor'    => count( $pacientes ),
				'nota'     => __( 'en total', 'medmovil' ),
				'color'    => 'var(--c-msk)',
				'url'      => medmovil_url_portal( 'pacientes' ),
			) );
		}
		if ( $ver_ingresos && medmovil_kpi_visible( 'profesional', 'ingresos' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-coin',
				'etiqueta' => __( 'Facturado este mes', 'medmovil' ),
				'valor'    => medmovil_precio( $t['monto'] ),
				'nota'     => sprintf( __( '%d atenciones realizadas', 'medmovil' ), $t['realizadas'] ),
				'color'    => 'var(--ok)',
				'url'      => medmovil_url_portal( 'ingresos' ),
			) );
		}
		if ( $ver_comision && medmovil_kpi_visible( 'profesional', 'comision' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-tag',
				'etiqueta' => __( 'Comisión de plataforma', 'medmovil' ),
				'valor'    => medmovil_precio( $t['comision'] ),
				'nota'     => sprintf( __( '%s%% del mes', 'medmovil' ), medmovil_comision_pct() ),
				'color'    => 'var(--c-lab)',
			) );
		}
		if ( medmovil_kpi_visible( 'profesional', 'pendiente' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-cloudup',
				'etiqueta' => __( 'Informes pendientes', 'medmovil' ),
				'valor'    => $pendientes,
				'nota'     => $pendientes ? __( 'súbelos hoy', 'medmovil' ) : __( 'todo al día', 'medmovil' ),
				'color'    => $pendientes ? 'var(--warn)' : 'var(--ok)',
				'url'      => medmovil_url_portal( 'informes' ),
			) );
		}
		?>
	<?php medmovil_kpis_cerrar(); ?>

	<div class="pgrid pgrid-2-1">
		<?php
		medmovil_caja_abrir( __( 'Tu día', 'medmovil' ), ucfirst( medmovil_fecha( 'l j \d\e F' ) ) );
		if ( $de_hoy ) {
			echo '<div class="hlist">';
			foreach ( $de_hoy as $a ) {
				medmovil_tarjeta_visita( $a );
			}
			echo '</div>';
		} else {
			medmovil_vacio( __( 'Hoy no tienes visitas agendadas.', 'medmovil' ), '', 'i-timer' );
		}
		medmovil_caja_cerrar();

		medmovil_caja_abrir( __( 'Tu mes', 'medmovil' ), __( 'Atenciones realizadas por semana.', 'medmovil' ) );
		medmovil_barras( medmovil_serie_semanas( $del_mes ), 'n', 'var(--blue)' );
		medmovil_caja_cerrar();
		?>
	</div>
	<?php
}

/**
 * Reparte una lista de atenciones en las semanas del mes.
 *
 * @param array $lista Atenciones.
 * @return array
 */
function medmovil_serie_semanas( $lista ) {
	$serie = array();
	for ( $i = 1; $i <= 5; $i++ ) {
		$serie[ 'S' . $i ] = 0;
	}
	foreach ( $lista as $a ) {
		if ( ! $a['ts'] || ! in_array( $a['estado'], array( 'realizada', 'informada' ), true ) ) {
			continue;
		}
		$semana = min( 5, (int) ceil( (int) wp_date( 'j', $a['ts'] ) / 7 ) );
		$serie[ 'S' . $semana ]++;
	}

	return $serie;
}

/**
 * Tarjeta de visita con lo que el profesional necesita para salir.
 *
 * @param array $a Atención.
 */
function medmovil_tarjeta_visita( $a ) {
	$p     = $a['paciente'] ? get_userdata( $a['paciente'] ) : null;
	$ficha = $a['paciente'] ? medmovil_ficha( $a['paciente'] ) : array();
	$nombres = array();
	foreach ( $a['examenes'] as $e ) {
		$nombres[] = $e['n'];
	}
	$movilidad = array(
		'camina'   => __( 'Camina sin ayuda', 'medmovil' ),
		'apoyo'    => __( 'Camina con apoyo', 'medmovil' ),
		'silla'    => __( 'Silla de ruedas', 'medmovil' ),
		'postrado' => __( 'Postrado', 'medmovil' ),
	);
	?>
	<details class="vcard">
		<summary>
			<span class="vcard-h"><?php echo esc_html( $a['hora'] ); ?></span>
			<span class="vcard-t">
				<b><?php echo esc_html( $p ? $p->display_name : __( 'Paciente', 'medmovil' ) ); ?></b>
				<span><?php echo esc_html( implode( ' · ', $nombres ) ); ?></span>
			</span>
			<span class="vcard-r">
				<?php medmovil_estado_pill( $a['estado'] ); ?>
				<?php medmovil_icono( 'i-chev' ); ?>
			</span>
		</summary>

		<div class="vcard-b">
			<div class="vcard-datos">
				<div><span><?php esc_html_e( 'Dirección', 'medmovil' ); ?></span>
					<b><?php echo esc_html( $a['direccion'] ? $a['direccion'] : __( 'Sin dirección cargada', 'medmovil' ) ); ?></b>
					<?php if ( $a['comuna'] ) : ?><b><?php echo esc_html( $a['comuna'] ); ?></b><?php endif; ?>
				</div>
				<div><span><?php esc_html_e( 'Contacto', 'medmovil' ); ?></span>
					<b><?php echo esc_html( ! empty( $ficha['contacto'] ) ? $ficha['contacto'] : ( $a['paciente'] ? get_user_meta( $a['paciente'], '_mm_fono', true ) : '—' ) ); ?></b>
				</div>
				<div><span><?php esc_html_e( 'Movilidad', 'medmovil' ); ?></span>
					<b><?php echo esc_html( ! empty( $ficha['movilidad'] ) && isset( $movilidad[ $ficha['movilidad'] ] ) ? $movilidad[ $ficha['movilidad'] ] : __( 'Sin dato', 'medmovil' ) ); ?></b>
				</div>
				<div><span><?php esc_html_e( 'Monto a cobrar', 'medmovil' ); ?></span>
					<b><?php echo esc_html( medmovil_precio( $a['monto'] ) ); ?></b>
				</div>
			</div>

			<?php if ( ! empty( $ficha['acceso'] ) ) : ?>
				<p class="vcard-n"><b><?php esc_html_e( 'Cómo llegar:', 'medmovil' ); ?></b> <?php echo esc_html( $ficha['acceso'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $ficha['antecedentes'] ) ) : ?>
				<p class="vcard-n"><b><?php esc_html_e( 'Antecedentes:', 'medmovil' ); ?></b> <?php echo esc_html( $ficha['antecedentes'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $ficha['medicamentos'] ) ) : ?>
				<p class="vcard-n"><b><?php esc_html_e( 'Medicamentos:', 'medmovil' ); ?></b> <?php echo esc_html( $ficha['medicamentos'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $ficha['alergias'] ) ) : ?>
				<p class="vcard-n vcard-alerta"><b><?php esc_html_e( 'Alergias:', 'medmovil' ); ?></b> <?php echo esc_html( $ficha['alergias'] ); ?></p>
			<?php endif; ?>

			<form method="post" class="vcard-form">
				<?php wp_nonce_field( 'mm_portal_estado', 'mm_nonce' ); ?>
				<input type="hidden" name="mm_accion" value="estado">
				<input type="hidden" name="atencion" value="<?php echo (int) $a['id']; ?>">
				<input type="hidden" name="volver" value="agenda">

				<label>
					<span><?php esc_html_e( 'Estado', 'medmovil' ); ?></span>
					<select name="estado">
						<?php foreach ( medmovil_estados() as $k => $e ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $a['estado'], $k ); ?>><?php echo esc_html( $e['n'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					<span><?php esc_html_e( 'Cobrado', 'medmovil' ); ?></span>
					<input type="number" name="pagado" value="<?php echo esc_attr( $a['pagado'] ); ?>" min="0" max="<?php echo (int) $a['monto']; ?>" step="1000">
				</label>
				<label>
					<span><?php esc_html_e( 'Medio de pago', 'medmovil' ); ?></span>
					<select name="medio">
						<?php foreach ( array( '' => __( 'Sin especificar', 'medmovil' ), 'efectivo' => __( 'Efectivo', 'medmovil' ), 'transferencia' => __( 'Transferencia', 'medmovil' ), 'tarjeta' => __( 'Tarjeta', 'medmovil' ) ) as $k => $v ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $a['medio'], $k ); ?>><?php echo esc_html( $v ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="pf-ancho">
					<span><?php esc_html_e( 'Notas de la atención', 'medmovil' ); ?></span>
					<textarea name="notas" rows="2" placeholder="<?php esc_attr_e( 'Lo que quieras dejar registrado para la próxima visita.', 'medmovil' ); ?>"><?php echo esc_textarea( $a['notas'] ); ?></textarea>
				</label>

				<button class="pbtn pbtn-p" type="submit"><?php medmovil_icono( 'i-save' ); ?> <?php esc_html_e( 'Guardar', 'medmovil' ); ?></button>
			</form>
		</div>
	</details>
	<?php
}

/**
 * Agenda completa.
 *
 * @param int $uid Usuario.
 */
function medmovil_pro_agenda( $uid ) {
	$desde = isset( $_GET['desde'] ) ? sanitize_text_field( wp_unslash( $_GET['desde'] ) ) : wp_date( 'Y-m-d' );
	$hasta = wp_date( 'Y-m-d', strtotime( $desde . ' +13 days' ) );
	$lista = medmovil_atenciones( array( 'profesional' => $uid, 'desde' => $desde, 'hasta' => $hasta, 'orden' => 'ASC', 'limite' => 200 ) );

	$por_dia = array();
	foreach ( $lista as $a ) {
		$por_dia[ $a['fecha'] ][] = $a;
	}

	medmovil_caja_abrir(
		__( 'Tu agenda', 'medmovil' ),
		sprintf( __( 'Del %1$s al %2$s', 'medmovil' ), medmovil_fecha( 'j M', strtotime( $desde ) ), medmovil_fecha( 'j M', strtotime( $hasta ) ) ),
		'<a class="pbtn" href="' . esc_url( medmovil_url_portal( 'agenda', array( 'desde' => wp_date( 'Y-m-d', strtotime( $desde . ' -14 days' ) ) ) ) ) . '">' . esc_html__( 'Antes', 'medmovil' ) . '</a>
		 <a class="pbtn" href="' . esc_url( medmovil_url_portal( 'agenda', array( 'desde' => wp_date( 'Y-m-d', strtotime( $desde . ' +14 days' ) ) ) ) ) . '">' . esc_html__( 'Después', 'medmovil' ) . '</a>'
	);

	if ( $por_dia ) {
		foreach ( $por_dia as $fecha => $dia ) {
			printf(
				'<h3 class="pn-dia">%s <i>%s</i></h3>',
				esc_html( ucfirst( medmovil_fecha( 'l j \d\e F', strtotime( $fecha ) ) ) ),
				esc_html( sprintf( _n( '%d visita', '%d visitas', count( $dia ), 'medmovil' ), count( $dia ) ) )
			);
			echo '<div class="hlist">';
			foreach ( $dia as $a ) {
				medmovil_tarjeta_visita( $a );
			}
			echo '</div>';
		}
	} else {
		medmovil_vacio( __( 'No tienes visitas en estas dos semanas.', 'medmovil' ), '', 'i-timer' );
	}
	medmovil_caja_cerrar();
}

/**
 * Disponibilidad semanal.
 *
 * @param int $uid Usuario.
 */
function medmovil_pro_horarios( $uid ) {
	medmovil_render_availability( $uid );
}

/**
 * Pacientes atendidos.
 *
 * @param int $uid Usuario.
 */
function medmovil_pro_pacientes( $uid ) {
	$lista = medmovil_atenciones( array( 'profesional' => $uid, 'limite' => 400 ) );
	$por_p = array();

	foreach ( $lista as $a ) {
		if ( ! $a['paciente'] ) {
			continue;
		}
		if ( ! isset( $por_p[ $a['paciente'] ] ) ) {
			$por_p[ $a['paciente'] ] = array( 'n' => 0, 'monto' => 0, 'ultima' => 0, 'atenciones' => array() );
		}
		$por_p[ $a['paciente'] ]['n']++;
		$por_p[ $a['paciente'] ]['monto'] += $a['monto'];
		$por_p[ $a['paciente'] ]['ultima'] = max( $por_p[ $a['paciente'] ]['ultima'], $a['ts'] );
		$por_p[ $a['paciente'] ]['atenciones'][] = $a;
	}

	medmovil_caja_abrir( __( 'Pacientes asignados', 'medmovil' ), __( 'Solicitudes confirmadas, visitas e historial de tus atenciones.', 'medmovil' ) );

	if ( $por_p ) {
		echo '<div class="ptabla-wrap"><table class="ptabla"><thead><tr>';
		printf( '<th>%s</th><th>%s</th><th>%s</th><th class="td-num">%s</th><th class="td-num">%s</th><th></th></tr></thead><tbody>',
			esc_html__( 'Paciente', 'medmovil' ),
			esc_html__( 'Comuna', 'medmovil' ),
			esc_html__( 'Última atención', 'medmovil' ),
			esc_html__( 'Visitas', 'medmovil' ),
			esc_html__( 'Valor de visitas', 'medmovil' )
		);

		foreach ( $por_p as $pid => $d ) {
			$u = get_userdata( $pid );
			if ( ! $u ) {
				continue;
			}
			$ficha = medmovil_ficha( $pid );
			?>
			<tr>
				<td data-th="<?php esc_attr_e( 'Paciente', 'medmovil' ); ?>">
					<b><?php echo esc_html( $u->display_name ); ?></b>
					<span class="td-sub"><?php echo esc_html( get_user_meta( $pid, '_mm_fono', true ) ); ?></span>
				</td>
				<td data-th="<?php esc_attr_e( 'Comuna', 'medmovil' ); ?>"><?php echo esc_html( get_user_meta( $pid, '_mm_comuna', true ) ); ?></td>
				<td data-th="<?php esc_attr_e( 'Última', 'medmovil' ); ?>"><?php echo esc_html( $d['ultima'] ? medmovil_fecha( 'j M Y', $d['ultima'] ) : '—' ); ?></td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Visitas', 'medmovil' ); ?>"><?php echo (int) $d['n']; ?></td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Total', 'medmovil' ); ?>"><?php echo esc_html( medmovil_precio( $d['monto'] ) ); ?></td>
				<td class="td-acc">
					<?php if ( ! empty( $ficha['alergias'] ) ) : ?>
						<span class="pill pill-warn" title="<?php echo esc_attr( $ficha['alergias'] ); ?>"><?php esc_html_e( 'Alergias', 'medmovil' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}
		echo '</tbody></table></div>';
	} else {
		medmovil_vacio( __( 'Todavía no has atendido a nadie con la plataforma.', 'medmovil' ), '', 'i-users' );
	}
	medmovil_caja_cerrar();
}

/**
 * Subida de informes.
 *
 * @param int $uid Usuario.
 */
function medmovil_pro_informes( $uid ) {
	$lista      = medmovil_atenciones( array( 'profesional' => $uid, 'estado' => array( 'realizada', 'informada' ), 'limite' => 100 ) );
	$pendientes = array_filter( $lista, function ( $a ) { return empty( $a['informe'] ); } );
	$listos     = array_filter( $lista, function ( $a ) { return ! empty( $a['informe'] ); } );

	medmovil_caja_abrir(
		__( 'Informes por subir', 'medmovil' ),
		__( 'Al subirlo, el paciente recibe un correo y lo ve al instante en su cuenta.', 'medmovil' )
	);

	if ( $pendientes ) {
		echo '<div class="hlist">';
		foreach ( $pendientes as $a ) {
			$p = $a['paciente'] ? get_userdata( $a['paciente'] ) : null;
			$nombres = array();
			foreach ( $a['examenes'] as $e ) {
				$nombres[] = $e['n'];
			}
			?>
			<form class="ucard" method="post" enctype="multipart/form-data"
			      data-paciente="<?php echo esc_attr( $p ? $p->display_name : __( 'este paciente', 'medmovil' ) ); ?>">
				<?php wp_nonce_field( 'mm_portal_informe', 'mm_nonce' ); ?>
				<input type="hidden" name="mm_accion" value="informe">
				<input type="hidden" name="atencion" value="<?php echo (int) $a['id']; ?>">

				<div class="ucard-t">
					<b><?php echo esc_html( implode( ' · ', $nombres ) ); ?></b>
					<span>
						<?php echo esc_html( $p ? $p->display_name : '' ); ?> ·
						<?php echo esc_html( $a['fecha'] ? medmovil_fecha( 'j M Y', $a['ts'] ) : '' ); ?>
					</span>
				</div>
				<label class="mp-upload"><span><?php medmovil_icono('i-cloudup'); ?> Seleccionar informe</span><input type="file" name="archivo" accept=".pdf,.jpg,.jpeg,.png" required aria-label="Seleccionar informe"><small>PDF, JPG o PNG</small></label>
				<button class="pbtn pbtn-p" type="submit"><?php medmovil_icono( 'i-cloudup' ); ?> <?php esc_html_e( 'Subir', 'medmovil' ); ?></button>
			</form>
			<?php
		}
		echo '</div>';
	} else {
		medmovil_vacio( __( 'No tienes informes pendientes. Bien ahí.', 'medmovil' ), '', 'i-ok' );
	}
	medmovil_caja_cerrar();

	medmovil_caja_abrir( __( 'Informes ya entregados', 'medmovil' ), '' );
	if ( $listos ) {
		echo '<div class="ptabla-wrap"><table class="ptabla">';
		medmovil_thead_atenciones( 'profesional' );
		echo '<tbody>';
		foreach ( array_slice( $listos, 0, 25 ) as $a ) {
			medmovil_fila_atencion( $a, 'profesional' );
		}
		echo '</tbody></table></div>';
	} else {
		medmovil_vacio( __( 'Todavía no has entregado informes.', 'medmovil' ), '', 'i-filesearch' );
	}
	medmovil_caja_cerrar();
}

/**
 * Ingresos del profesional.
 *
 * @param int $uid Usuario.
 */
function medmovil_pro_ingresos( $uid ) {
	if ( 'no' === medmovil_portal_opt( 'ver_ingresos', 'si' ) ) {
		medmovil_vacio( __( 'Esta sección está desactivada.', 'medmovil' ), '', 'i-lock' );
		return;
	}

	$meses = array();
	for ( $i = 5; $i >= 0; $i-- ) {
		$ini = wp_date( 'Y-m-01', strtotime( "-$i months" ) );
		$fin = wp_date( 'Y-m-t', strtotime( "-$i months" ) );
		$t   = medmovil_totales( medmovil_atenciones( array( 'profesional' => $uid, 'desde' => $ini, 'hasta' => $fin, 'limite' => 300 ) ) );
		$meses[ medmovil_fecha( 'M', strtotime( $ini ) ) ] = $t['monto'];
	}

	$mes = medmovil_totales( medmovil_atenciones( array( 'profesional' => $uid, 'desde' => wp_date( 'Y-m-01' ), 'limite' => 300 ) ) );
	$ver_comision = 'no' !== medmovil_portal_opt( 'ver_comision', 'si' );
	?>
	<?php medmovil_kpis_abrir(); ?>
		<?php
		medmovil_kpi( array( 'icono' => 'i-coin', 'etiqueta' => __( 'Facturado este mes', 'medmovil' ), 'valor' => medmovil_precio( $mes['monto'] ), 'nota' => sprintf( __( '%d atenciones', 'medmovil' ), $mes['realizadas'] ), 'color' => 'var(--ok)' ) );
		medmovil_kpi( array( 'icono' => 'i-checklist', 'etiqueta' => __( 'Cobrado', 'medmovil' ), 'valor' => medmovil_precio( $mes['pagado'] ), 'nota' => __( 'registrado por ti', 'medmovil' ) ) );
		if ( $ver_comision ) {
			medmovil_kpi( array( 'icono' => 'i-tag', 'etiqueta' => __( 'Comisión de plataforma', 'medmovil' ), 'valor' => medmovil_precio( $mes['comision'] ), 'nota' => sprintf( __( '%s%% de lo realizado', 'medmovil' ), medmovil_comision_pct() ), 'color' => 'var(--c-lab)' ) );
			medmovil_kpi( array( 'icono' => 'i-trend', 'etiqueta' => __( 'Te queda', 'medmovil' ), 'valor' => medmovil_precio( $mes['monto'] - $mes['comision'] ), 'nota' => __( 'después de la comisión', 'medmovil' ), 'color' => 'var(--blue)' ) );
		}
		?>
	<?php medmovil_kpis_cerrar(); ?>

	<?php
	medmovil_caja_abrir( __( 'Últimos seis meses', 'medmovil' ), __( 'Monto de las atenciones realizadas.', 'medmovil' ) );
	medmovil_barras( $meses, 'pesos', 'var(--ok)' );
	medmovil_caja_cerrar();

	medmovil_caja_abrir( __( 'Detalle del mes', 'medmovil' ), '' );
	$lista = medmovil_atenciones( array( 'profesional' => $uid, 'desde' => wp_date( 'Y-m-01' ), 'limite' => 200 ) );
	if ( $lista ) {
		echo '<div class="ptabla-wrap"><table class="ptabla">';
		medmovil_thead_atenciones( 'profesional' );
		echo '<tbody>';
		foreach ( $lista as $a ) {
			medmovil_fila_atencion( $a, 'profesional' );
		}
		echo '</tbody></table></div>';
	} else {
		medmovil_vacio( __( 'Este mes todavía no tiene atenciones.', 'medmovil' ), '', 'i-caldays' );
	}
	medmovil_caja_cerrar();
}

/**
 * Perfil público del profesional.
 *
 * @param int $uid Usuario.
 */
function medmovil_pro_perfil( $uid ) {
	$u = get_userdata( $uid );

	medmovil_caja_abrir(
		__( 'Tu perfil público', 'medmovil' ),
		__( 'Esto es lo que ve el paciente en la página de su comuna antes de pedirte.', 'medmovil' )
	);
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
			<label for="rol_prof"><?php esc_html_e( 'Profesión', 'medmovil' ); ?></label>
			<input type="text" id="rol_prof" name="rol_prof" placeholder="<?php esc_attr_e( 'Tecnólogo Médico', 'medmovil' ); ?>"
			       value="<?php echo esc_attr( get_user_meta( $uid, '_mm_rol_prof', true ) ); ?>">
		</div>
		<div class="pf">
			<label for="registro"><?php esc_html_e( 'Registro Superintendencia', 'medmovil' ); ?></label>
			<input type="text" id="registro" name="registro" value="<?php echo esc_attr( get_user_meta( $uid, '_mm_registro', true ) ); ?>">
		</div>
		<div class="pf">
			<label for="fono"><?php esc_html_e( 'Teléfono', 'medmovil' ); ?></label>
			<input type="tel" id="fono" name="fono" value="<?php echo esc_attr( get_user_meta( $uid, '_mm_fono', true ) ); ?>">
		</div>
		<div class="pf pf-ancho">
			<label for="bio"><?php esc_html_e( 'Cómo te presentas', 'medmovil' ); ?></label>
			<textarea id="bio" name="bio" rows="3" placeholder="<?php esc_attr_e( 'Dos o tres líneas en primera persona. Es lo que decide si te piden a ti.', 'medmovil' ); ?>"><?php echo esc_textarea( get_user_meta( $uid, '_mm_bio', true ) ); ?></textarea>
		</div>
<div class="pf pf-ancho"><label>Comunas de atención</label><p><?php echo esc_html( get_user_meta( $uid, '_mm_comunas', true ) ?: 'Sin cobertura configurada' ); ?></p><?php if (isset(medmovil_secciones('profesional')['horarios'])) : ?><a class="pbtn" href="<?php echo esc_url(medmovil_url_portal('horarios').'#mp-zonas'); ?>">Editar disponibilidad y zonas</a><?php endif; ?></div>

		<div class="pf-acc">
			<button class="pbtn pbtn-p" type="submit"><?php medmovil_icono( 'i-save' ); ?> <?php esc_html_e( 'Guardar mi perfil', 'medmovil' ); ?></button>
		</div>
	</form>
	<?php
	medmovil_caja_cerrar();
}
