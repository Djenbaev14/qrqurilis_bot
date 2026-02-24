<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $role=Role::create([
            'name'=>'Super Admin',
            'guard_name'=>'web'
        ]);

        // user create
        \App\Models\User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password' => Hash::make('admin'),
        ])->assignRole('Super Admin');
    }
}
