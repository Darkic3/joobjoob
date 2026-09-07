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
     * محاسبه قیمت خام ارسال (بدون اعمال سقف رایگان)
     */
    protected static function calculateRaw(ShippingMethod $shippingMethod, float $weightSum, float $finalPriceSum, ?Address $address): float
    {
        $price = 0;

        match ($shippingMethod->type) {
            'fixed_price' => $price = $shippingMethod->price ?? 0,
            'with_weight' => $price = ($weightSum / 100) * ($shippingMethod->price ?? 0),
            default => $price = $shippingMethod->price ?? 0,
        };

        // پس کرایه → ارسال رایگان (این همیشه اعمال می‌شود)
        if ($shippingMethod->freight_balance == 1) {
            $price = 0;
        }

        return floatval($price);
    }

    /**
     * استعلام قیمت چاپار
     */
    
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
            }

            // محاسبه قیمت اصلی ارسال (قبل از اعمال سقف رایگان) برای نمایش مبلغ سود
            $originalPrice = $price;
            if ($shipment->price_ceiling > 0 && $shipment->freight_balance != 1) {
                // اگر ارسال به خاطر سقف رایگان رایگان شده، قیمت اصلی را محاسبه کن
                if (is_numeric($price) && $price == 0 && $finalPriceSum >= $shipment->price_ceiling) {
                    $originalPrice = self::calculateRaw($shipment, $shipmentWeightSum, $finalPriceSum, $address);
                }
            }

            $methods[] = [
                'id' => $shipment->id,
                'title' => $shipment->title,
                'type' => $shipment->type,
                'freight_balance' => $shipment->freight_balance,
                'description' => $shipment->description,
                'price' => $price,
                'original_price' => is_numeric($originalPrice) ? $originalPrice : 0,
                'price_ceiling' => $shipment->price_ceiling ?? 0,
                'estimated_days_min' => $shipment->estimated_days_min ?? 1,
                'estimated_days_max' => $shipment->estimated_days_max ?? 3,
                'show_estimated_days' => $shipment->show_estimated_days ?? true,
            ];
        }

        return $methods;
    }
}
