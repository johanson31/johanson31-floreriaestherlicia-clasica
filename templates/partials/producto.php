<?php
/**
 * @var Floreria\Catalogo\Producto $producto
 * @var bool $consultarPrecio
 */
?>
<div class="td-color producto">
  <a href="<?= e($producto->fotoGrande ?: $producto->fotoMiniatura) ?>" class="js-ampliar" title="Ampliar" data-caption="Código: <?= e($producto->codigo) ?>">
    <img src="<?= e($producto->fotoMiniatura) ?>" alt="<?= e($producto->nombre) ?>" width="100" loading="lazy">
  </a>
  <span class="texto-pre">Código: <?= e($producto->codigo) ?></span>
  <strong class="desc"><?= e($producto->descripcion) ?></strong>
  <span class="precio">
    <?php if ($consultarPrecio || ! $producto->tienePrecio()): ?>
      (consultar precio)
    <?php else: ?>
      Precio: <span class="color-rojo"><?= e($producto->precioFormateado()) ?></span>
    <?php endif ?>
  </span>
  <a href="/pedido/<?= rawurlencode($producto->codigo) ?>" class="seleccionar">Seleccionar pedido</a>
</div>
