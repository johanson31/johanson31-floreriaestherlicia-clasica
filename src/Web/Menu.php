<?php

declare(strict_types=1);

namespace Floreria\Web;

/**
 * Estructura del menú lateral, igual a la web antigua.
 *
 * Es fija a propósito (la antigua también lo era): los slugs y los ids son los de
 * las categorías en Laravel, y los ids sirven para redirigir las URLs viejas
 * (prod-de.php?cate=18 -> /categoria/cruces).
 */
final class Menu
{
    private const FRASE_SOCIAL = '"Que alegría compartir éste día tan especial. Las flores son el lenguaje del corazón."';
    private const FRASE_DUELO = '"Nunca estamos preparados para perder lo que más amamos, pero Dios siempre está preparado para ayudarnos con ésa pérdida. Que la presencia de Dios alivie su corazón y su alma y que su amor le acompañe con la tranquilidad que sólo El puede brindar."';
    public const INTRO = 'El compromiso de la empresa es satisfacer las necesidades de nuestros clientes con precios competitivos y excelente servicio. Realizamos arreglos florales para todo tipo de compromisos sociales.';

    /** Grupo => [id, slug, nombre en el menú]. */
    private const GRUPOS = [
        'Compromisos Sociales' => [
            [27, 'tulipanes', 'Tulipanes'],
            [1, 'cumpleanos', 'Cumpleaños'],
            [22, 'tortas', 'Tortas'],
            [10, 'estuches', 'Estuches'],
            [3, 'topiarios', 'Topiarios'],
            [4, 'nacimientos', 'Nacimientos'],
            [20, 'aniversarios', 'Aniversarios'],
            [7, 'inauguraciones', 'Inauguraciones'],
            [6, 'quinceaneros', 'Quinceañeros'],
            [2, 'bautizo-confirmacion-y-1ra-comunion', 'Bautizo, Confirmación y Primera Comunión'],
            [8, 'panel-con-logo-de-empresa', 'Panel con logo Empresa'],
            [21, 'personajes', 'Personajes'],
            [11, 'accesorios', 'Accesorios'],
        ],
        'Eventos' => [
            [5, 'matrimonios', 'Matrimonios'],
            [24, 'showers', 'Showers'],
            [13, 'baby-showers', 'Baby Showers'],
            [12, 'fiesta-de-promocion', 'Fiesta de Promoción'],
        ],
        'Defunciones' => [
            [15, 'lagrimas', 'Lágrimas'],
            [14, 'mantos', 'Mantos'],
            [16, 'portalagrimas', 'Portalágrimas'],
            [17, 'coronas', 'Coronas'],
            [18, 'cruces', 'Cruces'],
            [19, 'almohadas-corazones-y-cojines', 'Almohadas, Corazones y Cojines'],
            [9, 'pie-de-feretro', 'Pie de Féretro'],
        ],
        'Ofrendas Fúnebres' => [
            [23, 'alfombras', 'Alfombras'],
        ],
    ];

    /** Frase bajo el título, por grupo; algunas categorías tenían la suya. */
    private const FRASES = [
        'Compromisos Sociales' => self::FRASE_SOCIAL,
        'Eventos'              => self::INTRO,
        'Defunciones'          => self::FRASE_DUELO,
        'Ofrendas Fúnebres'    => self::FRASE_DUELO,
    ];
    private const FRASES_CATEGORIA = [
        'matrimonios'               => '"Yo no me preocupo de mi felicidad, todo el tiempo lo invierto en la tuya. De la mía sé que te preocupas tú."',
        'panel-con-logo-de-empresa' => self::INTRO,
        'accesorios'                => self::INTRO,
    ];

    /** Categorías donde la web antigua siempre mostraba "(consultar precio)". */
    private const CONSULTAR_PRECIO = ['matrimonios', 'quinceaneros', 'panel-con-logo-de-empresa', 'accesorios', 'fiesta-de-promocion', 'showers'];

    /** Selectores del menú lateral. */
    public const TIPOS = ['gerberas' => 'Gerberas', 'girasoles' => 'Girasoles', 'hortencias' => 'Hortencias', 'lilium' => 'Lilium', 'orquideas' => 'Orquídeas', 'rosas' => 'Rosas', 'tulipanes' => 'Tulipanes'];
    public const RANGOS_PRECIO = ['25-90' => [25, 90], '91-150' => [91, 150], '151-300' => [151, 300], '301-500' => [301, 500], '501' => [501, null]];
    public const PREFIJOS = ['ACC-', 'ALF-', 'ALM-', 'ANI-', 'BAB-', 'BAU-', 'COR-', 'CRU-', 'CUM-', 'EST-', 'FIE-', 'INA-', 'LAG-', 'MAN-', 'MAT-', 'NAC-', 'PAN-', 'PER-', 'PIE-', 'POR-', 'QUI-', 'SHO-', 'TOP-', 'TOR-', 'TULI-'];

    /** @return array<string, list<array{id: int, slug: string, nombre: string}>> */
    public static function grupos(): array
    {
        return array_map(
            static fn (array $cats): array => array_map(
                static fn (array $c): array => ['id' => $c[0], 'slug' => $c[1], 'nombre' => $c[2]],
                $cats,
            ),
            self::GRUPOS,
        );
    }

    /** @return array{id: int, slug: string, nombre: string, grupo: string, frase: string, consultarPrecio: bool}|null */
    public static function categoria(string $slug): ?array
    {
        foreach (self::GRUPOS as $grupo => $cats) {
            foreach ($cats as [$id, $s, $nombre]) {
                if ($s === $slug) {
                    return [
                        'id'              => $id,
                        'slug'            => $s,
                        'nombre'          => $nombre,
                        'grupo'           => $grupo,
                        'frase'           => self::FRASES_CATEGORIA[$s] ?? self::FRASES[$grupo],
                        'consultarPrecio' => in_array($s, self::CONSULTAR_PRECIO, true),
                    ];
                }
            }
        }

        return null;
    }

    public static function slugPorId(int $id): ?string
    {
        foreach (self::GRUPOS as $cats) {
            foreach ($cats as [$cid, $slug]) {
                if ($cid === $id) {
                    return $slug;
                }
            }
        }

        return null;
    }

    public static function consultarPrecio(string $slug): bool
    {
        return in_array($slug, self::CONSULTAR_PRECIO, true);
    }
}
