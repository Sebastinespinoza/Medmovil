# MedMóvil 1.8.3 — Ajustes visuales y lateralidad

- Carrusel de categorías con holgura superior/inferior para hover, foco y sombras.
- Se retira la onda decorativa que se cortaba en el borde inferior de las tarjetas.
- Se sustituye el contorno rectangular de foco de las zonas SVG por un resaltado sobre el propio contorno anatómico; el teclado sigue funcionando.
- La selección resalta una zona anatómica concreta, no todas las zonas del mismo grupo. Muestra “Mano derecha”, “Mano izquierda”, etc. tanto en el panel como bajo la figura, con aviso accesible.
- Corrige una región de codo derecho etiquetada incorrectamente como hombro izquierdo, y añade el lado al codo izquierdo. No se modificaron las geometrías del SVG.
- Imágenes de las prestaciones de 160 a 210 px de alto en escritorio y 190 px en celular pequeño. Conserva tres tarjetas por fila en escritorio amplio, fotos editables y secciones inferiores.

El selector es una ilustración SVG frontal/posterior, no un modelo 3D. La lateralidad identifica la zona seleccionada en la interfaz; no se incorpora aún como dato clínico a una reserva. Los resultados siguen agrupados por región. Las fotos se editan en Prestaciones > Imagen destacada.

Instalar como actualización después de respaldar y limpiar la caché. Revisión local de JavaScript y selección con eventos simulados; pendiente comprobación visual y de PHP en WordPress.
