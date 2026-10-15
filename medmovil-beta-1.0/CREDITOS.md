# Créditos de terceros

## Figura anatómica del selector de zona

El trazado del cuerpo humano que usa el bloque "Muéstranos dónde te molesta"
(`inc/cuerpo.php`) se adaptó del pen **Body_selector** de Keyhole:

- https://codepen.io/yarrumk/pen/WbzvoM
- Licencia MIT · Copyright (c) 2026 Keyhole

Se conservó la geometría de las regiones anatómicas y se reemplazó por completo
el marcado, los estilos, los nombres de las zonas y la interacción.

## Iconos

El tema usa dos juegos de iconos, los dos dentro del mismo sprite SVG
(`inc/icons.php`), normalizados a 24×24 y con `currentColor` para que hereden el
color del texto.

**Coolicons** — los iconos de interfaz (`i-*`: flechas, calendario, carrito,
campana, candado…):

- https://github.com/krystonschwarze/coolicons
- Licencia MIT · Copyright (c) Kryston Schwarze

**UIcons de Flaticon** — todos los iconos médicos (`il-*` y los `i-*`
anatómicos: pulmones, corazón anatómico, hueso, radiografía, tubo de muestra,
enfermera, silla de ruedas, ambulancia):

- https://www.flaticon.com/uicons
- Licencia Flaticon gratuita · **exige atribución**

Los UIcons se distribuyen como tipografía. Cargarla entera son 377 KB por el
estilo *bold rounded*, así que se extrajeron **solo los glifos que el tema
ocupa** y se convirtieron a trazados SVG dentro del sprite: unos 15 KB en vez de
377. La licencia autoriza expresamente hacer modificaciones y obras derivadas.

La atribución obligatoria («Uicons by Flaticon», con enlace) va en el pie de
todas las páginas. Se puede quitar desde MedMóvil → Ajustes **solo si se
contrata la suscripción Premium de Flaticon**; sin ella, quitarla incumple la
licencia.

Ojo con una cosa: la licencia gratuita de Flaticon es **intransferible**. Cubre
el uso de MedMóvil en su sitio. Si algún día este tema se le vende o se le
instala a un tercero, esos iconos hay que reemplazarlos o el tercero necesita su
propia licencia.

## Qué se tomó de cada referencia que enviaste

Ninguna otra referencia aportó código. De todas se tomó una idea de interacción
o de estructura, y se volvió a escribir desde cero con los estilos del tema:

| Referencia | Qué se rescató | Dónde vive ahora |
|---|---|---|
| `body_selector` | La figura anatómica clicable | `inc/cuerpo.php` — 32 regiones mapeadas a 15 grupos de exámenes reales |
| `dailyui012-ecommerce-shop-single-item` | La caja de compra: imagen grande, precio, qué incluye, botón de agregar | `single-mm_prestacion.php` — bloque `.svc-side` |
| `ecommerce-landing-page-example` | Ficha de producto con precio y botón en la misma tarjeta | `medmovil_tarjeta_prestacion()` en `inc/render.php` |
| `ecommerce-product-delicious-apples` | El carrito lateral con total y vaciado | `inc/carrito.php` |
| `multi-step-form-with-progress-bar` | Los tres pasos con indicador de avance | Panel "Arma tu visita" del hero, `inc/hero.php` |
| `checkbox-group-styled-as-tiles` | Filtros como fichas en vez de casillas | Chips por línea de atención en `archive-mm_prestacion.php` |
| `mock-chat-animation` | La conversación con burbujas y los tres puntitos de "escribiendo" | `inc/chat.php` y `.chat-*` en `main.css` |
| `rotating-icon-buttons` | Botones flotantes que se encogen para no tapar la página | `.dock` — el asistente pasa a círculo al bajar |
| `hamburger-menu-animations` | Menú móvil de pantalla completa | Ya estaba en la cabecera desde la primera entrega |
| `responsive-landing-page-tailwind-medical` | Franja de garantías bajo el hero | `.strip` en la portada |
| `svg-loading-icons` | Trazos SVG que se dibujan solos | Ondas del hero y subrayado del titular |
| `penfolio` | Tarjetas de personas con foto, rol y datos | `.team` — profesionales por comuna |
| Panel de ventas tipo *e-commerce dashboard* | La estructura del panel: fila de indicadores arriba, gráficos al medio, tabla de movimientos abajo | `inc/portal/` — los tres portales |

No se copió CSS, HTML ni JavaScript de ninguno de esos archivos: el tema no
carga Tailwind, jQuery ni ninguna librería externa.

## Todo lo demás

El resto del tema —estilos, ilustraciones, plantillas, contenido clínico y
código PHP y JavaScript— es original de MedMóvil.

El mapa de comunas de la Región Metropolitana se generó a partir de datos
públicos de límites comunales de Chile.

Tipografías: Archivo, Instrument Sans e Instrument Serif, servidas por Google
Fonts bajo licencia SIL Open Font License.

## three.js

`assets/vendor/three/three.module.min.js` (versión 0.160.0) dibuja la figura 3D del selector de zona. Licencia MIT, ver `assets/vendor/three/LICENSE.txt`. https://threejs.org
