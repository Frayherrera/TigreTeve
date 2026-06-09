<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $categorias = [
            'Nacional',
            'Internacional',
            'Política',
            'Economía',
            'Deportes',
            'Cultura',
            'Tecnología',
            'Sociedad',
            'Opinión',
            'Salud',
            'Medio Ambiente'
        ];

        foreach ($categorias as $categoria) {
            Categoria::create([
                'nombre' => $categoria,
                'slug'   => Str::slug($categoria),
            ]);
        }
    }
}
