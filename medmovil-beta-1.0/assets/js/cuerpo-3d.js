/* ============================================================
   Selector de zona en 3D.

   Una figura de maniquí armada con piezas simples (cápsulas y
   esferas), una por zona, que el paciente gira arrastrando y toca
   para ver los exámenes. Usa el mismo panel y los mismos grupos que
   la figura plana de inc/cuerpo.php, que queda como respaldo cuando
   el navegador no tiene WebGL o si el paciente la prefiere.

   three.js (MIT) va dentro del tema en assets/vendor/three y solo se
   descarga cuando la sección está por aparecer en pantalla.
   ============================================================ */

const d = document;
const caja = d.getElementById('mm3d');
const figPlana = d.querySelector('.mmbody');
const volver = d.getElementById('mm3dVolver');
const pista = d.getElementById('zonasSeleccion');

const TEXTO_3D = 'Arrastra la figura para girarla y toca la zona donde te molesta.';
const TEXTO_PLANO = 'Vista espejo: posterior a la izquierda y frontal a la derecha. Selecciona una zona.';

function hayWebGL() {
	try {
		const c = d.createElement('canvas');
		return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
	} catch (e) {
		return false;
	}
}

function mostrar3D(si) {
	caja.hidden = !si;
	if (figPlana) { figPlana.style.display = si ? 'none' : ''; }
	if (volver) { volver.hidden = si; }
	if (pista) {
		pista.dataset.base = si ? TEXTO_3D : TEXTO_PLANO;
		if (!d.querySelector('.mmbody .mmz.act') || si) { pista.textContent = pista.dataset.base; }
	}
}

if (caja && figPlana && hayWebGL()) {
	mostrar3D(true);

	let iniciado = false;
	const arrancar = () => {
		if (iniciado) { return; }
		iniciado = true;
		import(caja.dataset.three)
			.then((THREE) => iniciar(THREE))
			.catch(() => mostrar3D(false));
	};

	if ('IntersectionObserver' in window) {
		const io = new IntersectionObserver((entradas) => {
			if (entradas.some((e) => e.isIntersecting)) { io.disconnect(); arrancar(); }
		}, { rootMargin: '300px' });
		io.observe(caja);
	} else {
		arrancar();
	}

	caja.querySelector('[data-vista="2d"]').addEventListener('click', () => mostrar3D(false));
	if (volver) { volver.addEventListener('click', () => mostrar3D(true)); }
}

/* ------------------------------------------------------------ */

function iniciar(THREE) {
	const lienzo = d.getElementById('mm3dLienzo');
	const tip = d.getElementById('mm3dTip');
	const reducido = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// Nombres de cada zona: los mismos <title> de la figura plana.
	const nombres = {};
	d.querySelectorAll('.mmbody .mmz').forEach((z) => {
		const t = z.querySelector('title');
		if (t) { nombres[z.dataset.zona] = t.textContent; }
	});

	/* ---------- colores del tema ---------- */
	const muestra = d.createElement('span');
	muestra.style.display = 'none';
	caja.appendChild(muestra);
	function colorDe(variable, respaldo) {
		muestra.style.color = respaldo;
		muestra.style.color = 'var(' + variable + ')';
		return new THREE.Color(getComputedStyle(muestra).color);
	}
	const oscuro = () => {
		const tema = d.documentElement.getAttribute('data-theme');
		return tema === 'dark' || (tema !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
	};
	let col = {};
	function leerColores() {
		col = {
			base: oscuro() ? colorDe('--surface-3', '#2a3440') : colorDe('--blue-soft', '#dbe7f5'),
			hover: colorDe('--blue-bright', '#4a90e2'),
			act: colorDe('--blue', '#1f5fae'),
		};
	}
	leerColores();

	/* ---------- escena ---------- */
	const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
	renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
	lienzo.appendChild(renderer.domElement);
	renderer.domElement.setAttribute('aria-hidden', 'true');

	const escena = new THREE.Scene();
	const camara = new THREE.PerspectiveCamera(26, 1, 0.1, 50);
	camara.position.set(0, 0.95, 4.3);
	camara.lookAt(0, 0.9, 0);

	escena.add(new THREE.HemisphereLight(0xffffff, 0xb9cbe0, 1.25));
	const sol = new THREE.DirectionalLight(0xffffff, 1.5);
	sol.position.set(1.8, 3, 3.5);
	escena.add(sol);
	const contra = new THREE.DirectionalLight(0xffffff, 0.55);
	contra.position.set(-2, 1.5, -3);
	escena.add(contra);

	// Sombra suave en el piso: un degradado radial, sin mapas de sombra.
	const sombraTx = (() => {
		const c = d.createElement('canvas');
		c.width = c.height = 128;
		const g = c.getContext('2d');
		const r = g.createRadialGradient(64, 64, 4, 64, 64, 64);
		r.addColorStop(0, 'rgba(11,53,96,.28)');
		r.addColorStop(1, 'rgba(11,53,96,0)');
		g.fillStyle = r;
		g.fillRect(0, 0, 128, 128);
		return new THREE.CanvasTexture(c);
	})();
	const piso = new THREE.Mesh(
		new THREE.PlaneGeometry(0.9, 0.5),
		new THREE.MeshBasicMaterial({ map: sombraTx, transparent: true, depthWrite: false })
	);
	piso.rotation.x = -Math.PI / 2;
	piso.position.y = 0.002;
	escena.add(piso);

	// El cuerpo gira alrededor de su propio eje, a media altura.
	const cuerpo = new THREE.Group();
	const pivote = new THREE.Group();
	pivote.position.y = 0.9;
	cuerpo.position.y = -0.9;
	pivote.add(cuerpo);
	escena.add(pivote);

	/* ---------- piezas ---------- */
	const zonas = [];
	const ARRIBA = new THREE.Vector3(0, 1, 0);

	function material() {
		return new THREE.MeshStandardMaterial({ color: col.base.clone(), roughness: 0.6, metalness: 0.04 });
	}
	function pieza(geo, zona, grupo) {
		const m = new THREE.Mesh(geo, material());
		m.userData = { zona: zona, grupo: grupo };
		cuerpo.add(m);
		zonas.push(m);
		return m;
	}
	function esfera(zona, grupo, r, x, y, z, esc) {
		const m = pieza(new THREE.SphereGeometry(r, 32, 20), zona, grupo);
		m.position.set(x, y, z || 0);
		if (esc) { m.scale.set(esc[0], esc[1], esc[2]); }
		return m;
	}
	// Cápsula entre dos puntos (brazos, piernas).
	function hueso(zona, grupo, r, a, b) {
		const ini = new THREE.Vector3(a[0], a[1], a[2] || 0);
		const fin = new THREE.Vector3(b[0], b[1], b[2] || 0);
		const dir = fin.clone().sub(ini);
		const largo = dir.length();
		const m = pieza(new THREE.CapsuleGeometry(r, Math.max(largo - 2 * r, 0.001), 8, 20), zona, grupo);
		m.position.copy(ini).add(fin).multiplyScalar(0.5);
		m.quaternion.setFromUnitVectors(ARRIBA, dir.normalize());
		return m;
	}
	// Medio tronco: un perfil girado (torno) sobre media vuelta, la mitad de
	// adelante o la de atrás, aplastado de frente para que no sea un tubo.
	function tronco(zona, grupo, perfil, adelante) {
		const puntos = perfil.map((p) => new THREE.Vector2(p[0], p[1]));
		const geo = new THREE.LatheGeometry(puntos, 28, adelante ? -Math.PI / 2 : Math.PI / 2, Math.PI);
		const m = pieza(geo, zona, grupo);
		m.material.side = THREE.DoubleSide;
		m.scale.z = 0.62;
		return m;
	}

	// Perfiles (radio, altura) de abajo hacia arriba. Adelante tórax, abdomen
	// y pelvis; atrás espalda alta, espalda baja y glúteos.
	const T = [
		{ p: [[0.16, 1.175], [0.17, 1.25], [0.182, 1.33], [0.185, 1.38], [0.168, 1.418], [0.12, 1.442], [0.05, 1.452]], f: ['chest', 'torax'], b: ['upper_back', 'columna'] },
		{ p: [[0.15, 0.985], [0.145, 1.04], [0.149, 1.1], [0.158, 1.169]], f: ['torso', 'abdomen'], b: ['lower_back', 'columna'] },
		{ p: [[0.06, 0.835], [0.125, 0.848], [0.16, 0.878], [0.166, 0.93], [0.151, 0.979]], f: ['pelvis', 'pelvis'], b: ['buttocks', 'cadera'] },
	];
	T.forEach((t) => {
		tronco(t.f[0], t.f[1], t.p, true);
		tronco(t.b[0], t.b[1], t.p, false);
	});

	// Cabeza y cuello.
	esfera('head_x0020_', 'tiroides', 0.1, 0, 1.655, 0.005, [0.86, 1.06, 0.96]);
	hueso('neck', 'tiroides', 0.05, [0, 1.43], [0, 1.56]);

	// Lado derecho del paciente = x negativo (la figura mira de frente a la cámara).
	[
		{ s: -1, l: 'right' },
		{ s: 1, l: 'left' },
	].forEach((lado) => {
		const s = lado.s;
		const n = (base) => lado.l + '_' + base;

		esfera(lado.l === 'left' ? 'left_shoulder_0' : 'right_shoulder', 'hombro', 0.068, s * 0.212, 1.392, 0, [1, 0.92, 0.9]);
		hueso(n('upper_arm'), 'brazo', 0.047, [s * 0.232, 1.35], [s * 0.262, 1.115]);
		esfera(n('elbow'), 'codo', 0.044, s * 0.266, 1.087);
		hueso(n('lower_arm'), 'brazo', 0.039, [s * 0.272, 1.058, 0.004], [s * 0.288, 0.85, 0.012]);
		esfera(n('wrist'), 'muneca', 0.032, s * 0.29, 0.825, 0.012);
		esfera(n('hand'), 'mano', 0.05, s * 0.296, 0.745, 0.016, [0.62, 1.25, 0.38]);

		esfera(n('hip'), 'cadera', 0.05, s * 0.143, 0.915, 0, [0.8, 1, 1.1]);
		hueso(n('thigh'), 'muslo', 0.074, [s * 0.092, 0.86], [s * 0.1, 0.535]);
		esfera(n('knee'), 'rodilla', 0.057, s * 0.1, 0.5, 0.008);
		hueso(n('shin'), 'pierna', 0.051, [s * 0.1, 0.468], [s * 0.1, 0.125]);
		esfera(n('ankle'), 'pie', 0.04, s * 0.1, 0.092);
		const pie = hueso(n('foot'), 'pie', 0.036, [s * 0.1, 0.038, -0.03], [s * 0.106, 0.034, 0.11]);
		pie.scale.x = 1.15;
	});

	zonas.forEach((m) => { m.userData.nombre = nombres[m.userData.zona] || ''; });

	/* ---------- dibujo a pedido ---------- */
	let pendiente = false;
	function dibujar() {
		if (pendiente) { return; }
		pendiente = true;
		requestAnimationFrame(() => {
			pendiente = false;
			renderer.render(escena, camara);
		});
	}

	function medir() {
		const w = lienzo.clientWidth;
		const h = lienzo.clientHeight;
		if (!w || !h) { return; }
		renderer.setSize(w, h, false);
		camara.aspect = w / h;
		// En pantallas angostas se aleja un poco para que entren los brazos.
		camara.position.z = w / h < 0.62 ? 4.9 : 4.3;
		camara.updateProjectionMatrix();
		dibujar();
	}
	if ('ResizeObserver' in window) { new ResizeObserver(medir).observe(lienzo); }
	window.addEventListener('resize', medir);
	medir();

	/* ---------- colores de cada pieza ---------- */
	let encima = null;
	let elegida = null;
	function pintar() {
		zonas.forEach((m) => {
			const z = m.userData.zona;
			const c = elegida && z === elegida ? col.act : (encima && z === encima ? col.hover : col.base);
			m.material.color.copy(c);
		});
		dibujar();
	}
	const tema = window.matchMedia('(prefers-color-scheme: dark)');
	const repintar = () => { leerColores(); pintar(); };
	if (tema.addEventListener) { tema.addEventListener('change', repintar); }
	new MutationObserver(repintar).observe(d.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

	/* ---------- giro ---------- */
	let giroY = 0.35;
	let giroX = 0;
	let animando = null;
	function aplicarGiro() {
		pivote.rotation.set(giroX, giroY, 0);
		dibujar();
	}
	function girarA(destino) {
		// Por el camino corto.
		let delta = ((destino - giroY) % (2 * Math.PI) + 3 * Math.PI) % (2 * Math.PI) - Math.PI;
		if (reducido) { giroY += delta; giroX = 0; aplicarGiro(); return; }
		const ini = giroY;
		const iniX = giroX;
		const t0 = performance.now();
		const dur = 650;
		if (animando) { cancelAnimationFrame(animando); }
		const paso = (t) => {
			const k = Math.min((t - t0) / dur, 1);
			const e = 1 - Math.pow(1 - k, 3);
			giroY = ini + delta * e;
			giroX = iniX * (1 - e);
			aplicarGiro();
			animando = k < 1 ? requestAnimationFrame(paso) : null;
		};
		animando = requestAnimationFrame(paso);
	}
	aplicarGiro();
	// Media vuelta de presentación para que se entienda que gira.
	if (!reducido) { setTimeout(() => girarA(0), 500); }

	caja.querySelector('[data-vista="frente"]').addEventListener('click', () => girarA(0));
	caja.querySelector('[data-vista="espalda"]').addEventListener('click', () => girarA(Math.PI));

	/* ---------- puntero: arrastrar gira, tocar elige ---------- */
	const rayo = new THREE.Raycaster();
	const punto = new THREE.Vector2();
	const lona = renderer.domElement;

	function tocada(ev) {
		const r = lona.getBoundingClientRect();
		punto.x = ((ev.clientX - r.left) / r.width) * 2 - 1;
		punto.y = -((ev.clientY - r.top) / r.height) * 2 + 1;
		rayo.setFromCamera(punto, camara);
		const hit = rayo.intersectObjects(zonas, false)[0];
		return hit ? hit.object : null;
	}

	let arrastre = null;
	lona.addEventListener('pointerdown', (ev) => {
		if (animando) { cancelAnimationFrame(animando); animando = null; }
		arrastre = { x: ev.clientX, y: ev.clientY, gy: giroY, gx: giroX, movio: false, id: ev.pointerId };
	});
	lona.addEventListener('pointermove', (ev) => {
		if (arrastre && arrastre.id === ev.pointerId) {
			const dx = ev.clientX - arrastre.x;
			const dy = ev.clientY - arrastre.y;
			if (!arrastre.movio && Math.hypot(dx, dy) > 6) {
				arrastre.movio = true;
				try { lona.setPointerCapture(ev.pointerId); } catch (e) { /* nada */ }
				lona.classList.add('girando');
				ocultarTip();
			}
			if (arrastre.movio) {
				giroY = arrastre.gy + dx * 0.011;
				giroX = Math.max(-0.35, Math.min(0.35, arrastre.gx + dy * 0.004));
				aplicarGiro();
			}
			return;
		}
		if (ev.pointerType !== 'mouse') { return; }
		const m = tocada(ev);
		const z = m ? m.userData.zona : null;
		lona.style.cursor = m ? 'pointer' : 'grab';
		if (z !== encima) { encima = z; pintar(); }
		if (m) { mostrarTip(m.userData.nombre, ev); } else { ocultarTip(); }
	});
	function soltar(ev) {
		if (!arrastre || arrastre.id !== ev.pointerId) { return; }
		const fue = arrastre;
		arrastre = null;
		lona.classList.remove('girando');
		if (fue.movio || ev.type === 'pointercancel') { return; }
		const m = tocada(ev);
		if (m) { elegir(m); }
	}
	lona.addEventListener('pointerup', soltar);
	lona.addEventListener('pointercancel', soltar);
	lona.addEventListener('pointerleave', () => {
		if (encima) { encima = null; pintar(); }
		ocultarTip();
	});

	function mostrarTip(texto, ev) {
		if (!tip || !texto) { return; }
		const r = caja.getBoundingClientRect();
		tip.textContent = texto;
		tip.style.left = (ev.clientX - r.left) + 'px';
		tip.style.top = (ev.clientY - r.top) + 'px';
		tip.hidden = false;
	}
	function ocultarTip() { if (tip) { tip.hidden = true; } }

	function elegir(m) {
		elegida = m.userData.zona;
		pintar();
		if (window.medmovilZonas) { window.medmovilZonas.elegir(m.userData.grupo, m.userData.nombre); }
		// En el celular el panel queda abajo: lo acercamos.
		if (window.matchMedia('(max-width: 900px)').matches) {
			const panel = d.getElementById('zonasPanel');
			if (panel) { panel.scrollIntoView({ behavior: reducido ? 'auto' : 'smooth', block: 'start' }); }
		}
	}

	d.addEventListener('mm:zona-limpiar', () => { elegida = null; pintar(); });

	pintar();
}
