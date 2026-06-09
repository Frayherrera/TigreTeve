<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Roles
        $admin = Role::create(['name' => 'Administrator']);
        $editor = Role::create(['name' => 'Editor']);
        $reportero = Role::create(['name' => 'Reportero']);
        $lector = Role::create(['name' => 'Lector']);
        
        $permisos = ['crear noticias','editar noticias','eliminar noticias','ver noticias'];

        foreach($permisos as $permiso){
            Permission::firstOrCreate(['name'=>$permiso]);
        }

        $admin->givePermissionTo($permisos);
        $editor->givePermissionTo(['crear noticias', 'editar noticias','ver noticias']);
        $reportero->givePermissionTo(['crear noticias','ver noticias']);
        $lector->givePermissionTo(['ver noticias']);
    }
}
