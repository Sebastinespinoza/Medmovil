/**
 * MedMóvil — portales. Solo lo justo: abrir el menú en el teléfono y dos atajos
 * en la grilla de disponibilidad.
 */
(function () {
	'use strict';

	var d = document;
	var $ = function (s) { return d.querySelector(s); };
	var $$ = function (s) { return Array.prototype.slice.call(d.querySelectorAll(s)); };

	/* ---- menú lateral en el teléfono ---- */
	var side = $('#pnSide');
	var burger = $('#pnBurger');
	var bg = $('#pnBg');

	if (side && burger && bg && !d.querySelector('.pn-pulse')) {
		var abrir = function () {
			side.classList.add('open');
			bg.hidden = false;
			d.body.style.overflow = 'hidden';
		};
		var cerrar = function () {
			side.classList.remove('open');
			bg.hidden = true;
			d.body.style.overflow = '';
		};

		burger.addEventListener('click', abrir);
		bg.addEventListener('click', cerrar);
		d.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') { cerrar(); }
		});
	}

	/* ---- atajos de la grilla de disponibilidad ---- */
	var limpiar = $('#grillaLimpiar');
	if (limpiar) {
		limpiar.addEventListener('click', function () {
			$$('.grilla input[type=checkbox]').forEach(function (c) { c.checked = false; });
		});
	}

	var habil = $('#grillaHabil');
	if (habil) {
		habil.addEventListener('click', function () {
			$$('.grilla input[type=checkbox]').forEach(function (c) {
				var dia = parseInt(c.name.replace(/\D/g, ''), 10);
				var hora = parseInt(c.value.split(':')[0], 10);
				c.checked = dia >= 1 && dia <= 5 && hora >= 9 && hora <= 18;
			});
		});
	}

	/* ---- confirmación antes de subir un informe al paciente equivocado ---- */
	$$('.ucard').forEach(function (form) {
		form.addEventListener('submit', function (e) {
			var archivo = form.querySelector('input[type=file]');
			if (!archivo || !archivo.files.length) { return; }
			/* El nombre del paciente viene en el atributo: en la tarjeta el <b> es
			   la lista de exámenes, no la persona. */
			var paciente = form.getAttribute('data-paciente') || '';
			var ok = window.confirm(
				'Vas a subir "' + archivo.files[0].name + '" como informe de:\n\n' +
				paciente +
				'\n\nLe llega por correo de inmediato. ¿Es el archivo correcto?'
			);
			if (!ok) { e.preventDefault(); }
		});
	});
})();
