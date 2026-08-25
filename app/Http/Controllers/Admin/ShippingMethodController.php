<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $methods = ShippingMethod::with('cities')->latest()->get();
        return view('admin.shipping-methods.index', compact('methods'));
    }

    public function create()
    {
        $cities = City::orderBy('name')->get();
        return view('admin.shipping-methods.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:fixed_price,with_weight,chapar,cod24',
            'price' => 'nullable|numeric|min:0',
            'price_ceiling' => 'nullable|numeric|min:0',
            'estimated_days_min' => 'required|integer|min:1|max:30',
            'estimated_days_max' => 'required|integer|min:1|max:30',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'freight_balance' => 'nullable|boolean',
            'chapar_type' => 'nullable|string',
            'sender_name' => 'nullable|string|max:255',
            'sender_company' => 'nullable|string|max:255',
            'sender_city_id' => 'nullable|exists:cities,id',
            'sender_phone' => 'nullable|string|max:20',
            'sender_mobile' => 'nullable|string|max:20',
            'sender_email' => 'nullable|email|max:255',
            'sender_address' => 'nullable|string|max:1000',
            'sender_postal_code' => 'nullable|string|max:20',
            'cities' => 'nullable|array',
        ]);

        $validated['estimated_days_min'] = min($validated['estimated_days_min'], $validated['estimated_days_max']);
        $validated['estimated_days_max'] = max($validated['estimated_days_min'], $validated['estimated_days_max']);

        $cities = $validated['cities'] ?? [];
        unset($validated['cities']);

        $method = ShippingMethod::create($validated);
        $method->cities()->sync($cities);

        return redirect()->route('admin.shipping-methods.index')
            ->with('success', 'روش ارسال با موفقیت ایجاد شد');
    }

    public function edit(ShippingMethod $shippingMethod)
    {
        $shippingMethod->load('cities');
        $cities = City::orderBy('name')->get();
        $selectedCityIds = $shippingMethod->cities->pluck('id')->toArray();

        return view('admin.shipping-methods.edit', compact('shippingMethod', 'cities', 'selectedCityIds'));
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:fixed_price,with_weight,chapar,cod24',
            'price' => 'nullable|numeric|min:0',
            'price_ceiling' => 'nullable|numeric|min:0',
            'estimated_days_min' => 'required|integer|min:1|max:30',
            'estimated_days_max' => 'required|integer|min:1|max:30',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'freight_balance' => 'nullable|boolean',
            'chapar_type' => 'nullable|string',
            'sender_name' => 'nullable|string|max:255',
            'sender_company' => 'nullable|string|max:255',
            'sender_city_id' => 'nullable|exists:cities,id',
            'sender_phone' => 'nullable|string|max:20',
            'sender_mobile' => 'nullable|string|max:20',
            'sender_email' => 'nullable|email|max:255',
            'sender_address' => 'nullable|string|max:1000',
            'sender_postal_code' => 'nullable|string|max:20',
            'cities' => 'nullable|array',
        ]);

        $validated['estimated_days_min'] = min($validated['estimated_days_min'], $validated['estimated_days_max']);
        $validated['estimated_days_max'] = max($validated['estimated_days_min'], $validated['estimated_days_max']);

        $cities = $validated['cities'] ?? [];
        unset($validated['cities']);

        $shippingMethod->update($validated);
        $shippingMethod->cities()->sync($cities);

        return redirect()->route('admin.shipping-methods.index')
            ->with('success', 'روش ارسال با موفقیت به‌روزرسانی شد');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();
        return redirect()->route('admin.shipping-methods.index')
            ->with('success', 'روش ارسال حذف شد');
    }

    public function toggleStatus(ShippingMethod $shippingMethod)
    {
        $shippingMethod->update(['status' => $shippingMethod->status ? 0 : 1]);
        return back()->with('success', 'وضعیت تغییر کرد');
    }
}
