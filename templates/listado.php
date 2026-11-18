<?php
/**
 * @var Floreria\Web\Vista $vista
 * @var string $encabezado
 * @var string $frase
 * @var Floreria\Catalogo\PaginaProductos $pagina
 * @var string $rutaBase
 * @var array<string, string> $queryExtra
 * @var string $sinResultados
 */
use Floreria\Web\Menu;

$url = static fn (int $n): string => $rutaBase . '?' . http_build_query($queryExtra + ['pagina' => $n]);
?>
<h1 class="titulo-base"><?= e($encabezado) ?></h1>
<div class="datos">
  <?php if ($frase !== ''): ?>
    <p class="texto12"><strong><?= e($frase) ?></strong></p>
  <?php endif ?>

  <?php if ($pagina->productos === []): ?>
    <p class="sin-resultados"><?= e($sinResultados) ?></p>
  <?php else: ?>
    <div class="grilla">
      <?php foreach ($pagina->productos as $producto): ?>
        <?= $vista->parcial('partials/producto', [
            'producto'        => $producto,
            'consultarPrecio' => Menu::consultarPrecio($producto->categoriaSlug),
        ]) ?>
      <?php endforeach ?>
    </div>
  <?php endif ?>

  <p class="text-centro-abajo">"Las flores embellecen las buenas ocasiones y suavizan las tristes.<br>
    Cualquier cosa que quiera decir dígalo con Flores."</p>

  <?php if ($pagina->ultimaPagina > 1): ?>
    <nav class="paginador" aria-label="Páginas">
      <?php if ($pagina->paginaActual > 1): ?>
        <a href="<?= e($url($pagina->paginaActual - 1)) ?>"><img src="/assets/img/ico-atras.png" width="16" height="16" alt="Anterior"></a>
      <?php endif ?>
      <?php for ($n = 1; $n <= $pagina->ultimaPagina; $n++): ?>
        <?php if ($n > 1): ?> - <?php endif ?>
        <?php if ($n === $pagina->paginaActual): ?>
          <span class="paginator-hover" aria-current="page"><?= $n ?></span>
        <?php else: ?>
          <a href="<?= e($url($n)) ?>" class="paginator-a"><?= $n ?></a>
        <?php endif ?>
      <?php endfor ?>
      <?php if ($pagina->paginaActual < $pagina->ultimaPagina): ?>
        <a href="<?= e($url($pagina->paginaActual + 1)) ?>"><img src="/assets/img/ico-siguiente.png" width="16" height="16" alt="Siguiente"></a>
      <?php endif ?>
    </nav>
  <?php endif ?>
</div>
