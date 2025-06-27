<style>
    /* #page-topbar{
        background-color:#1aa79c
    }
    .noti-icon i{
        color:#fff;
    }
    .header-item{
        color:#fff;
    }*/
    .fybtn{
        font-size: 20px;
        text-shadow: 1px 2px 3px #000;
    }
    .tabb{
        max-height:400px;
        overflow:scroll;
    }
    .crtlist:hover{
        background-color:bisque;
    }
</style>
<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{route('admin.dashboard')}}" class="logo logo-dark">
                    <span class="logo-sm p-2">
                        <img src="{{ small_site_logo }}" alt="">
                    </span>
                    <span class="logo-lg">
                        <img src="{{site_logo}}" alt="">
                    </span>
                </a>
                <a href="{{route('admin.dashboard')}}" class="logo logo-light">
                    <span class="logo-sm p-2">
                        <img src="{{small_site_logo }}" alt="">
                    </span>
                    <span class="logo-lg">
                        <img src="{{site_logo}}" alt="">
                    </span>
                </a>

                    @if(Session::has('branch_name'))
                        <div class="bg-light rounded">{{Session::get('branch_name')}}</div>
                    @endif
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

           <!-- App Search-->
           <form class="app-search d-lg-block">
            <div class="position-relative">
                <input type="text" class="form-control" id="topnavsearch" placeholder="Search Account for Cart">
                <span class="bx bx-search-alt"></span>
            </div>
            <div class="SchListDiv" id="srchAccountListDiv">
                <div id="srchActList">

                </div>
            </div>
        </form>

        <div class="dropdown dropdown-mega d-lg-block ms-2">
            <button type="button" class="btn header-item waves-effect" data-bs-toggle="dropdown" aria-haspopup="false" aria-expanded="false">
                <span key="t-megamenu">Reports</span>
                <i class="mdi mdi-chevron-down"></i>
            </button>
            <div class="dropdown-menu dropdown-megamenu p-0">
                <div class="row g-0">
                    <div class="col-lg-8">
                        <div class="row g-0">
                            <!-- Bill Register Section -->
                            <div class="col-lg-4 border-end">
                                <div class="dropdown-header bg-light">
                                    <i class="mdi mdi-file-document-outline me-2 text-primary"></i>
                                    <span class="fw-semibold">Bill Register</span>
                                </div>
                                <div class="dropdown-item-group p-2">
                                    <a href="{{route('admin.report.bill-register','')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-file-document me-2 text-primary"></i>
                                        <span>All Bill Register</span>
                                    </a>
                                    <a href="{{route('admin.report.sale-order-details')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-file-document me-2 text-primary"></i>
                                        <span>Sale Order Detail</span>
                                    </a>
                                    <a href="{{route('admin.report.bill-register-detail','sale')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-file-document me-2 text-primary"></i>
                                        <span>Sale Detail</span>
                                    </a>
                                    <a href="{{route('admin.report.bill-register-detail','sale-return')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-file-document me-2 text-primary"></i>
                                        <span>Sale Return Detail</span>
                                    </a>
                                    <a href="{{route('admin.report.purchase-order-details')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-file-document me-2 text-primary"></i>
                                        <span>Purchase Order Detail</span>
                                    </a>
                                    <a href="{{route('admin.report.bill-register-detail','purchase')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-file-document me-2 text-primary"></i>
                                        <span>Purchase Detail</span>
                                    </a>
                                    <a href="{{route('admin.report.bill-register-detail','purchase-return')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-file-document me-2 text-primary"></i>
                                        <span>Purchase Return Detail</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Product & Stock Section -->
                            <div class="col-lg-4 border-end">
                                <div class="dropdown-header bg-light">
                                    <i class="mdi mdi-package-variant me-2 text-success"></i>
                                    <span class="fw-semibold">Product & Stock</span>
                                </div>
                                <div class="dropdown-item-group p-2">
                                    <a href="{{route('admin.report.productwise-pending-order')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-package-variant me-2 text-success"></i>
                                        <span>Productwise Pending Order</span>
                                    </a>
                                    <a href="{{route('admin.report.unordered-purchase')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-package-variant me-2 text-success"></i>
                                        <span>OverOrder Purchase</span>
                                    </a>
                                    <a href="{{route('admin.report.need-order-report')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-package-variant me-2 text-success"></i>
                                        <span>Need Stock Report</span>
                                    </a>
                                    <a href="{{route('admin.stock-adjustment.index')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-package-variant me-2 text-success"></i>
                                        <span>Stock Adjustment</span>
                                    </a>
                                    <a href="{{route('admin.generate-qr-for-purchaseitem')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-qrcode me-2 text-success"></i>
                                        <span>GenQR For Purchase</span>
                                    </a>
                                </div>
                            </div>

                            <!-- Financial Reports Section -->
                            <div class="col-lg-4 border-end">
                                <div class="dropdown-header bg-light">
                                    <i class="mdi mdi-finance me-2 text-info"></i>
                                    <span class="fw-semibold">Financial Reports</span>
                                </div>
                                <div class="dropdown-item-group p-2">
                                    @if(Auth::user()->hasDirectPermission('misreport_stock_value'))
                                    <a href="{{route('admin.misreport.product-stock-value')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-currency-inr me-2 text-info"></i>
                                        <span>Current Stock Value</span>
                                    </a>
                                    @endif
                                    <a href="{{route('admin.misreport.account-financial-status')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-finance me-2 text-info"></i>
                                        <span>Account Financial Status</span>
                                    </a>
                                    <a href="{{route('admin.report.bill-payment-status',['sale'])}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-finance me-2 text-info"></i>
                                        <span>Sale Payment status</span>
                                    </a>
                                    <a href="{{route('admin.report.bill-payment-status',['purchase'])}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-finance me-2 text-info"></i>
                                        <span>Purchase Payment status</span>
                                    </a>
                                     <a href="{{route('admin.misreport.overdue','sale')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-currency-inr me-2 text-info"></i>
                                        <span>Sale Overdue</span>
                                    </a>
                                    <a href="{{route('admin.misreport.overdue','purchase')}}" class="dropdown-item d-flex align-items-center py-2">
                                        <i class="mdi mdi-currency-inr me-2 text-info"></i>
                                        <span>Purchase Overdue</span>
                                    </a>
                                    
                                    <div class="dropdown-divider my-2"></div>
                                    <div class="dropdown-header bg-light">
                                        <i class="mdi mdi-account-group me-2 text-warning"></i>
                                        <span class="fw-semibold">Employee Reports</span>
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client Reports Section -->
                    <div class="col-lg-4">
                        <div class="dropdown-header bg-light">
                            <i class="mdi mdi-account-group me-2 text-danger"></i>
                            <span class="fw-semibold">Client Reports</span>
                        </div>
                        <div class="dropdown-item-group p-2">
                           
                            <a href="{{url('admin/supplier-sale-request')}}" class="dropdown-item d-flex align-items-center py-2">
                                <i class="mdi mdi-account me-2 text-danger"></i>
                                <span> Supplier Sale Request</span>
                            </a>
                            <a href="{{url('admin/customer-purchase-request')}}" class="dropdown-item d-flex align-items-center py-2">
                                <i class="mdi mdi-account me-2 text-danger"></i>
                                <span> customer Purchase Request</span>
                            </a>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center">

    <div class="dropdown d-none d-lg-block">
            <button type="button" class="btn header-item noti-icon waves-effec fybtn"
    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Financial Year">
                <i class="mdi mdi-calendar"></i> {{ Session::get('fyear.name') }}


            </button>

            @if(!empty(financialYears()) && count(financialYears())>1)
                <span class="badge bg-info">FY</span>
            <div class="dropdown-menu dropdown-menu-end">
                <span class="dropdown-item dropdown-header">{{Auth::user()?->fyear?->name}}</span>
				<div class="dropdown-divider"></div>
                @php
                    print_r(Auth::user()->fyear);
                    $fyrs = financialYears();
                @endphp
                @if(!empty(financialYears()))
                    @foreach($fyrs as $fy)
                        <a href="{{route('admin.fy-change',$fy->id)}}" data-id="{{$fy->id}}" class="dropdown-item fychange">
                            <i class="fa fa-list mr-2"></i> {{ $fy->name }}
                        </a>
                        <div class="dropdown-divider"></div>
                    @endforeach
                @endif
            </div>
            @endif
        </div>
    </div>
    <div class="d-flex">
        <!-- <div class="dropdown d-inline-block d-lg-none ms-2"> -->
        <div class="dropdown  d-none ms-2">
            <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-search-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-magnify"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                aria-labelledby="page-header-search-dropdown">
                <form class="p-3">
                    <div class="form-group m-0">
                        <div class="input-group">
                            <input type="text" class="form-control" id="topnavsearch1" placeholder="@lang('translation.Search')" aria-label="Search input">

                            <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                        </div>
                    </div>
                </form>
                <div class="SchListDiv" id="srchAccountListDiv">
                <div id="srchActList">

                </div>
            </div>
            </div>
        </div>
        <div class="dropdown d-inline-block">
            @php
                $customerCart=cartdata(1);
                $supplierCart=cartdata(2);
            @endphp
            <button type="button" class="btn header-item waves-effect noti-icon" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

                    <i class="bx bx-shopping-bag"></i>
                    <!-- <img src="https://abbangles.com/public/images/other/cart.png" class="" title="Cart" width="35px"> -->
                    <span class="badge bg-danger rounded-pill" style="top:3px;right:0"><small>{{$customerCart->count()}}|{{$supplierCart->count()}}</small></span>

            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-en">
                <div class="px-lg-2">
                <div class="carticon">
					@if(session()->has('accountid'))
                        <span class="dropdown-item bg-danger bg-gradient text-white dropdown-header">
                            {{ucfirst(session()->get('accountname'))}}
                        </span>
					@else
					    <span class="dropdown-item dropdown-header">No active cart</span>
					@endif
				</div>
				<div class="tabb">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item bg-light" role="presentation">
                            <button class="nav-link px-2 active" id="customer-tab" data-bs-toggle="tab" data-bs-target="#customer" type="button" role="tab" aria-controls="customer" aria-selected="true">
                                Customer <span class="badge bg-success navbar-badge">{{ $customerCart->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item bg-light" role="presentation">
                            <button class="nav-link  px-2" id="supplier-tab" data-bs-toggle="tab" data-bs-target="#supplier" type="button" role="tab" aria-controls="supplier" aria-selected="false">
                                Supplier <span class="badge bg-info navbar-badge">{{ $supplierCart->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item bg-light" role="presentation">
                            <button class="nav-link  px-2" id="activecart-tab" data-bs-toggle="tab" data-bs-target="#activecart" type="button" role="tab" aria-controls="activecart" aria-selected="false">
                                Active <span class="badge bg-warning text-white navbar-badge">{{ Session::has('cart') ? count(Session::get('cart')) : 0 }}</span>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="myTabContent">
                        <!-- Customer Tab -->
                        <div class="tab-pane fade show active p-1" id="customer" role="tabpanel" aria-labelledby="customer-tab">
                            @if($customerCart->count() > 0)
                                @foreach($customerCart as $crt)
                                    <div class="d-flex align-items-center mb-3 crtlist">
                                        <!-- Image -->
                                        <div class="flex-shrink-0">
                                            <img src="https://cdn-icons-png.flaticon.com/128/10438/10438143.png" alt="User Avatar" class="avatar-xs rounded-circle">
                                        </div>
                                        <!-- Text Content -->
                                        <div class="flex-grow-1 ms-3">
                                            <a href="{{ url('admin/cart/open-cart-detail/' . $crt->account_id) }}" class=" text-dark p-0" title="{{ $crt->remark }}">
                                                <p class="dropdown-item-title text-sm mb-1  text-wrap ">
                                                    {!! $crt->account_name . " ($crt->account_state_name) " !!}
                                                    <span class="badge bg-success float-end">{{ $crt->item_count }}</span>
                                                </p>
                                                <p class="text-sm mb-1">Updated by: {{ $crt->addedby }} |
                                                    <span class=" badge bg-danger">
                                                        <input type="checkbox" name="takeaway" class="takeaway" data-id="{{$crt->id}}" {{$crt->takeaway=='1' ? 'checked':''}}> TW
                                                    </span>
                                                </p>
                                                <p class="text-sm text-muted mb-0">
                                                    <i class="fa fa-clock-o mr-1"></i> Date: {{ date('d-m-y h:i', strtotime($crt->created_at)) }}
                                                    <span class="float-end">
                                                    <a href="{{ url('admin/cart/cart-delete/' . $crt->id) }}">
                                                        <span class="float-end"><i class="fa fa-trash"></i></span>
                                                    </a>
                                                    </span>
                                                </p>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                @endforeach
                            @else
                                <div class="dropdown-divider"></div>
                                <a href="javascript:void(0)" class="dropdown-item dropdown-footer">No open carts</a>
                            @endif
                        </div>

                        <!-- Supplier Tab -->
                        <div class="tab-pane fade p-2" id="supplier" role="tabpanel" aria-labelledby="supplier-tab">
                            @if($supplierCart->count() > 0)
                                @foreach($supplierCart as $crt)
                                    <div class="d-flex align-items-center mb-3">
                                        <!-- Image -->
                                        <div class="flex-shrink-0">
                                            <img src="https://cdn-icons-png.flaticon.com/128/10438/10438143.png" alt="User Avatar" class="avatar-xs rounded-circle">
                                        </div>
                                        <!-- Text Content -->
                                        <div class="flex-grow-1 ms-3">
                                            <a href="{{ url('admin/cart/open-cart-detail/' . $crt->account_id) }}" class="text-dark p-0" title="{{ $crt->remark }}">
                                                <p class="dropdown-item-title text-sm mb-1 text-wrap">
                                                {!! $crt->account_name . " ($crt->account_state_name) " !!}
                                                    <span class="badge bg-danger float-end">{{ $crt->item_count }}</span>
                                                </p>
                                                <p class="text-sm mb-1">Updated by: {{ $crt->added_by }}</p>
                                                <p class="text-sm text-muted mb-0">
                                                    <i class="fa fa-clock-o mr-1"></i> Date: {{ date('d-m-y h:i', strtotime($crt->created_at)) }}
                                                    <a href="{{ url('admin/cart/cart-delete/' . $crt->id) }}">
                                                        <span class="float-end"><i class="fa fa-trash"></i></span>
                                                    </a>
                                                </p>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                @endforeach
                            @else
                                <div class="dropdown-divider"></div>
                                <a href="javascript:void(0)" class="dropdown-item dropdown-footer">No open carts</a>
                            @endif
                        </div>

                        <!-- Active Cart Tab -->
                        <div class="tab-pane fade p-2" id="activecart" role="tabpanel" aria-labelledby="activecart-tab">
                            @if(Session::has('cart'))
                                @foreach(Session::get('cart') as $acrt)
                                    <div class="d-flex align-items-center mb-3">
                                        <!-- Image -->
                                        <div class="flex-shrink-0">
                                            <img src="https://cdn-icons-png.flaticon.com/128/10438/10438143.png" alt="User Avatar" class="avatar-xs rounded-circle">
                                        </div>
                                        <!-- Text Content -->
                                        <div class="flex-grow-1 ms-3">
                                            <p class="dropdown-item-title text-sm mb-0 text-wrap">
                                                <a href="{{ url('admin/cart/open-cart-detail/' . $acrt['accountid']) }}">{!! $acrt['accountname'] !!}</a>
                                                <a href="{{ url('admin/cart/deactive-cart/' . $acrt['accountid']) }}">
                                                    <span class="text-danger float-end">
                                                        <i class="fas fa-times-circle"></i>
                                                    </span>
                                                </a>

                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="dropdown-divider"></div>
                                <a href="javascript:void(0)" class="dropdown-item dropdown-footer">No active carts</a>
                            @endif
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="dropdown d-inline-block">
           <button type="button" class="btn header-item noti-icon waves-effect"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Catalogue">

                @switch(Session::get('lang'))
                    @case('ru')
                        <img src="{{ URL::asset('/assets/images/flags/russia.jpg')}}" alt="Header Language" height="16"> <span class="align-middle">Russian</span>
                    @break
                    @case('it')
                        <img src="{{ URL::asset('/assets/images/flags/italy.jpg')}}" alt="Header Language" height="16"> <span class="align-middle">Italian</span>
                    @break
                    @case('de')
                        <img src="{{ URL::asset('/assets/images/flags/germany.jpg')}}" alt="Header Language" height="16"> <span class="align-middle">German</span>
                    @break
                    @case('es')
                        <img src="{{ URL::asset('/assets/images/flags/spain.jpg')}}" alt="Header Language" height="16"> <span class="align-middle">Spanish</span>
                    @break
                    @default
                    <i class="bx bx-book-open"></i>
                    <!-- <img src="https://www.abbangles.com/public/images/other/catalog-icon-19.png" alt="Catalogue" height="35"> -->
                @endswitch
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <span class="dropdown-item dropdown-header">Catalogue</span>
                <div class="px-2">
                    <form action="{{url('admin/catalogue/main-product-catalogue')}}" method="POST">
                        @csrf
                        <input type="hidden" name="length" value="9999">
                        <div class="input-group">
                            <input type="text" class="form-control rounded" minlength="3" name="prodName" placeholder="Search by name 3 digit'">
                            <button class="btn brn-sm btn-danger rounded-circle" type="submit" name="submit" id="button-addon">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
				<div class="dropdown-divider"></div>
                <!-- item-->
					<a href="{{url('admin/catalogue/main-product-catalogue')}}"  class="dropdown-item">
						<i class="fa fa-list mr-2"></i> Catalogue Main
					</a>
				<div class="dropdown-divider"></div>
					<a href="{{url('admin/catalogue/offer-product-catalogue')}}" class="dropdown-item">
						<i class="fa fa-star mr-2"></i> Offer Catalogue
					</a>
					<a href="{{url('/admin/catalogue/latest-product-catalogue')}}" class="dropdown-item">
						<i class="fa fa-star mr-2"></i> Latest Prod Catalogue
					</a>
				<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="{{url('admin/catalogue/upcoming-product-catalogue')}}">
						<i class="fa fa-star mr-2"></i> Upcoming Prod Catalogue
					</a>
					<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="{{url('admin/unused-product-catalogue')}}">
						<i class="fa fa-star mr-2"></i> Un-used Prod Catalogue
					</a>
                <div class="dropdown-divider"></div>
					<a class="dropdown-item" href="{{url('admin/catalogue/party-catalogue')}}">
						<i class="fa fa-star mr-2"></i> Party Catalogue
					</a>
                <div class="dropdown-divider"></div>
					<a class="dropdown-item" href="{{url('admin/catalogue/trending-catalogue')}}">
						<i class="fa fa-star mr-2"></i> Trending Catalogue
					</a>
				
            </div>
        </div>
        <div class="dropdown d-lg-inline-block ms-1">
            <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-customize bx-spin"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0">
                <div class="p-3">
                    <h6 class="dropdown-header text-center mb-2">
                        <i class="mdi mdi-lightning-bolt me-1 text-warning"></i>
                        Quick Access
                    </h6>
                    <div class="list-group list-group-flush">
                        <!-- New Leads -->
                        <a href="{{route('admin.inquiry.index')}}" class="list-group-item list-group-item-action d-flex align-items-center py-2">
                            <div class="icon-wrapper bg-primary bg-opacity-10 rounded-3 p-2 me-2">
                                <i class="mdi mdi-account-plus mdi-24px text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">New Leads</h6>
                                <small class="text-muted">Manage customer inquiries</small>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>

                        <!-- Sale Inquiry -->
                        <a href="{{route('admin.sales-inquiry.index')}}" class="list-group-item list-group-item-action d-flex align-items-center py-2">
                            <div class="icon-wrapper bg-success bg-opacity-10 rounded-3 p-2 me-2">
                                <i class="mdi mdi-message-question mdi-24px text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Sale Inquiry</h6>
                                <small class="text-muted">Process sales inquiries</small>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>

                        <!-- Sale -->
                        <a href="{{route('admin.sale.index')}}" class="list-group-item list-group-item-action d-flex align-items-center py-2">
                            <div class="icon-wrapper bg-info bg-opacity-10 rounded-3 p-2 me-2">
                                <i class="mdi mdi-cart mdi-24px text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Sale</h6>
                                <small class="text-muted">Manage sales transactions</small>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>

                        <!-- Purchase -->
                        <a href="{{route('admin.purchase.index')}}" class="list-group-item list-group-item-action d-flex align-items-center py-2">
                            <div class="icon-wrapper bg-warning bg-opacity-10 rounded-3 p-2 me-2">
                                <i class="mdi mdi-package-variant mdi-24px text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Purchase</h6>
                                <small class="text-muted">Handle purchase orders</small>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>

                        <!-- Dispatch -->
                        <a href="{{route('admin.dispatch.index')}}" class="list-group-item list-group-item-action d-flex align-items-center py-2">
                            <div class="icon-wrapper bg-danger bg-opacity-10 rounded-3 p-2 me-2">
                                <i class="mdi mdi-truck-delivery mdi-24px text-danger"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Dispatch</h6>
                                <small class="text-muted">Manage product dispatch</small>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>

                        <!-- Main Catalogue -->
                        <a href="{{route('admin.sample.index')}}" class="list-group-item list-group-item-action d-flex align-items-center py-2">
                            <div class="icon-wrapper bg-purple bg-opacity-10 rounded-3 p-2 me-2">
                                <i class="mdi mdi-book-open-variant mdi-24px text-purple"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">Sample Room</h6>
                                <small class="text-muted">List of item for sample rooms</small>
                            </div>
                            <i class="mdi mdi-chevron-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="dropdown d-none d-lg-inline-block ms-1">
            <button type="button" class="btn header-item noti-icon waves-effect" data-toggle="dropdown">
                <i class="bx bx-fullscreen"></i>
            </button>
        </div>

        <div class="dropdown d-inline-block">
            @php $notificationCount=TakeawayDispatchNotification()->count(); @endphp
            <button type="button" class="btn header-item noti-icon waves-effect" id="page-header-notifications-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-bell bx-tada"></i>
                <span class="badge bg-danger rounded-pill">{{$notificationCount}}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-3"
                aria-labelledby="page-header-notifications-dropdown">
                <div class="p-3">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-0" key="t-notifications"> Notifications </h6>
                        </div>
                        <div class="col-auto">
                            <a href="#!" class="small" key="t-view-all"> View All</a>
                        </div>
                    </div>
                </div>
                <div data-simplebar style="max-height: 230px;" class="p-3">
                    @if(TakeawayDispatchNotification()->count()>0)
                    @php $takeaway=TakeawayDispatchNotification(); @endphp
                    @foreach( $takeaway as $tw)
                                           <a href="" class="text-reset notification-item">
                            <div class="media">
                                <div class="avatar-xs me-3">
                                    <span class="avatar-title bg-primary rounded-circle font-size-16">
                                        <i class="bx bx-cart"></i>
                                    </span>
                                </div>
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1" key="t-your-order">
                                        {{ $tw->saleorder->invoice_No }} | {{ $tw->saleorder->account_name }}
                                    </h6>
                                    <div class="font-size-12 text-muted">
                                        <p class="mb-1" key="t-grammer"></p>
                                        <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span key="t-min-ago">By: {{ myDateFormat($tw->saleorder->billDate) }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                    @endif
                </div>
                <div class="p-2 border-top d-grid">
                    <a class="btn btn-sm btn-link font-size-14 text-center" href="javascript:void(0)">
                        <i class="mdi mdi-arrow-right-circle me-1"></i> <span key="t-view-more">@lang('translation.View_More')</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="dropdown d-inline-block">
            <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img class="rounded-circle header-profile-user" src="{{ isset(Auth::user()->profile_image) ? asset(Auth::user()->profile_image) : asset('/assets/images/users/avatar-1.jpg') }}"
                    alt="Header Avatar">
                <span class="d-none d-xl-inline-block ms-1" key="t-henry">{{ucfirst(@Auth::user()->first_name.' '.@Auth::user()->last_name )}}</span>
                <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end">
                <!-- item-->
                @if(Auth::user()->type == 'super_admin')
                <a class="dropdown-item" href="{{route('superadmin.profile')}}"><i class="bx bx-user font-size-16 align-middle me-1"></i> <span key="t-profile">Profile</span></a>
                @else
                    <a class="dropdown-item" href="{{route('admin.profile')}}"><i class="bx bx-user font-size-16 align-middle me-1"></i> <span key="t-profile">Profile</span></a>
                @endif


                <a class="dropdown-item d-block" href="#" data-bs-toggle="modal" data-bs-target=".change-password"><i class="bx bx-wrench font-size-16 align-middle me-1"></i> <span key="t-settings">Change Password</span></a>

                <div class="dropdown-divider"></div>
                <!-- <a class="dropdown-item text-danger" href="javascript:void();" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span key="t-logout">Logout</span></a> -->

                <a class="dropdown-item text-danger" href="{{route('front.logout')}}"><i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span key="t-logout">@lang('translation.Logout')</span></a>

            </div>
        </div>

    </div>
</div>
</header>

<!--  Change-Password example -->
<div class="modal fade change-password" tabindex="-1" role="dialog"
aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="change-password">
                    @csrf
                    <input type="hidden" value="{{ @Auth::user()->id }}" id="data_id">
                    <div class="mb-3">
                        <label for="current_password">Current Password</label>
                        <input id="current-password" type="password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            name="current_password" autocomplete="current_password"
                            placeholder="Enter Current Password" value="{{ old('current_password') }}">
                        <div class="text-danger" id="current_passwordError" data-ajax-feedback="current_password"></div>
                    </div>

                    <div class="mb-3">
                        <label for="newpassword">New Password</label>
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            autocomplete="new_password" placeholder="Enter New Password">
                        <div class="text-danger" id="passwordError" data-ajax-feedback="password"></div>
                    </div>

                    <div class="mb-3">
                        <label for="userpassword">Confirm Password</label>
                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation"
                            autocomplete="new_password" placeholder="Enter New Confirm password">
                        <div class="text-danger" id="password_confirmError" data-ajax-feedback="password-confirm"></div>
                    </div>

                    <div class="mt-3 d-grid">
                        <button class="btn btn-primary waves-effect waves-light UpdatePassword" data-id="{{ @Auth::user()->id }}"
                            type="submit">Update Password</button>
                    </div>
                </form>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

