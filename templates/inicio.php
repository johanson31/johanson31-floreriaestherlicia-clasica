<?php /** @var Floreria\Web\Vista $vista */ ?>
<h1 class="titulo-base">Bienvenidos a Florería Estherlicia</h1>
<div class="datos">
  <p class="texto12">El compromiso de la empresa es satisfacer las necesidades de nuestros clientes con precios competitivos y excelente servicio.
    Realizamos arreglos florales para todo tipo de compromisos sociales.<br>
    Floreria Estherlicia brinda además servicio de <strong>Delivery a todo Lima y Provincias las 24 horas del día.</strong></p>

  <div class="portada-categorias">
    <?php foreach ([
        ['cumpleanos', 'cate-cumple.jpg', 'Cumpleaños'],
        ['estuches', 'cate-estu.jpg', 'Estuches'],
        ['baby-showers', 'cate-naci.jpg', 'Baby Showers'],
        ['inauguraciones', 'cate-ina.jpg', 'Inauguraciones'],
        ['lagrimas', 'cate-lagri.jpg', 'Lágrimas'],
        ['portalagrimas', 'cate-porta.jpg', 'Portalágrimas'],
        ['coronas', 'cate-coro.jpg', 'Coronas'],
        ['cruces', 'cate-cruces.jpg', 'Cruces'],
    ] as [$slug, $img, $nombre]): ?>
      <a href="/categoria/<?= e($slug) ?>"><img src="/assets/img/categorias/<?= e($img) ?>" alt="<?= e($nombre) ?>" width="140" height="200" class="box-prod"></a>
    <?php endforeach ?>
  </div>

  <p class="frase-centro">"Las flores embellecen las buenas ocasiones y suavizan las tristes.<br>
    Cualquier cosa que quiera decir dígalo con Flores."</p>

  <?= $vista->parcial('partials/contacto-pie') ?>
</div>
