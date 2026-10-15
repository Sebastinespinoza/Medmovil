<?php
/**
 * Catálogo — Cardiología, función pulmonar, radiología, laboratorio y enfermería.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	/* ---------------- Cardiología ---------------- */

	array(
		'titulo'   => 'Electrocardiograma de reposo',
		'linea'    => 'Cardiología',
		'icono'    => 'il-cardio',
		'precio'   => 45000,
		'duracion' => 20,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Registro de 12 derivaciones con informe médico, el examen básico del corazón, hecho en tu casa en 20 minutos.',
		'contenido' => 'El electrocardiograma registra la actividad eléctrica del corazón en 12 derivaciones. Detecta arritmias, signos de isquemia, crecimiento de cavidades y trastornos de conducción. Es el examen preoperatorio más pedido y el primer estudio cardiológico de cualquier control.

Toma veinte minutos, no duele y no requiere ninguna preparación. Es, probablemente, el examen que menos justifica un traslado.',
		'incluye'  => "Visita del profesional a tu domicilio\nRegistro de 12 derivaciones\nTrazado impreso y digital\nInforme de médico\nPDF en tu portal de paciente",
		'ventajas' => "Es un examen de 20 minutos | No tiene ningún sentido perder media mañana en traslados por un examen tan breve.\nPreoperatorio sin vueltas | Si te lo pidieron antes de una cirugía, resolverlo en casa la misma semana evita postergar la operación.\nSin el estrés del traslado | Llegar apurado altera la frecuencia cardíaca. En reposo real, el trazado es más representativo.",
		'condiciones' => "Preoperatorio | Evaluación previa a cirugía | El examen más solicitado antes de cualquier operación con anestesia.\nArritmia | Palpitaciones | Sensación de que el corazón se acelera o salta latidos.\nDolor | Dolor de pecho | Estudio inicial ante molestias torácicas, siempre junto a evaluación médica.\nControl | Control de hipertensión | Para ver si el corazón muestra signos de sobrecarga.\nMareos | Mareos y desmayos | Búsqueda de trastornos del ritmo o de la conducción.",
		'preparacion' => 'Ninguna. No requiere ayuno ni suspender medicamentos. Usa ropa fácil de abrir por delante y evita cremas en el pecho ese día. Si eres hombre con mucho vello en el tórax, puede ser necesario rasurar pequeñas zonas para que los electrodos peguen bien.',
		'faq'      => "¿Duele? | No. Solo se pegan electrodos adhesivos en el pecho, los brazos y las piernas. No pasa corriente: el equipo solo registra.\n¿Debo suspender mis remedios del corazón? | No, salvo indicación expresa de tu médico. De hecho, el examen sirve justamente para ver cómo está el corazón con el tratamiento.\n¿Un ECG normal descarta un infarto? | No. Un electrocardiograma normal no descarta enfermedad coronaria, y si tienes dolor de pecho ahora, eso se evalúa en urgencia, no agendando un examen.\n¿Sirve para el preoperatorio? | Sí, es exactamente el examen que se pide, y el informe médico va incluido.",
	),

	array(
		'titulo'   => 'Holter de ritmo de 24 horas',
		'linea'    => 'Cardiología',
		'icono'    => 'il-cardio',
		'precio'   => 98000,
		'duracion' => 30,
		'edad'     => 'Desde los 5 años',
		'resumen'  => 'Registro continuo del ritmo cardíaco durante 24 horas, con instalación y retiro en tu casa.',
		'contenido' => 'El Holter graba el ritmo cardíaco de forma continua durante 24 horas mientras haces tu vida normal. Detecta arritmias que aparecen y desaparecen, y que por lo tanto nunca se ven en un electrocardiograma de veinte minutos.

Se instala en una visita y se retira en otra al día siguiente. Ambas visitas van incluidas en el valor, y ambas se hacen en tu domicilio.',
		'incluye'  => "Visita de instalación en tu domicilio\nEquipo Holter por 24 horas\nVisita de retiro al día siguiente\nAnálisis del registro completo e informe médico\nPDF en tu portal de paciente",
		'ventajas' => "Dos traslados menos, no uno | El Holter exige ir a instalarlo y volver a retirarlo. A domicilio se ahorran ambos viajes.\nEl registro refleja tu día real | La idea es grabar tu vida normal. Si el día empieza con un traslado estresante, el registro empieza distorsionado.\nIdeal para adulto mayor | Que un paciente mayor tenga que ir dos veces en dos días es la principal razón por la que este examen se posterga.",
		'condiciones' => "Palpitaciones | Palpitaciones intermitentes | Cuando el corazón se acelera de vez en cuando y el ECG siempre sale normal.\nSíncope | Desmayos y mareos | Búsqueda de pausas o arritmias que expliquen la pérdida de conciencia.\nFibrilación | Control de fibrilación auricular | Para ver la frecuencia promedio y si el tratamiento la está controlando.\nMarcapasos | Control de marcapasos | Evaluación del funcionamiento del dispositivo.\nExtrasístoles | Sensación de latidos saltados | Cuantifica cuántas extrasístoles hay realmente en el día.",
		'preparacion' => 'Ninguna especial, pero organiza tu día: no podrás bañarte con el equipo puesto durante las 24 horas. Báñate justo antes de la instalación. Usa ropa holgada, y ten a mano un cuaderno o el teléfono para anotar la hora en que sientas síntomas: esa anotación es clave para interpretar el registro.',
		'faq'      => "¿Puedo bañarme con el Holter? | No. El equipo no puede mojarse. Por eso conviene ducharse antes de la instalación.\n¿Puedo hacer mi vida normal? | Sí, y es lo que se espera. Trabaja, camina, sube escaleras. Solo evita ejercicio intenso que despegue los electrodos.\n¿Para qué sirve anotar los síntomas? | Para que el médico compare la hora exacta en que sentiste algo con lo que el equipo registró en ese minuto. Es lo que le da valor al examen.\n¿El retiro también es a domicilio? | Sí, y va incluido en el precio. Coordinamos la hora al momento de instalar.",
	),

	array(
		'titulo'   => 'Monitoreo de presión arterial de 24 horas',
		'linea'    => 'Cardiología',
		'icono'    => 'il-clockx',
		'precio'   => 92000,
		'duracion' => 30,
		'edad'     => 'Desde los 12 años',
		'resumen'  => 'Registro automático de la presión durante un día completo, incluida la noche, para saber cómo está de verdad tu presión.',
		'contenido' => 'El monitoreo ambulatorio de presión arterial, o MAPA, mide la presión de forma automática cada 15 a 30 minutos durante 24 horas, incluyendo el período de sueño. Es el examen que distingue la hipertensión real de la hipertensión de delantal blanco, esa que solo sube cuando estás frente al médico.

También detecta la hipertensión nocturna, que no se ve de ninguna otra forma y que es un factor de riesgo importante por sí sola.',
		'incluye'  => "Visita de instalación en tu domicilio\nEquipo de monitoreo por 24 horas\nVisita de retiro al día siguiente\nInforme médico con promedios de día, noche y carga hipertensiva\nPDF en tu portal de paciente",
		'ventajas' => "Justamente se trata de tu día normal | El examen mide tu presión en tu vida real. Empezarlo después de un traslado estresante contamina el resultado.\nDos visitas resueltas en casa | Instalación y retiro, sin ir dos veces a ninguna parte.\nDescarta el efecto delantal blanco | Y ese efecto empieza en la puerta del centro médico.",
		'condiciones' => "Delantal blanco | Presión alta solo en la consulta | Para saber si la hipertensión es real o solo una reacción al ambiente médico.\nControl | Ajuste de tratamiento antihipertensivo | Para ver si los medicamentos están cubriendo las 24 horas.\nNocturna | Hipertensión nocturna | La presión que no baja de noche es un factor de riesgo que solo este examen detecta.\nResistente | Hipertensión de difícil manejo | Cuando la presión no se controla con tres o más fármacos.\nEmbarazo | Control de presión en el embarazo | Seguimiento de hipertensión gestacional.",
		'preparacion' => 'Ninguna. Sigue tomando tus medicamentos igual que siempre, salvo indicación distinta de tu médico: el examen busca ver cómo está tu presión con el tratamiento que usas. Usa ropa de manga suelta, porque el manguito queda bajo la manga. Anota la hora en que te acuestas y en que te levantas.',
		'faq'      => "¿Debo suspender mis remedios de la presión? | No, salvo que tu médico lo indique. Se busca evaluar tu presión con el tratamiento actual.\n¿Me va a despertar de noche? | El manguito se infla también mientras duermes, y algunas personas lo notan. Se programa con menor frecuencia nocturna justamente por eso.\n¿Puedo bañarme? | No durante las 24 horas de registro. Báñate antes de la instalación.\n¿Qué es la carga hipertensiva? | El porcentaje de mediciones que salieron sobre el límite normal. Es uno de los datos más importantes del informe.",
	),

	/* ---------------- Función pulmonar ---------------- */

	array(
		'titulo'   => 'Espirometría',
		'linea'    => 'Función pulmonar',
		'icono'    => 'il-lung',
		'precio'   => 68000,
		'duracion' => 30,
		'edad'     => 'Desde los 6 años',
		'resumen'  => 'Medición de la capacidad pulmonar con informe médico, en el estudio y control del asma y la EPOC.',
		'contenido' => 'La espirometría mide cuánto aire puedes botar y a qué velocidad. Es el examen que confirma el diagnóstico de asma y de enfermedad pulmonar obstructiva crónica, y el que mide si el tratamiento está funcionando.

El examen exige soplar con toda la fuerza varias veces, guiado por el profesional. Cuando se indica, se repite después de administrar un broncodilatador para ver si la obstrucción revierte.',
		'incluye'  => "Visita del profesional a tu domicilio\nMediciones basales según protocolo\nPrueba con broncodilatador si la orden lo indica\nInforme médico con curvas e interpretación\nPDF en tu portal de paciente",
		'ventajas' => "Sin exponerse en sala de espera | Un paciente con enfermedad pulmonar crónica es justamente quien menos debería compartir aire en un box lleno.\nSin llegar agitado | El examen mide tu capacidad respiratoria. Llegar después de caminar y subir escaleras la altera.\nCon oxígeno domiciliario | Los pacientes con oxígeno permanente tienen enormes dificultades para trasladarse.",
		'condiciones' => "Asma | Diagnóstico y control del asma | Confirma la obstrucción y si revierte con broncodilatador.\nEPOC | Enfermedad pulmonar obstructiva crónica | Es el examen que define el diagnóstico y la etapa.\nTos | Tos crónica | Estudio de tos que lleva más de ocho semanas.\nDisnea | Falta de aire | Evaluación de la capacidad pulmonar ante ahogo con el esfuerzo.\nPreoperatorio | Evaluación previa a cirugía | En pacientes fumadores o con enfermedad pulmonar conocida.\nLaboral | Control de exposición laboral | Seguimiento en trabajos con exposición a polvo o químicos.",
		'preparacion' => 'No fumes las 6 horas previas. No hagas ejercicio intenso 30 minutos antes. No comas en abundancia justo antes. Sobre los broncodilatadores: consulta con tu médico, porque a veces hay que suspenderlos y a veces no, según lo que se quiera medir. Usa ropa que no apriete el pecho ni la cintura.',
		'faq'      => "¿Debo suspender mis inhaladores? | Depende de lo que busque tu médico. Si quiere ver si hay reversibilidad, sí; si quiere ver tu situación con tratamiento, no. Pregúntale y avísanos al agendar.\n¿Es cansador? | Hay que soplar con fuerza varias veces y puede dar algo de mareo o tos. Se descansa entre cada intento.\n¿Por qué se repite tantas veces? | Porque se necesitan al menos tres maniobras técnicamente correctas y reproducibles. Es un requisito del protocolo.\n¿Sirve para niños? | Desde los 6 años, cuando ya entienden la instrucción de soplar fuerte y seguido.",
	),

	/* ---------------- Radiología ---------------- */

	array(
		'titulo'   => 'Radiografía de tórax',
		'linea'    => 'Radiología',
		'icono'    => 'il-rx',
		'precio'   => 78000,
		'duracion' => 25,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Radiografía digital portátil de tórax, de frente y perfil, con informe validado por médico radiólogo en 24 horas.',
		'contenido' => 'La radiografía de tórax es el examen de imagen más solicitado del mundo. Muestra los pulmones, el corazón, las costillas y el diafragma, y es la puerta de entrada al estudio de casi cualquier síntoma respiratorio.

Se realiza con equipo digital portátil, que produce imágenes de calidad diagnóstica al instante, y el informe lo valida un médico radiólogo. Todo el procedimiento se hace respetando las medidas de protección radiológica correspondientes.',
		'incluye'  => "Visita del profesional a tu domicilio con equipo digital portátil\nProyecciones de frente y perfil según indicación\nImágenes digitales de calidad diagnóstica\nInforme validado por médico radiólogo\nResultados en menos de 24 horas",
		'ventajas' => "Para el paciente que no se puede mover | Un paciente postrado o con oxígeno requiere ambulancia para ir a un centro. El equipo portátil elimina eso.\nSin esperar en un servicio saturado | Las salas de rayos de los centros son de las más congestionadas.\nInforme radiológico incluido | No es solo la imagen: el informe lo firma un médico radiólogo.",
		'condiciones' => "Tos | Tos persistente o con fiebre | Estudio inicial de neumonía y bronquitis.\nDisnea | Falta de aire | Evaluación de los pulmones y del tamaño del corazón.\nPreoperatorio | Evaluación previa a cirugía | Junto al electrocardiograma, el examen más pedido antes de operar.\nControl | Control de enfermedad pulmonar crónica | Seguimiento en EPOC, fibrosis y secuelas.\nTrauma | Después de un golpe en el tórax | Descarte de fractura costal y de neumotórax.\nCorazón | Evaluación de insuficiencia cardíaca | Tamaño cardíaco y signos de congestión.",
		'preparacion' => 'Ninguna. Quítate cadenas, collares y todo lo que tenga metal a la altura del pecho, incluidos los sostenes con aro. Avisa si estás embarazada o crees que podrías estarlo. Ten disponible un espacio despejado donde el profesional pueda posicionar el equipo.',
		'faq'      => "¿Es seguro hacer radiografías en la casa? | Sí, con las medidas de protección radiológica que corresponden. El profesional despeja la zona durante el disparo y usa el equipamiento de protección que exige la normativa.\n¿Puedo hacérmela estando embarazada? | Se evita salvo que sea estrictamente necesario y con protección abdominal. Avisa siempre si hay posibilidad de embarazo.\n¿Cuándo llega el informe? | En menos de 24 horas, firmado por médico radiólogo, directo a tu portal.\n¿Necesito orden médica? | Sí, y debe especificar la proyección solicitada.",
	),

	array(
		'titulo'   => 'Radiografía de columna',
		'linea'    => 'Radiología',
		'icono'    => 'il-rx',
		'precio'   => 88000,
		'duracion' => 30,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Radiografía digital de columna cervical, dorsal o lumbar, con informe de médico radiólogo.',
		'contenido' => 'La radiografía de columna evalúa la alineación de las vértebras, la altura de los espacios entre ellas y la presencia de signos degenerativos, fracturas o desviaciones. Se realiza sobre el segmento que indique la orden médica: cervical, dorsal o lumbar.

Es el estudio inicial del dolor de espalda persistente y el que permite decidir si hace falta un examen más complejo.',
		'incluye'  => "Visita del profesional a tu domicilio con equipo digital portátil\nProyecciones del segmento indicado\nImágenes digitales de calidad diagnóstica\nInforme validado por médico radiólogo\nResultados en menos de 24 horas",
		'ventajas' => "Con dolor de espalda, moverse es el problema | El traslado en auto o micro con lumbago agudo es exactamente lo que hay que evitar.\nSin camillas ni pasillos | El posicionamiento se hace en tu propia cama o de pie en tu living.\nPara el adulto mayor con dolor | Las fracturas vertebrales por osteoporosis son frecuentes y trasladarse duele mucho.",
		'condiciones' => "Lumbago | Dolor lumbar persistente | Cuando el dolor lleva semanas o no responde al tratamiento.\nCervical | Dolor de cuello | Después de un accidente o por dolor crónico.\nEscoliosis | Desviación de la columna | Medición del ángulo de curvatura y control de su evolución.\nOsteoporosis | Fracturas vertebrales | Muy frecuentes en adultos mayores, a veces sin un golpe evidente.\nCiática | Dolor irradiado a la pierna | Estudio inicial antes de decidir una resonancia.\nControl | Seguimiento post operatorio | Control de material de osteosíntesis y de fusiones.",
		'preparacion' => 'Ninguna para columna cervical y dorsal. Para columna lumbar, conviene evitar comidas que produzcan gases el día anterior, porque el gas intestinal puede restar nitidez. Quítate todo lo metálico: cinturones, broches, botones metálicos y piercings de la zona. Avisa si estás o podrías estar embarazada.',
		'faq'      => "¿Ve las hernias del disco? | No directamente. La radiografía muestra el hueso y los espacios; la hernia se ve en resonancia. Pero la radiografía es el primer paso y muchas veces basta.\n¿Se puede hacer de pie? | Sí, y para escoliosis es lo que corresponde, porque la curva se evalúa con el peso del cuerpo.\n¿Cuánta radiación implica? | Es una dosis baja y controlada. El profesional usa la técnica mínima necesaria para obtener una imagen diagnóstica.\n¿Debo estar en ayuno? | No.",
	),

	array(
		'titulo'   => 'Radiografía de pelvis',
		'linea'    => 'Radiología',
		'icono'    => 'il-rx',
		'precio'   => 82000,
		'duracion' => 25,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Radiografía digital de pelvis y ambas caderas, en el estudio de artrosis, dolor y control post operatorio.',
		'contenido' => 'La radiografía de pelvis muestra ambas caderas, el sacro y las articulaciones sacroilíacas. Es el examen que define el grado de artrosis de cadera, midiendo el espacio articular y buscando osteofitos y esclerosis del hueso.

También es el examen de control después de una prótesis de cadera y el estudio inicial ante una caída en el adulto mayor.',
		'incluye'  => "Visita del profesional a tu domicilio con equipo digital portátil\nProyección de pelvis con ambas caderas\nImágenes digitales de calidad diagnóstica\nInforme validado por médico radiólogo\nResultados en menos de 24 horas",
		'ventajas' => "Después de una caída | Trasladar a un adulto mayor que se cayó, sin saber si hay fractura, es riesgoso. El equipo portátil resuelve eso en su cama.\nCon prótesis de cadera | Los controles son periódicos y el paciente muchas veces camina con dificultad.\nSin cambiarse de ropa en un box | El posicionamiento se hace en su propia cama.",
		'condiciones' => "Artrosis | Artrosis de cadera | Es el examen que mide el grado y define si corresponde una prótesis.\nCaída | Sospecha de fractura de cadera | En el adulto mayor, después de una caída, aunque pueda mover la pierna.\nPrótesis | Control de prótesis de cadera | Seguimiento del implante y de su fijación.\nSacroilíacas | Dolor sacroilíaco | Estudio de espondiloartropatías.\nNiños | Displasia de cadera sobre los 6 meses | Cuando el bebé ya osificó y la ecografía dejó de servir.",
		'preparacion' => 'Ninguna. Quítate cinturones, broches y ropa con metal en la zona. Avisa siempre si estás o podrías estar embarazada. Ten a mano un espacio donde el paciente pueda estar acostado boca arriba con las piernas estiradas.',
		'faq'      => "¿Sirve para ver fractura de cadera? | Sí, es el examen de primera línea. Si la radiografía es normal pero la sospecha clínica es alta, el médico puede pedir un escáner o una resonancia.\n¿Se ven las dos caderas? | Sí, la proyección estándar incluye ambas, lo que permite comparar.\n¿Reemplaza a la resonancia? | Para artrosis y fracturas evidentes, sí. Para lesiones del labrum o necrosis inicial se necesita resonancia.\n¿Es el mismo examen que la ecografía de cadera del lactante? | No. En bebés menores de 6 meses se usa ecografía; después de esa edad, radiografía.",
	),

	array(
		'titulo'   => 'Radiografía de extremidades',
		'linea'    => 'Radiología',
		'icono'    => 'il-rx',
		'precio'   => 75000,
		'duracion' => 25,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Radiografía digital de brazo, antebrazo, mano, muslo, pierna, tobillo o pie, con informe de médico radiólogo.',
		'contenido' => 'La radiografía de extremidades estudia el hueso: fracturas, luxaciones, artrosis, calcificaciones y control de material quirúrgico. Se realiza sobre el segmento que indique la orden, con las proyecciones que corresponda.

Es el examen de control obligado después de un yeso o una cirugía traumatológica, y en esos casos el paciente casi siempre tiene dificultad para desplazarse.',
		'incluye'  => "Visita del profesional a tu domicilio con equipo digital portátil\nProyecciones del segmento indicado\nImágenes digitales de calidad diagnóstica\nInforme validado por médico radiólogo\nResultados en menos de 24 horas",
		'ventajas' => "Con yeso o muletas | Es justo el paciente al que más le cuesta llegar a un centro.\nControl post operatorio sin ambulancia | Después de una cirugía de cadera o rodilla, el primer control radiológico es el traslado más difícil.\nSin esperas de pie | Con una extremidad lesionada, cada minuto de pie cuenta.",
		'condiciones' => "Fractura | Sospecha de fractura | Después de una caída o un golpe.\nControl | Control de consolidación | Para ver si la fractura está soldando bien.\nCirugía | Control de material de osteosíntesis | Placas, tornillos y clavos endomedulares.\nArtrosis | Artrosis de rodilla o de mano | Medición del espacio articular y de los osteofitos.\nCuerpo extraño | Búsqueda de cuerpos extraños | Vidrio o metal bajo la piel.\nGota | Estudio de artropatías | Cambios óseos en gota y artritis de larga evolución.",
		'preparacion' => 'Ninguna. Quítate anillos, pulseras y relojes de la zona a estudiar. Si tienes yeso, no lo retires: la radiografía se toma con el yeso puesto. Avisa si estás o podrías estar embarazada.',
		'faq'      => "¿Se puede tomar con yeso puesto? | Sí, y de hecho el control de consolidación se hace justamente con el yeso.\n¿Cuántas proyecciones incluye? | Las que indique la orden médica, habitualmente dos: de frente y de perfil.\n¿Sirve para ver un esguince? | Muestra el hueso y sirve para descartar fractura. El ligamento en sí se ve con ecografía o resonancia.\n¿Cuándo llega el informe? | En menos de 24 horas, firmado por médico radiólogo.",
	),

	/* ---------------- Laboratorio ---------------- */

	array(
		'titulo'   => 'Toma de muestras a domicilio',
		'linea'    => 'Laboratorio',
		'icono'    => 'il-lab',
		'precio'   => 25000,
		'duracion' => 15,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Toma de muestras de sangre y orina en tu casa, con resultados en línea. El valor de los exámenes se cotiza aparte.',
		'contenido' => 'La toma de muestras a domicilio evita el peor momento de cualquier examen de laboratorio: llegar en ayunas a un centro, esperar de pie y salir mareado. El profesional llega a tu casa, toma la muestra y la traslada al laboratorio en las condiciones que corresponden.

El valor publicado corresponde a la visita y la toma. Los exámenes propiamente tales se cotizan según lo que indique tu orden, y te confirmamos el total antes de agendar.',
		'incluye'  => "Visita del profesional a tu domicilio\nToma de muestra de sangre y orina\nMaterial estéril de un solo uso\nTraslado de la muestra al laboratorio en cadena de frío cuando corresponde\nResultados en línea, avisados a tu correo",
		'ventajas' => "El ayuno se termina en tu cocina | Puedes desayunar apenas termina la toma, en vez de aguantar el viaje de vuelta.\nSin desmayos en la sala de espera | Muchas lipotimias ocurren justamente por el ayuno más la espera de pie.\nPara adultos mayores y niños | El paciente está en su ambiente, tranquilo, y eso hace toda la diferencia con una guagua o con alguien con demencia.",
		'condiciones' => "Rutina | Chequeo anual | Perfil bioquímico, hemograma, lípidos y función tiroidea.\nCrónico | Control de diabetes e hipertensión | Hemoglobina glicosilada, función renal y electrolitos.\nPostrado | Paciente en cama | Controles periódicos que de otra forma exigen ambulancia.\nTratamiento | Control de anticoagulantes | INR y otros controles de tratamiento.\nPreoperatorio | Exámenes previos a cirugía | Todo el panel preoperatorio en una sola visita.",
		'preparacion' => 'Ayuno de 8 a 12 horas para la mayoría de los perfiles. Puedes tomar agua. Si tomas medicamentos, consulta con tu médico si debes tomarlos antes o después de la muestra. Ten a mano tu orden médica y, si el examen incluye orina, un frasco estéril; si no lo tienes, avísanos al agendar y lo llevamos.',
		'faq'      => "¿El precio incluye los exámenes? | No. El valor publicado es la visita y la toma de muestra. Los exámenes se cotizan según tu orden y te confirmamos el total antes de que agendes.\n¿Cuándo llegan los resultados? | La mayoría en 24 a 48 horas, avisados a tu correo. Algunos exámenes especializados demoran más.\n¿Y si soy difícil de puncionar? | Avísanos al agendar. Se toma el tiempo necesario y se usan técnicas para venas complicadas.\n¿Toman muestras a niños? | Sí, desde lactantes, con el acompañamiento del cuidador.",
	),

	array(
		'titulo'   => 'Perfil bioquímico y hemograma',
		'linea'    => 'Laboratorio',
		'icono'    => 'il-lab',
		'precio'   => 52000,
		'duracion' => 15,
		'edad'     => 'Todas las edades',
		'resumen'  => 'El panel básico de control: glicemia, función renal, hepática, electrolitos y hemograma completo, con la toma en tu casa.',
		'contenido' => 'El perfil bioquímico entrega una fotografía general del funcionamiento del organismo: azúcar en la sangre, función del riñón, enzimas del hígado, proteínas, calcio y ácido úrico. Junto al hemograma, es el paquete de control anual más pedido.

El valor incluye la visita, la toma de muestra y los exámenes del panel.',
		'incluye'  => "Visita del profesional a tu domicilio\nToma de muestra de sangre\nPerfil bioquímico completo\nHemograma con recuento y fórmula\nResultados en línea en 24 a 48 horas",
		'ventajas' => "Todo en una visita | Se combina con ecografía o electrocardiograma el mismo día y a la misma hora.\nEl ayuno se hace en casa | Y se rompe apenas termina la toma.\nControl anual que efectivamente se hace | La barrera del traslado en ayunas es la razón número uno por la que la gente posterga su chequeo.",
		'condiciones' => "Chequeo | Control anual de salud | El panel base de cualquier chequeo preventivo.\nDiabetes | Control de glicemia | Seguimiento en diabéticos y prediabéticos.\nRiñón | Función renal | Creatinina, nitrógeno ureico y filtración glomerular estimada.\nHígado | Perfil hepático | Transaminasas, fosfatasas y bilirrubina.\nAnemia | Estudio de anemia | Hemograma con índices y recuento de plaquetas.",
		'preparacion' => 'Ayuno de 8 a 12 horas. Solo agua. Evita el alcohol las 24 horas previas y el ejercicio intenso el día anterior, porque alteran varios parámetros. Toma tus medicamentos según lo que te haya indicado tu médico.',
		'faq'      => "¿Este precio incluye los exámenes? | Sí. A diferencia de la toma de muestras suelta, aquí el valor cubre la visita y todo el panel.\n¿Puedo tomar café en el ayuno? | No. Ni siquiera negro sin azúcar: altera la glicemia y los lípidos. Solo agua.\n¿Cuánta sangre se saca? | Dos o tres tubos pequeños, alrededor de 10 mililitros en total. Es una cantidad mínima.\n¿Se puede sumar el perfil lipídico? | Sí, se agrega en la misma toma sin otra punción. Lo indicas al agendar.",
	),

	array(
		'titulo'   => 'Perfil lipídico y tiroideo',
		'linea'    => 'Laboratorio',
		'icono'    => 'il-lab',
		'precio'   => 58000,
		'duracion' => 15,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Colesterol total, HDL, LDL, triglicéridos y hormonas tiroideas, con la toma de muestra en tu domicilio.',
		'contenido' => 'El perfil lipídico mide el colesterol y los triglicéridos, y es la base del cálculo de riesgo cardiovascular. El perfil tiroideo mide TSH y T4 libre, y detecta hipotiroidismo e hipertiroidismo, cuadros muy frecuentes y muchas veces silenciosos.

Se toman en la misma punción y el valor incluye la visita y ambos paneles.',
		'incluye'  => "Visita del profesional a tu domicilio\nToma de muestra de sangre\nPerfil lipídico completo con LDL calculado\nTSH y T4 libre\nResultados en línea en 24 a 48 horas",
		'ventajas' => "Se combina con el Doppler carotídeo | Ambos forman parte del mismo control cardiovascular y se resuelven en una sola visita.\nControl de tiroides sin traslados | Muchos pacientes controlan su TSH cada seis meses durante años.\nEl ayuno se rompe al terminar | Sin viaje de vuelta con hambre.",
		'condiciones' => "Colesterol | Control de dislipidemia | Seguimiento del tratamiento con estatinas.\nRiesgo | Evaluación de riesgo cardiovascular | Junto a la presión y la glicemia, define el riesgo a 10 años.\nTiroides | Control de hipotiroidismo | Ajuste de dosis de levotiroxina.\nSíntomas | Cansancio, caída de pelo, cambios de peso | Estudio inicial de la función tiroidea.\nChequeo | Control anual | Panel habitual del chequeo preventivo del adulto.",
		'preparacion' => 'Ayuno de 12 horas para el perfil lipídico. Solo agua. No consumas alcohol las 24 horas previas: eleva los triglicéridos de forma marcada. Si tomas levotiroxina, tómala después de la toma de muestra, no antes.',
		'faq'      => "¿Debo tomar la levotiroxina antes del examen? | No. Tómala después de la muestra, porque tomarla justo antes altera el valor de T4 libre.\n¿Por qué 12 horas de ayuno y no 8? | Por los triglicéridos, que suben con cualquier comida reciente y falsean el cálculo del colesterol LDL.\n¿Sirve un solo examen para saber si tengo el colesterol alto? | Se suele confirmar con una segunda muestra antes de iniciar tratamiento. Tu médico lo define.\n¿Incluye T3? | El panel base incluye TSH y T4 libre. Si tu orden pide T3, lo agregamos en la misma toma.",
	),

	/* ---------------- Enfermería ---------------- */

	array(
		'titulo'   => 'Curaciones avanzadas a domicilio',
		'linea'    => 'Enfermería',
		'icono'    => 'il-nurse',
		'precio'   => 35000,
		'duracion' => 40,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Curación de heridas, úlceras por presión y pie diabético, con técnica estéril y registro fotográfico de la evolución.',
		'contenido' => 'La curación avanzada trata heridas que no cierran solas: úlceras por presión, pie diabético, heridas quirúrgicas complicadas y úlceras venosas. Se realiza con técnica estéril, se evalúa el tejido, se elige el apósito adecuado y se deja registro fotográfico para seguir la evolución.

Trasladar a un paciente con una úlcera es contraproducente. Este es, de todas las prestaciones, la que más claramente pertenece al domicilio.',
		'incluye'  => "Visita del profesional a tu domicilio\nEvaluación de la herida y del tejido\nCuración con técnica estéril\nSelección y aplicación del apósito adecuado\nRegistro fotográfico y nota de evolución en el portal",
		'ventajas' => "Trasladar una úlcera la empeora | El roce, la presión y el tiempo sentado son exactamente lo contrario de lo indicado.\nSeguimiento con fotos | Cada curación queda registrada, así el médico ve la evolución real, no un relato.\nEl paciente no se expone | Una herida abierta en una sala de espera es riesgo de infección.",
		'condiciones' => "Presión | Úlceras por presión | En pacientes postrados. Es la indicación más frecuente.\nDiabetes | Pie diabético | Curación con evaluación de la irrigación y del riesgo de progresión.\nVenosa | Úlcera venosa | Heridas del tobillo asociadas a insuficiencia venosa.\nCirugía | Herida quirúrgica complicada | Dehiscencia o infección de una herida operatoria.\nQuemadura | Quemaduras menores | Curación y seguimiento ambulatorio.",
		'preparacion' => 'Ninguna. Ten a mano un espacio limpio y bien iluminado, y los apósitos o indicaciones que haya dejado el médico tratante. Si tienes la última fotografía o el informe previo de la herida, tenlo disponible: comparar es lo que muestra si va mejorando.',
		'faq'      => "¿Incluye los materiales? | La visita incluye el material básico de curación. Los apósitos especializados de alto costo se cotizan aparte y te avisamos antes.\n¿Con qué frecuencia hay que curar? | Depende del tipo de herida y del apósito. Puede ir de dos veces por semana a diario. Lo indica el médico tratante.\n¿Quién realiza la curación? | Un profesional de enfermería con registro vigente en la Superintendencia de Salud.\n¿Se puede coordinar una serie de visitas? | Sí, se agenda el plan completo de una vez y queda con el mismo profesional.",
	),

	array(
		'titulo'   => 'Administración de medicamentos e inyecciones',
		'linea'    => 'Enfermería',
		'icono'    => 'il-nurse',
		'precio'   => 28000,
		'duracion' => 20,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Inyecciones intramusculares, subcutáneas y administración de tratamientos indicados por tu médico, en tu casa.',
		'contenido' => 'Administración de medicamentos por vía intramuscular o subcutánea según indicación médica: antibióticos, antiinflamatorios, anticoagulantes, vitamina B12, hierro y tratamientos hormonales.

El profesional verifica la indicación, administra el medicamento y deja registro. Los medicamentos los aporta el paciente con su receta.',
		'incluye'  => "Visita del profesional a tu domicilio\nVerificación de la indicación médica\nAdministración por la vía indicada\nMaterial estéril de un solo uso\nRegistro de administración en el portal",
		'ventajas' => "Tratamientos de varios días | Un antibiótico intramuscular de cinco días son cinco traslados. En casa son cinco visitas coordinadas.\nSin exponerse estando enfermo | Justamente cuando estás cursando una infección es cuando menos deberías salir.\nHorario que se ajusta a la dosis | Muchos tratamientos exigen intervalos exactos, y eso es más fácil de cumplir en casa.",
		'condiciones' => "Antibiótico | Tratamiento antibiótico intramuscular | Esquemas de varios días indicados por el médico.\nAnticoagulante | Heparina de bajo peso molecular | Administración diaria en pacientes en tratamiento.\nVitaminas | Vitamina B12 y hierro | Esquemas de reposición.\nHormonal | Tratamientos hormonales | Administración según protocolo indicado.\nDolor | Analgesia indicada | Manejo del dolor por vía parenteral según receta.",
		'preparacion' => 'Ten a mano la receta médica y el medicamento. Sin receta vigente no se puede administrar nada: es un requisito legal, no un trámite. Si el medicamento requiere refrigeración, mantenlo en el refrigerador hasta que llegue el profesional.',
		'faq'      => "¿Ustedes traen el medicamento? | No. El medicamento lo compra el paciente con su receta. Nosotros lo administramos.\n¿Necesito receta? | Sí, siempre y vigente. Es una exigencia legal.\n¿Administran suero o tratamientos endovenosos? | Las vías endovenosas requieren evaluación previa. Escríbenos por WhatsApp con la indicación y te decimos si es posible.\n¿Se puede agendar todo el tratamiento de una vez? | Sí, y conviene: se reservan todas las visitas del esquema con el mismo profesional.",
	),

	array(
		'titulo'   => 'Control de signos vitales y evaluación de enfermería',
		'linea'    => 'Enfermería',
		'icono'    => 'il-nurse',
		'precio'   => 25000,
		'duracion' => 20,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Presión, pulso, saturación, temperatura, glicemia capilar y evaluación general, con informe para el médico tratante.',
		'contenido' => 'El control de signos vitales mide presión arterial, frecuencia cardíaca, saturación de oxígeno, temperatura y glicemia capilar, y suma una evaluación general del paciente: estado de conciencia, hidratación, piel y estado de los dispositivos que tenga instalados.

Es el control de rutina del paciente crónico en domicilio, y el informe queda disponible para su médico tratante.',
		'incluye'  => "Visita del profesional a tu domicilio\nPresión arterial, pulso, saturación y temperatura\nGlicemia capilar\nEvaluación general de enfermería\nInforme en el portal del paciente",
		'ventajas' => "Control periódico sin desgaste | Para un paciente crónico, cada traslado significa horas y ayuda de terceros.\nDatos reales, no de consultorio | La presión medida en casa refleja mejor la situación habitual del paciente.\nEl familiar recibe un informe | Muchas veces quien cuida está lejos, y el registro en el portal le permite seguir el estado del paciente.",
		'condiciones' => "Crónico | Control del paciente crónico | Seguimiento de hipertensión, diabetes e insuficiencia cardíaca.\nPostrado | Paciente en cama | Evaluación integral periódica.\nAlta | Después del alta hospitalaria | Control en los días siguientes a una hospitalización.\nAdulto mayor | Control geriátrico | Evaluación general del estado del paciente mayor.\nCuidador | Apoyo al cuidador | Orientación sobre manejo, posiciones y prevención de úlceras.",
		'preparacion' => 'Ninguna. Ten a mano la lista de medicamentos que toma el paciente y sus controles anteriores si los tienes. Si el paciente usa oxígeno o tiene dispositivos instalados, avísanos al agendar.',
		'faq'      => "¿Reemplaza una consulta médica? | No. Es una evaluación de enfermería, con informe para el médico tratante. Si algo requiere atención médica, se indica de inmediato.\n¿Incluye glicemia? | Sí, glicemia capilar con dispositivo de punción digital.\n¿Se puede contratar como visita periódica? | Sí, se agendan varias visitas de una vez y quedan con el mismo profesional.\n¿Quién realiza la visita? | Un profesional de enfermería con registro vigente.",
	),

	array(
		'titulo'   => 'Instalación y cambio de sonda',
		'linea'    => 'Enfermería',
		'icono'    => 'il-nurse',
		'precio'   => 45000,
		'duracion' => 30,
		'edad'     => 'Todas las edades',
		'resumen'  => 'Instalación y recambio de sonda urinaria y sonda de alimentación, con técnica estéril y educación al cuidador.',
		'contenido' => 'Instalación y cambio programado de sonda vesical y de sonda nasogástrica según indicación médica, con técnica estéril. Incluye la evaluación del paciente, el procedimiento y educación al cuidador sobre el manejo diario del dispositivo.

Es una prestación pensada para pacientes con manejo domiciliario permanente, donde el recambio periódico es parte de la rutina de cuidado.',
		'incluye'  => "Visita del profesional a tu domicilio\nEvaluación previa del paciente\nInstalación o recambio con técnica estéril\nEducación al cuidador sobre manejo y signos de alarma\nRegistro del procedimiento en el portal",
		'ventajas' => "Evita ir a urgencia por un recambio | Muchos pacientes terminan en un servicio de urgencia solo por un cambio programado de sonda.\nEl cuidador aprende en el lugar | La educación se hace con los materiales reales y en el ambiente donde se va a manejar.\nMenor riesgo de infección | Menos traslados y menos exposición hospitalaria.",
		'condiciones' => "Urinaria | Recambio programado de sonda vesical | Cada 4 a 6 semanas según indicación.\nRetención | Instalación por retención urinaria | Con indicación médica previa.\nAlimentación | Sonda nasogástrica | Instalación y recambio en pacientes con alimentación enteral.\nCuidado | Educación al cuidador | Manejo diario, higiene y signos de alarma.\nPostrado | Paciente con manejo domiciliario permanente | Parte del plan de cuidados en casa.",
		'preparacion' => 'Ten a mano la indicación médica, la sonda del calibre indicado y el material que haya recetado el médico. Prepara un espacio limpio y bien iluminado. Si el paciente tiene antecedentes de dificultad en instalaciones anteriores, avísanos al agendar.',
		'faq'      => "¿Traen la sonda? | El paciente aporta la sonda del calibre indicado y la bolsa recolectora. El material estéril del procedimiento lo llevamos nosotros.\n¿Necesito indicación médica? | Sí, siempre.\n¿Y si no se logra instalar? | En algunos casos la instalación no es posible en domicilio y se requiere evaluación urológica. Te lo indicamos en el momento y coordinamos la derivación.\n¿Cada cuánto se cambia? | Habitualmente cada 4 a 6 semanas la sonda vesical, según el tipo y la indicación del médico.",
	),
);
