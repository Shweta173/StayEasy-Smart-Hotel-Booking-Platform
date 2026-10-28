<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'admin@hotel.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ],
            [
                'name' => 'Hotel Manager',
                'email' => 'manager@hotel.com',
                'password' => Hash::make('manager123'),
                'role' => 'manager',
            ],
            [
                'name' => 'Front Desk Staff',
                'email' => 'frontdesk@hotel.com',
                'password' => Hash::make('frontdesk123'),
                'role' => 'front_desk',
            ],
        ];

        foreach ($admins as $admin) {
            AdminUser::updateOrCreate(
                ['email' => $admin['email']],
                $admin
            );
        }

        $this->command->info('Admin users created successfully!');
        $this->command->info('Admin: admin@hotel.com / admin123');
        $this->command->info('Manager: manager@hotel.com / manager123');
        $this->command->info('Front Desk: frontdesk@hotel.com / frontdesk123');
    }
}
