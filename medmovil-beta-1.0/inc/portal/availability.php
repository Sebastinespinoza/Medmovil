<?php
/** Configuración de cobertura y planificación del operario. No asigna reservas. */
defined('ABSPATH') || exit;

function medmovil_coverage_catalog() {
 $out=array();
 foreach (medmovil_comunas(500) as $c) { $out[(int)$c->ID]=(string)$c->post_title; }
 return $out;
}

function medmovil_availability_config($uid) {
 $data=get_user_meta($uid,'_mm_availability_v1',true);
 if (is_array($data)) { return wp_parse_args($data,array('coverage'=>array(),'zones'=>array(),'exceptions'=>array(),'duration'=>45,'travel'=>30)); }
 $names=preg_split('/\R/u',(string)get_user_meta($uid,'_mm_comunas',true));
 $coverage=array(); foreach(medmovil_coverage_catalog() as $id=>$name) { if(in_array($name,array_map('trim',$names),true)) {$coverage[]=$id;} }
 return array('coverage'=>$coverage,'zones'=>array(),'exceptions'=>array(),'duration'=>45,'travel'=>30);
}

/** Valida todo antes de escribir; IDs de comuna del catálogo, nunca texto libre. */
function medmovil_validate_availability($raw, $catalog) {
 if (!is_array($raw)) { return new WP_Error('invalid','Configuración inválida.'); }
 $coverage=array();
 foreach((array)($raw['coverage']??array()) as $id) {
  if(!is_scalar($id) || !ctype_digit((string)$id) || !isset($catalog[(int)$id])) {return new WP_Error('coverage','Selecciona comunas válidas del catálogo.');}
  $coverage[]=(int)$id;
 }
 $coverage=array_values(array_unique($coverage));
 $week=array();$zones=array();
 foreach(array_keys(medmovil_dias()) as $day) {
  foreach((array)($raw['d'.$day]??array()) as $hour) {
   if(!is_string($hour) || !in_array($hour,medmovil_horas(),true)) {return new WP_Error('hour','Hay un horario inválido. Revisa tu semana.');}
   $week[$day][]=$hour;
  }
  if(isset($week[$day])) {$week[$day]=array_values(array_unique($week[$day]));sort($week[$day]);}
  foreach(array('am','pm') as $period) {
   $value=$raw['zone_'.$day.'_'.$period]??'0';
   if(!is_scalar($value) || !ctype_digit((string)$value) || ((int)$value && !in_array((int)$value,$coverage,true))) {return new WP_Error('zone','Cada sector debe pertenecer a tus comunas seleccionadas.');}
   $zones[$day][$period]=(int)$value;
  }
 }
 if($week && !$coverage) {return new WP_Error('coverage','Selecciona al menos una comuna para los horarios disponibles.');}
 $duration=$raw['duration']??45;$travel=$raw['travel']??30;
 if(!is_scalar($duration)||!in_array((string)$duration,array('30','45','60','90','120'),true)||!is_scalar($travel)||!in_array((string)$travel,array('15','30','45','60','90'),true)) {return new WP_Error('time','Elige una duración y un margen de traslado válidos.');}
 $exceptions=array();$rows=$raw['exceptions']??array();
 if(!is_array($rows)||count($rows)>50) {return new WP_Error('limit','Puedes guardar hasta 50 fechas bloqueadas.');}
 foreach($rows as $row) {
  if(!is_array($row)) {return new WP_Error('date','Fecha bloqueada inválida.');}
  $date=$row['date']??'';$from=$row['from']??'00:00';$to=$row['to']??'23:59';
  if(!is_string($date)||!is_string($from)||!is_string($to)) {return new WP_Error('date','Revisa las fechas y horas bloqueadas.');}
  if($date==='') {continue;}
  $parsed=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
  if(!$parsed||$parsed->format('Y-m-d')!==$date||!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',$from)||!preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/',$to)||$from>=$to) {return new WP_Error('date','Revisa las fechas bloqueadas: la hora de término debe ser posterior al inicio.');}
  $exceptions[$date.' '.$from.' '.$to]=array('date'=>$date,'from'=>$from,'to'=>$to);
 }
 ksort($exceptions);
 return array('week'=>$week,'coverage'=>$coverage,'zones'=>$zones,'exceptions'=>array_values($exceptions),'duration'=>(int)$duration,'travel'=>(int)$travel);
}

/** Se invoca después de verificar sesión y nonce del formulario del portal. */
function medmovil_save_availability($uid,$raw) {
 if ('profesional'!==medmovil_rol() || (int)$uid!==get_current_user_id()) {return new WP_Error('forbidden','No puedes modificar la disponibilidad de otra cuenta.');}
 $catalog=medmovil_coverage_catalog();$clean=medmovil_validate_availability($raw,$catalog);
 if(is_wp_error($clean)) {return $clean;}
 update_user_meta($uid,'_mm_availability_v1',$clean);
 update_user_meta($uid,'_mm_semana',$clean['week']);
 $names=array();foreach($clean['coverage'] as $id){$names[]=$catalog[$id];}
 update_user_meta($uid,'_mm_comunas',implode("\n",$names));
 return true;
}

function medmovil_availability_exception_row($index,$row=array()) {
 ?><div class="mp-exception">
 <label>Fecha<input type="date" name="exceptions[<?php echo (int)$index; ?>][date]" value="<?php echo esc_attr($row['date']??''); ?>"></label>
 <label>Desde<input type="time" name="exceptions[<?php echo (int)$index; ?>][from]" value="<?php echo esc_attr($row['from']??'00:00'); ?>" required></label>
 <label>Hasta<input type="time" name="exceptions[<?php echo (int)$index; ?>][to]" value="<?php echo esc_attr($row['to']??'23:59'); ?>" required></label>
 <button type="button" class="pbtn" data-remove-exception aria-label="Quitar fecha bloqueada"><?php medmovil_icono('i-trash'); ?>Quitar</button>
 </div><?php
}

function medmovil_render_availability($uid) {
 $config=medmovil_availability_config($uid);$week=medmovil_disponibilidad($uid);$catalog=medmovil_coverage_catalog();
 // Conservar entradas al rechazar un formulario, sin guardarlas ni confiar en ellas.
 if(isset($GLOBALS['mm_availability_input']) && is_array($GLOBALS['mm_availability_input'])) {
  $raw=$GLOBALS['mm_availability_input'];
  $config['coverage']=array_map('intval',array_filter((array)($raw['coverage']??array()),'is_scalar'));
  foreach(array_keys(medmovil_dias()) as $day){$week[$day]=array_filter((array)($raw['d'.$day]??array()),'is_string');foreach(array('am','pm') as $period){$v=$raw['zone_'.$day.'_'.$period]??0;$config['zones'][$day][$period]=is_scalar($v)?(int)$v:0;}}
  foreach(array('duration','travel') as $key){if(isset($raw[$key])&&is_scalar($raw[$key])){$config[$key]=(int)$raw[$key];}}
  $config['exceptions']=array();foreach(array_slice((array)($raw['exceptions']??array()),0,50) as $row){if(is_array($row)){$config['exceptions'][]=array_map(static function($v){return is_string($v)?$v:'';},array_intersect_key($row,array_flip(array('date','from','to'))));}}
 }
 $legacy=array_filter(array_map('trim',preg_split('/\R/u',(string)get_user_meta($uid,'_mm_comunas',true))));
 $unmapped=array_diff($legacy,array_values($catalog));
 ?>
 <div class="mp-schedule-heading"><div><span class="mp-kicker">Organiza tu atención</span><h2>Disponibilidad y zonas</h2><p>Define cuándo trabajas y en qué comunas puedes atender.</p></div><a class="pbtn" href="<?php echo esc_url(medmovil_url_portal('agenda')); ?>"><?php medmovil_icono('i-caldays'); ?>Ver visitas asignadas</a></div>
 <form method="post" class="mp-availability" id="mpAvailability">
 <?php wp_nonce_field('mm_portal_disponibilidad','mm_nonce'); ?><input type="hidden" name="mm_accion" value="disponibilidad">
 <section class="pcard mp-schedule-panel" id="mp-semana" data-availability-panel="semana">
 <header class="pcard-h"><div><h2>Tu semana habitual</h2><p>Marca las horas de inicio en las que puedes recibir una visita.</p></div><span class="mp-selection-count" aria-live="polite"></span></header>
 <div class="pcard-b"><div class="mp-calendar-toolbar"><div class="mp-calendar-legend"><span><i class="available"></i>Disponible</span><span><i></i>No disponible</span></div><label class="mp-day-picker">Día<select id="mpCalendarDay"><?php foreach(medmovil_dias() as $day=>$name): ?><option value="<?php echo (int)$day; ?>"><?php echo esc_html(ucfirst($name)); ?></option><?php endforeach; ?></select></label></div>
 <div class="grilla-wrap"><table class="grilla mp-week-grid" data-mobile-day="1"><caption class="sr">Semana habitual: selecciona las horas disponibles de cada día</caption><thead><tr><th scope="col">Hora</th><?php foreach(medmovil_dias() as $day=>$name): ?><th scope="col" data-day="<?php echo (int)$day; ?>"><?php echo esc_html(ucfirst($name)); ?></th><?php endforeach; ?></tr></thead><tbody>
 <?php foreach(medmovil_horas() as $hour): ?><tr><th scope="row"><?php echo esc_html($hour); ?></th><?php foreach(medmovil_dias() as $day=>$name):$id='mpg'.$day.str_replace(':','',$hour); ?>
 <td data-day="<?php echo (int)$day; ?>"><input type="checkbox" id="<?php echo esc_attr($id); ?>" name="d<?php echo (int)$day; ?>[]" value="<?php echo esc_attr($hour); ?>" <?php checked(in_array($hour,$week[$day]??array(),true)); ?>><label for="<?php echo esc_attr($id); ?>"><span class="sr"><?php echo esc_html(ucfirst($name).' '.$hour); ?></span><span aria-hidden="true" class="mp-slot-mark">✓</span></label></td>
 <?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div>
 <div class="pf-acc"><button class="pbtn" type="button" id="grillaHabil">Lunes a viernes, 9 a 18</button><button class="pbtn" type="button" id="grillaLimpiar">Desmarcar todo</button></div>
 <p class="pn-legal">Esta es tu semana tipo. Las visitas confirmadas se consultan en Mi agenda; cambiar esta plantilla no las cancela.</p></div></section>
 <section class="pcard mp-schedule-panel" id="mp-zonas" data-availability-panel="zonas"><header class="pcard-h"><div><h2>Comunas y sectores</h2><p>Elige tu cobertura y distribuye tus sectores entre mañana y tarde.</p></div></header><div class="pcard-b">
 <?php if(!$catalog): ?><p class="pn-nota">Aún no hay comunas publicadas. Administración debe agregar las zonas de atención antes de habilitar horarios.</p><?php endif; ?>
 <?php if($unmapped): ?><p class="pn-nota pn-nota-warn">Estas comunas de tu perfil anterior no coinciden con el catálogo: <?php echo esc_html(implode(', ',$unmapped)); ?>. Al guardar se utilizarán únicamente las comunas seleccionadas abajo.</p><?php endif; ?>
 <div class="mp-coverage-list"><?php foreach($catalog as $id=>$name): ?><label class="mp-coverage-choice"><input type="checkbox" name="coverage[]" value="<?php echo (int)$id; ?>" data-name="<?php echo esc_attr($name); ?>" <?php checked(in_array($id,$config['coverage'],true)); ?>><?php medmovil_icono('i-pin'); ?><span><?php echo esc_html($name); ?></span></label><?php endforeach; ?></div>
 <p class="mp-zone-help">“Toda mi cobertura” permite cualquiera de tus comunas seleccionadas. Mañana: antes de las 13:00. Tarde: desde las 13:00.</p>
 <div class="mp-zone-days"><?php foreach(medmovil_dias() as $day=>$name): ?><div class="mp-zone-day"><strong><?php echo esc_html(ucfirst($name)); ?></strong><?php foreach(array('am'=>'Mañana','pm'=>'Tarde') as $period=>$label): ?><label><?php echo esc_html($label); ?><select name="zone_<?php echo (int)$day; ?>_<?php echo esc_attr($period); ?>" data-zone-select><option value="0">Toda mi cobertura</option><?php foreach($catalog as $id=>$commune): ?><option value="<?php echo (int)$id; ?>" <?php selected($config['zones'][$day][$period]??0,$id); ?>><?php echo esc_html($commune); ?></option><?php endforeach; ?></select></label><?php endforeach; ?></div><?php endforeach; ?></div>
 </div></section>
 <section class="pcard mp-schedule-panel" id="mp-excepciones" data-availability-panel="excepciones"><header class="pcard-h"><div><h2>Fechas bloqueadas</h2><p>Reserva una fecha completa o un intervalo para vacaciones, trámites o descanso.</p></div></header><div class="pcard-b"><div id="mpExceptions"><?php foreach($config['exceptions'] as $i=>$row){medmovil_availability_exception_row($i,$row);} ?></div><p id="mpNoExceptions" <?php echo $config['exceptions']?'hidden':''; ?>>Sin fechas bloqueadas. Puedes añadir hasta 50.</p><button type="button" class="pbtn" id="mpAddException"><?php medmovil_icono('i-caladd'); ?>Añadir fecha</button><template id="mpExceptionTemplate"><?php medmovil_availability_exception_row(0); ?></template><p class="pn-legal">Un bloqueo no cancela una visita ya asignada. Revisa tu agenda y coordina cualquier cambio con administración.</p></div></section>
 <section class="pcard mp-schedule-panel" id="mp-tiempos" data-availability-panel="tiempos"><header class="pcard-h"><div><h2>Tiempos de visita</h2><p>Deja espacio para atender con tranquilidad y trasladarte al siguiente domicilio.</p></div></header><div class="pcard-b"><div class="pform"><div class="pf"><label for="mpDuration">Duración habitual de atención</label><select id="mpDuration" name="duration"><?php foreach(array(30,45,60,90,120) as $n): ?><option value="<?php echo $n; ?>" <?php selected($config['duration'],$n); ?>><?php echo $n; ?> minutos</option><?php endforeach; ?></select></div><div class="pf"><label for="mpTravel">Margen de traslado entre visitas</label><select id="mpTravel" name="travel"><?php foreach(array(15,30,45,60,90) as $n): ?><option value="<?php echo $n; ?>" <?php selected($config['travel'],$n); ?>><?php echo $n; ?> minutos</option><?php endforeach; ?></select></div></div><p class="mp-time-total" aria-live="polite"></p><p class="pn-legal">Es una referencia para planificar. La duración puede variar según el examen y el traslado real.</p></div></section>
 <div class="mp-savebar"><div><strong id="mpSaveState" role="status">Tu planificación</strong><small>La asignación de reservas continúa a cargo de administración.</small></div><button class="pbtn pbtn-p" type="submit"><?php medmovil_icono('i-save'); ?>Guardar disponibilidad</button></div>
 </form><?php
}
