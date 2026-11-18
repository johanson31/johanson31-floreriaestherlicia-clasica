/*
 * Florería Estherlicia - comportamiento de la web clásica.
 * Reemplaza jQuery 1.4, jquery.cross-slide, highslide y el script de margaritas
 * de la web antigua (librerías abandonadas, con vulnerabilidades conocidas).
 * Sin dependencias y sin código en línea (lo exige la CSP).
 */
(function () {
  'use strict';

  var body = document.body;
  var API = (body.dataset.api || '').replace(/\/$/, '');
  var WHATSAPP = body.dataset.whatsapp || '';

  // --- Selectores de precio y tipo: navegan al elegir (solo rutas propias) ---
  document.querySelectorAll('.js-navegar').forEach(function (sel) {
    sel.addEventListener('change', function () {
      if (sel.value.charAt(0) === '/') { window.location.href = sel.value; }
    });
  });

  // --- "Regresar": vuelve atrás si se vino desde el mismo sitio ---
  document.querySelectorAll('.js-regresar').forEach(function (a) {
    a.addEventListener('click', function (ev) {
      if (document.referrer.indexOf(window.location.origin) === 0) { ev.preventDefault(); history.back(); }
    });
  });

  // --- Rotador de banners (fundido + paneo, como cross-slide) ---
  var banners = document.querySelectorAll('.rotador img');
  if (banners.length > 1) {
    var actual = 0;
    setInterval(function () {
      banners[actual].classList.remove('activo', 'arriba');
      actual = (actual + 1) % banners.length;
      banners[actual].removeAttribute('loading');
      banners[actual].classList.toggle('arriba', actual % 2 === 1);
      banners[actual].classList.add('activo');
    }, 6000);
  }

  // --- Visor de fotos (reemplaza highslide) ---
  document.querySelectorAll('.js-ampliar').forEach(function (a) {
    a.addEventListener('click', function (ev) {
      ev.preventDefault();
      var visor = document.createElement('div');
      visor.className = 'visor';
      var fig = document.createElement('figure');
      var img = document.createElement('img');
      img.src = a.getAttribute('href');
      img.alt = a.dataset.caption || '';
      var cap = document.createElement('figcaption');
      cap.textContent = a.dataset.caption || ''; // textContent: nunca se interpreta como HTML
      fig.appendChild(img);
      fig.appendChild(cap);
      visor.appendChild(fig);
      function cerrar() { visor.remove(); document.removeEventListener('keydown', esc); }
      function esc(e) { if (e.key === 'Escape') { cerrar(); } }
      visor.addEventListener('click', cerrar);
      document.addEventListener('keydown', esc);
      body.appendChild(visor);
    });
  });

  // --- Margaritas que caen (la animación de la web original) ---
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var flores = [];
    for (var i = 0; i < 8; i++) {
      var f = document.createElement('img');
      f.src = '/assets/img/margaritas-caen.png';
      f.alt = '';
      f.className = 'margarita';
      body.appendChild(f);
      flores.push({ el: f, x: Math.random() * (innerWidth - 70), y: Math.random() * innerHeight, vel: Math.random() * 2 + 1, paso: Math.random() * 0.05 + 0.02, c: 0 });
    }
    (function caer() {
      flores.forEach(function (fl) {
        fl.y += fl.vel;
        fl.x += Math.cos(fl.c) * 1.2;
        fl.c += fl.paso;
        if (fl.y > innerHeight) { fl.y = -60; fl.x = Math.random() * (innerWidth - 70); }
        fl.el.style.transform = 'translate(' + Math.min(innerWidth - 40, fl.x) + 'px,' + (fl.y + scrollY) + 'px)';
      });
      requestAnimationFrame(caer);
    })();
  }

  // --- Formularios de pedido y contacto -> API de Laravel (/api/contacto) ---
  function valor(form, nombre) {
    var campo = form.elements[nombre];
    return campo ? campo.value.trim() : '';
  }

  function armarPedido(form) {
    var d = form.dataset;
    var lineas = [
      'PEDIDO WEB - Producto ' + d.codigo + ' - ' + d.precio,
      '',
      'COMPRADOR',
      'Nombre: ' + valor(form, 'nombre'),
      'País / Distrito: ' + valor(form, 'pais') + ' / ' + valor(form, 'distrito'),
      'Teléfono: ' + valor(form, 'telefono'),
      'Correo: ' + valor(form, 'email'),
      'Accesorios: ' + (valor(form, 'accesorios') || '-'),
      '',
      'DESTINATARIO',
      'Entregar a: ' + valor(form, 'entregar_a'),
      'Distrito: ' + valor(form, 'distrito_entrega'),
      'Dirección: ' + valor(form, 'direccion'),
      'Referencia: ' + (valor(form, 'referencia') || '-'),
      'Fecha y hora: ' + valor(form, 'fecha') + ' ' + valor(form, 'hora'),
      'Teléfono entrega: ' + valor(form, 'telefono_entrega'),
      'Dedicatoria: ' + (valor(form, 'dedicatoria') || '-')
    ];
    return { asunto: 'Pedido web ' + d.codigo, mensaje: lineas.join('\n') };
  }

  function armarContacto(form) {
    return {
      asunto: 'Contacto web' + (valor(form, 'empresa') ? ' - ' + valor(form, 'empresa') : ''),
      mensaje: [
        'Empresa: ' + (valor(form, 'empresa') || '-'),
        'Dirección: ' + (valor(form, 'direccion') || '-'),
        '',
        valor(form, 'comentario')
      ].join('\n')
    };
  }

  document.querySelectorAll('.js-form-api').forEach(function (form) {
    var estado = form.querySelector('.js-estado');
    var boton = form.querySelector('button[type=submit]');
    var gracias = form.parentNode.querySelector('.gracias');

    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      estado.classList.remove('error');
      if (!form.checkValidity()) {
        form.reportValidity();
        estado.textContent = 'Revise los campos marcados.';
        estado.classList.add('error');
        return;
      }

      var datos = form.dataset.tipo === 'pedido' ? armarPedido(form) : armarContacto(form);
      var payload = {
        nombre: valor(form, 'nombre'),
        email: valor(form, 'email'),
        telefono: valor(form, 'telefono'),
        asunto: datos.asunto.slice(0, 200),
        mensaje: datos.mensaje.slice(0, 2000)
      };

      boton.disabled = true;
      estado.textContent = 'Enviando...';

      fetch(API + '/api/contacto', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      }).then(function (r) {
        if (r.status === 429) { throw new Error('Demasiados envíos seguidos. Espere un minuto e intente de nuevo.'); }
        if (r.status === 422) { throw new Error('Revise el correo y los campos obligatorios.'); }
        if (!r.ok) { throw new Error('No se pudo enviar. Llámenos al 349-9273.'); }
        form.hidden = true;
        gracias.hidden = false;
        var wa = gracias.querySelector('.js-whatsapp');
        if (wa && WHATSAPP) {
          wa.href = 'https://wa.me/' + WHATSAPP + '?text=' + encodeURIComponent(datos.mensaje);
        } else if (wa) {
          wa.hidden = true;
        }
        gracias.scrollIntoView({ behavior: 'smooth' });
      }).catch(function (err) {
        // TypeError = sin conexión o API caída: mensaje genérico, no el texto técnico del navegador.
        estado.textContent = (err instanceof TypeError || !err.message) ? 'No se pudo enviar. Llámenos al 349-9273.' : err.message;
        estado.classList.add('error');
        boton.disabled = false;
      });
    });
  });
})();
