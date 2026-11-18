<?php
/**
 * @var Floreria\Catalogo\Producto $producto
 * @var bool $consultarPrecio
 */
$precio = ($consultarPrecio || ! $producto->tienePrecio()) ? '(consultar precio)' : $producto->precioFormateado();
?>
<h1 class="titulo-base">Detalles del pedido <a href="/categoria/<?= e($producto->categoriaSlug) ?>" class="link_regresar js-regresar">&lt;- Regresar</a></h1>
<div class="datos">
  <p class="texto12">El compromiso de la empresa es satisfacer las necesidades de nuestros clientes con precios competitivos y excelentes servicios.
    Realizamos arreglos florales para todo tipo de compromisos sociales.<br>
    Floreria Estherlicia brinda además servicio de <strong>Delivery a todo Lima y Provincias las 24 horas del día.</strong></p>

  <form class="form-pedido js-form-api" data-tipo="pedido" novalidate
        data-codigo="<?= e($producto->codigo) ?>" data-precio="<?= e($precio) ?>">
    <div class="pedido-cols">
      <fieldset class="col-comprador">
        <legend class="titulo-tabla">DATOS DEL COMPRADOR</legend>
        <label>Nombre completo <span>:</span><input name="nombre" required maxlength="100" autocomplete="name"></label>
        <label>País <span>:</span><input name="pais" required maxlength="50" autocomplete="country-name"></label>
        <label>Distrito <span>:</span><input name="distrito" required maxlength="60"></label>
        <label>Teléfono <span>:</span><input name="telefono" required maxlength="20" inputmode="tel" autocomplete="tel"></label>
        <label>Correo <span>:</span><input name="email" type="email" required maxlength="100" autocomplete="email"></label>
      </fieldset>
      <div class="col-producto">
        <div class="titulo-tabla centro">Producto: <?= e($producto->codigo) ?></div>
        <img src="<?= e($producto->fotoMiniatura) ?>" alt="<?= e($producto->nombre) ?>" width="100" class="borde">
        <p class="texto12 centro"><?= e($producto->descripcion) ?></p>
        <p class="centro"><strong><?= e($precio) ?></strong></p>
      </div>
    </div>
    <label class="ancho">Accesorios para el arreglo floral <span>:</span><input name="accesorios" maxlength="150">
      <small>ej. (globo, peluche, chocolate)</small></label>

    <fieldset>
      <legend class="titulo-tabla">DATOS DEL DESTINATARIO</legend>
      <label class="ancho">Entregar a <span>:</span><input name="entregar_a" required maxlength="100"></label>
      <label class="ancho">Distrito <span>:</span><input name="distrito_entrega" required maxlength="60"></label>
      <label class="ancho">Dirección <span>:</span><input name="direccion" required maxlength="150"></label>
      <label class="ancho">Referencia <span>:</span><textarea name="referencia" maxlength="200" rows="2"></textarea></label>
      <label>Fecha entrega <span>:</span><input name="fecha" type="date" required></label>
      <label>Hora entrega <span>:</span><input name="hora" type="time" required></label>
      <label>Telf. entrega <span>:</span><input name="telefono_entrega" required maxlength="20" inputmode="tel"></label>
      <label class="ancho">Dedicatoria <span>:</span><textarea name="dedicatoria" maxlength="400" rows="3"></textarea></label>
    </fieldset>

    <p class="aviso">Los precios no incluyen gastos de movilidad ni IGV</p>
    <p class="centro"><button type="submit" class="btn-img"><img src="/assets/img/btn-enviar.jpg" alt="Enviar" width="100" height="22"></button></p>
    <p class="centro texto-estado js-estado" role="status">Todos los campos obligatorios, salvo accesorios, referencia y dedicatoria.</p>
  </form>

  <div class="gracias" hidden>
    <h2 class="titulo-base">¡Gracias por su pedido!</h2>
    <p class="texto12">Recibimos su pedido de <strong><?= e($producto->codigo) ?></strong>. Nos comunicaremos con usted para confirmarlo.</p>
    <p class="centro"><a class="btn-whatsapp js-whatsapp" href="#" target="_blank" rel="noopener">Enviar también por WhatsApp</a></p>
  </div>
</div>
