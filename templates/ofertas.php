<?php
/**
 * @var Floreria\Web\Vista $vista
 * @var Floreria\Catalogo\PaginaProductos $pagina
 */
?>
<h1 class="titulo-base">Ofertas</h1>
<div class="datos">
  <p class="texto12">El compromiso de la empresa es satisfacer las necesidades de nuestros clientes con precios competitivos y excelente servicio.
    Realizamos arreglos florales para todo tipo de compromisos sociales.<br>
    Floreria Estherlicia brinda además servicio de <strong>Delivery a todo Lima y Provincias las 24 horas del día.</strong></p>

  <?php if ($pagina->productos === []): ?>
    <p class="text-centro-abajo">Proximamente Nuevas OFERTAS!</p>
    <p class="tarjetas"><img src="/assets/img/banners/bnn12.jpg" alt="" width="500"></p>
  <?php else: ?>
    <div class="grilla">
      <?php foreach ($pagina->productos as $producto): ?>
        <?= $vista->parcial('partials/producto', ['producto' => $producto, 'consultarPrecio' => false]) ?>
      <?php endforeach ?>
    </div>
  <?php endif ?>
</div>
