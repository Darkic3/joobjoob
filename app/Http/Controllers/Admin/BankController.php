<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BankRequest;
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

    public function store(BankRequest $request)
    {
        $validated = $request->validated();

        // Handle icon upload
        $validated['icon'] = $this->handleImageUpload($request->file('icon'));
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

    public function update(BankRequest $request, Bank $bank)
    {
        $validated = $request->validated();

        // Handle icon upload
        if ($request->hasFile('icon') && $request->file('icon')->isValid()) {
            // Delete old icon
            if ($bank->icon) {
                ImageHandler::delete('bank', $bank->icon);
            }
            $validated['icon'] = $this->handleImageUpload($request->file('icon'));
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
        if ($bank->icon) {
            ImageHandler::delete('bank', $bank->icon);
        }
        $bank->delete();
        return redirect()->route('admin.banks.index')
            ->with('success', 'درگاه پرداخت حذف شد');
    }

    public function toggleStatus(Bank $bank)
    {
        $bank->update(['status' => $bank->status ? 0 : 1]);
        return back()->with('success', 'وضعیت تغییر کرد');
    }

    /**
     * Handle image upload using project's ImageHandler
     */
    private function handleImageUpload($file): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        return ImageHandler::handle('bank', $file);
    }
}
