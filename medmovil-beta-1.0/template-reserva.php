<?php
/**
 * Template Name: Solicitar hora
 * Formulario de solicitud. La agenda se confirma manualmente.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
$prestaciones = get_posts( array( 'post_type' => 'mm_prestacion', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
$pedido = isset( $_GET['examen'] ) ? absint( $_GET['examen'] ) : 0;
$solicitud = isset( $_GET['solicitud'] ) ? medmovil_atencion( absint( $_GET['solicitud'] ) ) : null;
$solicitud = get_current_user_id() && 'paciente' === medmovil_rol() && $solicitud && (int) $solicitud['paciente'] === get_current_user_id() ? $solicitud : null;
?>
<section class="band reserva-page"><div class="wrap">
	<p class="eyebrow"><?php esc_html_e( 'MedMóvil · a domicilio', 'medmovil' ); ?></p>
	<h1><?php esc_html_e( 'Solicita tu ecografía', 'medmovil' ); ?></h1>
	<p class="lede"><?php esc_html_e( 'Elige los exámenes y tu horario preferido. Confirmaremos disponibilidad y el profesional asignado antes de dejar la hora agendada.', 'medmovil' ); ?></p>
	<p class="reserva-titular"><?php medmovil_icono( 'i-info' ); ?> <?php esc_html_e( 'La solicitud en línea es para el titular de la cuenta. Para organizar el examen de otra persona, coordínalo con nuestro equipo.', 'medmovil' ); ?></p>
	<?php if ( $solicitud ) : ?>
		<div class="reserva-aviso" id="reservaCompletada" role="status"><?php medmovil_icono( 'i-calcheck' ); ?><div><h2><?php esc_html_e( 'Solicitud recibida', 'medmovil' ); ?></h2><p><?php echo esc_html( sprintf( __( 'Número #%d. La hora está pendiente de confirmación y no se ha realizado ningún cobro.', 'medmovil' ), $solicitud['id'] ) ); ?></p><a class="btn btn-p" href="<?php echo esc_url( medmovil_url_portal( 'horas' ) ); ?>"><?php esc_html_e( 'Ver mis horas', 'medmovil' ); ?></a></div></div>
	<?php elseif ( 'paciente' !== medmovil_rol() ) : ?>
		<div class="reserva-aviso"><?php medmovil_icono( 'i-lock' ); ?><div><h2><?php esc_html_e( 'Entra como paciente para continuar', 'medmovil' ); ?></h2><p><?php esc_html_e( 'Los exámenes que agregaste quedan guardados en este navegador mientras entras a tu cuenta.', 'medmovil' ); ?></p><div class="reserva-acciones"><a class="btn btn-p" href="<?php echo esc_url( add_query_arg( 'volver', medmovil_url_reserva( $pedido ), medmovil_url_portal() ) ); ?>"><?php esc_html_e( 'Entrar como paciente', 'medmovil' ); ?></a><a class="btn btn-o" href="<?php echo esc_url( add_query_arg( array( 'crear' => 1, 'volver' => medmovil_url_reserva( $pedido ) ), medmovil_url_portal() ) ); ?>"><?php esc_html_e( 'Crear cuenta', 'medmovil' ); ?></a></div></div></div>
	<?php else : ?>
		<?php if ( ! empty( $GLOBALS['mm_reserva_error'] ) ) : ?><p class="acc-error" role="alert"><?php echo esc_html( $GLOBALS['mm_reserva_error'] ); ?></p><?php endif; ?>
		<form method="post" class="reserva-form" id="mmReservaForm" data-examen="<?php echo (int) $pedido; ?>">
			<?php wp_nonce_field( 'mm_solicitar_hora', 'mm_reserva_nonce' ); ?>
			<input type="hidden" name="mm_solicitar" value="1">
			<div class="reserva-campos"><div class="reserva-panel"><h2>01 · <?php esc_html_e( 'Tus exámenes', 'medmovil' ); ?></h2><p><?php esc_html_e( 'El precio se recalcula con los valores publicados al enviar la solicitud.', 'medmovil' ); ?></p>
				<div class="reserva-examenes" id="reservaExamenes">
				<?php foreach ( $prestaciones as $prestacion ) : $precio = (int) get_post_meta( $prestacion->ID, '_mm_precio', true ); if ( $precio <= 0 ) { continue; } ?>
					<label class="reserva-examen"><input type="checkbox" name="examenes[]" value="<?php echo (int) $prestacion->ID; ?>" data-precio="<?php echo (int) $precio; ?>" <?php checked( isset( $_POST['examenes'] ) ? in_array( (string) $prestacion->ID, (array) wp_unslash( $_POST['examenes'] ), true ) : $pedido === $prestacion->ID ); ?>><span><?php echo esc_html( $prestacion->post_title ); ?></span><b><?php echo esc_html( medmovil_precio( $precio ) ); ?></b></label>
				<?php endforeach; ?>
				</div><p><a href="<?php echo esc_url( get_post_type_archive_link( 'mm_prestacion' ) ); ?>"><?php esc_html_e( 'Ver detalles del catálogo', 'medmovil' ); ?></a></p>
			</div><div class="reserva-panel"><h2>02 · <?php esc_html_e( 'Dónde y cuándo', 'medmovil' ); ?></h2>
				<div class="reserva-grid"><label><?php esc_html_e( 'Comuna', 'medmovil' ); ?><select name="comuna" required><option value=""><?php esc_html_e( 'Selecciona', 'medmovil' ); ?></option><?php foreach ( medmovil_comunas( 100 ) as $c ) : $valor = isset( $_POST['comuna'] ) ? sanitize_text_field( wp_unslash( $_POST['comuna'] ) ) : get_user_meta( get_current_user_id(), '_mm_comuna', true ); ?><option value="<?php echo esc_attr( $c->post_title ); ?>" <?php selected( $valor, $c->post_title ); ?>><?php echo esc_html( $c->post_title ); ?></option><?php endforeach; ?></select></label>
				<label><?php esc_html_e( 'Fecha preferida', 'medmovil' ); ?><input type="date" name="fecha" required min="<?php echo esc_attr( ( new DateTimeImmutable( 'today', wp_timezone() ) )->modify( '+1 day' )->format( 'Y-m-d' ) ); ?>" max="<?php echo esc_attr( ( new DateTimeImmutable( 'today', wp_timezone() ) )->modify( '+90 days' )->format( 'Y-m-d' ) ); ?>" value="<?php echo esc_attr( isset( $_POST['fecha'] ) ? sanitize_text_field( wp_unslash( $_POST['fecha'] ) ) : '' ); ?>"></label>
				<label><?php esc_html_e( 'Hora preferida', 'medmovil' ); ?><select name="hora" required><option value=""><?php esc_html_e( 'Selecciona', 'medmovil' ); ?></option><?php foreach ( medmovil_horas() as $h ) : ?><option value="<?php echo esc_attr( $h ); ?>" <?php selected( isset( $_POST['hora'] ) ? sanitize_text_field( wp_unslash( $_POST['hora'] ) ) : '', $h ); ?>><?php echo esc_html( $h ); ?></option><?php endforeach; ?></select></label>
				<label><?php esc_html_e( 'Teléfono de contacto', 'medmovil' ); ?><input type="tel" name="fono" required maxlength="40" value="<?php echo esc_attr( isset( $_POST['fono'] ) ? sanitize_text_field( wp_unslash( $_POST['fono'] ) ) : get_user_meta( get_current_user_id(), '_mm_fono', true ) ); ?>"></label>
				<label class="reserva-ancho"><?php esc_html_e( 'Dirección', 'medmovil' ); ?><input type="text" name="direccion" required maxlength="250" autocomplete="street-address" value="<?php echo esc_attr( isset( $_POST['direccion'] ) ? sanitize_text_field( wp_unslash( $_POST['direccion'] ) ) : get_user_meta( get_current_user_id(), '_mm_direccion', true ) ); ?>"></label></div>
			</div></div>
			<aside class="reserva-final"><div><strong><?php esc_html_e( 'Total de referencia', 'medmovil' ); ?></strong><b id="reservaTotal">$0</b><p><?php esc_html_e( 'Pagas al momento de la atención. No hay cobro en línea ni hora confirmada hasta que nuestro equipo te contacte.', 'medmovil' ); ?></p></div><button class="btn btn-p" type="submit"><?php medmovil_icono( 'i-calcheck' ); ?> <?php esc_html_e( 'Enviar solicitud de hora', 'medmovil' ); ?></button></aside>
		</form>
	<?php endif; ?>
</div></section>
<?php get_footer(); ?>
