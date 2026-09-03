<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('is_admin', false)->take(3)->get();
        $tehran = State::where('name', 'تهران')->first();
        $tehranCity = City::where('name', 'تهران')->first();

        if ($users->isEmpty() || ! $tehran || ! $tehranCity) {
            return;
        }

        $addresses = [
            ['state_id' => $tehran->id, 'city_id' => $tehranCity->id, 'address' => 'تهران، خیابان ولیعصر، کوچه گل، پلاک ۱۲، واحد ۳', 'receiptor_full_name' => 'علی احمدی', 'receiptor_mobile' => '09121111111', 'postal_code' => '1234567890'],
            ['state_id' => $tehran->id, 'city_id' => $tehranCity->id, 'address' => 'تهران، خیابان آزادی، نبش کوچه باغ، پلاک ۴۵', 'receiptor_full_name' => 'سارا کریمی', 'receiptor_mobile' => '09122222222', 'postal_code' => '0987654321'],
        ];

        foreach ($users as $i => $user) {
            if (isset($addresses[$i])) {
                Address::updateOrCreate(
                    ['user_id' => $user->id, 'receiptor_mobile' => $addresses[$i]['receiptor_mobile']],
                    $addresses[$i]
                );
            }
        }

        $this->command?->info('آدرس‌های نمونه ساخته شد.');
    }
}
