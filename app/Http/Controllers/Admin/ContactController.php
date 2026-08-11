<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $contacts = ContactRequest::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            })
            ->when($request->boolean('status') === true, fn ($query) => $query->unread())
            ->orderByRaw('read_at IS NOT NULL, created_at DESC')
            ->paginate(15)
            ->withQueryString();

        return view('admin.contact.list', compact('contacts'));
    }

    public function show(ContactRequest $contact): View
    {
        $contact->markAsRead();

        return view('admin.contact.show', compact('contact'));
    }

    public function destroy(ContactRequest $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('admin.contact.index')
            ->with('success', __('admin.contact.deleted'));
    }
}
