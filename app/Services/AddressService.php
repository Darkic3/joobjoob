<?php

namespace App\Services;

use App\Models\Address;

class AddressService
{
    public static function find()
    {
        $addresses = Address::orderBy('id', 'DESC')
            ->authUser()
            ->with(['city', 'state'])
            ->get();

        return ['addresses' => $addresses];
    }

    public static function findOne($addressId)
    {
        return Address::findOrFail($addressId);
    }

    public static function create($userId, $stateId, $cityId, $address, $postalCode, $receiptorFullName, $receiptorMobile)
    {
        return Address::create([
            'user_id' => $userId,
            'state_id' => $stateId,
            'city_id' => $cityId,
            'address' => $address,
            'postal_code' => $postalCode,
            'receiptor_full_name' => $receiptorFullName,
            'receiptor_mobile' => $receiptorMobile,
        ]);
    }

    public static function update($addressId, $userId, $stateId, $cityId, $address, $postalCode, $receiptorFullName, $receiptorMobile)
    {
        $location = Address::findOrFail($addressId);

        $location->update([
            'user_id' => $userId,
            'state_id' => $stateId,
            'city_id' => $cityId,
            'address' => $address,
            'postal_code' => $postalCode,
            'receiptor_full_name' => $receiptorFullName,
            'receiptor_mobile' => $receiptorMobile,
        ]);

        return $location;
    }

    public static function delete($addressId)
    {
        $address = Address::findOrFail($addressId);
        $address->delete();
    }
}
