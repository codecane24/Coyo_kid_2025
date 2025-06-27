<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title>  {!! isset($title)?print_title($title).' | ':'' !!}{!! print_title(site_name) !!}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="{{isset($title)?print_title($title).' | ':''}}{{ print_title(site_name) }}" name="description" />
    <meta content="{{isset($title)?print_title($title).' | ':''}}{{ print_title(site_name) }}" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- App favicon -->
    <link href="{{Favicon}}" rel="icon" type="image/x-icon">
    
    @include('layouts.head-css')
    <script>
         AdminUrl='{{url("admin/")}}';
    </script>
</head>

@section('body')
    <body data-sidebar="dark">
    <div class="loading" id="loader_display_d" style="z-index: 9999;">Loading&#8230;</div>
@show
    <!-- Begin page -->
    <div id="layout-wrapper">
        @include('layouts.topbar')
       
        @include('layouts.sidebar')

        
        
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            @include('layouts.footer')
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    @include('layouts.right-sidebar')
    <!-- /Right-bar -->
    
    <!-- JAVASCRIPT -->
    
    @include('layouts.vendor-scripts')

    <!-- add Modal -->
     
    @include('admin.components.cart')
    @include('admin.components.common_modal_popup')
    
<script>
  @if(Session::has('message'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
  		toastr.success("{{ session('message') }}");
  @endif

  @if(Session::has('error'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
  		toastr.error("{{ session('error') }}");
  @endif

  @if(Session::has('info'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
  		toastr.info("{{ session('info') }}");
  @endif

  @if(Session::has('warning'))
        toastr.options =
        {
            "closeButton" : true,
            "progressBar" : true
        }
  		toastr.warning("{{ session('warning') }}");
  @endif
</script>
</body>

</html>
