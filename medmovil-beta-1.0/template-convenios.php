<?php
/**
 * Template Name: Convenios institucionales
 *
 * @package MedMovil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="svc-top">
	<div class="wrap" style="padding-block:34px 40px">
		<?php medmovil_migas( array( array( __( 'Inicio', 'medmovil' ), home_url( '/' ) ), array( get_the_title(), '' ) ) ); ?>
		<p class="eyebrow" style="margin-top:20px"><?php esc_html_e( 'B2B', 'medmovil' ); ?></p>
		<h1 style="font-size:clamp(2rem,4vw,3rem);margin-top:14px"><?php esc_html_e( 'Convenios para instituciones', 'medmovil' ); ?></h1>
		<div class="prose" style="margin-top:18px"><?php while ( have_posts() ) { the_post(); the_content(); } ?></div>
	</div>
</div>

<section class="band">
	<div class="wrap">
		<div class="forwho">
			<article class="fw" style="--g1:#1B5FA8;--g2:#0B3560" data-rv>
				<?php medmovil_icono( 'il-elder', 'ilo' ); ?>
				<b><?php esc_html_e( 'ELEAM y centros de reposo', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Atendemos a varios residentes en una misma jornada, sin ambulancias, sin acompañantes y sin sacar a nadie del recinto.', 'medmovil' ); ?></p>
			</article>
			<article class="fw" style="--g1:#2E8F63;--g2:#14523A" data-rv>
				<?php medmovil_icono( 'il-nurse', 'ilo' ); ?>
				<b><?php esc_html_e( 'Consultorios y clínicas', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Resolvemos la demanda de imágenes que tu centro no alcanza a cubrir, con informe radiológico incluido.', 'medmovil' ); ?></p>
			</article>
			<article class="fw" style="--g1:#14798B;--g2:#083B47" data-rv>
				<?php medmovil_icono( 'il-shield', 'ilo' ); ?>
				<b><?php esc_html_e( 'Empresas y mutuales', 'medmovil' ); ?></b>
				<p><?php esc_html_e( 'Exámenes preventivos y de control en el lugar de trabajo, sin que nadie pierda una jornada.', 'medmovil' ); ?></p>
			</article>
		</div>

		<div class="cta-band" style="margin-top:44px" data-rv>
			<svg class="cta-wave" viewBox="0 0 1200 300" preserveAspectRatio="none" aria-hidden="true">
				<path d="M-20 210C120 60 220 60 380 210s220 150 400 0 240-150 440 0"/>
			</svg>
			<div style="position:relative;z-index:2">
				<h2><?php esc_html_e( 'Conversemos un convenio', 'medmovil' ); ?></h2>
				<p><?php esc_html_e( 'Cuéntanos cuántos pacientes atiendes y qué exámenes necesitas con más frecuencia, y te preparamos una propuesta con tarifas por volumen.', 'medmovil' ); ?></p>
			</div>
			<div style="position:relative;z-index:2;display:flex;flex-direction:column;gap:11px">
				<a class="btn btn-w" style="justify-content:center" href="<?php echo esc_url( medmovil_whatsapp( __( 'Hola, represento a una institución y quiero conversar un convenio.', 'medmovil' ) ) ); ?>" target="_blank" rel="noopener">
					<?php medmovil_icono( 'i-wa' ); ?> <?php esc_html_e( 'Hablar por WhatsApp', 'medmovil' ); ?>
				</a>
				<a class="btn btn-o" style="justify-content:center;color:#fff;border-color:rgba(255,255,255,.45)" href="<?php echo esc_url( 'mailto:' . medmovil_opt( 'email', '' ) ); ?>">
					<?php medmovil_icono( 'i-mail' ); ?> <?php esc_html_e( 'Escribir un correo', 'medmovil' ); ?>
				</a>
			</div>
		</div>
	</div>
</section>
<?php get_footer(); ?>
