<div class="box-body">
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                <label>عنوان:</label>
                <input class="form-control rounded-custom" type="text" id="role" name="role"  data-required="true"
                       value="{{ old('role', $role->role ?? '') }}" placeholder="عنوان را وارد کنید . . .">
            </div>

            <div class="col-md-6 d-flex align-items-center" style="margin-top: 25px;">
                <input type="checkbox" name="select_all" value="1" id="select_all" class="form-check-input m-2">
                <label for="select_all"><span class="text fs-6">انتخاب همه</span></label>
            </div>
        </div>
    </div>
    <hr>
    <div class="form-group">
        <div class="row row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1 g-0"
             data-masonry='{"percentPosition": true , "originLeft": false }'>

            @foreach($groupedPermissions as $group => $permissions)
                @if($group === 'dashboard')
                    <div class="widget col p-2 " style="height: fit-content;">
                        <div class="card bg-light rounded-custom overflow-hidden p-0 border"
                             style="border-color:gray !important;">
                            <div class="widget col-md-12" style="background-color: rgb(248, 248, 248)">
                                <div class="widget-header bordered-bottom bordered-themesecondary p-3"
                                     style="background-color: #dcd9ff;">
                                    <i class="widget-icon bi bi-house-door-fill themesecondary"></i>
                                    <span class="widget-caption themesecondary">{{ config('permissions.groups.' . $group, $group) }}</span>
                                </div>
                                <div class="px-3 py-2">
                                    <input type="hidden" name="access[]" value="admin.dashboard.index">
                                    <div class="mt-2 d-flex align-items-center">
                                        <input type="checkbox" class="form-check-input" value="admin.dashboard.index" checked disabled>
                                        <label class="text ms-2">دسترسی (اجباری)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="widget col p-2 rounded-custom" style="height: fit-content;">
                        <div class="card bg-light rounded-custom overflow-hidden p-0 border"
                             style="border-color:gray !important;">
                            <div class="widget-header bordered-bottom bordered-themesecondary p-3"
                                 style="background-color: #dcd9ff;">
                                <i class="widget-icon bi bi-unlock-fill themesecondary"></i>
                                <span class="widget-caption themesecondary">{{ config('permissions.groups.' . $group, $group) }}</span>
                            </div>
                            <div class="widget-body px-3 py-1">
                                <div class="widget-main no-padding">
                                    <div class="tickets-container">

                                        @foreach($permissions as $permissionName => $action)
                                            <div class="col-12 d-flex align-items-center justify-content-between my-1 px-2 py-1 rounded"
                                                 style="background-color: #e4e4e4">

                                                <input type="checkbox" class="form-check-input my-0 me-1 my-checkbox"
                                                       id="{{ $permissionName }}"
                                                       name="access[]"
                                                       value="{{ $permissionName }}"
                                                       @if(in_array($permissionName, $currentAccess)) checked @endif>

                                                <label class="w-100" for="{{ $permissionName }}">
                                                    <span class="text">{{ config('permissions.actions.' . $action, $action) }}</span>
                                                </label>
                                            </div>
                                        @endforeach

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="box-footer mt-4">
        <button type="submit" id="submitFormCms" class="btn btn-custom rounded-custom w-fit px-4 py-2 me-2">
            ذخیره
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAllCheckbox = document.getElementById('select_all');
        const checkboxes = document.querySelectorAll('.my-checkbox');

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                checkboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = selectAllCheckbox.checked;
                    }
                });
            });
        }
    });
</script>
