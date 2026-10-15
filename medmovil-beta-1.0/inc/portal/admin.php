<?php
/**
 * Panel de control del dueño.
 *
 * Responde las preguntas que Sebastián necesita todos los días: cuánto se
 * agendó, cuánto se realizó, cuánto se canceló, cuánta comisión le corresponde y
 * de qué comunas viene la demanda para saber dónde poner el presupuesto de
 * Google Ads.
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
function medmovil_portal_admin( $seccion ) {
	switch ( $seccion ) {
		case 'atenciones':
			medmovil_adm_atenciones();
			break;
		case 'comisiones':
			medmovil_adm_comisiones();
			break;
		case 'equipo':
			medmovil_adm_equipo();
			break;
		case 'demanda':
			medmovil_adm_demanda();
			break;
		default:
			medmovil_adm_resumen();
	}
}

/**
 * Rango del mes en curso, o el que venga por la URL.
 *
 * @return array [desde, hasta, etiqueta]
 */
function medmovil_rango() {
	$mes = isset( $_GET['mes'] ) ? sanitize_text_field( wp_unslash( $_GET['mes'] ) ) : wp_date( 'Y-m' );
	if ( ! preg_match( '/^\d{4}-\d{2}$/', $mes ) ) {
		$mes = wp_date( 'Y-m' );
	}
	$ts = strtotime( $mes . '-01' );

	return array( wp_date( 'Y-m-01', $ts ), wp_date( 'Y-m-t', $ts ), medmovil_fecha( 'F \d\e Y', $ts ), $mes );
}

/**
 * Selector de mes.
 */
function medmovil_selector_mes() {
	list( , , , $mes ) = medmovil_rango();
	$ts    = strtotime( $mes . '-01' );
	$antes = wp_date( 'Y-m', strtotime( '-1 month', $ts ) );
	$luego = wp_date( 'Y-m', strtotime( '+1 month', $ts ) );

	return '<a class="pbtn" href="' . esc_url( medmovil_url_portal( isset( $_GET['p'] ) ? sanitize_key( wp_unslash( $_GET['p'] ) ) : 'resumen', array( 'mes' => $antes ) ) ) . '">' . esc_html__( 'Mes anterior', 'medmovil' ) . '</a>
	        <a class="pbtn" href="' . esc_url( medmovil_url_portal( isset( $_GET['p'] ) ? sanitize_key( wp_unslash( $_GET['p'] ) ) : 'resumen', array( 'mes' => $luego ) ) ) . '">' . esc_html__( 'Mes siguiente', 'medmovil' ) . '</a>';
}

/**
 * Resumen general.
 */
function medmovil_adm_resumen() {
	list( $desde, $hasta, $etiqueta ) = medmovil_rango();

	$lista = medmovil_atenciones( array( 'desde' => $desde, 'hasta' => $hasta, 'limite' => 500 ) );
	$t     = medmovil_totales( $lista );

	// Mes anterior, para las variaciones.
	$ant   = medmovil_atenciones( array(
		'desde'  => wp_date( 'Y-m-01', strtotime( $desde . ' -1 month' ) ),
		'hasta'  => wp_date( 'Y-m-t', strtotime( $desde . ' -1 month' ) ),
		'limite' => 500,
	) );
	$ta = medmovil_totales( $ant );
	$solicitudes = medmovil_atenciones( array( 'estado' => 'solicitada', 'limite' => 100 ) );
	if ( $solicitudes ) {
		printf( '<div class="pn-nota pn-nota-warn"><div><b>%1$s</b><p>%2$s</p><a class="pbtn pbtn-p" href="%3$s">%4$s</a></div></div>',
			esc_html( sprintf( _n( '%d solicitud pendiente', '%d solicitudes pendientes', count( $solicitudes ), 'medmovil' ), count( $solicitudes ) ) ),
			esc_html__( 'Revisa cobertura y credenciales, asigna al operador y confirma la hora desde Atenciones.', 'medmovil' ),
			esc_url( medmovil_url_portal( 'atenciones', array( 'estado' => 'solicitada' ) ) ),
			esc_html__( 'Revisar solicitudes', 'medmovil' )
		);
	}

	$delta = function ( $hoy, $antes ) {
		if ( ! $antes ) {
			return null;
		}
		return (int) round( ( $hoy - $antes ) / $antes * 100 );
	};

	$pacientes = array();
	foreach ( $lista as $a ) {
		if ( $a['paciente'] ) {
			$pacientes[ $a['paciente'] ] = 1;
		}
	}
	$ticket = $t['realizadas'] ? round( $t['monto'] / $t['realizadas'] ) : 0;
	?>
	<div class="pn-rango">
		<b><?php echo esc_html( ucfirst( $etiqueta ) ); ?></b>
		<?php echo wp_kses_post( medmovil_selector_mes() ); ?>
	</div>

	<?php medmovil_kpis_abrir(); ?>
		<?php
		if ( medmovil_kpi_visible( 'admin', 'ventas' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-coin',
				'etiqueta' => __( 'Monto realizado', 'medmovil' ),
				'valor'    => medmovil_precio( $t['monto'] ),
				'delta'    => $delta( $t['monto'], $ta['monto'] ),
				'nota'     => __( 'vs. mes anterior', 'medmovil' ),
				'color'    => 'var(--ok)',
			) );
		}
		if ( medmovil_kpi_visible( 'admin', 'comision' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-tag',
				'etiqueta' => __( 'Tu comisión', 'medmovil' ),
				'valor'    => medmovil_precio( $t['comision'] ),
				'delta'    => $delta( $t['comision'], $ta['comision'] ),
				'nota'     => sprintf( __( '%s%% de lo cobrado', 'medmovil' ), medmovil_comision_pct() ),
				'color'    => 'var(--blue)',
				'url'      => medmovil_url_portal( 'comisiones' ),
			) );
		}
		if ( medmovil_kpi_visible( 'admin', 'atenciones' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-calcheck',
				'etiqueta' => __( 'Atenciones', 'medmovil' ),
				'valor'    => $t['n'],
				'delta'    => $delta( $t['n'], $ta['n'] ),
				'nota'     => sprintf( __( '%d realizadas', 'medmovil' ), $t['realizadas'] ),
				'color'    => 'var(--c-dop)',
				'url'      => medmovil_url_portal( 'atenciones' ),
			) );
		}
		if ( medmovil_kpi_visible( 'admin', 'pacientes' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-users',
				'etiqueta' => __( 'Pacientes distintos', 'medmovil' ),
				'valor'    => count( $pacientes ),
				'nota'     => __( 'en el mes', 'medmovil' ),
				'color'    => 'var(--c-msk)',
			) );
		}
		if ( medmovil_kpi_visible( 'admin', 'ticket' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-trend',
				'etiqueta' => __( 'Ticket promedio', 'medmovil' ),
				'valor'    => medmovil_precio( $ticket ),
				'nota'     => __( 'por visita realizada', 'medmovil' ),
				'color'    => 'var(--c-lab)',
			) );
		}
		if ( medmovil_kpi_visible( 'admin', 'canceladas' ) ) {
			medmovil_kpi( array(
				'icono'    => 'i-warn',
				'etiqueta' => __( 'Canceladas', 'medmovil' ),
				'valor'    => $t['canceladas'],
				'nota'     => $t['n'] ? sprintf( __( '%s%% del total', 'medmovil' ), round( $t['canceladas'] / $t['n'] * 100 ) ) : '',
				'color'    => 'var(--bad)',
			) );
		}
		?>
	<?php medmovil_kpis_cerrar(); ?>

	<div class="pgrid pgrid-2-1">
		<?php
		medmovil_caja_abrir( __( 'Monto realizado, últimos seis meses', 'medmovil' ), __( 'Solo atenciones efectivamente realizadas.', 'medmovil' ) );
		$serie = array();
		for ( $i = 5; $i >= 0; $i-- ) {
			$ini = wp_date( 'Y-m-01', strtotime( "-$i months" ) );
			$fin = wp_date( 'Y-m-t', strtotime( "-$i months" ) );
			$tm  = medmovil_totales( medmovil_atenciones( array( 'desde' => $ini, 'hasta' => $fin, 'limite' => 500 ) ) );
			$serie[ ucfirst( medmovil_fecha( 'M', strtotime( $ini ) ) ) ] = $tm['monto'];
		}
		medmovil_linea( $serie, 'var(--blue)' );
		medmovil_caja_cerrar();

		medmovil_caja_abrir( __( 'En qué estado están', 'medmovil' ), __( 'Del mes en curso.', 'medmovil' ) );
		$por_estado = array();
		foreach ( medmovil_estados() as $k => $e ) {
			$por_estado[ $k ] = 0;
		}
		foreach ( $lista as $a ) {
			if ( isset( $por_estado[ $a['estado'] ] ) ) {
				$por_estado[ $a['estado'] ]++;
			}
		}
		$colores = array(
			'agendada'   => '#9E6A12',
			'confirmada' => '#2F7FD4',
			'realizada'  => '#177A52',
			'informada'  => '#14798B',
			'cancelada'  => '#B23F37',
		);
		$datos = array();
		foreach ( $por_estado as $k => $n ) {
			if ( $n ) {
				$e = medmovil_estados();
				$datos[] = array( $e[ $k ]['n'], $n, $colores[ $k ] );
			}
		}
		medmovil_anillo( $datos );
		medmovil_caja_cerrar();
		?>
	</div>

	<div class="pgrid pgrid-2-1">
		<?php
		medmovil_caja_abrir(
			__( 'Últimas atenciones', 'medmovil' ),
			'',
			'<a class="pbtn" href="' . esc_url( medmovil_url_portal( 'atenciones' ) ) . '">' . esc_html__( 'Ver todas', 'medmovil' ) . '</a>'
		);
		if ( $lista ) {
			echo '<div class="ptabla-wrap"><table class="ptabla">';
			medmovil_thead_atenciones( 'admin', true );
			echo '<tbody>';
			foreach ( array_slice( $lista, 0, 8 ) as $a ) {
				medmovil_fila_atencion( $a, 'admin', true );
			}
			echo '</tbody></table></div>';
		} else {
			medmovil_vacio( __( 'Sin movimiento este mes.', 'medmovil' ), '', 'i-table' );
		}
		medmovil_caja_cerrar();

		medmovil_caja_abrir(
			__( 'Exámenes más pedidos', 'medmovil' ),
			__( 'Del mes. Sirve para saber qué stock de insumos llevar.', 'medmovil' )
		);
		$cuenta = array();
		foreach ( $lista as $a ) {
			foreach ( $a['examenes'] as $e ) {
				if ( ! isset( $cuenta[ $e['n'] ] ) ) {
					$cuenta[ $e['n'] ] = array( 'n' => 0, 'monto' => 0, 'i' => $e['i'] );
				}
				$cuenta[ $e['n'] ]['n']++;
				$cuenta[ $e['n'] ]['monto'] += $e['p'];
			}
		}
		uasort( $cuenta, function ( $x, $y ) { return $y['n'] <=> $x['n']; } );

		if ( $cuenta ) {
			echo '<ol class="pranking">';
			$pos = 0;
			foreach ( array_slice( $cuenta, 0, 6, true ) as $nombre => $d ) {
				$pos++;
				?>
				<li>
					<span class="prk-n"><?php echo (int) $pos; ?></span>
					<span class="prk-ic"><?php medmovil_icono( $d['i'], 'ilo' ); ?></span>
					<span class="prk-t">
						<b><?php echo esc_html( $nombre ); ?></b>
						<span><?php echo esc_html( sprintf( _n( '%d vez', '%d veces', $d['n'], 'medmovil' ), $d['n'] ) ); ?></span>
					</span>
					<b class="prk-v"><?php echo esc_html( medmovil_precio( $d['monto'] ) ); ?></b>
				</li>
				<?php
			}
			echo '</ol>';
		} else {
			medmovil_vacio( __( 'Este mes todavía no hay atenciones cargadas.', 'medmovil' ), '', 'i-chart' );
		}
		medmovil_caja_cerrar();
		?>
	</div>
	<?php
}

/**
 * Listado de atenciones con filtros.
 */
function medmovil_adm_atenciones() {
	$estado = isset( $_GET['estado'] ) ? sanitize_key( wp_unslash( $_GET['estado'] ) ) : '';
	$lista  = medmovil_atenciones( array( 'estado' => $estado, 'limite' => 300 ) );
	?>
	<div class="pn-filtros">
		<a class="chip<?php echo '' === $estado ? ' on' : ''; ?>" href="<?php echo esc_url( medmovil_url_portal( 'atenciones' ) ); ?>">
			<?php esc_html_e( 'Todas', 'medmovil' ); ?>
		</a>
		<?php foreach ( medmovil_estados() as $k => $e ) : ?>
			<a class="chip<?php echo $estado === $k ? ' on' : ''; ?>"
			   href="<?php echo esc_url( medmovil_url_portal( 'atenciones', array( 'estado' => $k ) ) ); ?>">
				<?php echo esc_html( $e['n'] ); ?>
			</a>
		<?php endforeach; ?>
	</div>

	<?php
	medmovil_caja_abrir(
		__( 'Atenciones', 'medmovil' ),
		sprintf( __( '%d resultados. Para editar una, usa el lápiz.', 'medmovil' ), count( $lista ) ),
		'<a class="pbtn pbtn-p" href="' . esc_url( admin_url( 'post-new.php?post_type=mm_atencion' ) ) . '">' . esc_html__( 'Cargar una a mano', 'medmovil' ) . '</a>'
	);

	if ( $lista ) {
		echo '<div class="ptabla-wrap"><table class="ptabla">';
		medmovil_thead_atenciones( 'admin' );
		echo '<tbody>';
		foreach ( $lista as $a ) {
			medmovil_fila_atencion( $a, 'admin' );
		}
		echo '</tbody></table></div>';
	} else {
		medmovil_vacio( __( 'No hay atenciones con ese filtro.', 'medmovil' ), '', 'i-filesearch' );
	}
	medmovil_caja_cerrar();
}

/**
 * Comisiones por profesional.
 */
function medmovil_adm_comisiones() {
	list( $desde, $hasta, $etiqueta ) = medmovil_rango();
	$lista = medmovil_atenciones( array( 'desde' => $desde, 'hasta' => $hasta, 'limite' => 500 ) );

	$por_prof = array();
	foreach ( $lista as $a ) {
		if ( ! in_array( $a['estado'], array( 'realizada', 'informada' ), true ) ) {
			continue;
		}
		$pid = $a['profesional'];
		if ( ! isset( $por_prof[ $pid ] ) ) {
			$por_prof[ $pid ] = array( 'n' => 0, 'monto' => 0, 'pagado' => 0, 'comision' => 0 );
		}
		$por_prof[ $pid ]['n']++;
		$por_prof[ $pid ]['monto']    += $a['monto'];
		$por_prof[ $pid ]['pagado']   += $a['pagado'];
		$por_prof[ $pid ]['comision'] += $a['comision'];
	}

	$t = medmovil_totales( $lista );
	?>
	<div class="pn-rango">
		<b><?php echo esc_html( ucfirst( $etiqueta ) ); ?></b>
		<?php echo wp_kses_post( medmovil_selector_mes() ); ?>
	</div>

	<?php medmovil_kpis_abrir(); ?>
		<?php
		medmovil_kpi( array( 'icono' => 'i-coin', 'etiqueta' => __( 'Monto realizado', 'medmovil' ), 'valor' => medmovil_precio( $t['monto'] ), 'color' => 'var(--ok)' ) );
		medmovil_kpi( array( 'icono' => 'i-tag', 'etiqueta' => __( 'Comisión que te corresponde', 'medmovil' ), 'valor' => medmovil_precio( $t['comision'] ), 'nota' => sprintf( __( 'al %s%%', 'medmovil' ), medmovil_comision_pct() ), 'color' => 'var(--blue)' ) );
		medmovil_kpi( array( 'icono' => 'i-checklist', 'etiqueta' => __( 'Atenciones realizadas', 'medmovil' ), 'valor' => $t['realizadas'], 'color' => 'var(--c-dop)' ) );
		medmovil_kpi( array( 'icono' => 'i-users', 'etiqueta' => __( 'Profesionales activos', 'medmovil' ), 'valor' => count( $por_prof ), 'color' => 'var(--c-msk)' ) );
		?>
	<?php medmovil_kpis_cerrar(); ?>

	<?php
	medmovil_caja_abrir(
		__( 'Cuánto te debe cada uno', 'medmovil' ),
		__( 'Este es el número que tiene que calzar con la planilla que te pasan. Ellos ven exactamente lo mismo en su portal.', 'medmovil' )
	);

	if ( $por_prof ) {
		echo '<div class="ptabla-wrap"><table class="ptabla"><thead><tr>';
		printf(
			'<th>%s</th><th class="td-num">%s</th><th class="td-num">%s</th><th class="td-num">%s</th><th class="td-num">%s</th></tr></thead><tbody>',
			esc_html__( 'Profesional', 'medmovil' ),
			esc_html__( 'Atenciones', 'medmovil' ),
			esc_html__( 'Monto', 'medmovil' ),
			esc_html__( 'Cobrado', 'medmovil' ),
			esc_html__( 'Te debe', 'medmovil' )
		);

		foreach ( $por_prof as $pid => $d ) {
			$u = $pid ? get_userdata( $pid ) : null;
			?>
			<tr>
				<td data-th="<?php esc_attr_e( 'Profesional', 'medmovil' ); ?>">
					<b><?php echo esc_html( $u ? $u->display_name : __( 'Sin asignar', 'medmovil' ) ); ?></b>
					<span class="td-sub"><?php echo esc_html( $u ? get_user_meta( $pid, '_mm_rol_prof', true ) : '' ); ?></span>
				</td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Atenciones', 'medmovil' ); ?>"><?php echo (int) $d['n']; ?></td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Monto', 'medmovil' ); ?>"><?php echo esc_html( medmovil_precio( $d['monto'] ) ); ?></td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Cobrado', 'medmovil' ); ?>">
					<?php echo esc_html( medmovil_precio( $d['pagado'] ) ); ?>
					<?php if ( $d['pagado'] && $d['pagado'] !== $d['monto'] ) : ?>
						<span class="pill pill-warn"><?php esc_html_e( 'no calza', 'medmovil' ); ?></span>
					<?php endif; ?>
				</td>
				<td class="td-num td-com" data-th="<?php esc_attr_e( 'Te debe', 'medmovil' ); ?>"><b><?php echo esc_html( medmovil_precio( $d['comision'] ) ); ?></b></td>
			</tr>
			<?php
		}
		echo '</tbody></table></div>';
	} else {
		medmovil_vacio( __( 'Este mes no hay atenciones realizadas todavía.', 'medmovil' ), '', 'i-coin' );
	}
	?>
	<p class="pn-legal">
		<?php medmovil_icono( 'i-info' ); ?>
		<span><?php esc_html_e( 'La comisión se calcula sobre cobros registrados en visitas marcadas como realizadas. Concilia estos importes con tu planilla antes de liquidar.', 'medmovil' ); ?></span>
	</p>
	<?php
	medmovil_caja_cerrar();
}

/**
 * Profesionales registrados.
 */
function medmovil_adm_equipo() {
	$profesionales = get_users( array( 'role' => 'mm_profesional', 'number' => 100 ) );

	medmovil_caja_abrir(
		__( 'Profesionales', 'medmovil' ),
		__( 'Quiénes están registrados, dónde atienden y cuánto han hecho.', 'medmovil' ),
		'<a class="pbtn pbtn-p" href="' . esc_url( admin_url( 'user-new.php' ) ) . '">' . esc_html__( 'Invitar a uno', 'medmovil' ) . '</a>'
	);

	if ( $profesionales ) {
		echo '<div class="equipo">';
		foreach ( $profesionales as $u ) {
			$t      = medmovil_totales( medmovil_atenciones( array( 'profesional' => $u->ID, 'limite' => 400 ) ) );
			$semana = medmovil_disponibilidad( $u->ID );
			$bloques = 0;
			foreach ( $semana as $horas ) {
				$bloques += count( (array) $horas );
			}
			$comunas = array_filter( array_map( 'trim', explode( "\n", (string) get_user_meta( $u->ID, '_mm_comunas', true ) ) ) );
			?>
			<article class="ecard">
				<div class="ecard-h">
					<span class="pn-av"><?php echo esc_html( mb_substr( $u->display_name, 0, 1 ) ); ?></span>
					<div>
						<b><?php echo esc_html( $u->display_name ); ?></b>
						<span><?php echo esc_html( get_user_meta( $u->ID, '_mm_rol_prof', true ) ?: __( 'Profesional', 'medmovil' ) ); ?></span>
					</div>
				</div>
				<ul class="ecard-d">
					<li><span><?php esc_html_e( 'Atenciones', 'medmovil' ); ?></span><b><?php echo (int) $t['realizadas']; ?></b></li>
					<li><span><?php esc_html_e( 'Facturado', 'medmovil' ); ?></span><b><?php echo esc_html( medmovil_precio( $t['monto'] ) ); ?></b></li>
					<li><span><?php esc_html_e( 'Comisión', 'medmovil' ); ?></span><b><?php echo esc_html( medmovil_precio( $t['comision'] ) ); ?></b></li>
					<li><span><?php esc_html_e( 'Bloques abiertos', 'medmovil' ); ?></span><b><?php echo (int) $bloques; ?></b></li>
				</ul>
				<?php if ( $comunas ) : ?>
					<div class="ecard-c">
						<?php foreach ( array_slice( $comunas, 0, 6 ) as $c ) : ?>
							<span><?php echo esc_html( $c ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p class="ecard-av"><?php medmovil_icono( 'i-warn' ); ?> <?php esc_html_e( 'No ha elegido comunas todavía', 'medmovil' ); ?></p>
				<?php endif; ?>
			</article>
			<?php
		}
		echo '</div>';
	} else {
		medmovil_vacio(
			__( 'Todavía no hay profesionales con cuenta. Créales un usuario con el rol «Profesional MedMóvil» y entran solos a su portal.', 'medmovil' ),
			'<a class="pbtn pbtn-p" href="' . esc_url( admin_url( 'user-new.php' ) ) . '">' . esc_html__( 'Crear el primero', 'medmovil' ) . '</a>',
			'i-useradd'
		);
	}
	medmovil_caja_cerrar();
}

/**
 * Demanda por comuna: de acá sale dónde invertir en Google Ads.
 */
function medmovil_adm_demanda() {
	$lista = medmovil_atenciones( array( 'limite' => 800 ) );
	$por_c = array();

	foreach ( $lista as $a ) {
		$c = $a['comuna'] ? $a['comuna'] : __( 'Sin comuna', 'medmovil' );
		if ( ! isset( $por_c[ $c ] ) ) {
			$por_c[ $c ] = array( 'n' => 0, 'monto' => 0, 'cancel' => 0 );
		}
		$por_c[ $c ]['n']++;
		if ( in_array( $a['estado'], array( 'realizada', 'informada' ), true ) ) {
			$por_c[ $c ]['monto'] += $a['monto'];
		}
		if ( 'cancelada' === $a['estado'] ) {
			$por_c[ $c ]['cancel']++;
		}
	}
	uasort( $por_c, function ( $x, $y ) { return $y['monto'] <=> $x['monto']; } );

	medmovil_caja_abrir(
		__( 'Demanda por comuna', 'medmovil' ),
		__( 'Histórico completo, ordenado por lo que deja cada comuna. Esto es lo que decide dónde poner el presupuesto de Google Ads.', 'medmovil' )
	);

	if ( $por_c ) {
		$grafico = array();
		foreach ( array_slice( $por_c, 0, 10, true ) as $c => $d ) {
			$grafico[ $c ] = $d['monto'];
		}
		medmovil_barras( $grafico, 'pesos', 'var(--blue)' );

		echo '<div class="ptabla-wrap" style="margin-top:22px"><table class="ptabla"><thead><tr>';
		printf(
			'<th>%s</th><th class="td-num">%s</th><th class="td-num">%s</th><th class="td-num">%s</th><th class="td-num">%s</th></tr></thead><tbody>',
			esc_html__( 'Comuna', 'medmovil' ),
			esc_html__( 'Atenciones', 'medmovil' ),
			esc_html__( 'Monto', 'medmovil' ),
			esc_html__( 'Ticket', 'medmovil' ),
			esc_html__( 'Canceladas', 'medmovil' )
		);
		foreach ( $por_c as $c => $d ) {
			?>
			<tr>
				<td data-th="<?php esc_attr_e( 'Comuna', 'medmovil' ); ?>"><b><?php echo esc_html( $c ); ?></b></td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Atenciones', 'medmovil' ); ?>"><?php echo (int) $d['n']; ?></td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Monto', 'medmovil' ); ?>"><?php echo esc_html( medmovil_precio( $d['monto'] ) ); ?></td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Ticket', 'medmovil' ); ?>"><?php echo esc_html( medmovil_precio( $d['n'] ? round( $d['monto'] / $d['n'] ) : 0 ) ); ?></td>
				<td class="td-num" data-th="<?php esc_attr_e( 'Canceladas', 'medmovil' ); ?>"><?php echo (int) $d['cancel']; ?></td>
			</tr>
			<?php
		}
		echo '</tbody></table></div>';
	} else {
		medmovil_vacio(
			__( 'Todavía no hay atenciones cargadas, así que no hay demanda que medir. En cuanto empieces a registrar visitas, este cuadro se llena solo.', 'medmovil' ),
			'',
			'i-map'
		);
	}
	?>
	<p class="pn-legal">
		<?php medmovil_icono( 'i-info' ); ?>
		<span><?php esc_html_e( 'Esto mide la demanda que efectivamente se convirtió en atención, no las visitas al sitio. Para lo otro está Google Analytics, que se conecta en MedMóvil → Ajustes.', 'medmovil' ); ?></span>
	</p>
	<?php
	medmovil_caja_cerrar();
}
