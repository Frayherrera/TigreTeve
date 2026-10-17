```php
<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Noticia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class NoticiasRegionalesSeeder extends Seeder
{
    public function run(): void
    {
        $usuario = User::first();

        if (!$usuario) {
            throw new RuntimeException(
                'Debes crear al menos un usuario antes de ejecutar este seeder.'
            );
        }

        $noticias = [
            [
                'titulo' => 'Aguachica enfrenta un nuevo ataque contra la estación de Policía',
                'slug' => 'aguachica-ataque-estacion-policia-octubre-2026',
                'categoria' => 'Judicial',
                'resumen' => 'Un ataque con drones contra la estación de Policía de Aguachica dejó un detenido herido, según los reportes periodísticos del 5 de octubre de 2026.',
                'cuerpo' => <<<'TEXTO'
Un nuevo ataque contra la estación de Policía de Aguachica, Cesar, fue reportado durante la madrugada del 5 de octubre de 2026. De acuerdo con la información publicada por Caracol Radio, se escucharon varias detonaciones y un hombre que permanecía detenido resultó herido en una pierna.

Las autoridades reportaron su reacción ante los hechos y el caso volvió a poner la seguridad del municipio en el centro de la atención regional.

Esta nota es una síntesis informativa. Consulta la publicación original para conocer los detalles y las actualizaciones oficiales.

Fuente original:
https://caracol.com.co/2026/10/05/nuevo-ataque-con-drones-contra-estacion-de-policia-de-aguachica-deja-un-interno-herido/
TEXTO,
                'fuente' => 'Caracol Radio',
                'fecha' => '2026-10-05 10:50:00',
            ],
            [
                'titulo' => 'Anuncian obras para mejorar la movilidad entre Aguachica y Ocaña',
                'slug' => 'obras-viales-aguachica-ocana-septiembre-2026',
                'categoria' => 'Infraestructura',
                'resumen' => 'Un proyecto vial socializado en Ocaña contempla un puente elevado y una glorieta en la carretera que conecta con Aguachica.',
                'cuerpo' => <<<'TEXTO'
El 18 de septiembre de 2026, Caracol Radio informó sobre la socialización de un proyecto para mejorar la intersección vial ubicada en el PR 50+600 de la carretera Aguachica–Ocaña, correspondiente a la Ruta Nacional 7007.

Según el reporte, la iniciativa contempla la construcción de un puente elevado y una glorieta, con una inversión aproximada de 33.000 millones de pesos. La obra busca facilitar la circulación y reducir los conflictos entre vehículos en este punto de conexión regional.

Los plazos mencionados corresponden a las estimaciones publicadas en esa fecha y pueden cambiar según el desarrollo del proyecto.

Fuente original:
https://caracol.com.co/2026/09/18/puente-elevado-y-glorieta-transformaran-la-movilidad-entre-ocana-y-aguachica/
TEXTO,
                'fuente' => 'Caracol Radio',
                'fecha' => '2026-09-18 09:43:00',
            ],
            [
                'titulo' => 'Personería de Ocaña alerta sobre señalamientos en redes sociales',
                'slug' => 'personeria-ocana-alerta-senalamientos-redes-sociales-2026',
                'categoria' => 'Comunidad',
                'resumen' => 'La Personería de Ocaña alertó sobre perfiles en redes sociales que señalan a habitantes del Catatumbo y generan preocupación por su seguridad.',
                'cuerpo' => <<<'TEXTO'
Una publicación de Caracol Radio del 30 de septiembre de 2026 informó que más de ocho personas acudieron a la Personería de Ocaña después de aparecer señaladas en páginas de redes sociales por presuntos vínculos con grupos armados ilegales.

El personero señaló que estas publicaciones generan preocupación entre los afectados y sus familias. La entidad indicó que ha contribuido a activar rutas de atención y remisión ante las autoridades competentes.

El caso evidencia la importancia de verificar la información difundida en internet y de evitar compartir acusaciones no confirmadas contra personas de la región.

Fuente original:
https://caracol.com.co/2026/09/30/personeria-de-ocana-alerta-por-nuevas-paginas-que-senalan-a-habitantes-del-catatumbo/
TEXTO,
                'fuente' => 'Caracol Radio',
                'fecha' => '2026-09-30 10:24:00',
            ],
            [
                'titulo' => 'Ocaña será sede de un encuentro regional sobre seguridad',
                'slug' => 'ocana-encuentro-regional-seguridad-septiembre-2026',
                'categoria' => 'Región',
                'resumen' => 'Autoridades nacionales y regionales se reunieron en Ocaña para analizar la situación de seguridad del Catatumbo y coordinar medidas de protección.',
                'cuerpo' => <<<'TEXTO'
El 11 de septiembre de 2026, autoridades nacionales y regionales participaron en un encuentro de seguridad en Ocaña para revisar la situación de orden público del Catatumbo y municipios vecinos.

La información publicada por La Opinión menciona la participación del gobernador de Norte de Santander, representantes de los ministerios del Interior y de Defensa, y alcaldes de la región.

El encuentro estuvo orientado al análisis de los hechos recientes y a la coordinación de medidas para fortalecer la protección de la población civil.

Fuente original:
https://laopinion.co/region/gobernador-junto-los-ministros-de-defensa-y-del-interior-lideran-consejo-de-seguridad-en
TEXTO,
                'fuente' => 'La Opinión',
                'fecha' => '2026-09-11 12:00:00',
            ],
            [
                'titulo' => 'Río de Oro avanza en la liberación de zonas rurales de sospecha de minas',
                'slug' => 'rio-de-oro-zona-rural-libre-sospecha-minas-2026',
                'categoria' => 'Región',
                'resumen' => 'El Ejército informó sobre la entrega de una zona rural de Río de Oro después de actividades de desminado humanitario y educación preventiva.',
                'cuerpo' => <<<'TEXTO'
El Heraldo informó el 29 de agosto de 2026 sobre la entrega oficial de la Zona 1 del municipio de Río de Oro, Cesar, como resultado de labores de desminado humanitario.

El reporte indica que las actividades incluyeron intervenciones en sectores rurales y jornadas de educación para que las comunidades reconocieran los riesgos relacionados con minas antipersonales y otros artefactos explosivos.

Este avance busca contribuir a la seguridad de las comunidades rurales. La información debe interpretarse dentro del alcance específico de la zona declarada y no como una afirmación de que todo el municipio esté libre de riesgos.

Fuente original:
https://elheraldoco-elheraldo-prod.web.arc-cdn.net/colombia/2026/08/29/ejercito-declaro-a-rio-de-oro-libre-de-minas-antipersonales/
TEXTO,
                'fuente' => 'El Heraldo',
                'fecha' => '2026-08-29 12:00:00',
            ],
        ];

        foreach ($noticias as $dato) {
            $categoria = Categoria::firstOrCreate(
                ['slug' => Str::slug($dato['categoria'])],
                ['nombre' => $dato['categoria']]
            );

            Noticia::updateOrCreate(
                ['slug' => $dato['slug']],
                [
                    'user_id' => $usuario->id,
                    'category_id' => $categoria->id,
                    'titulo' => $dato['titulo'],
                    'resumen' => $dato['resumen'],
                    'cuerpo' => $dato['cuerpo'],
                    'estado' => 'publicada',
                    'publicado_en' => $dato['fecha'],
                    'fuente' => $dato['fuente'],
                    'portada_path' => null,
                    'is_featured' => false,
                ]
            );
        }
    }
}
```