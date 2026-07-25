
<div class="col-lg-4">
    <div id="list">
        <div class="alert alert-info alert-dismissable rounded-lg"
             style="background-color:#1271fd !important;direction: rtl; margin: 0px auto;">
            <i class="fa fa-check"></i>
            <span style="color:white;font-size: 14px;">  با درگ کردن ترتیب مورد نظر را انتخاب نمایید.  </span>
        </div>
        <div id="response"></div>
        <ul>
            <hr>
            <div class="alert alert-primary alert-dismissable rounded-lg"
                 style="background-color:#1271fd !important;direction: rtl; margin: 0px auto;">
                <i class="fa fa-arrow-circle-down"></i>
                <span style="color:white;font-size: 10px;">ترتیب دستگاه</span>
            </div>
            @foreach ($my_menu_items as $rowSort)
                <li class="list-unstyled  my-2 p-2 shadow-sm rounded-lg"
                    id="deviceorder_{!! stripslashes($rowSort['id']) !!}" style="background-color:white">
                    <p class="text-black pt-2">
                        {{$rowSort['name']}}
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@push('scripts')
    <script src="{{asset('assets/admin/js/jquery-ui.min.js?v0.02')}}"></script>
    <meta name="csrf-token" content="{!! csrf_token() !!}"/>
    <script type="text/javascript">
        $(document).ready(function () {
            function slideout() {
                setTimeout(function () {
                    $("#response").slideUp("slow", function () {
                    });
                }, 2000);
            }

            $("#response").hide();
            $(function () {
                $("#list ul").sortable({
                    opacity: 0.8, cursor: 'move', update: function () {
                        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                        var order = $(this).sortable("serialize") + '&update=update' + '&_token=' + CSRF_TOKEN;
                        $.post("{{route('admin.menu-sort')}}", order, function (theResponse) {
                            $("#response").html(theResponse);
                            $("#response").slideDown('slow');
                            slideout();
                        });

                    }
                });
            });
        });
    </script>
@endpush
