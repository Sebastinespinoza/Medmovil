<?php
/** Portal PulsePACS adaptado a MedMóvil. Presentación; conserva rutas y permisos. */
defined( 'ABSPATH' ) || exit;

/** Áreas laterales y opciones horizontales; respeta las secciones habilitadas. */
function medmovil_pulse_navigation( $rol, $section, $menu ) {
 $groups = 'profesional' === $rol
  ? array( array('i-grid','Inicio',array('resumen')), array('i-caldays','Mi trabajo',array('agenda','horarios')), array('i-users','Atenciones',array('pacientes','informes')), array('i-user','Mi cuenta',array('perfil','ingresos')) )
  : array( array('i-grid','Inicio',array('resumen')), array('i-calcheck','Mi atención',array('horas','informes')), array('i-user','Mi cuenta',array('perfil','ficha','pagos')) );
 if ('admin' === $rol) { $groups = array(array('i-grid','Inicio',array('resumen')),array('i-caldays','Operación',array('atenciones','equipo')),array('i-chart','Resultados',array('comisiones','demanda'))); }
 $side = array(); $tabs = array(); $active = ''; $members=array();
 foreach ( $groups as $group ) {
  $available = array_intersect_key( array_fill_keys($group[2],true), $menu );
  if (!$available) { continue; }
  $first = key($available); $side[$first] = array($group[0], __($group[1],'medmovil')); $members[$first]=array_keys($available);
  if (isset($available[$section])) { $active=$first; foreach(array_keys($available) as $key) {$tabs[$key]=$menu[$key];} }
 }
 return array('side'=>$side,'tabs'=>$tabs,'active'=>$active,'members'=>$members);
}

function medmovil_pulse_tabs( $rol, $section, $navigation ) {
 if ('resumen' === $section) {
  $menu = medmovil_secciones($rol);
  $keys = 'profesional' === $rol ? array('agenda','horarios','pacientes','informes') : array('horas','informes');
  if ('admin' === $rol) {$keys=array('atenciones','equipo','comisiones','demanda');}
  $navigation['tabs'] = array_intersect_key($menu, array_fill_keys($keys,true));
 }
 ?><nav class="mp-tabs" aria-label="Opciones de esta área">
 <?php if ('horarios' === $section && 'profesional' === $rol) : ?>
  <?php foreach (array('semana'=>array('i-caldays','Mi semana'),'zonas'=>array('i-pin','Comunas y sectores'),'excepciones'=>array('i-calcheck','Fechas bloqueadas'),'tiempos'=>array('i-timer','Tiempos de visita')) as $id=>$item) : ?>
   <a href="#mp-<?php echo esc_attr($id); ?>" data-availability-tab="<?php echo esc_attr($id); ?>" <?php echo 'semana'===$id?'aria-current="location"':''; ?>><?php medmovil_icono($item[0]);echo esc_html($item[1]); ?></a>
  <?php endforeach; ?>
 <?php else : ?>
  <?php foreach ($navigation['tabs'] as $key=>$item) : ?>
   <a href="<?php echo esc_url(medmovil_url_portal($key)); ?>" <?php echo $section===$key?'aria-current="page"':''; ?>><?php medmovil_icono($item[0]);echo esc_html($item[1]); ?></a>
  <?php endforeach; ?>
 <?php endif; ?>
 </nav><?php
}

function medmovil_pulse_abrir( $rol, $seccion, $titulo ) {
	$user = wp_get_current_user();
	$menu = medmovil_secciones( $rol );
	$navigation = medmovil_pulse_navigation( $rol, $seccion, $menu );
	$avisos = array_intersect_key( medmovil_avisos( $rol ), $menu );
	$area_avisos=array(); foreach($navigation['members'] as $key=>$members) {$area_avisos[$key]=array_sum(array_intersect_key($avisos,array_fill_keys($members,true)));}
	$operario = 'profesional' === $rol;
	$nombre_rol = $operario ? __( 'Operario · Atención a domicilio', 'medmovil' ) : __( 'Portal del paciente', 'medmovil' );
	$admin = 'admin' === $rol;
	if ($admin) {$nombre_rol = 'Administración · Atención a domicilio';}
	$principal = $operario ? 'agenda' : 'horas';
	$inicial = mb_substr( $user->display_name, 0, 1 );
	?>
	<div class="pn pn-pulse is-pinned" data-rol="<?php echo esc_attr( $rol ); ?>" data-section="<?php echo esc_attr( $seccion ); ?>">
		<a class="mp-skip" href="#mpContent"><?php esc_html_e( 'Saltar al contenido', 'medmovil' ); ?></a>
		<aside class="pn-side" id="pnSide" aria-label="<?php esc_attr_e( 'Navegación de MedMóvil', 'medmovil' ); ?>">
			<div class="mp-brand">
				<button type="button" class="mp-icon mp-pin" id="mpPin" aria-expanded="false" aria-controls="pnSide" aria-label="<?php esc_attr_e( 'Expandir menú', 'medmovil' ); ?>" title="<?php esc_attr_e( 'Expandir menú', 'medmovil' ); ?>"><?php medmovil_icono( 'i-menu' ); ?></button>
				<a class="pn-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( MEDMOVIL_URI . '/assets/img/logo.png' ); ?>" alt="MedMóvil" width="480" height="134"></a>
			</div>
			<p class="pn-side-h"><?php echo esc_html( $admin ? 'Mi gestión' : ($operario ? __( 'Mi trabajo', 'medmovil' ) : __( 'Mi atención', 'medmovil' )) ); ?></p>
			<nav class="pn-nav" aria-label="<?php esc_attr_e( 'Secciones del portal', 'medmovil' ); ?>">
				<?php foreach ( $navigation['side'] as $clave => $item ) : ?>
				<a class="pn-l<?php echo $clave === $navigation['active'] ? ' on' : ''; ?>" href="<?php echo esc_url( medmovil_url_portal( $clave ) ); ?>" title="<?php echo esc_attr( $item[1] ); ?>" <?php echo $clave === $navigation['active'] ? 'aria-current="page"' : ''; ?>>
					<?php medmovil_icono( $item[0] ); ?><span><?php echo esc_html( $item[1] ); ?></span>
					<?php if ( ! empty( $area_avisos[ $clave ] ) ) : ?><i class="pn-badge"><?php echo (int) $area_avisos[ $clave ]; ?></i><?php endif; ?>
				</a>
				<?php endforeach; ?>
			</nav>
			<div class="pn-side-f">
				<a class="pn-l" href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>" title="<?php esc_attr_e( 'Ver exámenes', 'medmovil' ); ?>"><?php medmovil_icono( 'i-bag' ); ?><span><?php esc_html_e( 'Ver exámenes', 'medmovil' ); ?></span></a>
				<a class="pn-l" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" title="<?php esc_attr_e( 'Cerrar sesión', 'medmovil' ); ?>"><?php medmovil_icono( 'i-logout' ); ?><span><?php esc_html_e( 'Cerrar sesión', 'medmovil' ); ?></span></a>
			</div>
		</aside>
		<div class="pn-main">
			<header class="pn-top">
				<button type="button" class="pn-burger mp-icon" id="pnBurger" aria-controls="pnSide" aria-expanded="false" aria-label="<?php esc_attr_e( 'Abrir menú', 'medmovil' ); ?>"><?php medmovil_icono( 'i-menu' ); ?></button>
				<div class="pn-top-t"><h1 class="mp-role-title"><?php echo esc_html( $nombre_rol ); ?></h1><p><?php echo esc_html( medmovil_saludo( $user ) ); ?></p></div>
				<div class="pn-top-r">
					<span class="mp-session"><?php medmovil_icono( 'i-lock' ); ?><?php esc_html_e( 'Mi espacio privado', 'medmovil' ); ?></span>
					<button type="button" class="mp-icon" id="mpRefresh" aria-label="<?php esc_attr_e( 'Actualizar vista', 'medmovil' ); ?>" title="<?php esc_attr_e( 'Actualizar vista', 'medmovil' ); ?>">↻</button>
					<div class="mp-account-identity" aria-label="Sesión actual"><span class="pn-av"><?php echo esc_html( $inicial ); ?></span><span class="mp-identity"><b><?php echo esc_html( $user->display_name ); ?></b><small><?php echo esc_html( $admin ? 'Administrador' : ($operario ? 'Operario' : 'Paciente') ); ?></small></span></div>
				</div>
			</header>
			<?php medmovil_pulse_tabs( $rol, $seccion, $navigation ); ?>
			<div class="pn-body" id="mpContent" tabindex="-1">
				<?php if ( 'resumen' === $seccion ) : ?>
				<div class="mp-intro"><div><span class="mp-kicker">MedMóvil · <?php esc_html_e( 'Atención a domicilio', 'medmovil' ); ?></span><h2><?php echo esc_html( $admin ? 'Tu operación, en un solo lugar' : ($operario ? __( 'Todo listo para tu próxima visita', 'medmovil' ) : __( 'Tu atención, en un solo lugar', 'medmovil' )) ); ?></h2><p><?php echo esc_html( $admin ? 'Coordina las atenciones, revisa tu equipo y sigue los resultados de MedMóvil.' : ($operario ? __( 'Consulta tu agenda, prepara cada atención y entrega los informes de tus pacientes.', 'medmovil' ) : __( 'Revisa tus horas, encuentra tus informes y mantén tus datos al día.', 'medmovil' )) ); ?></p></div>
				<?php if ( 'paciente' === $rol ) : ?><a class="pbtn pbtn-p" href="<?php echo esc_url( medmovil_url_reserva() ); ?>"><?php medmovil_icono( 'i-caladd' ); ?><?php esc_html_e( 'Solicitar un examen', 'medmovil' ); ?></a>
				<?php endif; ?></div>
				<?php endif; ?>
	<?php
}

function medmovil_pulse_cerrar() {
	?>
			</div>
			<footer class="pn-foot"><span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> MedMóvil · <?php esc_html_e( 'Atención a domicilio', 'medmovil' ); ?></span><span><a href="<?php echo esc_url( home_url( '/privacidad/' ) ); ?>"><?php esc_html_e( 'Privacidad', 'medmovil' ); ?></a><a href="<?php echo esc_url( medmovil_whatsapp() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Necesito ayuda', 'medmovil' ); ?></a></span></footer>
		</div>
		<div class="pn-bg" id="pnBg" hidden></div>
		<dialog class="mp-document" id="mpDocument" aria-labelledby="mpDocumentTitle"><header><div><span class="mp-kicker">MedMóvil</span><h2 id="mpDocumentTitle"><?php esc_html_e( 'Informe del examen', 'medmovil' ); ?></h2></div><a class="pbtn" id="mpDocumentOpen" target="_blank" rel="noopener"><?php esc_html_e( 'Abrir documento', 'medmovil' ); ?></a><button class="mp-icon" type="button" id="mpDocumentClose" aria-label="<?php esc_attr_e( 'Cerrar informe', 'medmovil' ); ?>">×</button></header><p class="mp-document-help"><?php esc_html_e( 'Si tu navegador no muestra el archivo, utiliza «Abrir documento» para verlo o descargarlo.', 'medmovil' ); ?></p><iframe title="<?php esc_attr_e( 'Documento del examen', 'medmovil' ); ?>" referrerpolicy="no-referrer"></iframe></dialog>
	</div>
	<?php
}
