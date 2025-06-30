{{-- Include CSS and JS files --}}
<link rel="stylesheet" href="{{ asset('css/topnav.css') }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/topnav.js') }}"></script>

<style>
/* Updated Typography Styles */
.nav-wrapper {
    font-size: 1rem;
}

.logo-text {
    font-size: 1.4rem !important;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.search-input {
    font-size: 1rem !important;
    padding: 0.75rem 1rem !important;
}

.search-input::placeholder {
    font-size: 1rem;
}

.nav-btn {
    font-size: 1rem !important;
    padding: 0.75rem 1.25rem !important;
}

.nav-btn i {
    font-size: 1.2rem !important;
    margin-right: 0.5rem;
}

.badge {
    font-size: 0.9rem !important;
    padding: 0.25rem 0.5rem !important;
    min-width: 24px;
}

.user-info {
    margin-left: 1rem;
}

.user-name {
    font-size: 1.1rem !important;
    font-weight: 600;
    color: #fff;
}

.user-role {
    font-size: 0.95rem !important;
    opacity: 0.9;
}

.user-avatar {
    width: 40px !important;
    height: 40px !important;
    font-size: 1.2rem !important;
}

.dropdown-menu a {
    font-size: 1rem !important;
    padding: 0.75rem 1.25rem !important;
}

.dropdown-menu i {
    font-size: 1.2rem !important;
    margin-right: 0.75rem;
}

/* Improved Spacing */
.nav-right {
    gap: 1.5rem;
}

.action-buttons {
    gap: 1rem;
}

/* Enhanced Mobile Styles */
@media (max-width: 768px) {
    .logo-text {
        font-size: 1.2rem !important;
    }

    .nav-btn span {
        font-size: 0.95rem !important;
    }

    .user-name {
        font-size: 1rem !important;
    }
}
</style>

{!! csrf_field() !!}
<nav class="main-header">
    <div class="nav-wrapper">
        <!-- Left Side: Logo -->
        <div class="nav-left">
            <button type="button" class="mobile-toggle" id="sidebarToggle">
                <span class="toggle-icon"></span>
            </button>
            <a class="brand-logo" href="{{url('/user/dashboard')}}">
                <img src="{{ site_logo }}" alt="Logo" class="logo-img">
            </a>
        </div>

        <!-- Right Side Container -->
        <div class="nav-right">
            <!-- Search Bar (for customers) -->
            @if(Auth::user()->type=='customer')
            <div class="nav-search">
                <form class="search-form" method="POST" action="{{url('user/product-catalogue')}}">
                    @csrf
                    <div class="search-wrapper">
                        <input type="text" name="searchprodName" placeholder="Search products..." class="search-input">
                        <button type="submit" name="productsearch" class="search-btn">
                            <i class="glyphicon glyphicon-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Actions -->
            <div class="action-buttons">
                <a href="{{url('user/dashboard')}}" class="nav-btn cart-btn">
                    <i class="glyphicon glyphicon-home"></i>
                </a>
                @if(Auth::user()->type=='customer')
                <a href="{{url('user/cart-detail')}}" class="nav-btn cart-btn">
                    <i class="glyphicon glyphicon-shopping-cart"></i>
                    <span class="badge" id="cartCount">0</span>
                </a>
                <a href="{{url('user/orders-detail/purchase-order')}}" class="nav-btn">
                    <i class="glyphicon glyphicon-th"></i>
                    <span>Orders</span>
                </a>
                @else
                <a href="{{url('user/cart-detail')}}" class="nav-btn cart-btn">
                    <i class="glyphicon glyphicon-shopping-cart"></i>
                    <span class="badge" id="cartCount">0</span>
                </a>
                <a href="{{url('user/orders-detail/sale-order')}}" class="nav-btn">
                    <i class="glyphicon glyphicon-th"></i>
                    <span>Orders</span>
                </a>
                @endif

                @if(Auth::user()->type=='supplier')
                <!-- <a href="{{url('user/ab-needs')}}" class="nav-btn warning">
                    <i class="glyphicon glyphicon-bell"></i>
                    <span>Needs</span>
                    <span class="badge" id="needCount">0</span>
                </a> -->
                @endif

                <!-- Billing Dropdown -->
                <div class="nav-btn billing-dropdown">
                    <i class="glyphicon glyphicon-file"></i>
                    <span>Billing</span>
                    <div class="dropdown-menu billing-menu">
                        @if(Auth::user()->type=='supplier')
                        <a href="{{url('user/sale-requisition')}}" class="dropdown-item">
                            <i class="glyphicon glyphicon-list-alt"></i>
                            <span>My Sale Request</span>
                        </a>
                        @else
                        <a href="{{url('user/purchase-requisition')}}" class="dropdown-item">
                            <i class="glyphicon glyphicon-shopping-cart"></i>
                            <span>My Purchase Request</span>
                        </a>
                        @endif
                        <a href="{{url('user/bill/sale')}}" class="dropdown-item">
                            <i class="glyphicon glyphicon-shopping-cart"></i>
                            <span>My Sale</span>
                        </a>
                        <a href="{{url('user/bill/sale-order')}}" class="dropdown-item">
                            <i class="glyphicon glyphicon-list-alt"></i>
                            <span>My Sales Order</span>
                        </a>
                        <a href="{{url('user/bill/sale-return')}}" class="dropdown-item">
                            <i class="glyphicon glyphicon-arrow-left"></i>
                            <span>My Sales Return</span>
                        </a>
                        <a href="{{url('user/bill/purchase')}}" class="dropdown-item">
                            <i class="glyphicon glyphicon-shopping-cart"></i>
                            <span>My Purchase</span>
                        </a>
                        <a href="{{url('user/bill/purchase-order')}}" class="dropdown-item">
                            <i class="glyphicon glyphicon-list-alt"></i>
                            <span>My Purchase Order</span>
                        </a>
                        <a href="{{url('user/bill/purchse-return')}}" class="dropdown-item">
                            <i class="glyphicon glyphicon-arrow-right"></i>
                            <span>My Purchase Return</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="user-profile dropdown" id="userProfileDropdown">
                <div class="user-avatar">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ Auth::user()->type == 'supplier' ? 'Vendor' : 'Customer' }}</div>
                </div>
                <div class="dropdown-menu">
                    <a href="{{ url('user/profile') }}" class="dropdown-item">
                        <i class="glyphicon glyphicon-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="{{ url('user/change-my-password') }}" class="dropdown-item">
                        <i class="glyphicon glyphicon-lock"></i>
                        <span>Change Password</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ url('user/logout') }}" class="dropdown-item">
                        <i class="glyphicon glyphicon-log-out"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
