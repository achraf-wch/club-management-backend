<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserRolesSeeder extends Seeder
{
    public function run()
    {
        Person::firstOrCreate(
            ['email' => 'achrafwandich1@gmail.com'],
            [
                'first_name' => 'Achraf',
                'last_name'  => 'Wandich',
                'password'   => Hash::make('achraf123'),
                'member_code' => 'ADMIN002',
                'role'       => 'admin',
                'is_active'  => true,
            ]
        );

        Person::firstOrCreate(
            ['email' => 'souhaylaelabboudy2@gmail.com'],
            [
                'first_name' => 'Souhayla',
                'last_name'  => 'Elabboudy',
                'password'   => Hash::make('zofy123'),
                'member_code' => 'ADMIN003',
                'role'       => 'admin',
                'is_active'  => true,
            ]
        );

        $this->command->info('✅ Admins created!');
        $this->command->info('👑 achrafwandich1@gmail.com       | achraf123');
        $this->command->info('👑 souhaylaelabboudy2@gmail.com   | zofy123');
    }
}