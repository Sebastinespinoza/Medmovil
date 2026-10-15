<?php
/**
 * Template Name: Cómo funciona
 *
 * Era una página de texto plano. Aquí se explica el servicio como lo explica una
 * tienda: qué pasa en cada paso, qué incluye el precio, cómo se paga y qué
 * ocurre el día de la visita.
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$datos  = medmovil_datos_publicos();
$desde  = 0;
foreach ( $datos['ex'] as $e ) {
	if ( $e['p'] && ( ! $desde || $e['p'] < $desde ) ) {
		$desde = $e['p'];
	}
}

$pasos = array(
	array(
		'ic' => 'il-pick',
		'n'  => __( 'Paso 01', 'medmovil' ),
		't'  => __( 'Eliges tu examen', 'medmovil' ),
		'p'  => __( 'Buscas por nombre, por zona del cuerpo o escribiendo el código que trae tu orden médica. Ves el precio, la duración y la preparación antes de decidir nada.', 'medmovil' ),
		'd'  => array(
			__( 'Puedes juntar varios exámenes en una sola visita', 'medmovil' ),
			__( 'El precio que ves es el que pagas', 'medmovil' ),
		),
	),
	array(
		'ic' => 'il-book',
		'n'  => __( 'Paso 02', 'medmovil' ),
		't'  => __( 'Reservas tu bloque', 'medmovil' ),
		'p'  => __( 'Nos dices tu comuna y coordinamos el bloque que te acomode. Trabajamos en bloques de una hora completa, con el tiempo de traslado ya considerado.', 'medmovil' ),
		'd'  => array(
			__( 'Bloques de una hora: nunca llegamos apurados', 'medmovil' ),
			__( 'Puedes pedir a un profesional en particular', 'medmovil' ),
		),
	),
	array(
		'ic' => 'il-arrive',
		'n'  => __( 'Paso 03', 'medmovil' ),
		't'  => __( 'Llegamos a tu domicilio', 'medmovil' ),
		'p'  => __( 'El profesional llega con el equipo digital portátil y realiza el examen donde esté el paciente: en su cama, en el sillón o donde le resulte cómodo.', 'medmovil' ),
		'd'  => array(
			__( 'No hay que mover al paciente de su cama', 'medmovil' ),
			__( 'Sin cobro de traslado en toda la región', 'medmovil' ),
		),
	),
	array(
		'ic' => 'il-report',
		'n'  => __( 'Paso 04', 'medmovil' ),
		't'  => __( 'Recibes tu informe', 'medmovil' ),
		'p'  => __( 'Las imágenes van al médico informante el mismo día. El informe llega en PDF en menos de 24 horas, firmado por médico radiólogo.', 'medmovil' ),
		'd'  => array(
			__( 'En PDF, listo para enviárselo a tu médico', 'medmovil' ),
			__( 'Sin volver a retirar nada en papel', 'medmovil' ),
		),
	),
);

$pagos = array(
	array( 'i-coin', __( 'Efectivo', 'medmovil' ), __( 'Al momento del examen, con boleta.', 'medmovil' ) ),
	array( 'i-send', __( 'Transferencia', 'medmovil' ), __( 'Te enviamos los datos al confirmar la hora.', 'medmovil' ) ),
	array( 'i-note', __( 'Tarjeta', 'medmovil' ), __( 'Débito o crédito, con máquina portátil.', 'medmovil' ) ),
);
?>

<section class="cf-hero">
	<div class="wrap">
		<?php medmovil_migas( array( array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ), array( get_the_title(), '' ) ) ); ?>
		<div class="cf-hero-in">
			<div>
				<p class="eyebrow" style="margin-top:20px"><?php esc_html_e( 'Cómo funciona', 'medmovil' ); ?></p>
				<h1><?php esc_html_e( 'Cuatro pasos y listo,', 'medmovil' ); ?><br>
					<span class="serif" style="color:var(--blue)"><?php esc_html_e( 'sin salir de casa', 'medmovil' ); ?></span></h1>
				<p class="lede" style="margin-top:20px">
					<?php esc_html_e( 'Pensado para que lo pueda hacer un adulto mayor solo, o un hijo desde el trabajo. Sin formularios largos, sin registro obligatorio y sin llamadas que nadie contesta.', 'medmovil' ); ?>
				</p>
				<div class="cf-btns">
					<a class="btn btn-p" href="<?php echo esc_url( home_url( '/cotizador/' ) ); ?>">
						<?php medmovil_icono( 'i-coin' ); ?> <?php esc_html_e( 'Ver precios y cotizar', 'medmovil' ); ?>
					</a>
					<a class="btn btn-o" href="<?php echo esc_url( medmovil_url_agenda() ); ?>">
						<?php medmovil_icono( 'i-cal' ); ?> <?php esc_html_e( 'Agendar ahora', 'medmovil' ); ?>
					</a>
				</div>
			</div>

			<div class="cf-num">
				<div><b><?php echo esc_html( count( $datos['ex'] ) ); ?></b><span><?php esc_html_e( 'exámenes a domicilio', 'medmovil' ); ?></span></div>
				<div><b><?php echo esc_html( count( $datos['co'] ) ); ?></b><span><?php esc_html_e( 'comunas con cobertura', 'medmovil' ); ?></span></div>
				<div><b>24 h</b><span><?php esc_html_e( 'para tener el informe', 'medmovil' ); ?></span></div>
				<div><b><?php echo esc_html( $desde ? medmovil_precio( $desde ) : '—' ); ?></b><span><?php esc_html_e( 'desde, todo incluido', 'medmovil' ); ?></span></div>
			</div>
		</div>
	</div>
</section>

<section class="band" style="padding-block:60px">
	<div class="wrap">
		<div class="cf-pasos">
			<?php foreach ( $pasos as $i => $paso ) : ?>
				<article class="cf-paso" data-rv>
					<div class="cf-paso-ic"><?php medmovil_icono( $paso['ic'], 'ilo' ); ?></div>
					<div class="cf-paso-tx">
						<span class="cf-paso-n"><?php echo esc_html( $paso['n'] ); ?></span>
						<h2><?php echo esc_html( $paso['t'] ); ?></h2>
						<p><?php echo esc_html( $paso['p'] ); ?></p>
						<ul>
							<?php foreach ( $paso['d'] as $d ) : ?>
								<li><?php medmovil_icono( 'i-check' ); ?> <?php echo esc_html( $d ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
					<span class="cf-paso-big"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<section class="band" style="background:var(--surface); border-block:1px solid var(--line); padding-block:60px">
	<div class="wrap">
		<div class="sec-head" data-rv>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Sin letra chica', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Qué incluye el precio que ves', 'medmovil' ); ?></h2>
				<p class="lede"><?php esc_html_e( 'Un solo valor. No hay cobro de traslado, ni recargo por horario, ni costo del informe aparte.', 'medmovil' ); ?></p>
			</div>
		</div>

		<div class="cf-incluye">
			<div class="cf-si">
				<b><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Está incluido', 'medmovil' ); ?></b>
				<ul>
					<li><?php esc_html_e( 'La visita del profesional a tu domicilio', 'medmovil' ); ?></li>
					<li><?php esc_html_e( 'El examen con equipo digital portátil', 'medmovil' ); ?></li>
					<li><?php esc_html_e( 'El informe firmado por médico radiólogo', 'medmovil' ); ?></li>
					<li><?php esc_html_e( 'El envío del PDF en menos de 24 horas', 'medmovil' ); ?></li>
					<li><?php esc_html_e( 'El traslado dentro de la Región Metropolitana', 'medmovil' ); ?></li>
					<li><?php esc_html_e( 'La orientación previa sobre la preparación', 'medmovil' ); ?></li>
				</ul>
			</div>
			<div class="cf-no">
				<b><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'No está incluido', 'medmovil' ); ?></b>
				<ul>
					<li><?php esc_html_e( 'La consulta médica que interpreta el resultado', 'medmovil' ); ?></li>
					<li><?php esc_html_e( 'Exámenes que no estén en el catálogo', 'medmovil' ); ?></li>
					<li><?php esc_html_e( 'Atención de urgencia: eso se ve en un servicio hospitalario', 'medmovil' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Si necesitas algo que no aparece, escríbenos igual: muchas veces lo coordinamos.', 'medmovil' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="band" style="padding-block:60px">
	<div class="wrap">
		<div class="sec-head" data-rv>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Formas de pago', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Pagas cuando se hace el examen, no antes', 'medmovil' ); ?></h2>
				<p class="lede"><?php esc_html_e( 'No pedimos datos de tarjeta para reservar. El pago se hace en tu casa, cuando el profesional ya realizó el examen.', 'medmovil' ); ?></p>
			</div>
		</div>

		<div class="cf-pagos">
			<?php foreach ( $pagos as $pago ) : ?>
				<article class="cf-pago" data-rv>
					<?php medmovil_icono( $pago[0] ); ?>
					<b><?php echo esc_html( $pago[1] ); ?></b>
					<p><?php echo esc_html( $pago[2] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
		<p class="cz-pie" style="margin-top:20px">
			<?php esc_html_e( 'El pago en línea llegará más adelante. Mientras tanto lo hacemos así, que es lo que la mayoría prefiere: ver el examen hecho antes de pagarlo.', 'medmovil' ); ?>
		</p>
	</div>
</section>

<section class="band" style="background:var(--surface); border-block:1px solid var(--line); padding-block:60px">
	<div class="wrap">
		<div class="sec-head" data-rv>
			<div>
				<p class="eyebrow"><?php esc_html_e( 'El día de la visita', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Qué pasa cuando tocamos el timbre', 'medmovil' ); ?></h2>
			</div>
		</div>
		<div class="walk">
			<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'il-arrive', 'ilo' ); ?></div>
				<b><?php esc_html_e( 'Llega el profesional', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Dentro del bloque que reservaste, identificado y con su equipo.', 'medmovil' ); ?></p></div>
			<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'il-portal', 'ilo' ); ?></div>
				<b><?php esc_html_e( 'Revisa la orden', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Confirma qué pidió el médico y pregunta por los antecedentes.', 'medmovil' ); ?></p></div>
			<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'il-eco', 'ilo' ); ?></div>
				<b><?php esc_html_e( 'Realiza el examen', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Donde el paciente esté cómodo. No hay que trasladarlo.', 'medmovil' ); ?></p></div>
			<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'i-send', 'ilo' ); ?></div>
				<b><?php esc_html_e( 'Envía el estudio', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Las imágenes van al médico informante el mismo día.', 'medmovil' ); ?></p></div>
			<div class="wk" data-rv><div class="wk-ic"><?php medmovil_icono( 'il-report', 'ilo' ); ?></div>
				<b><?php esc_html_e( 'Llega tu informe', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'En PDF, en menos de 24 horas, firmado.', 'medmovil' ); ?></p></div>
		</div>
	</div>
</section>

<section class="band" style="padding-block:60px">
	<div class="wrap">
		<div class="vs" data-rv>
			<div class="vs-col">
				<div class="vs-h" style="color:var(--blue)"><?php medmovil_icono( 'i-home' ); ?> <?php esc_html_e( 'En tu casa, con MedMóvil', 'medmovil' ); ?></div>
				<ul class="vs-yes">
					<li><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Hora reservada: el examen empieza cuando dijimos', 'medmovil' ); ?></li>
					<li><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Cero traslados, cero estacionamiento, cero permiso en el trabajo', 'medmovil' ); ?></li>
					<li><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Informe en menos de 24 horas, sin volver a retirarlo', 'medmovil' ); ?></li>
					<li><?php medmovil_icono( 'i-check' ); ?> <?php esc_html_e( 'Precio publicado, sin cobro de traslado', 'medmovil' ); ?></li>
				</ul>
			</div>
			<div class="vs-col">
				<div class="vs-h" style="color:var(--ink-3)"><?php medmovil_icono( 'i-clock' ); ?> <?php esc_html_e( 'El camino de siempre', 'medmovil' ); ?></div>
				<ul class="vs-no">
					<li><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'Llegar 40 minutos antes y esperar de pie', 'medmovil' ); ?></li>
					<li><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'Trasladarse justo cuando el cuerpo no ayuda', 'medmovil' ); ?></li>
					<li><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'Volver otro día a retirar el informe impreso', 'medmovil' ); ?></li>
					<li><?php medmovil_icono( 'i-x' ); ?> <?php esc_html_e( 'Buscar el sobre con los exámenes viejos en la casa', 'medmovil' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
</section>

<section class="band" style="background:var(--surface); border-block:1px solid var(--line); padding-block:60px">
	<div class="wrap">
		<p class="eyebrow" data-rv><?php esc_html_e( 'Preguntas frecuentes', 'medmovil' ); ?></p>
		<h2 style="margin:14px 0 26px" data-rv><?php esc_html_e( 'Lo que más nos preguntan antes de agendar', 'medmovil' ); ?></h2>
		<?php
		medmovil_faq( array(
			array( __( '¿Necesito orden médica?', 'medmovil' ), __( 'Para la mayoría de los exámenes sí, porque es lo que indica qué hay que estudiar y con qué detalle. Si no la tienes, escríbenos igual y te orientamos.', 'medmovil' ) ),
			array( __( '¿Tengo que registrarme para solicitar una hora?', 'medmovil' ), __( 'Sí. La cuenta de paciente permite seguir la confirmación, ver los exámenes y consultar los pagos registrados. Puedes cotizar sin cuenta.', 'medmovil' ) ),
			array( __( '¿Puedo solicitar una hora para otra persona?', 'medmovil' ), __( 'La solicitud en línea se asocia al titular de la cuenta. Si organizas el examen de otra persona, contáctanos para coordinar su identificación y autorización.', 'medmovil' ) ),
			array( __( '¿Cuánto se demora la visita?', 'medmovil' ), __( 'El examen en sí dura entre 20 y 45 minutos según cuál sea. Reservamos una hora completa para no llegar apurados ni apurar al paciente.', 'medmovil' ) ),
			array( __( '¿Y si el paciente está postrado?', 'medmovil' ), __( 'Es justamente para eso. El equipo es portátil y el examen se hace en su cama, sin moverlo. Avísanos al agendar para llegar preparados.', 'medmovil' ) ),
			array( __( '¿Atienden fines de semana?', 'medmovil' ), sprintf( __( 'Nuestro horario es %s. Fuera de eso lo coordinamos según disponibilidad.', 'medmovil' ), medmovil_opt( 'horario', 'de lunes a sábado' ) ) ),
			array( __( '¿Quién firma el informe?', 'medmovil' ), __( 'Un médico radiólogo. El profesional que va a tu casa realiza el examen y captura las imágenes; el informe lo emite y firma el médico informante.', 'medmovil' ) ),
			array( __( '¿Puedo pedir al mismo profesional de la vez anterior?', 'medmovil' ), __( 'Sí. Dinos su nombre al agendar y coordinamos según su disponibilidad en tu comuna.', 'medmovil' ) ),
		) );
		?>
	</div>
</section>

<section class="band" style="padding-block:60px">
	<div class="wrap">
		<div class="cta-band" data-rv>
			<svg class="cta-wave" viewBox="0 0 1200 300" preserveAspectRatio="none" aria-hidden="true">
				<path d="M-20 210C120 60 220 60 380 210s220 150 400 0 240-150 440 0"/>
			</svg>
			<div style="position:relative; z-index:2">
				<p class="eyebrow" style="color:var(--blue-bright)"><?php esc_html_e( 'Empecemos', 'medmovil' ); ?></p>
				<h2 style="margin-top:14px"><?php esc_html_e( 'Busca tu examen y arma el presupuesto', 'medmovil' ); ?></h2>
				<p><?php esc_html_e( 'Toma dos minutos y no te compromete a nada. Si prefieres, escríbenos y lo vemos juntos por WhatsApp.', 'medmovil' ); ?></p>
			</div>
			<div style="position:relative; z-index:2; display:flex; flex-direction:column; gap:11px">
				<a class="btn btn-w" style="justify-content:center" href="<?php echo esc_url( home_url( '/cotizador/' ) ); ?>">
					<?php medmovil_icono( 'i-coin' ); ?> <?php esc_html_e( 'Ir al cotizador', 'medmovil' ); ?>
				</a>
				<a class="btn btn-g" style="justify-content:center" href="<?php echo esc_url( medmovil_whatsapp() ); ?>" target="_blank" rel="noopener">
					<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Escribir por WhatsApp', 'medmovil' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>

<?php
get_footer();
