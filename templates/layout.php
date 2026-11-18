<?php
/**
 * @var string|null $titulo
 * @var string $contenido HTML ya renderizado de la página
 * @var string $apiUrl
 * @var string $whatsapp
 */
use Floreria\Web\Menu;

?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=880">
<title><?= $titulo ? e($titulo) . ' :: ' : '' ?>Florería Estherlicia</title>
<meta name="description" content="Florería Estherlicia: arreglos florales para toda ocasión. Delivery a todo Lima y provincias las 24 horas.">
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" href="/assets/css/estilos.css?v=2">
<script src="/assets/js/sitio.js?v=2" defer></script>
</head>
<body data-api="<?= e($apiUrl) ?>" data-whatsapp="<?= e($whatsapp) ?>">
<div id="todo_bg">

  <header id="header">
    <nav class="nav-top" aria-label="Principal">
      <a href="/">Inicio</a> | <a href="/nuestra-empresa">Nuestra Empresa</a> | <a href="/contactenos">Contáctenos</a>
    </nav>
  </header>

  <div id="contenido">
    <main class="contenido-der">
      <div class="rotador" aria-hidden="true">
        <?php for ($i = 0; $i <= 13; $i++): ?>
          <img src="/assets/img/banners/bnn<?= $i ?>.jpg" alt="" class="<?= $i === 0 ? 'activo' : '' ?>" <?= $i > 0 ? 'loading="lazy"' : '' ?> width="642" height="204">
        <?php endfor ?>
      </div>
      <?= $contenido ?>
    </main>

    <aside class="contenido-izq">
      <div class="fondo-cate-top2"></div>
      <div class="fnd-cates">
        <a href="/ofertas" class="boton-pr">OFERTAS</a>
        <a href="/cuidado-de-las-flores" class="boton-pr">CUIDADO DE LAS FLORES</a>
        <a href="/dedicatorias" class="boton-pr">DEDICATORIAS</a>

        <div class="select-tipo">
          <label class="oculto" for="sel-precio">Buscar por precio</label>
          <select id="sel-precio" class="select-lado js-navegar">
            <option value="">Precio Soles</option>
            <?php foreach (Menu::RANGOS_PRECIO as $clave => [$min, $max]): ?>
              <option value="/precio?rango=<?= e($clave) ?>">S/. <?= $min ?><?= $max ? ' a S/. ' . $max : ' +' ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="select-tipo">
          <label class="oculto" for="sel-tipo">Tipo de flores</label>
          <select id="sel-tipo" class="select-tipo-txt js-navegar">
            <option value="">TIPO DE FLORES</option>
            <?php foreach (Menu::TIPOS as $slug => $nombre): ?>
              <option value="/tipo/<?= e($slug) ?>"><?= e($nombre) ?></option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="td-categorias">CATEGORÍAS</div>
      </div>

      <?php foreach (Menu::grupos() as $grupo => $categorias): ?>
        <div class="fondo-cate-top"></div>
        <div class="fondo-cate-middle">
          <span class="color-rojo2"><?= e(mb_strtoupper($grupo)) ?></span>
          <?php foreach ($categorias as $c): ?>
            <a href="/categoria/<?= e($c['slug']) ?>" class="boton"><?= e($c['nombre']) ?></a>
          <?php endforeach ?>
        </div>
        <div class="fondo-cate-bottom"></div>
      <?php endforeach ?>

      <div class="fondo-cate-top"></div>
      <div class="fondo-cate-middle">
        <span class="color-rojo2">BUSCAR PRODUCTO</span>
        <form action="/buscar" method="get" class="buscar">
          <label class="oculto" for="prefijo">Prefijo</label>
          <select name="prefijo" id="prefijo" class="select-buscar">
            <?php foreach (Menu::PREFIJOS as $p): ?>
              <option value="<?= e($p) ?>"<?= $p === 'ANI-' ? ' selected' : '' ?>><?= e($p) ?></option>
            <?php endforeach ?>
          </select>
          <label class="oculto" for="numero">Número</label>
          <input name="numero" id="numero" class="input-buscar" inputmode="numeric" pattern="\d{1,6}" maxlength="6" required>
          <button type="submit" class="btn-buscar"><img src="/assets/img/buscar.png" width="32" height="32" alt="Buscar"></button>
          <div class="ejemplo">Ej. ANI-1503</div>
        </form>
      </div>
      <div class="fondo-cate-bottom"></div>
    </aside>
  </div>

  <footer id="foot">
    <span class="credito">Desarrollado por: <a href="http://www.webstardata.com/" class="link_webstardata" rel="noopener" target="_blank">WEBSTARDATA</a> - 2607600</span>
  </footer>
</div>
</body>
</html>
