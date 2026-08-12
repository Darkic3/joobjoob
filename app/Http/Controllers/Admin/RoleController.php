<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])->latest()->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create', $this->permissionTree(null));
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->input('name')]);
        $role->permissions()->sync($this->expandedPermissionIds($request->input('permissions', [])));

        User::bumpPermissionsVersion();

        return redirect()->route('admin.roles.index')->with('success', __('admin.roles.created'));
    }

    public function edit(Role $role): View
    {
        $this->authorizeSystemRole($role);
        $role->load('permissions');

        return view('admin.roles.edit', array_merge(compact('role'), $this->permissionTree($role)));
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorizeSystemRole($role);

        $role->update(['name' => $request->input('name')]);
        $role->permissions()->sync($this->expandedPermissionIds($request->input('permissions', [])));

        User::bumpPermissionsVersion();

        return redirect()->route('admin.roles.edit', $role)->with('success', __('admin.roles.updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        // نقش‌های سیستمی (مثل main) هرگز قابل حذف نیستند — حتی توسط سوپرادمین
        abort_if($role->is_system, 403, 'نقش سیستمی قابل حذف نیست.');

        $role->permissions()->detach();
        $role->delete();

        User::bumpPermissionsVersion();

        return redirect()->route('admin.roles.index')->with('success', __('admin.roles.deleted'));
    }

    /**
     * ویرایش نقش سیستمی فقط برای سوپرادمین مجاز است (خطر قفل‌کردن دیگران).
     */
    private function authorizeSystemRole(Role $role): void
    {
        if ($role->is_system && ! auth()->user()->is_admin) {
            abort(403);
        }
    }

    /**
     * درخت دسترسی‌های فرم (dedupe بر اساس لیبل) + دسترسی‌های تیک‌خورده و تعداد آن‌ها.
     * منطق فرم از ویو خارج شده و این‌جا آماده می‌شود.
     *
     * @return array{normalized: array, checkedCount: int, oldPerms: array}
     */
    private function permissionTree(?Role $role): array
    {
        $rolePerms = $role ? $role->permissions->pluck('name')->all() : [];
        $oldPerms = old('permissions', $rolePerms);

        $tree = config('permissions.permissions', []);
        $normalized = [];
        foreach ($tree as $groupKey => $group) {
            $items = [];
            foreach ($group['access'] as $access => $label) {
                $items[$label][] = "admin.{$groupKey}.{$access}";
            }
            $normalized[$groupKey] = ['title' => $group['title'], 'items' => $items];
        }

        $checkedCount = 0;
        foreach ($normalized as $group) {
            foreach ($group['items'] as $names) {
                if (array_intersect($names, $oldPerms)) {
                    $checkedCount++;
                }
            }
        }

        return compact('normalized', 'checkedCount', 'oldPerms');
    }

    /**
     * نام دسترسی‌های هم‌لیبل (مثل create+store یا edit+update) را بسط می‌دهد
     * و id های متناظر را برمی‌گرداند. چک‌باکس‌ها فقط یک بار برای هر لیبل نمایش داده می‌شوند.
     */
    private function expandedPermissionIds(array $names): array
    {
        $labelMap = [];

        foreach (config('permissions.permissions', []) as $group => $item) {
            foreach ($item['access'] as $access => $label) {
                $labelMap[$label][] = "admin.{$group}.{$access}";
            }
        }

        $expanded = [];
        foreach ($names as $name) {
            foreach ($labelMap as $permNames) {
                if (in_array($name, $permNames, true)) {
                    $expanded = array_merge($expanded, $permNames);
                    break;
                }
            }
        }

        $expanded = array_values(array_unique($expanded));

        return Permission::whereIn('name', $expanded)->pluck('id')->all();
    }
}
