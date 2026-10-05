<?php

namespace Database\Seeders;

use App\Models\Numero;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NumeroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numeros = [
            '3127382163',
            '3153035948',
        ];

        foreach ($numeros as $numero) {
            Numero::firstOrCreate(['numero' => $numero]);
        }
    }
}