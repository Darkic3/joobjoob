<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $mainRole = Role::where('name', 'main')->first();

        $users = [
            ['name' => 'علی احمدی', 'email' => 'ali@joobjoob.test', 'mobile' => '09121111111', 'is_admin' => false, 'is_active' => true],
            ['name' => 'سارا کریمی', 'email' => 'sara@joobjoob.test', 'mobile' => '09122222222', 'is_admin' => false, 'is_active' => true],
            ['name' => 'محمد رضایی', 'email' => 'mohammad@joobjoob.test', 'mobile' => '09123333333', 'is_admin' => false, 'is_active' => true],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['password' => Hash::make('password')])
            );
            if ($mainRole && ! $user->roles()->where('role_id', $mainRole->id)->exists()) {
                $user->roles()->attach($mainRole->id);
            }
        }

        $this->command?->info('۳ کاربر نمونه ساخته شد.');
    }
}
