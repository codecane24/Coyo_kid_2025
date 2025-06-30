@extends('layouts.admin.app')

@section('h_style')
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
    }

    .login-page {
        display: flex;
        height: 100vh;
        width: 100%;
    }

    /* Left side - Full-height marketing image - 70% width */
    .marketing-side {
        flex: 7; /* 70% of the space */
        position: relative;
        overflow: hidden;
    }

    .marketing-side img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        position: absolute;
        top: 0;
        left: 0;
    }

    .marketing-overlay {
        
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 100%);
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 2rem;
        display:none;
    }

    .marketing-content {
        color: white;
        max-width: 500px;
        margin-left: 10%;
    }

    .marketing-content h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .marketing-content p {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    /* Right side - Login form - 30% width */
    .login-side {
        flex: 3; /* 30% of the space */
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f8f9fa;
    }

    .login-container {
        width: 90%;
        max-width: 400px;
        padding: 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
    }

    .login-logo {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-logo img {
        height: 60px;
        border-radius: 8px;
    }

    .login-title {
        text-align: center;
        font-size: 1.75rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 2rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        font-size: 1rem;
        border: 1px solid #e1e1e1;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .form-control:focus {
        border-color: #4a6cf7;
        box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.15);
        outline: none;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #555;
    }

    .form-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .remember-me {
        display: flex;
        align-items: center;
    }

    .remember-me input {
        margin-right: 8px;
    }

    .forgot-link {
        color: #4a6cf7;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s;
    }

    .forgot-link:hover {
        color: #2a4cd7;
        text-decoration: underline;
    }

    .login-btn {
        display: block;
        width: 100%;
        padding: 12px;
        background: linear-gradient(135deg, #4a6cf7 0%, #2a4cd7 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .login-btn:hover {
        background: linear-gradient(135deg, #2a4cd7 0%, #1a3cc7 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(42, 76, 215, 0.3);
    }

    .alert {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .alert-danger {
        background-color: #fee2e2;
        color: #ef4444;
        border: 1px solid #fecaca;
    }

    .alert-success {
        background-color: #dcfce7;
        color: #22c55e;
        border: 1px solid #bbf7d0;
    }

    .alert-close {
        position: absolute;
        top: 12px;
        right: 15px;
        cursor: pointer;
    }

    /* Responsive styles */
    @media (max-width: 992px) {
        .login-page {
            flex-direction: column;
        }

        .marketing-side {
            flex: none;
            height: 60vh; /* Increased height on mobile to maintain the ratio feeling */
        }

        .login-side {
            flex: none;
            padding: 2rem 1rem;
        }

        .marketing-content {
            margin: 0 auto;
            text-align: center;
        }
    }

    /* Placeholder styling */
    ::placeholder {
        color: #aaa;
        opacity: 1;
    }
</style>
@endsection

@section('content')
<div class="login-page">
    <!-- Marketing Side with Full Height Image -->
    <div class="marketing-side">
        <img src="{{ asset('assets/images/marketingLogin.jpg') }}" alt="Marketing Image">
        <div class="marketing-overlay">
            <div class="marketing-content">
                <h1>Welcome to Our Platform</h1>
                <p>Access your dashboard to manage all your business needs in one place. Powerful tools designed for productivity and growth.</p>
            </div>
        </div>
    </div>

    <!-- Login Form Side -->
    <div class="login-side">
        <div class="login-container">
            <div class="login-logo">
                <!-- <a href="{{route('admin.login')}}">
                    <img src="{{site_logo}}" alt="Logo">
                </a> -->
            </div>

            <h2 class="login-title">{{ __('panel.lbl_login') }}</h2>

            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    <div class="alert-text">{{session('error')}}</div>
                    <div class="alert-close"><i class="flaticon2-cross" data-dismiss="alert"></i></div>
                </div>
            @elseif(session('success'))
                <div class="alert alert-success" role="alert">
                    <div class="alert-text">{{session('success')}}</div>
                    <div class="alert-close"><i class="flaticon2-cross" data-dismiss="alert"></i></div>
                </div>
            @endif

            <form action="{{ route('admin.login_post') }}" name="form_login" id="form_login" method="post">
                @csrf

                <div class="form-group">
                    <label for="username" class="form-label">Username or Email</label>
                    <input class="form-control" type="text"
                           placeholder="Enter your username or email"
                           name="username" id="username" value="{{old('username')}}" autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input class="form-control" type="password"
                           placeholder="Enter your password"
                           name="password" id="password" value="{{old('password')}}">
                </div>

                <div class="form-row">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember"{{ old('remember') ? 'checked' : '' }}>
                        <label for="remember">{{ __('Remember Me') }}</label>
                    </div>

                    <a href="{{route('admin.forgot_password')}}" class="forgot-link">
                        {{__('Forgot Password')}}
                    </a>
                </div>
                <button type="submit" class="login-btn" id="kt_login_signin_submit">
                    {{__('Login')}}
                </button>
            </form>
        </div>
        
    </div>
</div>
@endsection

@section('script')
    <script>
        $(function () {
            // Form validation
            $(document).on('submit', '#form_login', function () {
                $(this).validate({
                    rules: {
                        username: {required: true,},
                        password: {required: true}
                    },
                    messages: {
                        username: {required: 'Please enter username or email'},
                        password: {required: 'Please enter password'}
                    },
                    errorElement: 'span',
                    errorClass: 'text-danger',
                    errorPlacement: function(error, element) {
                        error.insertAfter(element);
                    },
                    highlight: function(element) {
                        $(element).addClass('is-invalid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid');
                    }
                });
                return $(this).valid();
            });

            // Auto dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@endsection
