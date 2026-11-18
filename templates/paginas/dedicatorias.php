<?php
$dedicatorias = [
    'Amor' => [
        'Esta fecha ha sido inolvidable desde que te conocí.',
        'Personas como tú son las que hacen la vida más hermosa.',
        'Las flores que ves representan la ternura, la pasión y la calidez que despiertas en mi.',
    ],
    'Amistad' => [
        'Con gratitud para una persona muy especial.',
        'Siempre es importante despertar y saber que contamos con un(a) gran amigo(a) como tu.',
        'Un buen amigo siempre está ahí para ayudar y dar buenos consejos, escucha con el corazón sincero y siempre dice la verdad.',
    ],
    'Felicitaciones' => [
        'Que Dios te de muchos años más de vida y de alegrías.',
        'Recibe en cada flor nuestros sinceros deseos de felicidad.',
        'Hay un día muy especial en el año, en que nos damos cuenta lo hermosa que es la vida y lo bueno que es tener con quien compartirla. Feliz Cumpleaños.',
        'Que la llegada de este bebé llene de alegría y felicidad tu hogar.',
        'Felicidades por este nuevo triunfo y que sea un escalón más para alcanzar el éxito.',
        'Madre, tu día no es solo hoy, son los 365 días del año.',
        'Como homenaje a tu ardua y eficiente labor, en el día de las secretarias.',
        'Para la más linda, dulce y cariñosa de las quinceañeras, en este día tan especial.',
    ],
    'Pésame' => [
        'Sinceramente, compartimos con ustedes estos momentos de pena.',
        'No olvides que en los amigos siempre encontrarás consuelo. Te acompañamos.',
        'Dios ilumine vuestro camino y les traiga fortaleza y resignación en esta hora de dolor.',
    ],
    'Para los enfermos' => [
        'Mis más sinceros deseos por tu pronta recuperación.',
        'Tu salud es sinónimo de vida y nuestra vida no es igual sin ti. Te necesitamos.',
        'Animo. Recuerda que siempre has sabido sobreponerte a las dificultades con entereza y alegría. Deseamos que te mejores.',
    ],
];
?>
<h1 class="titulo-base">Dedicatorias</h1>
<div class="datos texto12">
  <p>Con las flores las personas expresan amor, amistad, felicitaciones y pésame, entre otros sentimientos. Es muy importante encontrar las palabras justas para acompañar los arreglos florales. Aquí les presentamos algunos bonitos ejemplos:</p>
  <?php foreach ($dedicatorias as $tema => $frases): ?>
    <ul class="lista-no-check"><li><?= e($tema) ?>:</li></ul>
    <ul class="lista-check">
      <?php foreach ($frases as $f): ?><li><?= e($f) ?></li><?php endforeach ?>
    </ul>
  <?php endforeach ?>
</div>
