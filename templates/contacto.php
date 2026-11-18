<?php /** @var Floreria\Web\Vista $vista */ ?>
<h1 class="titulo-base">Contáctenos</h1>
<div class="datos">
  <form class="form-contacto js-form-api" data-tipo="contacto" novalidate>
    <label class="ancho">Nombre completo <span>:</span><input name="nombre" required maxlength="100" autocomplete="name"> *</label>
    <label class="ancho">Empresa <span>:</span><input name="empresa" maxlength="100" autocomplete="organization"></label>
    <label class="ancho">Dirección <span>:</span><input name="direccion" maxlength="150"></label>
    <label>Teléfono <span>:</span><input name="telefono" maxlength="20" inputmode="tel" autocomplete="tel"></label>
    <label class="ancho">Correo <span>:</span><input name="email" type="email" required maxlength="100" autocomplete="email"> *</label>
    <label class="ancho">Comentario <span>:</span><textarea name="comentario" required maxlength="1500" rows="5"></textarea> *</label>
    <p class="centro">
      <button type="reset" class="btn-img"><img src="/assets/img/btn-limpiar.jpg" alt="Limpiar" width="100" height="22"></button>
      <button type="submit" class="btn-img"><img src="/assets/img/btn-enviar.jpg" alt="Enviar" width="100" height="22"></button>
    </p>
    <p class="centro texto-estado js-estado" role="status">(*) Campos obligatorios.</p>
  </form>
  <div class="gracias" hidden>
    <h2 class="titulo-base">¡Gracias por escribirnos!</h2>
    <p class="texto12">Recibimos su mensaje y le responderemos a la brevedad.</p>
  </div>

  <?= $vista->parcial('partials/contacto-pie') ?>
</div>
