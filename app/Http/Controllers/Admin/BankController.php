<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::latest()->get();
        return view('admin.banks.index', compact('banks'));
    }

    public function create()
    {
        return view('admin.banks.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'bank_type' => 'required|string|max:100',
            'status' => 'required|boolean',
            'admin_test' => 'nullable|boolean',
            'config' => 'nullable|string',
            'card_number' => 'nullable|string|max:30',
            'card_holder_name' => 'nullable|string|max:255',
            'card_description' => 'nullable|string|max:1000',
        ];

        if ($request->hasFile('icon')) {
            $rules['icon'] = 'required|image|mimes:png,jpg,svg,webp|max:2048';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('icon') && $request->file('icon')->isValid()) {
            $validated['icon'] = $request->file('icon')->store('bank', 'public');
        } else {
            unset($validated['icon']);
        }

        $validated['config'] = $validated['config'] ? json_decode($validated['config'], true) : null;
        $validated['admin_test'] = $validated['admin_test'] ?? 0;

        Bank::create($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', 'درگاه پرداخت با موفقیت ایجاد شد');
    }

    public function edit(Bank $bank)
    {
        return view('admin.banks.edit', compact('bank'));
    }

    public function update(Request $request, Bank $bank)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'bank_type' => 'required|string|max:100',
            'status' => 'required|boolean',
            'admin_test' => 'nullable|boolean',
            'config' => 'nullable|string',
            'card_number' => 'nullable|string|max:30',
            'card_holder_name' => 'nullable|string|max:255',
            'card_description' => 'nullable|string|max:1000',
        ];

        if ($request->hasFile('icon')) {
            $rules['icon'] = 'required|image|mimes:png,jpg,svg,webp|max:2048';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('icon') && $request->file('icon')->isValid()) {
            $validated['icon'] = $request->file('icon')->store('bank', 'public');
        } else {
            unset($validated['icon']);
        }

        $validated['config'] = $validated['config'] ? json_decode($validated['config'], true) : null;
        $validated['admin_test'] = $validated['admin_test'] ?? 0;

        $bank->update($validated);

        return redirect()->route('admin.banks.index')
            ->with('success', 'درگاه پرداخت با موفقیت به‌روزرسانی شد');
    }

    public function destroy(Bank $bank)
    {
        $bank->delete();
        return redirect()->route('admin.banks.index')
            ->with('success', 'درگاه پرداخت حذف شد');
    }

    public function toggleStatus(Bank $bank)
    {
        $bank->update(['status' => $bank->status ? 0 : 1]);
        return back()->with('success', 'وضعیت تغییر کرد');
    }
}
