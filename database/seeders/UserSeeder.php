<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'OCRI UNCP',
            'email' => 'cooperaciontecnica@uncp.edu.pe',
            'password' => 'Cooperacion@2025',
        ]);
    }
}
