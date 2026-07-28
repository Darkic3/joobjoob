@if(isset($errors))
    @if($errors->any() || Session::has('error'))

        @if(Session::has('error'))
            <script>
                var msg = " {!!Session::get('error')!!}";

                swal.fire({

                    icon: 'error',
                    text: msg,
                    showCancelButton: false,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-right',
                    timer: 5000

                });
                // toastr["error"](msg, "خطا")
            </script>
        @endif
        @if(isset($errors))
            @foreach($errors->all() as $error )
                <script>
                    var msg = "{!! $error !!}";
                    swal.fire({

                        icon: 'error',
                        text: msg,
                        showCancelButton: false,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-right',
                        timer: 5000

                    });
                </script>
            @endforeach
        @endif
    @endif
@endif


@if(Session::has('mobileError'))

    <script>
        var msg = " {!!Session::get('mobileError')!!}";
        swal.fire({
            icon: 'error',
            text: msg,
            showCancelButton: false,
            showConfirmButton: false,
            toast: true,
            position: 'top-right',
            timer: 5000

        }).then((e) => {
            window.location.href = 'https://cookielanguage.ir/code'; //Will take you to Google.
        });
        // toastr["error"](msg, "شما قبلاً ثبت نام کردید. کافیست ورود را بزنید. ")
    </script>

@endif

@php $segs = Request::segments(); @endphp
@if(@$segs[1] == "inquiry" && @$segs[2] == "list")


    @if(Session::has('success'))
        <script>
            var msg = "{!!Session::get('success')!!}";
            swal.fire({

                icon: 'success',
                text: msg,
                showCancelButton: false,
                showConfirmButton: false,
                toast: true,
                position: 'top-right',
                timer: 5000

            });

        </script>
    @endif
    @if(isset($success))
        <script>
            var msg = "{!!$success!!}";
            swal.fire({

                icon: 'success',
                text: msg,
                showCancelButton: false,
                showConfirmButton: false,
                toast: true,
                position: 'top-right',
                timer: 5000

            });
        </script>
    @endif

    <style>
        .swal-modal{
            width: 42% !important;
        }
        .swal-text{
            text-align: center !important;
        }
    </style>
@else


    @if(Session::has('success'))

        <script>
            var msg = "{!!Session::get('success')!!}";
            swal.fire({
                icon: 'success',
                text: msg,
                showCancelButton: false,
                showConfirmButton: false,
                toast: true,
                position: 'top-right',
                timer: 5000

            });
            // toastr["error"](msg, "شما قبلاً ثبت نام کردید. کافیست ورود را بزنید. ")
        </script>

{{--        <script>--}}
{{--            var msg = "{!!Session::get('success')!!}";--}}
{{--            swal("", msg, "success");--}}

{{--        </script>--}}
    @endif
    @if(isset($success))
        <script>
            var msg = "{!!$success!!}";
            swal.fire({
                icon: 'success',
                text: msg,
                showCancelButton: false,
                showConfirmButton: false,
                toast: true,
                position: 'top-right',
                timer: 5000

            });
            // toastr["error"](msg, "شما قبلاً ثبت نام کردید. کافیست ورود را بزنید. ")
        </script>
{{--        --}}
{{--        <script>--}}
{{--            var msg = "{!!$success!!}";--}}
{{--            swal("", msg, "success");--}}

{{--        </script>--}}
    @endif
@endif
