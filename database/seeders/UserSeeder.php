<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Admin', 'last_name' => 'Système',
                'email' => 'admin@center.com', 'username' => 'admin',
                'password' => 'Admin123',
                'role' => 'administrator',
            ],
            [
                'first_name' => 'Jean', 'last_name' => 'Martin',
                'email' => 'dr.martin@healthcenter.cm', 'username' => 'dr.martin',
                'role' => 'general_practitioner',
            ],
            [
                'first_name' => 'Sophie', 'last_name' => 'Dubois',
                'email' => 'dr.dubois@healthcenter.cm', 'username' => 'dr.dubois',
                'role' => 'specialist',
            ],
            [
                'first_name' => 'Marie', 'last_name' => 'Kamga',
                'email' => 'nurse.kamga@healthcenter.cm', 'username' => 'infirmiere.kamga',
                'role' => 'nurse',
            ],
            [
                'first_name' => 'Paul', 'last_name' => 'Mbarga',
                'email' => 'pharmacien@healthcenter.cm', 'username' => 'pharmacien',
                'role' => 'pharmacist',
            ],
            [
                'first_name' => 'Élodie', 'last_name' => 'Nkengue',
                'email' => 'elodie.nkengue@healthcenter.cm', 'username' => 'elodie.nkengue',
                'role' => 'pharmacist',
            ],
            [
                'first_name' => 'Hervé', 'last_name' => 'Biyong',
                'email' => 'herve.biyong@healthcenter.cm', 'username' => 'herve.biyong',
                'role' => 'pharmacist',
            ],
            [
                'first_name' => 'Alice', 'last_name' => 'Fotso',
                'email' => 'caissier@healthcenter.cm', 'username' => 'caissier',
                'role' => 'cashier',
            ],
            [
                'first_name' => 'David', 'last_name' => 'Njoya',
                'email' => 'receptionniste@healthcenter.cm', 'username' => 'receptionniste',
                'role' => 'receptionist',
            ],
            [
                'first_name' => 'Christine', 'last_name' => 'Talla',
                'email' => 'directeur@healthcenter.cm', 'username' => 'directeur',
                'role' => 'director',
            ],

            // Pharmacy roles
            [
                'first_name' => 'Stéphane', 'last_name' => 'Essomba',
                'email' => 'preparateur@healthcenter.cm', 'username' => 'preparateur',
                'role' => 'preparateur',
            ],
            [
                'first_name' => 'Bertrand', 'last_name' => 'Zanga',
                'email' => 'stockmanager@healthcenter.cm', 'username' => 'stockmanager',
                'role' => 'stock_manager',
            ],
        ];

        foreach ($users as $data) {
            $password = $data['password'] ?? 'Password1';

            $user = User::firstOrCreate(
                ['username' => $data['username']],
                [
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'email'      => $data['email'],
                    'password'   => Hash::make($password),
                    'is_active'  => true,
                ]
            );

            if (!$user->wasRecentlyCreated) {
                $user->update(['password' => Hash::make($password)]);
            }

            $user->syncRoles([$data['role']]);
        }
    }
}
