<?php
/**
 * Piezas del panel: barra lateral, cabecera, tarjetas de indicador, gráficos y
 * tablas. Todo lo dibuja el propio tema —los gráficos son SVG hechos a mano—
 * para no cargar ninguna librería en un panel que se abre todos los días.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL de una sección del portal.
 *
 * @param string $seccion Clave.
 * @param array  $extra   Parámetros extra.
 * @return string
 */
function medmovil_url_portal( $seccion = '', $extra = array() ) {
	$base = get_permalink( get_page_by_path( 'portal' ) );
	if ( ! $base ) {
		$base = home_url( '/portal/' );
	}
	$args = $seccion ? array_merge( array( 'p' => $seccion ), $extra ) : $extra;

	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * ¿Estamos dentro del panel, con sesión iniciada y rol asignado?
 *
 * Sirve para que la cabecera y el pie de la tienda no se dibujen encima del
 * panel: el panel ya trae su propia barra superior, su menú lateral y su pie.
 * Tener las dos cosas a la vez confunde y roba pantalla.
 *
 * @return bool
 */
function medmovil_en_panel() {
	static $panel = null;

	if ( null !== $panel ) {
		return $panel;
	}

	$panel = is_page_template( 'template-portal.php' ) && is_user_logged_in() && (bool) medmovil_rol();

	return $panel;
}

/**
 * Secciones de cada rol.
 *
 * @param string $rol      Rol.
 * @param bool   $filtrar  Si es false, devuelve todas, incluso las ocultas.
 * @return array
 */
function medmovil_secciones( $rol, $filtrar = true ) {
	$menus = array(
		'paciente' => array(
			'resumen'   => array( 'i-grid', __( 'Mi resumen', 'medmovil' ) ),
			'horas'     => array( 'i-calcheck', __( 'Mis horas', 'medmovil' ) ),
			'pagos'     => array( 'i-coin', __( 'Mis pagos', 'medmovil' ) ),
			'informes'  => array( 'i-filecheck', __( 'Mis informes', 'medmovil' ) ),
			'ficha'     => array( 'i-notebook', __( 'Mi ficha clínica', 'medmovil' ) ),
			'perfil'    => array( 'i-user', __( 'Mis datos', 'medmovil' ) ),
		),
		'profesional' => array(
			'resumen'    => array( 'i-grid', __( 'Resumen', 'medmovil' ) ),
			'agenda'     => array( 'i-caldays', __( 'Mi agenda', 'medmovil' ) ),
			'horarios'   => array( 'i-timer', __( 'Disponibilidad y zonas', 'medmovil' ) ),
			'pacientes'  => array( 'i-users', __( 'Mis pacientes', 'medmovil' ) ),
			'informes'   => array( 'i-cloudup', __( 'Subir informes', 'medmovil' ) ),
			'ingresos'   => array( 'i-chart', __( 'Mis ingresos', 'medmovil' ) ),
			'perfil'     => array( 'i-idcard', __( 'Mi perfil público', 'medmovil' ) ),
		),
		'admin' => array(
			'resumen'      => array( 'i-grid', __( 'Resumen', 'medmovil' ) ),
			'atenciones'   => array( 'i-table', __( 'Atenciones', 'medmovil' ) ),
			'comisiones'   => array( 'i-coin', __( 'Mis comisiones', 'medmovil' ) ),
			'equipo'       => array( 'i-users', __( 'Profesionales', 'medmovil' ) ),
			'demanda'      => array( 'i-map', __( 'Demanda por comuna', 'medmovil' ) ),
		),
	);

	$menu = isset( $menus[ $rol ] ) ? $menus[ $rol ] : array();

	if ( ! $filtrar ) {
		return $menu;
	}

	// El dueño decide qué secciones ve cada rol.
	foreach ( (array) medmovil_portal_opt( 'ocultar_' . $rol, array() ) as $clave ) {
		unset( $menu[ $clave ] );
	}

	return $menu;
}

/**
 * Abre el panel: barra lateral, cabecera y contenedor.
 *
 * @param string $rol     Rol.
 * @param string $seccion Sección activa.
 * @param string $titulo  Título de la página.
 */
function medmovil_panel_abrir( $rol, $seccion, $titulo ) {
	if ( in_array( $rol, array( 'paciente', 'profesional', 'admin' ), true ) ) {
		medmovil_pulse_abrir( $rol, $seccion, $titulo );
		return;
	}
	$user  = wp_get_current_user();
	$menu  = medmovil_secciones( $rol );
	$roles = array(
		'paciente'    => __( 'Portal del paciente', 'medmovil' ),
		'profesional' => __( 'Portal del profesional', 'medmovil' ),
		'admin'       => __( 'Panel de control', 'medmovil' ),
	);
	$avisos = medmovil_avisos( $rol );
	?>
	<div class="pn" data-rol="<?php echo esc_attr( $rol ); ?>">

		<aside class="pn-side" id="pnSide">
			<a class="pn-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( MEDMOVIL_URI . '/assets/img/logo.png' ); ?>"
				     alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="480" height="134">
			</a>

			<p class="pn-side-h"><?php echo esc_html( isset( $roles[ $rol ] ) ? $roles[ $rol ] : '' ); ?></p>

			<nav class="pn-nav" aria-label="<?php esc_attr_e( 'Secciones del portal', 'medmovil' ); ?>">
				<?php foreach ( $menu as $clave => $item ) : ?>
					<a class="pn-l<?php echo $clave === $seccion ? ' on' : ''; ?>"
					   href="<?php echo esc_url( medmovil_url_portal( $clave ) ); ?>">
						<?php medmovil_icono( $item[0] ); ?>
						<span><?php echo esc_html( $item[1] ); ?></span>
						<?php if ( ! empty( $avisos[ $clave ] ) ) : ?>
							<i class="pn-badge"><?php echo (int) $avisos[ $clave ]; ?></i>
						<?php endif; ?>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="pn-side-f">
				<a class="pn-l" href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>">
					<?php medmovil_icono( 'i-bag' ); ?><span><?php esc_html_e( 'Ir a la tienda', 'medmovil' ); ?></span>
				</a>
				<a class="pn-l" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
					<?php medmovil_icono( 'i-logout' ); ?><span><?php esc_html_e( 'Cerrar sesión', 'medmovil' ); ?></span>
				</a>
			</div>
		</aside>

		<div class="pn-main">
			<header class="pn-top">
				<button type="button" class="pn-burger" id="pnBurger" aria-label="<?php esc_attr_e( 'Abrir menú', 'medmovil' ); ?>">
					<?php medmovil_icono( 'i-menu' ); ?>
				</button>

				<div class="pn-top-t">
					<h1><?php echo esc_html( $titulo ); ?></h1>
					<p><?php echo esc_html( medmovil_saludo( $user ) ); ?></p>
				</div>

				<div class="pn-top-r">
					<?php if ( array_sum( $avisos ) ) : ?>
						<span class="pn-camp" title="<?php esc_attr_e( 'Tienes cosas pendientes', 'medmovil' ); ?>">
							<?php medmovil_icono( 'i-bellon' ); ?>
							<i><?php echo (int) array_sum( $avisos ); ?></i>
						</span>
					<?php else : ?>
						<span class="pn-camp"><?php medmovil_icono( 'i-bell' ); ?></span>
					<?php endif; ?>

					<span class="pn-yo">
						<span class="pn-av"><?php echo esc_html( mb_substr( $user->display_name, 0, 1 ) ); ?></span>
						<span>
							<b><?php echo esc_html( $user->display_name ); ?></b>
							<small><?php echo esc_html( isset( $roles[ $rol ] ) ? $roles[ $rol ] : '' ); ?></small>
						</span>
					</span>
				</div>
			</header>

			<div class="pn-body">
	<?php
}

/**
 * Cierra el panel.
 */
function medmovil_panel_cerrar() {
	if ( in_array( medmovil_rol(), array( 'paciente', 'profesional', 'admin' ), true ) ) {
		medmovil_pulse_cerrar();
		return;
	}
	?>
			</div>
			<footer class="pn-foot">
				<span>
					<?php
					printf(
						/* translators: 1: año, 2: nombre */
						esc_html__( '© %1$s %2$s', 'medmovil' ),
						esc_html( gmdate( 'Y' ) ),
						esc_html( medmovil_opt( 'razon_social', get_bloginfo( 'name' ) ) )
					);
					?>
				</span>
				<span>
					<a href="<?php echo esc_url( home_url( '/privacidad/' ) ); ?>"><?php esc_html_e( 'Privacidad', 'medmovil' ); ?></a>
					<a href="<?php echo esc_url( medmovil_whatsapp() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Soporte', 'medmovil' ); ?></a>
					<?php echo esc_html( 'v' . MEDMOVIL_VERSION ); ?>
				</span>
			</footer>
		</div>
		<div class="pn-bg" id="pnBg" hidden></div>
	</div>
	<?php
}

/**
 * Saludo según la hora, para que el panel no se sienta una planilla.
 *
 * @param WP_User $user Usuario.
 * @return string
 */
function medmovil_saludo( $user ) {
	$h = (int) wp_date( 'G' );
	if ( $h < 12 ) {
		$saludo = __( 'Buenos días', 'medmovil' );
	} elseif ( $h < 20 ) {
		$saludo = __( 'Buenas tardes', 'medmovil' );
	} else {
		$saludo = __( 'Buenas noches', 'medmovil' );
	}

	$nombre = $user->first_name ? $user->first_name : $user->display_name;

	return sprintf( '%s, %s · %s', $saludo, $nombre, medmovil_fecha( 'l j \d\e F' ) );
}

/**
 * Abre la fila de indicadores.
 *
 * Guarda la salida en memoria porque la cantidad de tarjetas depende de lo que
 * el dueño haya dejado visible en MedMóvil → Portales, y recién al cerrar
 * sabemos cuántas quedaron para repartirlas en filas parejas.
 */
function medmovil_kpis_abrir() {
	ob_start();
}

/**
 * Cierra la fila de indicadores y la imprime con el reparto correcto.
 */
function medmovil_kpis_cerrar() {
	$tarjetas = ob_get_clean();
	$cuantas  = substr_count( $tarjetas, 'class="pkpi"' );

	if ( ! $cuantas ) {
		return;
	}

	printf( '<div class="pkpis pkpis-%d">%s</div>', (int) $cuantas, $tarjetas ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML ya escapado en medmovil_kpi().
}

/**
 * Una tarjeta de indicador.
 *
 * @param array $d Datos.
 */
function medmovil_kpi( $d ) {
	$d = wp_parse_args( $d, array(
		'icono' => 'i-chart', 'etiqueta' => '', 'valor' => '', 'nota' => '',
		'delta' => null, 'color' => 'var(--blue)', 'url' => '',
	) );
	$tag = $d['url'] ? 'a' : 'div';
	?>
	<<?php echo esc_attr( $tag ); ?> class="pkpi" style="--ac:<?php echo esc_attr( $d['color'] ); ?>"
		<?php echo $d['url'] ? 'href="' . esc_url( $d['url'] ) . '"' : ''; ?>>
		<span class="pkpi-ic"><?php medmovil_icono( $d['icono'] ); ?></span>
		<span class="pkpi-l"><?php echo esc_html( $d['etiqueta'] ); ?></span>
		<b class="pkpi-v"><?php echo esc_html( $d['valor'] ); ?></b>
		<span class="pkpi-f">
			<?php if ( null !== $d['delta'] ) : ?>
				<span class="pkpi-d <?php echo $d['delta'] >= 0 ? 'up' : 'down'; ?>">
					<?php medmovil_icono( $d['delta'] >= 0 ? 'i-trend' : 'i-trenddown' ); ?>
					<?php echo esc_html( ( $d['delta'] >= 0 ? '+' : '' ) . $d['delta'] . '%' ); ?>
				</span>
			<?php endif; ?>
			<?php if ( $d['nota'] ) : ?><small><?php echo esc_html( $d['nota'] ); ?></small><?php endif; ?>
		</span>
	</<?php echo esc_attr( $tag ); ?>>
	<?php
}

/**
 * Abre una tarjeta del panel.
 *
 * @param string $titulo Título.
 * @param string $nota   Bajada.
 * @param string $accion HTML del botón de la esquina.
 */
function medmovil_caja_abrir( $titulo, $nota = '', $accion = '' ) {
	?>
	<section class="pcard">
		<?php if ( $titulo ) : ?>
			<header class="pcard-h">
				<div>
					<h2><?php echo esc_html( $titulo ); ?></h2>
					<?php if ( $nota ) : ?><p><?php echo esc_html( $nota ); ?></p><?php endif; ?>
				</div>
				<?php echo $accion ? wp_kses_post( $accion ) : ''; ?>
			</header>
		<?php endif; ?>
		<div class="pcard-b">
	<?php
}

/**
 * Cierra la tarjeta.
 */
function medmovil_caja_cerrar() {
	echo '</div></section>';
}

/**
 * Gráfico de barras en SVG. Sin librerías.
 *
 * @param array  $datos  Pares etiqueta => valor.
 * @param string $formato 'pesos' o 'n'.
 * @param string $color   Color de las barras.
 */
function medmovil_barras( $datos, $formato = 'n', $color = 'var(--blue)' ) {
	if ( ! $datos ) {
		medmovil_vacio( __( 'Todavía no hay datos para este período.', 'medmovil' ) );
		return;
	}

	$max = max( 1, max( $datos ) );
	?>
	<div class="chart-b" style="--ac:<?php echo esc_attr( $color ); ?>">
		<?php foreach ( $datos as $etiqueta => $valor ) :
			$alto = round( $valor / $max * 100 );
			$txt  = 'pesos' === $formato ? medmovil_precio( $valor ) : number_format_i18n( $valor );
			?>
			<div class="chart-col" title="<?php echo esc_attr( $etiqueta . ': ' . $txt ); ?>">
				<span class="chart-v"><?php echo esc_html( $txt ); ?></span>
				<span class="chart-bar" style="height:<?php echo esc_attr( max( $alto, 2 ) ); ?>%"></span>
				<span class="chart-x"><?php echo esc_html( $etiqueta ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Gráfico de línea en SVG.
 *
 * @param array  $datos  Pares etiqueta => valor.
 * @param string $color  Color.
 */
function medmovil_linea( $datos, $color = 'var(--blue)' ) {
	if ( count( $datos ) < 2 ) {
		medmovil_vacio( __( 'Hacen falta al menos dos períodos para dibujar la tendencia.', 'medmovil' ) );
		return;
	}

	$valores = array_values( $datos );
	$max     = max( 1, max( $valores ) );
	$n       = count( $valores );
	$puntos  = array();

	foreach ( $valores as $i => $v ) {
		$x = round( $i / ( $n - 1 ) * 100, 2 );
		$y = round( 100 - ( $v / $max * 88 ) - 6, 2 );
		$puntos[] = $x . ',' . $y;
	}

	$linea = implode( ' ', $puntos );
	$area  = '0,100 ' . $linea . ' 100,100';
	?>
	<div class="chart-l" style="--ac:<?php echo esc_attr( $color ); ?>">
		<svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
			<polygon class="cl-area" points="<?php echo esc_attr( $area ); ?>"/>
			<polyline class="cl-line" points="<?php echo esc_attr( $linea ); ?>"/>
		</svg>
		<div class="chart-lx">
			<?php foreach ( array_keys( $datos ) as $etiqueta ) : ?>
				<span><?php echo esc_html( $etiqueta ); ?></span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Gráfico de anillo.
 *
 * @param array $datos Lista de [etiqueta, valor, color].
 */
function medmovil_anillo( $datos ) {
	$total = 0;
	foreach ( $datos as $d ) {
		$total += $d[1];
	}
	if ( ! $total ) {
		medmovil_vacio( __( 'Todavía no hay datos que repartir.', 'medmovil' ) );
		return;
	}

	$acumulado = 0;
	$radio     = 15.9155; // circunferencia = 100, así los porcentajes son directos
	?>
	<div class="chart-d">
		<svg viewBox="0 0 42 42" aria-hidden="true">
			<circle class="cd-bg" cx="21" cy="21" r="<?php echo esc_attr( $radio ); ?>"/>
			<?php foreach ( $datos as $d ) :
				$pct = round( $d[1] / $total * 100, 2 );
				?>
				<circle cx="21" cy="21" r="<?php echo esc_attr( $radio ); ?>"
				        stroke="<?php echo esc_attr( $d[2] ); ?>"
				        stroke-dasharray="<?php echo esc_attr( $pct . ' ' . ( 100 - $pct ) ); ?>"
				        stroke-dashoffset="<?php echo esc_attr( 25 - $acumulado ); ?>"/>
				<?php $acumulado += $pct; ?>
			<?php endforeach; ?>
			<text x="21" y="20.5" class="cd-n"><?php echo esc_html( number_format_i18n( $total ) ); ?></text>
			<text x="21" y="25" class="cd-l"><?php esc_html_e( 'total', 'medmovil' ); ?></text>
		</svg>
		<ul class="chart-leg">
			<?php foreach ( $datos as $d ) : ?>
				<li>
					<i style="background:<?php echo esc_attr( $d[2] ); ?>"></i>
					<span><?php echo esc_html( $d[0] ); ?></span>
					<b><?php echo esc_html( number_format_i18n( $d[1] ) ); ?></b>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Estado vacío.
 *
 * @param string $texto  Mensaje.
 * @param string $boton  HTML del botón.
 * @param string $icono  Símbolo.
 */
function medmovil_vacio( $texto, $boton = '', $icono = 'i-filesearch' ) {
	?>
	<div class="pn-vacio">
		<?php medmovil_icono( $icono ); ?>
		<p><?php echo esc_html( $texto ); ?></p>
		<?php echo $boton ? wp_kses_post( $boton ) : ''; ?>
	</div>
	<?php
}

/**
 * Fila de una atención, en formato tabla.
 *
 * @param array  $a   Atención.
 * @param string $rol Rol que la mira.
 */
function medmovil_fila_atencion( $a, $rol, $compacta = false ) {
	$nombres = array();
	foreach ( $a['examenes'] as $e ) {
		$nombres[] = $e['n'];
	}
	$otro = 'paciente' === $rol ? $a['profesional'] : $a['paciente'];
	$otro_u = $otro ? get_userdata( $otro ) : null;
	?>
	<tr>
		<td class="td-fecha" data-th="<?php esc_attr_e( 'Fecha', 'medmovil' ); ?>">
			<b><?php echo esc_html( $a['fecha'] ? medmovil_fecha( 'j M Y', $a['ts'] ) : '—' ); ?></b>
			<span class="td-sub"><?php echo esc_html( $a['hora'] ? $a['hora'] . ' h' : '' ); ?></span>
		</td>
		<td data-th="<?php esc_attr_e( 'Examen', 'medmovil' ); ?>">
			<b><?php echo esc_html( $nombres ? implode( ' · ', $nombres ) : __( 'Sin detalle', 'medmovil' ) ); ?></b>
			<span class="td-sub"><?php echo esc_html( $a['comuna'] ); ?></span>
		</td>
		<?php if ( 'admin' === $rol || 'profesional' === $rol ) : ?>
			<td data-th="<?php esc_attr_e( 'Paciente', 'medmovil' ); ?>">
				<?php echo esc_html( $otro_u ? $otro_u->display_name : '—' ); ?>
			</td>
		<?php else : ?>
			<td data-th="<?php esc_attr_e( 'Profesional', 'medmovil' ); ?>">
				<?php echo esc_html( $otro_u ? $otro_u->display_name : __( 'Por asignar', 'medmovil' ) ); ?>
			</td>
		<?php endif; ?>
		<td data-th="<?php esc_attr_e( 'Estado', 'medmovil' ); ?>"><?php medmovil_estado_pill( $a['estado'] ); ?></td>
		<?php if ( $compacta ) : ?>
			</tr>
			<?php
			return;
		endif;
		?>
		<td class="td-num" data-th="<?php esc_attr_e( 'Monto', 'medmovil' ); ?>"><?php echo esc_html( medmovil_precio( $a['monto'] ) ); ?></td>
		<?php if ( 'admin' === $rol ) : ?>
			<td class="td-num td-com" data-th="<?php esc_attr_e( 'Comisión', 'medmovil' ); ?>">
				<?php echo esc_html( in_array( $a['estado'], array( 'realizada', 'informada' ), true ) ? medmovil_precio( $a['comision'] ) : '—' ); ?>
			</td>
		<?php endif; ?>
		<td class="td-acc" data-th="">
			<?php if ( $a['informe'] ) : ?>
				<a class="pbtn pbtn-xs" href="<?php echo esc_url( medmovil_url_informe( $a['id'] ) ); ?>" target="_blank" rel="noopener">
					<?php medmovil_icono( 'i-dl' ); ?> <?php esc_html_e( 'Informe', 'medmovil' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( 'admin' === $rol ) : ?>
				<a class="pbtn pbtn-xs" href="<?php echo esc_url( get_edit_post_link( $a['id'] ) ); ?>">
					<?php medmovil_icono( 'i-edit' ); ?>
				</a>
			<?php endif; ?>
		</td>
	</tr>
	<?php
}

/**
 * Cabecera de la tabla de atenciones.
 *
 * @param string $rol      Rol.
 * @param bool   $compacta Sin monto, comisión ni botones: para el resumen,
 *                         donde la tabla va en media pantalla.
 */
function medmovil_thead_atenciones( $rol, $compacta = false ) {
	?>
	<thead>
		<tr>
			<th><?php esc_html_e( 'Fecha', 'medmovil' ); ?></th>
			<th><?php esc_html_e( 'Examen', 'medmovil' ); ?></th>
			<th><?php echo esc_html( 'paciente' === $rol ? __( 'Profesional', 'medmovil' ) : __( 'Paciente', 'medmovil' ) ); ?></th>
			<th><?php esc_html_e( 'Estado', 'medmovil' ); ?></th>
			<?php if ( ! $compacta ) : ?>
				<th class="td-num"><?php esc_html_e( 'Monto', 'medmovil' ); ?></th>
				<?php if ( 'admin' === $rol ) : ?>
					<th class="td-num"><?php esc_html_e( 'Comisión', 'medmovil' ); ?></th>
				<?php endif; ?>
				<th></th>
			<?php endif; ?>
		</tr>
	</thead>
	<?php
}
