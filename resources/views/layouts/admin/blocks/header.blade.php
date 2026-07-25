<div class="header bg-white px-0 py-3 border mt-4 rounded-custom shadow d-lg-block d-none">
    <nav class="navbar p-md-0">
        <div class="container">
            <div class="dashboard-header d-flex justify-content-between mb-md-0">
                <div class="v-avatar is-large">
                    <div class="dropdown">
                        <div class="d-flex align-items-center px-2">
                            <div class="dropdown dropdown-custom">
                                <div class="position-relative" type="button" data-bs-toggle="dropdown"
                                     aria-expanded="false">
                                    <img src="{{asset('assets/admin/images/user2.webp')}}"
                                         class="shadow me-2 rounded-circle" width="50">
                                    <i class="position-absolute bg-white rounded-circle small p-1 bottom-0 bi bi-circle-fill d-flex"
                                       style="right: 0; color:#28c76f;"></i>
                                </div>
                                <ul class="dropdown-menu p-2">
                                    <li class="border-bottom">
                                        <div class="dropdown-item d-flex align-items-center">
                                            <div class="position-relative">
                                                <img src="{{asset('assets/admin/images/user2.webp')}}"
                                                     class="border shadow me-2 rounded-circle" width="50">
                                                <i class="position-absolute bg-white rounded-circle small p-1 bottom-0 bi bi-circle-fill d-flex"
                                                   style="right: 0; color:#28c76f;"></i>
                                            </div>
                                            <p class="ms-2 my-1 fw-bolder text-nowrap d-flex flex-column color-custom">
                                                {{Auth::user()->name  ?? 'نامعلوم ' }}
                                                <small class="my-1 opacity-50" style="font-size: 12px;">
                                                    {{Auth::user()->family  ?? 'نامعلوم ' }}
                                                </small>
                                            </p>

                                        </div>
                                    </li>
                                    {{--                                    <li>--}}
                                    {{--                                        <div class="dropdown-item d-flex align-items-center w-100 py-2">--}}
                                    {{--                                            <a class="color-custom d-flex align-items-center"--}}
                                    {{--                                               href="">--}}
                                    {{--                                                <i class="bi bi-person-check h5 my-0 me-3 d-flex"></i>--}}
                                    {{--                                                پروفایل من--}}
                                    {{--                                            </a>--}}
                                    {{--                                        </div>--}}
                                    {{--                                    </li>--}}
                                    <li>
                                        <div class="dropdown-item d-flex align-items-center w-100 py-2">
                                            <a class="color-custom d-flex align-items-center"
                                               href="{{route('admin.logout')}}">
                                                <i class="bi bi-power h5 my-0 me-3 d-flex"></i>
                                                خروج از حساب
                                            </a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <a class="d-flex text-decoration-none mx-2" href="{{route('admin.dashboard.index')}}"
                               data-bs-toggle="tooltip"
                               data-bs-title="داشبورد">
                                <i class="bi bi-speedometer2  fs-4 d-flex color-custom"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <a class="d-flex text-decoration-none" href="{{route('admin.logout')}}"
               data-bs-toggle="tooltip" data-bs-title="خروج">
                <i class="bi bi-power fs-4 d-flex color-custom ms-2"></i>
            </a>

        </div>
    </nav>
</div>
