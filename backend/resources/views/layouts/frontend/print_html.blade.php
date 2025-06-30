<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Arbuda Bangles">
    <meta name="author" content="Vikram Singh">

    <link href="{{asset('assets/frontend/css/charisma-app.css')}}" rel="stylesheet">
    <link href="{{asset('assets/frontend/bower_components/fullcalendar/dist/fullcalendar.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/bower_components/fullcalendar/dist/fullcalendar.print.css')}}" rel='stylesheet' media='print'>
    <link href="{{asset('assets/frontend/bower_components/chosen/chosen.min.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/bower_components/colorbox/example3/colorbox.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/bower_components/responsive-tables/responsive-tables.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/bower_components/bootstrap-tour/build/css/bootstrap-tour.min.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/css/jquery.noty.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/css/noty_theme_default.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/css/elfinder.min.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/css/elfinder.theme.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/css/jquery.iphone.toggle.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/css/uploadify.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/frontend/css/animate.min.css')}}" rel='stylesheet'>

    <link href="{{asset('assets/frontend/css/bootstrap-united.min.css')}}" rel='stylesheet'>
    <link href="{{asset('assets/backend/css/toastr.min.css')}}" rel='stylesheet'>

    <!-- jQuery -->
    <script src="{{asset('assets/frontend/bower_components/jquery/jquery.min.js')}}"></script>

    <!-- The HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- The fav icon -->
    <link rel="shortcut icon" href="{{asset('assets/frontend/img/favicon.ico')}}">

    <!-- Developer css -->
    <link  rel='stylesheet' href="{{asset('assets/frontend/css/front_custom.css')}}">
    <link  rel='stylesheet' href="{{asset('assets/frontend/css/select2.min.css')}}">

</head>

<body>
<div class="ch-container">
    <div class="row">
      <div id="content" class="col-lg-12 col-sm-12">
      <div>
       {!! $data !!}
       </div>
      </div>
    </div>
    
</div><!--/.fluid-container-->


<script src="{{asset('assets/frontend/bower_components/bootstrap/dist/js/bootstrap.min.js')}}"></script>

<!-- library for cookie management -->
<script src="{{asset('assets/frontend/js/jquery.cookie.js')}}"></script>
<!-- calender plugin -->
<script src="{{asset('assets/frontend/bower_components/moment/min/moment.min.js')}}"></script>
<script src="{{asset('assets/frontend/bower_components/fullcalendar/dist/fullcalendar.min.js')}}"></script>
<!-- data table plugin -->
<script src="{{asset('assets/frontend/js/jquery.dataTables.min.js')}}"></script>

<!-- select or dropdown enhancer -->
<script src="{{asset('assets/frontend/bower_components/chosen/chosen.jquery.min.js')}}"></script>
<!-- plugin for gallery image view -->
<script src="{{asset('assets/frontend/bower_components/colorbox/jquery.colorbox-min.js')}}"></script>
<!-- notification plugin -->
<script src="{{asset('assets/frontend/js/jquery.noty.js')}}"></script>
<!-- library for making tables responsive -->
<script src="{{asset('assets/frontend/bower_components/responsive-tables/responsive-tables.js')}}"></script>
<!-- tour plugin -->
<script src="{{asset('assets/frontend/bower_components/bootstrap-tour/build/js/bootstrap-tour.min.js')}}"></script>
<!-- star rating plugin -->
<script src="{{asset('assets/frontend/js/jquery.raty.min.js')}}"></script>
<!-- for iOS style toggle switch -->
<script src="{{asset('assets/frontend/js/jquery.iphone.toggle.js')}}"></script>
<!-- autogrowing textarea plugin -->
<script src="{{asset('assets/frontend/js/jquery.autogrow-textarea.js')}}"></script>
<!-- multiple file upload plugin -->
<script src="{{asset('assets/frontend/js/jquery.uploadify-3.1.min.js')}}"></script>
<!-- history.js for cross-browser state change on ajax -->
<script src="{{asset('assets/frontend/js/jquery.history.js')}}"></script>
<!-- application script for Charisma demo -->
<script src="{{asset('assets/frontend/js/charisma.js')}}"></script>

<!-- TOASTr Messaging  -->
<script src="{{asset('assets/backend/js/toastr.min.js')}}"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.29.1/dist/sweetalert2.all.min.js"></script>

<script src="{{asset('assets/frontend/js/select2.min.js')}}"></script></body>
<script>
$('table').removeClass('table');

$( document ).ready(function() {
    window.print();
});

</script>
</html>
