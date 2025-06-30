<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle d-lg-none">
    <i class="glyphicon glyphicon-menu-hamburger"></i>
</button>

<!-- Modern Sidebar -->
<div class="col-sm-2 col-lg-2 sidebar-container">
    <div class="sidebar-nav">
        <div class="nav-canvas">
            <ul class="nav nav-pills nav-stacked main-menu">
                <li>
                    <a class="ajax-link {{ Request::is('user/dashboard') ? 'active' : '' }}"
                        href="{{ url('user/dashboard') }}">
                        <i class="glyphicon glyphicon-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <div class="nav-header">Bills History</div>

                <li class="accordion">
                    <a href="#" class="submenu-toggle">
                        <i class="glyphicon glyphicon-plus"></i>
                        <span>Billings</span>
                    </a>
                    <ul class="nav nav-pills nav-stacked submenu">
                        <li>
                            <a class="ajax-link {{ Request::is('user/get-my-sale-order') ? 'active' : '' }}"
                                href="{{ url('user/get-my-sale-order') }}">
                                <i class="glyphicon glyphicon-th"></i>
                                <span>My Sale</span>
                            </a>
                        </li>
                        <li>
                            <a class="ajax-link {{ Request::is('user/orders/sale-order') ? 'active' : '' }}"
                                href="{{ url('user/orders/sale-order') }}">
                                <i class="glyphicon glyphicon-th"></i>
                                <span>My Sales-order</span>
                            </a>
                        </li>
                        <li>
                            <a class="ajax-link {{ Request::is('user/orders/sale-return') ? 'active' : '' }}"
                                href="{{ url('user/orders/sale-return') }}">
                                <i class="glyphicon glyphicon-th"></i>
                                <span>My Sales-return</span>
                            </a>
                        </li>
                        <li>
                            <a class="ajax-link {{ Request::is('user/orders/purchase') ? 'active' : '' }}"
                                href="{{ url('user/orders/purchase') }}">
                                <i class="glyphicon glyphicon-th"></i>
                                <span>My Purchase</span>
                            </a>
                        </li>
                        <li>
                            <a class="ajax-link {{ Request::is('user/orders/purchase-order') ? 'active' : '' }}"
                                href="{{ url('user/orders/purchase-order') }}">
                                <i class="glyphicon glyphicon-th"></i>
                                <span>My purchase-order</span>
                            </a>
                        </li>
                        <li>
                            <a class="ajax-link {{ Request::is('user/orders/purchase-return') ? 'active' : '' }}"
                                href="{{ url('user/orders/purchase-return') }}">
                                <i class="glyphicon glyphicon-th"></i>
                                <span>My Purchase-return</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <div class="nav-header">Billing Request</div>
                @if (Auth::user()->user_type == '103')
                    <li>
                        <a class="ajax-link {{ Request::is('user/sale-requisition') ? 'active' : '' }}"
                            href="{{ url('user/sale-requisition') }}">
                            <i class="glyphicon glyphicon-th-list"></i>
                            <span>Sale Request</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a class="ajax-link {{ Request::is('user/product-list') ? 'active' : '' }}"
                            href="{{ url('user/product-list') }}">
                            <i class="glyphicon glyphicon-th-list"></i>
                            <span>Purchase Request</span>
                        </a>
                    </li>
                @endif

                <div class="nav-header">Products</div>
                <li>
                    <a class="ajax-link {{ Request::is('user/myproducts') ? 'active' : '' }}"
                        href="{{ url('user/myproducts') }}">
                        <i class="glyphicon glyphicon-th-list"></i>
                        <span>My products</span>
                    </a>
                </li>
                @if (Auth::user()->user_type == '103')
                    <li>
                        <a class="ajax-link {{ Request::is('user/product-requirement-status') ? 'active' : '' }}"
                            href="{{ url('user/product-requirement-status') }}">
                            <i class="glyphicon glyphicon-th-list"></i>
                            <span>Stock Status</span>
                        </a>
                    </li>
                @endif
                @if (Auth::user()->user_type == '104')
                    <li>
                        <a class="ajax-link {{ Request::is('user/product-catalogue') ? 'active' : '' }}"
                            href="{{ url('user/product-catalogue') }}">
                            <i class="glyphicon glyphicon-star"></i>
                            <span>Catalogue</span>
                        </a>
                    </li>
                    <li>
                        <a class="ajax-link {{ Request::is('user/offer-catalogue') ? 'active' : '' }}"
                            href="{{ url('user/offer-catalogue') }}">
                            <i class="glyphicon glyphicon-star"></i>
                            <span>Offer Catalogue</span>
                        </a>
                    </li>
                    <li>
                        <a class="ajax-link {{ Request::is('user/upcoming-product-catalogue') ? 'active' : '' }}"
                            href="{{ url('user/upcoming-product-catalogue') }}">
                            <i class="glyphicon glyphicon-star"></i>
                            <span>Upcoming Items</span>
                        </a>
                    </li>
                    <li>
                        <a class="ajax-link {{ Request::is('user/latest-arrival-catalogue') ? 'active' : '' }}"
                            href="{{ url('user/latest-arrival-catalogue') }}">
                            <i class="glyphicon glyphicon-star"></i>
                            <span>Latest Arrival</span>
                        </a>
                    </li>
                @endif

                <div class="nav-header">My Financial</div>
                <li>
                    <a class="ajax-link {{ Request::is('user/myledger') ? 'active' : '' }}"
                        href="{{ url('user/myledger') }}">
                        <i class="glyphicon glyphicon-th-list"></i>
                        <span>Financial log</span>
                    </a>
                </li>
                @if (Auth::user()->user_type == '104')
                    <li>
                        <a class="ajax-link {{ Request::is('user/request-new-item') ? 'active' : '' }}"
                            href="{{ url('user/request-new-item') }}">
                            <i class="glyphicon glyphicon-upload"></i>
                            <span>Introduce New Item</span>
                        </a>
                    </li>
                @endif
                <li>
                    <a class="ajax-link {{ Request::is('user/orders/dashboard') ? 'active' : '' }}"
                        href="{{ url('user/orders/dashboard') }}">
                        <i class="glyphicon glyphicon-star"></i>
                        <span>Account Ledger</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!--/span-->

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<script src="{{ asset('js/sidebar.js') }}"></script>
