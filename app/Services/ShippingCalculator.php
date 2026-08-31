<?php

namespace App\Services;

use App\Models\ShippingMethod;
use App\Models\Address;
use App\Helpers\NumberHelper;

class ShippingCalculator
{
    /**
     * محاسبه هزینه ارسال بر اساس روش ارسال
     *
     * @return array{price: string, freight_balance: int}
     */
    public static function calculate(ShippingMethod $shippingMethod, float $weightSum, float $finalPriceSum, ?Address $address = null): array
    {
        $price = '';
        $freightBalance = 0;

        if (! $shippingMethod) {
            return ['price' => $price, 'freight_balance' => $freightBalance];
        }

        match ($shippingMethod->type) {
            'fixed_price' => $price = $shippingMethod->price ?? 0,

            'with_weight' => $price = ($weightSum / 100) * ($shippingMethod->price ?? 0),

            'chapar' => $price = self::chaparQuote($shippingMethod, $weightSum, $finalPriceSum, $address),

            default => $price = $shippingMethod->price ?? 0,
        };

        // پس کرایه → ارسال رایگان
        if ($shippingMethod->freight_balance == 1) {
            $price = 0;
            $freightBalance = 1;
        }

        // سقف قیمت → ارسال رایگان
        if ($shippingMethod->price_ceiling > 0 && $finalPriceSum > $shippingMethod->price_ceiling) {
            $price = 0;
        }

        return ['price' => $price, 'freight_balance' => $freightBalance];
    }

    /**
     * استعلام قیمت چاپار
     */
    protected static function chaparQuote(ShippingMethod $shippingMethod, float $weightSum, float $finalPriceSum, ?Address $address): string
    {
        try {
            $response = \App\Services\ChaparShipment::quote([
                'origin' => $shippingMethod->senderCity?->chapar_id,
                'destination' => $address?->city?->chapar_id,
                'weight' => $weightSum / 1000, // وزن به کیلوگرم
                'value' => $finalPriceSum . '0', // قیمت به ریال
                'method' => $shippingMethod->chapar_type,
            ]);

            if (isset($response['result']) && $response['result'] === true) {
                $totalCost = $response['objects']['order']['price']['fld_Total_Cost'] ?? 0;
                return ($totalCost / 10); // ریال به تومان
            }

            return 0;
        } catch (\Exception $e) {
            \Log::error('Chapar quote failed', ['message' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * دریافت روش‌های ارسال موجود بر اساس آدرس
     *
     * @return array
     */
    public static function getAvailableMethods(Address $address, float $weightSum, float $finalPriceSum): array
    {
        $basket = session('cart', []);
        $methods = [];

        $shippingMethods = $address->city?->availableShippingMethods ?? collect();

        foreach ($shippingMethods as $shipment) {
            $shipmentWeightSum = $weightSum;
            if ($shipment->type === 'with_weight') {
                $shipmentWeightSum = ceil($weightSum / 100) * 100;
            }

            if (in_array($shipment->type, ['with_weight', 'fixed_price'])) {
                $result = self::calculate($shipment, $shipmentWeightSum, $finalPriceSum, $address);
                $price = $result['price'];
            } else {
                // برای chapar، قیمت لحظه‌ای محاسبه می‌شود
                $price = 'dynamic';
            }

            $methods[] = [
                'id' => $shipment->id,
                'title' => $shipment->title,
                'type' => $shipment->type,
                'freight_balance' => $shipment->freight_balance,
                'description' => $shipment->description,
                'price' => $price,
                'estimated_days_min' => $shipment->estimated_days_min ?? 1,
                'estimated_days_max' => $shipment->estimated_days_max ?? 3,
                'show_estimated_days' => $shipment->show_estimated_days ?? true,
            ];
        }

        return $methods;
    }
}
