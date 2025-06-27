<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Edobiz - Your Business Solution">
    <meta name="author" content="Edobiz">
    <title>@yield('title')</title>

    <!-- Core CSS -->
    <link href="{{ asset('assets/frontend/css/bootstrap-united.min.css') }}" rel='stylesheet'>
    <link href="{{ asset('assets/frontend/css/charisma-app.css') }}" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/topnav.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/frontend/css/front_custom.css') }}" rel="stylesheet">

    <!-- Plugin CSS -->
    <link href="{{ asset('assets/frontend/bower_components/chosen/chosen.min.css') }}" rel='stylesheet'>
    <link href="{{ asset('assets/frontend/bower_components/colorbox/example3/colorbox.css') }}" rel='stylesheet'>
    <link href="{{ asset('assets/frontend/bower_components/bootstrap-tour/build/css/bootstrap-tour.min.css') }}"
        rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"
        integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- jQuery -->
    <script src="{{ asset('assets/frontend/bower_components/jquery/jquery.min.js') }}"></script>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/frontend/img/favicon.ico') }}">

    @yield('topjscss')

    <style>
        :root {
            --header-height: 60px;
            --primary-color: #1A3765;
            --text-color: #333333;
            --text-light: #666666;
            --border-color: rgba(0, 0, 0, 0.1);
            --background-light: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text-color);
            background: var(--background-light);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            width: 100%;
            overflow-x: hidden;
            position: relative;
        }

        .main-wrapper {
            display: block;
            min-height: calc(100vh - var(--header-height));
            margin-top: var(--header-height);
            background: var(--background-light);
            width: 100%;
            position: relative;
        }

        .content-wrapper {
            width: 100%;
            min-height: calc(100vh - var(--header-height));
            margin: 0;
            padding: 0;
            background: var(--background-light);
            display: flex;
            flex-direction: column;
        }

        /* Dropdown Styles */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 1000;
            display: none;
            min-width: 240px;
            padding: 0.75rem 0;
            margin: 0.5rem 0 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--border-color);
        }

        .dropdown-menu.show {
            display: block;
            animation: dropdownFade 0.2s ease;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            font-size: 0.95rem;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .dropdown-item i {
            margin-right: 0.875rem;
            font-size: 1.1rem;
            color: var(--text-light);
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: rgba(26, 55, 101, 0.05);
            color: var(--primary-color);
        }

        .dropdown-item:hover i {
            color: var(--primary-color);
        }

        .dropdown-divider {
            height: 1px;
            margin: 0.5rem 0;
            background: var(--border-color);
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .footer {
            padding: 1.5rem;
            background: white;
            border-top: 1px solid var(--border-color);
            text-align: center;
            margin-top: auto;
            font-size: 0.95rem;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.05);
            width: 100%;
        }

        .footer strong {
            font-weight: 600;
        }

        .footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .footer a:hover {
            text-decoration: underline;
            opacity: 0.9;
        }

        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--background-light);
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #666;
        }

        @media (max-width: 768px) {
            .dropdown-menu {
                position: fixed;
                top: var(--header-height);
                right: 1rem;
                width: calc(100% - 2rem);
            }
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <!-- Top Navigation -->
    @include('layouts.frontend.topnav')

    <div class="main-wrapper">
        <!-- Main Content -->
        <div class="content-wrapper">
            @yield('main')

            <!-- Footer -->
            <footer class="footer">
                <p class="mb-0">
                    <strong>Copyright &copy; 2025 All rights reserved by
                        <a href="https://edobiz.com" class="text-primary">Edobiz</a>
                    </strong>
                </p>
            </footer>
        </div>
    </div>

    <!-- Core JavaScript -->
    <script src="{{ asset('assets/frontend/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/jquery.cookie.js') }}"></script>

    <!-- Plugin JavaScript -->
    <script src="{{ asset('assets/frontend/bower_components/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/bower_components/chosen/chosen.jquery.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.29.1/dist/sweetalert2.all.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('.datatable').DataTable({
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                'aoColumnDefs': [{
                    'bSortable': false,
                    'aTargets': ['nosort']
                }]
            });

            // Enhanced Dropdown Functionality
            $('.dropdown-toggle').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const dropdown = $(this).closest('.dropdown');
                $('.dropdown-menu').not(dropdown.find('.dropdown-menu')).removeClass('show');
                dropdown.find('.dropdown-menu').toggleClass('show');
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').removeClass('show');
                }
            });

            // Prevent dropdown from closing when clicking inside
            $('.dropdown-menu').on('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>

    @yield('bottomjscss')
    @if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}'
        });
    });
</script>
@endif
    {!! Toastr::message() !!}
</body>

</html>
