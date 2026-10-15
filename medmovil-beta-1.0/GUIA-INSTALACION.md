# MedMóvil 1.6.1 — guía de puesta en marcha

La versión 1.6.1 corrige un error de sintaxis en `inc/portal/auth.php` que impedía abrir el sitio al instalar la 1.6.0. Si estás recuperando una instalación afectada y no puedes entrar a WordPress, reemplaza ese archivo dentro de la carpeta activa del tema mediante el administrador de archivos del alojamiento. Comprueba que la página vuelve a cargar y luego instala el ZIP completo de esta versión.

Esta versión conserva las páginas, precios y fotos editados en WordPress. Crea la página **Solicitar hora** al actualizar un sitio que ya usa el tema.

## Qué puede hacer cada persona

- **Paciente:** crea cuenta en `/portal/`, solicita exámenes en `/reservar/`, ve solicitudes y horas confirmadas, informes y pagos registrados.
- **Operador:** entra en `/portal/?acceso=operador` con una cuenta creada por administración con rol **Profesional MedMóvil**. Ve su agenda, sus pacientes asignados, disponibilidad y subida de informes.
- **Administración:** revisa **Solicitudes pendientes** en el portal, abre la atención en WordPress, comprueba disponibilidad y credenciales, asigna un operador y cambia el estado a **Confirmada**. El paciente recibe un correo de confirmación.

El cliente elige una **fecha y una hora preferidas**. La solicitud no bloquea un cupo ni cobra en línea. El valor se calcula de nuevo en el servidor con los precios publicados. **Mis pagos** muestra importes que el equipo marcó como cobrados; no constituye una boleta.

## Configuración rápida

1. Haz copia de los archivos y la base de datos. Actualiza el tema desde **Apariencia > Temas** en una instalación de ensayo y comprueba `/reservar/` y `/portal/`.
2. En **Usuarios > Añadir nuevo**, crea las cuentas del equipo y asigna el rol **Profesional MedMóvil** después de comprobar identidad, registro profesional y competencias correspondientes a cada prestación. La creación pública de cuentas solo da el rol Paciente.
3. En **Prestaciones > Editar**, agrega o reemplaza la **Imagen destacada** en la barra lateral. La foto aparece en el catálogo y en la página individual. No subas imágenes clínicas con datos identificables a la biblioteca de medios pública. Para cambiar los SVG, entra a **MedMóvil > Iconos**.
4. Haz una solicitud de prueba con un paciente nuevo. Verifica que figura como **Pendiente de confirmación**, asígnale un operador y cambia el estado a **Confirmada**. Comprueba el aviso por correo, la agenda del operador y que **Mis pagos** siga vacío hasta registrar un cobro real.

## Documentos clínicos y correo

Los informes se entregan mediante un endpoint que comprueba sesión y asignación. Por defecto se guardan en `wp-content/uploads/medmovil-privado`, protegidos por `.htaccess` **solo en servidores que lo respetan**. Para otros servidores, configura `MEDMOVIL_PRIVATE_DIR` en `wp-config.php` con una ruta absoluta **fuera de todo directorio servido por la web**, y comprueba permisos y respaldos. Si ya hay informes, mueve los archivos existentes a esa ruta antes de activarla; sus nombres guardados en la base de datos se mantienen.

Prueba también que un visitante sin sesión no pueda descargar un PDF por URL directa, que un paciente no vea el informe de otro y que el envío de correos funcione en el alojamiento. El flujo de firma/validación de informes y las credenciales del operador requieren revisión clínica y jurídica antes de usar datos reales de pacientes.

## Pendiente de definición

“Calificar pacientes” podría significar valorar la experiencia de la visita, verificar criterios para hacer el examen o permitir que el paciente evalúe al operador. No se añadió una puntuación de personas sin precisar qué quieres medir y quién podría verla.
