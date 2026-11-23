<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@docuflow.com'],
            [
                'name' => 'Administrador DocuFlow',
                'password' => Hash::make('Admin123*'),
                'role' => 'Administrador',
            ]
        );

        User::updateOrCreate(
            ['email' => 'operador@docuflow.com'],
            [
                'name' => 'Operador Tramite',
                'password' => Hash::make('Operador123*'),
                'role' => 'Operador',
            ]
        );

        $categories = [
            ['name' => 'Administrativo', 'description' => 'Oficios, memorandos y comunicaciones internas'],
            ['name' => 'Legal', 'description' => 'Contratos, resoluciones y convenios'],
            ['name' => 'Financiero', 'description' => 'Comprobantes, ordenes de servicio y presupuestos'],
            ['name' => 'Academico', 'description' => 'Informes de facultad, syllabus y actas'],
        ];

        foreach ($categories as $cat) {
            DocumentCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}