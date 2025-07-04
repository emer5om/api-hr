<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::create([
            'name' => 'Admin Principal',
            'email' => 'admin@horaly.com', // Use o seu email
            'password' => Hash::make('Appsenha123@'), // Mude para uma senha segura
        ]);
    }
}