<?php
/**
 * Contenido inicial que se carga al activar el tema.
 *
 * Todo esto es editable desde el panel. Se carga una sola vez: si borras
 * una prestación, no vuelve a aparecer sola.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catálogo completo de prestaciones.
 *
 * @return array
 */
function medmovil_catalogo() {
	$archivos = array( 'generales', 'msk', 'doppler', 'otros' );
	$todo     = array();
	foreach ( $archivos as $archivo ) {
		$parte = include MEDMOVIL_DIR . '/inc/catalogo/' . $archivo . '.php';
		if ( is_array( $parte ) ) {
			$todo = array_merge( $todo, $parte );
		}
	}
	return $todo;
}

/**
 * Líneas de atención, en el orden en que aparecen en el sitio.
 *
 * @return array
 */
function medmovil_lineas_atencion() {
	return array(
		'Ecografías generales' => 'Abdomen, riñones, pelvis, tiroides, mama, testicular y partes blandas. La línea con más volumen y la que más se busca en Google.',
		'Musculoesquelética'   => 'Tendones, ligamentos y articulaciones vistos en movimiento. La línea que más agradece hacerse en casa, porque el paciente justamente no puede moverse bien.',
		'Doppler'             => 'Estudio del flujo sanguíneo para detectar trombosis, insuficiencia venosa y estenosis arteriales. Es la línea de mayor valor por examen.',
		'Cardiología'         => 'Electrocardiograma, Holter de ritmo y monitoreo de presión de 24 horas.',
		'Función pulmonar'    => 'Espirometría con informe médico para el estudio y control del asma y la EPOC.',
		'Radiología'          => 'Radiografía digital portátil con informe validado por médico radiólogo en 24 horas.',
		'Laboratorio'         => 'Toma de muestras y perfiles de laboratorio con resultados en línea.',
		'Enfermería'          => 'Curaciones, administración de medicamentos, control de signos vitales y manejo de sondas.',
	);
}

/**
 * Comunas de la Región Metropolitana con su nivel de cobertura.
 *
 * Zona A: donde se concentra la demanda y conviene invertir en publicidad.
 * Zona B: cobertura frecuente. Zona C: a pedido.
 *
 * @return array
 */
function medmovil_comunas_iniciales() {
	return array(
		array( 'Las Condes', 'a', 30, 'El Golf, Apoquindo, Estoril, San Damián, Los Dominicos' ),
		array( 'Vitacura', 'a', 30, 'Santa María de Manquehue, Jardín del Este, Lo Curro' ),
		array( 'Lo Barnechea', 'a', 40, 'La Dehesa, El Arrayán, Los Trapenses' ),
		array( 'Providencia', 'a', 25, 'Pedro de Valdivia, Los Leones, Bellavista, Manuel Montt' ),
		array( 'Ñuñoa', 'a', 25, 'Plaza Ñuñoa, Irarrázaval, Villa Frei, Estadio Nacional' ),
		array( 'La Reina', 'a', 30, 'Plaza Egaña, Larraín, Príncipe de Gales' ),
		array( 'Huechuraba', 'a', 35, 'Ciudad Empresarial, Pedro Fontova' ),
		array( 'Santiago', 'b', 25, 'Centro, Barrio Brasil, República, Parque Almagro' ),
		array( 'Macul', 'b', 30, 'Macul con Grecia, Villa Macul, Los Presidentes' ),
		array( 'Peñalolén', 'b', 35, 'Tobalaba, Grecia, Peñalolén Alto' ),
		array( 'La Florida', 'b', 35, 'Vicuña Mackenna, Walker Martínez, Rojas Magallanes' ),
		array( 'Maipú', 'b', 40, 'Plaza de Maipú, Pajaritos, Ciudad Satélite' ),
		array( 'Quilicura', 'b', 40, 'Lo Campino, Valle Lo Campino, San Ignacio' ),
		array( 'San Miguel', 'b', 30, 'Gran Avenida, Ciudad del Niño, El Llano' ),
		array( 'Puente Alto', 'b', 45, 'Concha y Toro, Bajos de Mena, Las Vizcachas' ),
		array( 'Estación Central', 'b', 30, 'Alameda, Meiggs, Ecuador' ),
		array( 'Recoleta', 'b', 30, 'Patronato, Einstein, Cerro Blanco' ),
		array( 'Independencia', 'b', 30, 'Hipódromo, Vivaceta, Barrio Universitario' ),
		array( 'San Bernardo', 'b', 45, 'Centro, Nos, Lo Herrera' ),
		array( 'Pudahuel', 'b', 40, 'Barrancas, Pudahuel Sur, Ciudad de los Valles' ),
		array( 'Colina', 'b', 50, 'Chicureo, Piedra Roja, Santa Elena' ),
		array( 'La Cisterna', 'b', 35, 'Gran Avenida, El Parrón' ),
		array( 'San Joaquín', 'b', 30, 'Pedro Alarcón, Legua' ),
		array( 'Cerrillos', 'c', 40, 'Ciudad Parque Bicentenario, Lo Errázuriz' ),
		array( 'Cerro Navia', 'c', 40, 'Mapocho, Neptuno' ),
		array( 'Conchalí', 'c', 35, 'Independencia norte, Dorsal' ),
		array( 'El Bosque', 'c', 40, 'Gran Avenida sur, Santa Rosa' ),
		array( 'La Granja', 'c', 35, 'Santa Rosa, San Gregorio' ),
		array( 'La Pintana', 'c', 45, 'Santo Tomás, El Castillo' ),
		array( 'Lo Espejo', 'c', 40, 'José María Caro, Clotario Blest' ),
		array( 'Lo Prado', 'c', 38, 'San Pablo, Neptuno' ),
		array( 'Pedro Aguirre Cerda', 'c', 35, 'Departamental, Club Hípico' ),
		array( 'Quinta Normal', 'c', 33, 'Yungay, Matucana, Carrascal' ),
		array( 'Renca', 'c', 38, 'Domingo Santa María, Huamachuco' ),
		array( 'San Ramón', 'c', 38, 'Santa Rosa, La Bandera' ),
		array( 'Padre Hurtado', 'c', 55, 'Centro, Santa Rosa de Chena' ),
		array( 'Peñaflor', 'c', 60, 'Centro, Malloco' ),
		array( 'Talagante', 'c', 65, 'Centro, Isla de Maipo' ),
		array( 'Buin', 'c', 60, 'Centro, Maipo, Alto Jahuel' ),
		array( 'Melipilla', 'c', 75, 'Centro, Pomaire' ),
	);
}

/**
 * Packs de chequeo.
 *
 * @return array
 */
function medmovil_packs_iniciales() {
	return array(
		array(
			'titulo'   => 'Chequeo integral del adulto mayor',
			'etiqueta' => 'Adulto mayor',
			'precio'   => 168000,
			'antes'    => 195000,
			'destacado' => '',
			'incluye'  => "Ecografía abdominal completa\nElectrocardiograma de reposo con informe\nToma de muestras y perfil bioquímico\nControl de signos vitales y evaluación de enfermería",
			'texto'    => 'Todo lo que el médico suele pedir en el control anual de un adulto mayor, resuelto en una sola visita y sin mover al paciente de su casa. Especialmente pensado para quien vive con movilidad reducida o en un centro de reposo.',
		),
		array(
			'titulo'   => 'Control cardiovascular completo',
			'etiqueta' => 'Más solicitado',
			'precio'   => 205000,
			'antes'    => 240000,
			'destacado' => '1',
			'incluye'  => "Doppler venoso de extremidades inferiores\nElectrocardiograma de reposo con informe\nMonitoreo de presión arterial de 24 horas\nPerfil lipídico completo",
			'texto'    => 'El panel que pide el cardiólogo cuando quiere ver el cuadro completo: circulación de las piernas, ritmo cardíaco, presión real de 24 horas y colesterol. Se instala todo en una visita y se retira al día siguiente.',
		),
		array(
			'titulo'   => 'Control del embarazo en casa',
			'etiqueta' => 'Embarazo',
			'precio'   => 142000,
			'antes'    => 165000,
			'destacado' => '',
			'incluye'  => "Ecografía obstétrica de control\nToma de muestras de laboratorio\nControl de presión arterial y signos vitales\nInforme el mismo día",
			'texto'    => 'Para embarazos con reposo indicado o para quien no quiere pasar la mañana en una sala de espera con otros niños en casa. Es una ecografía de control, no reemplaza los tamizajes de las semanas 11-14 y 20-24.',
		),
		array(
			'titulo'   => 'Chequeo musculoesquelético',
			'etiqueta' => 'Dolor articular',
			'precio'   => 148000,
			'antes'    => 175000,
			'destacado' => '',
			'incluye'  => "Dos ecografías musculoesqueléticas de la zona que indique tu médico\nRadiografía del segmento correspondiente\nInforme radiológico consolidado",
			'texto'    => 'Cuando el traumatólogo pide ecografía y radiografía de la misma zona, hacerlo en una sola visita ahorra un traslado completo y permite que el radiólogo informe ambos estudios en conjunto.',
		),
	);
}

/**
 * Testimonios iniciales.
 *
 * @return array
 */
function medmovil_testimonios_iniciales() {
	return array(
		array( 'Carolina M.', 'La Florida · Ecografía abdominal', 'Mi mamá tiene 84 años y sacarla de la casa para una ecografía era todo un operativo. Vinieron, la atendieron en su pieza y el informe llegó al día siguiente. No volvemos a hacerlo de otra forma.' ),
		array( 'Rodrigo S.', 'Ñuñoa · Ecografía de muñeca', 'Me operaron de la muñeca y no podía manejar. Agendé un domingo por la noche y el martes ya estaba el tecnólogo en la casa con el ecógrafo. Puntual y muy claro para explicar.' ),
		array( 'Paula V.', 'Convenio institucional · Providencia', 'Trabajo en un centro de reposo y coordinamos ocho exámenes en una mañana. Llegaron con todo el equipo y nos dejaron los informes cargados el mismo día. Un cambio enorme para nosotros.' ),
	);
}

/**
 * Alianzas iniciales.
 *
 * @return array
 */
function medmovil_aliados_iniciales() {
	return array(
		array( 'Abrazzo', 'Acompañamiento y asistencia al paciente en domicilio.', 'aliado-abrazzo.webp' ),
		array( 'Enfermería en Casa', 'Cuidados de enfermería, curaciones y control domiciliario.', 'aliado-enfermeria.webp' ),
	);
}

/**
 * Páginas que se crean al activar.
 *
 * @return array
 */
function medmovil_paginas_iniciales() {
	return array(
		'reservar' => array(
			'titulo' => 'Solicitar hora',
			'plantilla' => 'template-reserva.php',
			'contenido' => '',
		),
		'cobertura' => array(
			'titulo'    => 'Cobertura',
			'plantilla' => 'template-cobertura.php',
			'contenido' => 'Atendemos en toda la Región Metropolitana. Busca tu comuna para ver el tiempo estimado de llegada y las prestaciones disponibles.',
		),
		'precios' => array(
			'titulo'    => 'Precios',
			'plantilla' => 'template-precios.php',
			'contenido' => 'Todos nuestros valores particulares, publicados. El precio incluye la visita, el examen y el informe firmado por médico radiólogo, sin cobro de traslado dentro de la Región Metropolitana.',
		),
		'cotizador' => array(
			'titulo'    => 'Cotizador',
			'plantilla' => 'template-cotizador.php',
			'contenido' => 'Arma tu presupuesto marcando los exámenes que necesitas. Puedes buscarlos por nombre, por zona del cuerpo o escribiendo el código que trae tu orden médica.',
		),
		'portal' => array(
			'titulo'    => 'Mi cuenta',
			'plantilla' => 'template-portal.php',
			'contenido' => '',
		),
		'blog' => array(
			'titulo'    => 'Blog',
			'plantilla' => 'template-blog.php',
			'contenido' => 'Guías escritas por tecnólogos médicos sobre exámenes a domicilio, preparación, resultados y cuidado de adultos mayores en casa.',
		),
		'convenios' => array(
			'titulo'    => 'Convenios institucionales',
			'plantilla' => 'template-convenios.php',
			'contenido' => 'Para ELEAM, centros de reposo, consultorios y empresas: visitas programadas para varios pacientes en una misma jornada, tarifas por volumen e informes centralizados.',
		),
		'como-funciona' => array(
			'titulo'    => 'Cómo funciona',
			'plantilla' => 'template-como-funciona.php',
			'contenido' => "Eliges tu examen. Buscas la prestación y ves de inmediato el precio, la duración y la preparación que necesita.\n\nReservas tu hora. Escoges día y bloque horario según la disponibilidad real del equipo en tu comuna.\n\nLlegamos a tu domicilio. El profesional llega con el equipo portátil y realiza el examen en tu casa. Pagas al momento: efectivo, transferencia o tarjeta.\n\nRecibes tu informe. En menos de 24 horas queda disponible en PDF, firmado por médico radiólogo.",
		),
		'nosotros' => array(
			'titulo'    => 'Nuestro equipo',
			'plantilla' => '',
			'contenido' => 'Somos profesionales de la salud con registro vigente en el Registro Nacional de Prestadores Individuales de la Superintendencia de Salud. Trabajamos con equipos digitales portátiles de última generación y todos nuestros informes de imagen son validados por médico radiólogo.',
		),
		'contacto' => array(
			'titulo'    => 'Contacto',
			'plantilla' => '',
			'contenido' => 'Escríbenos por WhatsApp y te respondemos el mismo día. También puedes agendar directamente desde la página de cada examen.',
		),
		'privacidad' => array(
			'titulo'    => 'Política de privacidad',
			'plantilla' => '',
			'contenido' => "Este documento es un borrador de referencia y debe ser revisado por un abogado antes de publicarse.\n\nMedMóvil trata datos personales y datos sensibles de salud con la finalidad de prestar servicios de salud a domicilio. Los datos se almacenan de forma cifrada, el acceso está restringido al profesional que realiza la atención, y no se comparten con terceros salvo obligación legal o consentimiento expreso del titular.\n\nEl titular puede solicitar en cualquier momento el acceso, la rectificación, la eliminación y la portabilidad de sus datos escribiendo al correo de contacto publicado en este sitio.",
		),
	);
}
