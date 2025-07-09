<?php

use App\DeviceToken;
use App\PushLog;
use App\User;
use App\Models\SerialNo;
use App\Models\Account;
use App\Models\Payment;
use Carbon\Carbon;
use App\Models\Cart;
use App\Models\QrModel;
use Illuminate\Support\Facades\Redirect;
//use db;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
//use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; // Explicitly import GD Driver
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Encoders\JpegEncoder;

use SimpleSoftwareIO\QrCode\Facades\QrCode;


function getNewSerialNo($type)
{
    $sqry = SerialNo::withoutGlobalScope('fyear_branch_filter')->where('name', '=', $type)->first();

    if ($sqry && $sqry->type == 'transaction') {
        $billNo = SerialNo::where('name', '=', $type)
            ->select('prefix', 'length', 'financialYear', 'next_number')
            ->first();

    } else {
        $billNo = SerialNo::withoutGlobalScope('fyear_branch_filter')->where('name', '=', $type)
            ->select('prefix', 'length', 'financialYear', 'next_number')
            ->first();
    }

    if ($billNo) {
        $next_number = str_pad($billNo->next_number, $billNo->length, "0", STR_PAD_LEFT);
        // dd($billNo->prefix . $billNo->financialYear . $next_number);
        return $billNo->prefix . $billNo->financialYear . $next_number;
    } else {
        success_session('Serial Number Not Found..');
        // return redirect()->back()->with('error', 'Serial No not found');
    }

}

function increaseSerialNo($type)
{
    $sqry = SerialNo::withoutGlobalScope('fyear_branch_filter')->where('name', '=', $type)->first();

    if ($sqry && $sqry->type == 'transaction') {
        SerialNo::where('name', '=', $type)->increment('next_number', 1);
    } else {
        //======for transaction type 'MASTER'====
        SerialNo::withoutGlobalScope('fyear_branch_filter')->where('name', '=', $type)->increment('next_number', 1);
    }

}


if (!function_exists('send_response')) {

    function companyinfo(){
       
        $company=\App\Models\Company::first();
         $a['company'] = $company;
        return $a;
    }

    function send_response($Status, $Message = "", $ResponseData = NULL, $extraData = NULL, $null_remove = null)
    {
        $data = [];
        $valid_status = [412, 200, 401];
        if (is_array($ResponseData)) {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = $ResponseData;
        } else if (!empty($ResponseData)) {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = $ResponseData;
        } else {
            $data["status"] = $Status;
            $data["message"] = $Message;
            $data["data"] = new stdClass();
        }
        if (!empty($extraData) && is_array($extraData)) {
            foreach ($extraData as $key => $val) {
                $data[$key] = $val;
            }
        }
        //        if ($null_remove) {
//            null_remover($data['data']);
//        }
        $header_status = in_array($data['status'], $valid_status) ? $data['status'] : 412;
        response()->json($data, $header_status)->header('Content-Type', 'application/json')->send();
        die(0);
    }
}


//function null_remover($response, $ignore = [])
//{
//    array_walk_recursive($response, function (&$item) {
//        if (is_null($item)) {
//            $item = strval($item);
//        }
//    });
//    return $response;
//}

function token_generator()
{
    return genUniqueStr('', 100, 'device_tokens', 'token', true);
}

function get_header_auth_tokenddd()
{
    $full_token = request()->header('Authorization');
    return (substr($full_token, 0, 7) === 'Bearer ') ? substr($full_token, 7) : null;
}

function get_header_auth_token()
{
    return request()->header('mytoken') ?? request()->header('Authorization');
}


if (!function_exists('processImage')) {
    function processImage($sourcePath, $type = 'product', $dimensions = [], $crop = false, $quality = 85, $maxSizeKB = 800, $fileName = '')
    {
        try {
            $defaultDimensions = [
                'product' => [1280, 720],
                'thumbnails' => [320, 180],
                'user' => [300, 300],
            ];

            $size = !empty($dimensions) ? $dimensions : ($defaultDimensions[$type] ?? [1280, 720]);
            $width = $size[0];
            $height = $size[1];

            $filename = $fileName ?: uniqid() . '_' . time() . '.' . pathinfo($sourcePath, PATHINFO_EXTENSION);
            if ($type == 'product') {
                $directory = 'product';
            } elseif ($type == 'thumbnails') {
                $directory = 'product/thumbnails';
            } elseif ($type == 'user') {
                $directory = 'user';
            } else {
                $directory = 'others';
            }
            $destinationPath = "{$directory}/{$filename}";

            Storage::disk('public')->makeDirectory($directory);

            $manager = new ImageManager(new Driver());
            $image = $manager->read($sourcePath);

            if ($crop) {
                $image->crop($width, $height);
            } else {
                $image->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            $currentQuality = min($quality, 100);
            $tempPath = Storage::disk('public')->path($destinationPath);

            do {
                $image->save($tempPath, $currentQuality);

                $fileSizeKB = filesize($tempPath) / 1024;

                if ($fileSizeKB > $maxSizeKB && $currentQuality > 20) {
                    $currentQuality -= 20;
                    continue;
                }
                break;
            } while (true);

            // Removed $image->destroy() - not needed in 3.10.1

            if ($fileSizeKB > $maxSizeKB) {
                Storage::disk('public')->delete($destinationPath);
                \Log::warning("Image could not be optimized below {$maxSizeKB}KB");
                return null;
            }

            return $filename;

        } catch (\Exception $e) {
            \Log::error('Image processing failed: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('un_link_file')) {
    function un_link_file($image_name = "")
    {
        $pass = true;
        if (!empty($image_name)) {
            try {
                $default_url = URL::to('/');
                $get_default_images = config('constants.default');
                $file_name = str_replace($default_url, '', $image_name);
                $default_image_list = is_array($get_default_images) ? str_replace($default_url, '', array_values($get_default_images)) : [];
                if (!in_array($file_name, $default_image_list)) {
                    Storage::disk(get_constants('upload_type'))->delete($file_name);
                }
            } catch (Exception $exception) {
                $pass = $exception;
            }
        } else {
            $pass = 'Empty Field Name';
        }
        return $pass;
    }
}


function get_asset($val = "", $file_exits_check = true, $no_image_available = null)
{
    $no_image_available = ($no_image_available ?? asset(get_constants('default.no_image_available')));
    if ($val) {
        if ($file_exits_check) {
            return (file_exists(public_path($val))) ? asset($val) : $no_image_available;
        } else {
            return asset($val);
        }
    } else {
        return asset($no_image_available);
    }
}

function print_title($title)
{
    return ucfirst($title);
}

function get_constants($name)
{
    return config('constants.' . $name);
}

function calculate_percentage($amount = 0, $discount = 0)
{
    return ($amount && $discount) ? (($amount * $discount) / 100) : 0;
}

function flash_session($name = "", $value = "")
{
    session()->flash($name, $value);
}

function success_session($value = "")
{
    session()->flash('success', ucfirst($value));
}

function error_session($value = "")
{
    session()->flash('error', ucfirst($value));
}

function getDashboardRouteName()
{
    $name = 'front.dashboard';
    $user_data = Auth::user();
    if ($user_data) {
        if (in_array($user_data->type, ["admin", "local_admin", 'user', 'superadmin'])) {
            $name = 'admin.dashboard';
        }
    }
    return $name;
}

function admin_modules()
{
    return [
        [
            'route' => route('admin.dashboard'),
            'name' => __('Dashboard'),
            'icon' => 'kt-menu__link-icon fa fa-home',
            'visible' => Auth::user()->hasDirectPermission('dashboard_view'),
            'child' => [],
            'all_routes' => [
                'admin.dashboard',
            ]
        ],
        [
            'route' => 'javascript:;',
            'name' => __('User'),
            'icon' => '<img src="' . asset('assets/images/icons/user.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('user_view', 'role_view', 'permission_view'),
            'all_routes' => [
                'admin.user.index',
                'admin.user.show',
                'admin.user.add',
            ],
            'child' => [
                [
                    'route' => route('admin.user.index'),
                    'name' => __('Manage User'),
                    'icon' => '<img src="' . asset('assets/images/icons/user.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('user_view'),
                    'child' => [],
                    'all_routes' => [
                        'admin.user.index',
                        'admin.user.show',
                        'admin.user.add',
                    ],
                ],
                [
                    'route' => route('admin.role.index'),
                    'name' => __('Roles'),
                    'icon' => '<img src="' . asset('assets/images/icons/Roles.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('role_view'),
                    'child' => [],
                    'all_routes' => [
                        'admin.role.index',
                        'admin.role.show',
                        'admin.role.add',
                    ],
                ],
                [
                    'route' => route('admin.permission.index'),
                    'name' => __('Permission'),
                    'icon' => '<img src="' . asset('assets/images/icons/Permission.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('permission_view'),
                    'child' => [],
                    'all_routes' => [
                        'admin.permission.index',
                        'admin.permission.show',
                        'admin.permission.add',
                    ],

                ],
                [
                    'route' => route('admin.department.index'),
                    'name' => __('Department'),
                   'icon' => '<img src="' . asset('assets/images/icons/Department.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('branch_view'),
                    'child' => [],
                    'all_routes' => [
                        'admin.department.index',
                        'admin.branch.show',
                        'admin.department.add',
                    ],

                ],
            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Account'),
           'icon' => '<img src="' . asset('assets/images/icons/Account.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('accounts_view', 'city_view', 'account_group_view'),
            'all_routes' => [
                'admin.account.index',
                'admin.account.show',
                'admin.account.add',
            ],
            'child' => [
                [
                    'route' => route('admin.account.index'),
                    'name' => __('Account'),
                    'icon' => '<img src="' . asset('assets/images/icons/Account.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('accounts_view'),
                    // 'visible' =>true,
                    'all_routes' => [
                        'admin.account.index',
                        'admin.account.show',
                        'admin.account.add',
                    ],
                ],
                [
                    'route' => route('admin.account-group.index'),
                    'name' => __('Account Group'),
                   'icon' => '<img src="' . asset('assets/images/icons/Account_Group.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => true,
                    'all_routes' => [
                        'admin.account-group.index',
                        'admin.account-group.show',
                        'admin.account-group.add',
                    ],
                ],
                [
                    'route' => route('admin.account.opening-balance-update'),
                    'name' => __('Opening Balance'),
                    'icon' => '<img src="' . asset('assets/images/icons/Opening_Balance.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => true,
                    'all_routes' => [
                        'admin.account.opening-balance-update',
                    ],
                ],
                [
                    'route' => route('admin.city.index'),
                    'name' => __('Cities'),
                    'icon' => '<img src="' . asset('assets/images/icons/Cities.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('city_view'),
                    'all_routes' => [
                        'admin.city.index',
                        'admin.city.add',
                    ],
                ],
            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Inquiry'),
            'icon' => '<img src="' . asset('assets/images/icons/Inquiry.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('inquiry_view', 'sales_inquiry_view'),
            'child' => [
                [
                    'route' => route('admin.inquiry.index'),
                    'name' => __('New Leads'),
                   'icon' => '<img src="' . asset('assets/images/icons/New_Leads.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('inquiry_view'),
                    'all_routes' => [
                        'admin.inquiry.index',
                        'admin.inquiry.show',
                        'admin.inquiry.add',
                    ],
                ],
                [
                    'route' => route('admin.sales-inquiry.index'),
                    'name' => __('Sales Inquery'),
                    'icon' => '<img src="' . asset('assets/images/icons/Sales_Inquiry.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('sales_inquiry_view'),
                    'all_routes' => [
                        'admin.sales-inquiry.index',
                        'admin.sales-inquiry.show',
                        'admin.sales-inquiry.add',
                    ],
                ],
            ],
            'all_routes' => [
                'admin.inquery.index',
                'admin.inquery.show',
                'admin.inquery.add',
                'admin.sales-inquiry.index',
                'admin.sales-inquiry.show',
                'admin.sales-inquiry.add',
            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Sales'),
            'icon' => '<img src="' . asset('assets/images/icons/Sales.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('sales_view', 'sales_order_view', 'sales_return_view'),
            'child' => [
                [
                    'route' => route('admin.sale-order.index'),
                    'name' => __('Sale Order'),
                    'icon' => '<img src="' . asset('assets/images/icons/Sale_Order.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('sales_order_view'),
                    'all_routes' => [
                        'admin.sale-order.index',
                        'admin.sale-order.show',
                        'admin.sale-order.add',
                    ],
                ],
                [
                    'route' => route('admin.sale.index'),
                    'name' => __('Sale Bill'),
                    'icon' => '<img src="' . asset('assets/images/icons/Sale_Bill.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('sales_view'),
                    'all_routes' => [
                        'admin.sale.index',
                        'admin.sale.show',
                        'admin.sale.add',
                    ],
                ],
                [
                    'route' => route('admin.sale-return.index'),
                    'name' => __('Sale Return'),
                    'icon' => '<img src="' . asset('assets/images/icons/Sale_Return.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('sales_return_view'),
                    'all_routes' => [
                        'admin.sale-return.index',
                        'admin.sale-return.show',
                        'admin.sale-return.add',
                    ],
                ],
                [
                    'route' => url('admin/report/bill-register-detail/sale-order'),
                    'name' => __('So manual clear'),
                    'icon' => 'kt-menu__link-iconfas fa fa-bullseye',
                    'visible' => true,
                    'all_routes' => [
                        //
                    ],
                ],
            ],
            'all_routes' => [
                'admin.inquery.index',
                'admin.inquery.show',
                'admin.inquery.add',
            ],

        ],
        [
            'route' => route('admin.dispatch.index'),
            'name' => __('Dispatch'),
            'icon' => '<img src="' . asset('assets/images/icons/Dispatch.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasDirectPermission('dispatch_view'),
            'all_routes' => [
                'admin.dispatch.index',
                'admin.dispatch.show',
                'admin.dispatch.add',
            ],
            'child' => [],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Purchase'),
            'icon' => '<img src="' . asset('assets/images/icons/Purchase.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('purchase_view', 'purchase_order_view', 'purchase_return_view'),
            'all_routes' => [
                'admin.purchase-order.index',
                'admin.purchase-order.add',
                'admin.purchase-order.show',
            ],
            'child' => [
                [
                    'route' => route('admin.purchase-order.index'),
                    'name' => __('Purchase Order'),
                    'icon' => '<img src="' . asset('assets/images/icons/Purchase_Order.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('purchase_order_view'),
                    'all_routes' => [
                        'admin.purchase-order.index',
                        'admin.purchase-order.add',
                        'admin.purchase-order.show',
                    ],
                ],
                [
                    'route' => route('admin.purchase.index'),
                    'name' => __('Purchase'),
                    'icon' => '<img src="' . asset('assets/images/icons/Purchase.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('purchase_view'),
                    'all_routes' => [
                        'admin.purchase.index',
                        'admin.purchase.add',
                        'admin.purchase.show',
                    ],
                ],
                [
                    'route' => route('admin.purchase-return.index'),
                    'name' => __('Purchase Return'),
                    'icon' => '<img src="' . asset('assets/images/icons/Purchase_Return.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('purchase_return_view'),
                    'all_routes' => [
                        'admin.purchase-return.index',
                        'admin.purchase-return.add',
                        'admin.purchase-return.show',
                    ],
                ],
                [
                    'route' => url('admin/report/bill-register-detail/purchase-order'),
                    'name' => __('Po manual clear'),
                    'icon' => 'kt-menu__link-iconfas fa fa-bullseye',
                    'visible' => true,
                    'all_routes' => [
                       //
                    ],
                ],
            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Payments'),
            'icon' => '<img src="' . asset('assets/images/icons/Payments.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('payment_view', 'payment_inward_view', 'payment_outward_view', 'payment_transfer_view'),
            'all_routes' => [

            ],
            'child' => [
                [
                    'route' => route('admin.payment.inward.index'),
                    'name' => __('Inward'),
                    'icon' => '<img src="' . asset('assets/images/icons/Inward.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('payment_inward_view'),
                    'all_routes' => [
                        'admin.payment.inward.index',
                        'admin.payment.inward.show',
                        'admin.payment.inward.add',
                    ],
                ],
                [
                    'route' => route('admin.payment.outward.index'),
                    'name' => __('Outward'),
                    'icon' => '<img src="' . asset('assets/images/icons/Outward.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('payment_outward_view'),
                    'all_routes' => [
                        'admin.payment.outward.index',
                        'admin.payment.outward.show',
                        'admin.payment.outward.add',
                    ],
                ],
                [
                    'route' => route('admin.payment.transfer.index'),
                    'visible' => Auth::user()->hasDirectPermission('payment_transfer_view'),
                    'name' => __('Transfer'),
                    'icon' => '<img src="' . asset('assets/images/icons/Transfer.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'all_routes' => [
                        'admin.payment.transfer.index',
                        'admin.payment.transfer.show',
                        'admin.payment.transfer.add',
                    ],
                ],
            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Product'),
            'icon' => '<img src="' . asset('assets/images/icons/Products.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('product_view', 'category_view', 'parent_category_view', 'product_material_view', 'product_color_view', 'gst_view', 'product_stock', 'product_stock_price_update', 'product_stock_update', 'product_size_view'),
            'all_routes' => [
                'admin.product.index',
                'admin.product.show',
                'admin.product.add',
                'admin.category.index',
                'admin.category.show',
                'admin.category.add',
                'admin.parent-category.index',
                'admin.parent-category.show',
                'admin.parent-category.add',
                'admin.material.index',
                'admin.material.show',
                'admin.material.add',
                'admin.material.edit',
                'admin.size.index',
                'admin.size.show',
                'admin.size.add',
                'admin.size.edit',
                'admin.color.index',
                'admin.color.show',
                'admin.color.add',
                'admin.color.edit',
                'admin.gst.index',
                'admin.gst.show',
                'admin.gst.add',
                'admin.gst.edit',
            ],
            'child' => [
                [
                    'route' => route('admin.product.index'),
                    'name' => __('Products'),
                    'icon' => '<img src="' . asset('assets/images/icons/Products.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('product_view'),
                    'all_routes' => [
                        'admin.product.index',
                        'admin.product.show',
                        'admin.product.add',
                    ],
                ],
                [
                    'route' => route('admin.category.index'),
                    'name' => __('Categories'),
                    'icon' => '<img src="' . asset('assets/images/icons/Categories.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('category_view'),
                    'all_routes' => [
                        'admin.category.index',
                        'admin.category.show',
                        'admin.category.add',
                    ],
                ],
                [
                    'route' => route('admin.parent-category.index'),
                    'name' => __('Parent Categories'),
                    'icon' => '<img src="' . asset('assets/images/icons/Categories.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('parent_category_view'),
                    'all_routes' => [
                        'admin.parent-category.index',
                        'admin.parent-category.show',
                        'admin.parent-category.add',
                    ],
                ],
                [
                    'route' => route('admin.material.index'),
                    'name' => __('Materials'),
                    'icon' => '<img src="' . asset('assets/images/icons/Materials.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('product_material'),
                    'all_routes' => [
                        'admin.material.index',
                        'admin.material.show',
                        'admin.material.add',
                        'admin.material.edit',
                    ],
                ],
                [
                    'route' => route('admin.size.index'),
                    'name' => __('Sizes'),
                    'icon' => '<img src="' . asset('assets/images/icons/Size.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('product_size_view'),
                    'all_routes' => [
                        'admin.size.index',
                        'admin.size.show',
                        'admin.size.add',
                        'admin.size.edit',
                    ],
                ],
                [
                    'route' => route('admin.pair.index'),
                    'name' => __('Pairs'),
                    'icon' => '<img src="' . asset('assets/images/icons/Size.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    //'visible' => Auth::user()->hasDirectPermission('product_size_view'),
                    'visible' => true,
                    'all_routes' => [
                        'admin.pair.index',
                        'admin.pair.show',
                        'admin.pair.add',
                        'admin.pair.edit',
                    ],
                ],
                [
                    'route' => route('admin.color.index'),
                    'name' => __('Colors'),
                    'icon' => '<img src="' . asset('assets/images/icons/Colours.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('product_color_view'),
                    'all_routes' => [
                        'admin.color.index',
                        'admin.color.show',
                        'admin.color.add',
                        'admin.color.edit',
                    ],
                ],
                [
                    'route' => route('admin.gst.index'),
                    'name' => __('GST'),
                    'icon' => '<img src="' . asset('assets/images/icons/GST.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('gst_view'),
                    'all_routes' => [
                        'admin.gst.index',
                        'admin.gst.show',
                        'admin.gst.add',
                        'admin.gst.edit',
                    ],
                ],
                [
                    'route' => route('admin.stock.stock-status'),
                    'name' => __('Stocks Status'),
                    'icon' => '<img src="' . asset('assets/images/icons/Stocks_Status.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('product_stock_status'),
                    'all_routes' => [

                    ],
                ],
                [
                    'route' => route('admin.stock.price-update'),
                    'name' => __('Price Update'),
                    'icon' => '<img src="' . asset('assets/images/icons/Price_Update.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('product_stock_price_update'),
                    'all_routes' => [

                    ],
                ],
                [
                    'route' => route('admin.stock.stockqty-update'),
                    'name' => __('Stock Update'),
                    'icon' => '<img src="' . asset('assets/images/icons/Stock_Update.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('product_stock_update'),
                    'all_routes' => [

                    ],
                ],

            ],
        ],
        [
            'route' => route('admin.offer.index'),
            'name' => __('Disount-Offer Master '),
            'icon' => '<img src="' . asset('assets/images/icons/Discount_Offer_Master.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasDirectPermission('offer_discount_view'),
            'all_routes' => [
                'admin.offer.index',
            ],
            'child' => [],
        ],
        [
            'route' => route('admin.gift.index'),
            'name' => __('Gift'),
            'icon' => '<img src="' . asset('assets/images/icons/Gift.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasDirectPermission('gift_view'),
            'all_routes' => [
                'admin.gift.index',
            ],
            'child' => [],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Catalogue'),
            'icon' => '<img src="' . asset('assets/images/icons/Catalogue.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('catalogue_view', 'latest_product_catalogue', 'upcoming_product_catalogue', 'offer_catalogue', 'unused_product_catalogue'),
            'all_routes' => [
                'admin.catalogue.main-catalogue',
                'admin.catalogue.latest-product-catalogue',
                'admin.catalogue.upcoming-product-catalogue',
                'admin.catalogue.offer-product-catalogue',
                'admin.catalogue.unused-product-catalogue',
            ],
            'child' => [
                [
                    'route' => route('admin.catalogue.main-catalogue'),
                    'name' => 'Main Catalogue',
                    'visible' => Auth::user()->hasDirectPermission('catalogue_view'),
                    'icon' => '<img src="' . asset('assets/images/icons/Catalogue.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'all_routes' => [
                        'admin.catalogue.main-catalogue',
                    ],
                ],
                [
                    'route' => route('admin.catalogue.latest-product-catalogue'),
                    'name' => 'Latest Product Catalogue',
                    'visible' => Auth::user()->hasDirectPermission('latest_product_catalogue'),
                    'icon' => '<img src="' . asset('assets/images/icons/Catalogue.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'all_routes' => [
                        'admin.catalogue.latest-product-catalogue',
                    ],
                ],
                [
                    'route' => route('admin.catalogue.upcoming-product-catalogue'),
                    'name' => 'Upcoming Product Catalogue',
                    'visible' => Auth::user()->hasDirectPermission('upcoming_product_catalogue'),
                    'icon' => '<img src="' . asset('assets/images/icons/Upcoming_Product_Catalogue.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'all_routes' => [
                        'admin.catalogue.upcoming-product-catalogue',
                    ],
                ],
                [
                    'route' => route('admin.catalogue.offer-product-catalogue'),
                    'name' => 'Offer Catalogue',
                    'icon' => '<img src="' . asset('assets/images/icons/Offer_Catalogue.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('offer_catalogue'),
                    'all_routes' => [
                        'admin.catalogue.offer-product-catalogue',
                    ],
                ],
                [
                    'route' => route('admin.catalogue.unused-product-catalogue'),
                    'name' => 'Unused Product Catalogue',
                    'icon' => '<img src="' . asset('assets/images/icons/Unused_Product_Catalogue.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('unused_product_catalogue'),
                    'all_routes' => [
                        'admin.catalogue.unused-product-catalogue',
                    ],
                ]
            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Other'),
            'icon' => '<img src="' . asset('assets/images/icons/Manage_Transporter.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('transporter_view'),
            'all_routes' => [
                'admin.transporters.index',
                'admin.transporters.show',
                "admin.transporters.add",
                'admin.transporters.edit',
                'admin.transporters.delete',
                'admin.transporters.status',
            ],
            'child' => [
                [
                    'route' => route('admin.transporters.index'),
                    'name' => 'Manager Transporter',
                    'icon' => '<img src="' . asset('assets/images/icons/Manage_Transporter.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('transporter_view'),
                    'all_routes' => [
                        'admin.transporters.index',
                        'admin.transporters.show',
                        "admin.transporters.add",
                        'admin.transporters.edit',
                    ],
                ],

            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Reports'),
            'icon' => '<img src="' . asset('assets/images/icons/Reports.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('dispatch_all', 'dispatch_fullfil', 'bill_register', 'bill_register_detail'),
            'all_routes' => [
                'admin.report.all-dispatch',
                'admin.report.fullfil-dispatch',
                'admin.report.billwise-detail-report',
            ],
            'child' => [
                [
                    'route' => route('admin.report.all-dispatch'),
                    'name' => 'Dispatch All',
                    'icon' => '<img src="' . asset('assets/images/icons/Dispatch_All.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('dispatch_all'),
                    'all_routes' => [
                        'admin.report.all-dispatch',
                    ],
                ],
                [
                    'route' => route('admin.report.fullfil-dispatch'),
                    'name' => 'Dispatch Fulfil',
                    'icon' => '<img src="' . asset('assets/images/icons/Dispatch_Fulfil.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('dispatch_fullfil'),
                    'all_routes' => [
                        'admin.report.fullfil-dispatch',
                    ],
                ],
                [
                    'route' => route('admin.report.bill-register'),
                    'name' => 'Bill Register',
                    'icon' => '<img src="' . asset('assets/images/icons/Bill_Register.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('bill_register'),
                    'all_routes' => [
                        'admin.report.all-dispatch',
                    ],
                ],
                [
                    'route' => route('admin.report.bill-register-detail'),
                    'name' => 'Billwise Detail',
                    'icon' => '<img src="' . asset('assets/images/icons/Billwise_Details.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('bill_register_detail'),
                    'all_routes' => [
                        'admin.report.bill-register-detail',
                    ],
                ],
            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('General Settings'),
            'icon' => '<img src="' . asset('assets/images/icons/General_Setting.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('site', 'change_password'),
            'all_routes' => [
                'admin.get_update_password',
                'admin.get_site_settings',
            ],
            'child' => [
                [
                    'route' => route('admin.get_update_password'),
                    'name' => 'Change Password',
                    'visible' => true,
                    'icon' => '<img src="' . asset('assets/images/icons/Change_Password.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'all_routes' => [
                        'admin.get_update_password',
                    ],
                ],
                [
                    'route' => route('admin.get_site_settings'),
                    'name' => 'Site Settings',
                    'visible' => true,
                    'icon' => '<img src="' . asset('assets/images/icons/General_Setting.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'all_routes' => [
                        'admin.get_site_settings',
                    ],
                ],
                [
                    'route' => route('admin.generate_stock_qrcode'),
                    'name' => 'Generate Stock Qrcode',
                    'icon' => '<img src="' . asset('assets/images/icons/Generate_Stock_QR.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => true,
                    'all_routes' => [
                        'admin.generate_stock_qrcode',
                    ],
                ]
            ],
        ],
        [
            'route' => 'javascript:;',
            'name' => __('Company Settings'),
            'icon' => '<img src="' . asset('assets/images/icons/Company_Setting.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('financial_year_view', 'branch_view'),
            'all_routes' => [
                'admin.get_update_password',
                'admin.get_site_settings',
            ],
            'child' => [
                [
                    'route' => route('admin.branch.index'),
                    'name' => __('Branch'),
                    'icon' => '<img src="' . asset('assets/images/icons/Branch.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('branch_view'),
                    'child' => [],
                    'all_routes' => [
                        'admin.branch.index',
                        'admin.branch.show',
                        'admin.branch.add',
                    ],
                ],
                [
                    'route' => route('admin.company.create'),
                    'name' => __('Company'),
                    'icon' => '<img src="' . asset('assets/images/icons/Branch.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('user'),
                    'child' => [],
                    'all_routes' => [
                        'admin.company.create',
                        'admin.company.edit'
                    ],
                ],
                [
                    'route' => route('admin.fyear.index'),
                    'name' => __('Financial Year'),
                    'icon' => '<img src="' . asset('assets/images/icons/Financial_Year.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('financial_year_view'),
                    'child' => [],
                    'all_routes' => [
                        'admin.fyear.index',
                        'admin.fyear.show',
                        'admin.fyear.add',
                    ],

                ],
                [
                    'route' => route('admin.branch.branch-account-mapping'),
                    'name' => __('Branch Account Link'),
                    'icon' => '<img src="' . asset('assets/images/icons/Branch_account_Link.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('branch_view'),
                    'all_routes' => [
                        'admin.branch.branch-account-mapping',
                    ],
                ],
                [
                    'route' => route('admin.branch.branch-stock-mapping'),
                    'name' => __('Branch Stock Link'),
                    'icon' => '<img src="' . asset('assets/images/icons/Branch_account_Link.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
                    'visible' => Auth::user()->hasDirectPermission('branch_view'),
                    'all_routes' => [
                        'admin.branch.branch-stock-mapping',
                    ],
                ],
            ],
        ],
        [
            'route' => route('admin.logs.user-activity'),
            'name' => __('User Logs'),
            'icon' => '<img src="' . asset('assets/images/icons/User_Log.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'visible' => Auth::user()->hasAnyPermission('user_logs_view'),
            'all_routes' => [
                'admin.logs.user-activity'
            ],
            'child' => [],
        ],
        [
            'route' => route('front.logout'),
            'name' => __('Logout'),
            'icon' => '<img src="' . asset('assets/images/icons/Log_Out.png') . '" class="kt-menu__link-icon me-2" alt="User Icon" width="18">',
            'child' => [],
            'all_routes' => [],
            'visible' => true
        ],
    ];
}


function get_error_html($error)
{
    $content = "";
    if ($error->any() !== null && $error->any()) {
        foreach ($error->all() as $value) {
            $content .= '<div class="alert alert-danger alert-dismissible mb-1" role="alert">';
            $content .= '<div class="alert-text">' . $value . '</div>';
            $content .= '<div class="alert-close"><i class="flaticon2-cross kt-icon-sm" data-dismiss="alert"></i></div></div>';
        }
    }
    return $content;
}


function breadcrumb($aBradcrumb = array())
{
    $i = 0;
    $content = '';
    $is_login = Auth::user();
    if ($is_login) {
        if ($is_login->type == "admin") {
            $aBradcrumb = array_merge(['Home' => route('admin.dashboard')], $aBradcrumb);
        } elseif ($is_login->type == "vendor") {
            $aBradcrumb = array_merge(['Home' => route('vendor.dashboard')], $aBradcrumb);
        }
    }
    if (is_array($aBradcrumb) && count($aBradcrumb) > 0) {
        $total_bread_crumbs = count($aBradcrumb);
        foreach ($aBradcrumb as $key => $link) {
            $i += 1;
            $link = (!empty($link)) ? $link : 'javascript:void(0)';

            $content .= '<li class="breadcrumb-item"> <a href="' . $link . '">' . ucfirst($key) . '</a>';


            // $content .= "<a href='" . $link . "' class='kt-subheader__breadcrumbs-link'>" . ucfirst($key) . "</a>";
            // if ($total_bread_crumbs != $i) {
            //     $content .= "<span class='kt-subheader__breadcrumbs-separator'></span>";
            // }
        }
    }
    return $content;
}

function success_error_view_generator()
{
    $content = "";
    if (session()->has('error')) {
        $content = '<div class="alert alert-danger alert-dismissible" role="alert">
                                        <div class="alert-text">' . session('error') . '</div>
                                        <div class="alert-close"><i class="flaticon2-cross kt-icon-sm"
                                                                    data-dismiss="alert"></i></div></div>';
    } elseif (session()->has('success')) {
        $content = '<div class="alert alert-success alert-dismissible" role="alert">
                                        <div class="alert-text">' . session('success') . '</div>
                                        <div class="alert-close"><i class="flaticon2-cross kt-icon-sm"
                                                                    data-dismiss="alert"></i></div></div>';
    }
    return $content;
}

//=== For Try catch error === return
if (!function_exists('getErrorDetails')) {
    function getErrorDetails(\Exception $e)
    {
        return "Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage();
    }
}

function datatable_filters()
{
    $post = request()->all();
    return array(
        'offset' => isset($post['start']) ? intval($post['start']) : 0,
        'limit' => isset($post['length']) ? intval($post['length']) : 25,
        'sort' => isset($post['columns'][(isset($post["order"][0]['column'])) ? $post["order"][0]['column'] : 0]['data']) ? $post['columns'][(isset($post["order"][0]['column'])) ? $post["order"][0]['column'] : 0]['data'] : 'created_at',
        'order' => isset($post["order"][0]['dir']) ? $post["order"][0]['dir'] : 'DESC',
        'search' => isset($post["search"]['value']) ? $post["search"]['value'] : '',
        'sEcho' => isset($post['sEcho']) ? $post['sEcho'] : 1,
    );
}

function financialYears()
{
    $fydata = \App\Models\FinancialYear::get();
    return $fydata;
}

function send_push($user_id = 0, $data = [], $notification_entry = false)
{
    //    $sample_data = [
    //        'push_type' => 0,
    //        'push_message' => 0,
    //        'from_user_id' => 0,
    //        'push_title' => 0,
    //////        'push_from' => 0,
    //        'object_id' => 0,
    //        'extra' => [
    //            'jack' => 1
    //        ],
    //    ];


    $pem_secret = '';
    $bundle_id = 'com.zb.project.Bambaron';
    $pem_file = base_path('storage/app/uploads/user.pem');
    $main_name = defined('site_name') ? site_name : env('APP_NAME');
    $push_data = [
        'user_id' => $user_id,
        'from_user_id' => $data['from_user_id'] ?? null,
        'sound' => 'defualt',
        'push_type' => $data['push_type'] ?? 0,
        'push_title' => $data['push_title'] ?? $main_name,
        'push_message' => $data['push_message'] ?? "",
        'object_id' => $data['object_id'] ?? null,
    ];
    if ($push_data['user_id'] !== $push_data['from_user_id']) {
        //        $to_user_data = User::find($user_id);
//        if ($to_user_data) {
        $get_user_tokens = DeviceToken::get_user_tokens($user_id);
        $fire_base_header = ["Authorization: key=" . config('constants.firebase_server_key'), "Content-Type: application/json"];
        if (count($get_user_tokens)) {
            foreach ($get_user_tokens as $value) {
                $curl_extra = [];
                $push_status = "Sent";
                $value->update(['badge' => $value->badge + 1]);
                try {
                    $device_token = $value['push_token'];
                    $device_type = strtolower($value['type']);
                    if ($device_token) {
                        if ($device_type == "ios") {
                            $headers = ["apns-topic: $bundle_id"];
                            $url = "https://api.development.push.apple.com/3/device/$device_token";
                            $payload_data = [
                                'aps' => [
                                    'badge' => $value->badge,
                                    'alert' => $push_data['push_message'],
                                    'sound' => 'default',
                                    'push_type' => $push_data['push_type']
                                ],
                                'payload' => [
                                    'to' => $value['push_token'],
                                    'notification' => ['title' => $push_data['push_title'], 'body' => $push_data['push_message'], "sound" => "default", "badge" => $value->badge],
                                    'data' => $push_data,
                                    'priority' => 'high'
                                ]
                            ];
                            $curl_extra = [
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                                CURLOPT_SSLCERT => $pem_file,
                                CURLOPT_SSLCERTPASSWD => $pem_secret,
                            ];
                        } else {
                            $headers = $fire_base_header;
                            $url = "https://fcm.googleapis.com/fcm/send";
                            $payload_data = [
                                'to' => $value['push_token'],
                                'data' => $push_data,
                                'priority' => 'high'
                            ];
                        }
                        $ch = curl_init($url);
                        curl_setopt_array($ch, array_merge([
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POSTFIELDS => json_encode($payload_data),
                            CURLOPT_POST => 1,
                            CURLOPT_HTTPHEADER => $headers,
                        ], $curl_extra));
                        $result = curl_exec($ch);
                        //                            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        if (curl_errno($ch)) {
                            $push_status = 'Curl Error:' . curl_error($ch);
                        }
                        curl_close($ch);
                        if (config('constants.push_log')) {
                            PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $push_status, json_encode($push_data), $result);
                        }
                    } else {
                        PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "Token Is empty");
                    }
                } catch (Exception $e) {
                    if (config('constants.push_log')) {
                        PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], $e->getMessage());
                    }
                }
            }
        } else {
            if (config('constants.push_log')) {
                PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "Users Is not A Login With app");
            }
        }
        //            if ($notification_entry) {
//                      Notification::create([
//                    'push_type' => $push_data['push_type'],
//                    'user_id' => $push_data['user_id'],
//                    'from_user_id' => $push_data['from_user_id'],
//                    'push_title' => $push_data['push_title'],
//                    'push_message' => $push_data['push_message'],
//                    'object_id' => $push_data['object_id'],
//                    'extra' => (isset($data['extra']) && !empty($data['extra'])) ? json_encode($data['extra']) : json_encode([]),
//                    'country_id' => $push_data['country_id'],
//                ]);
//            }

        //        }
    } else {
        if (config('constants.push_log')) {
            PushLog::add_log($user_id, $push_data['from_user_id'], $push_data['push_type'], "User Cant Sent Push To Own Profile.");
        }
    }
}



function number_to_dec($number = "", $show_number = 2, $separated = '.', $thousand_separator = "")
{
    return number_format($number, $show_number, $separated, $thousand_separator);
}

function genUniqueStr($prefix = '', $length = 10, $table, $field, $isAlphaNum = false)
{
    $arr = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
    if ($isAlphaNum) {
        $arr = array_merge($arr, array(
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'v',
            'x',
            'y',
            'z',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'V',
            'X',
            'Y',
            'Z'
        ));
    }
    $token = $prefix;
    $maxLen = max(($length - strlen($prefix)), 0);
    for ($i = 0; $i < $maxLen; $i++) {
        $index = rand(0, count($arr) - 1);
        $token .= $arr[$index];
    }
    if (isTokenExist($token, $table, $field)) {
        return genUniqueStr($prefix, $length, $table, $field, $isAlphaNum);
    } else {
        return $token;
    }
}

function isTokenExist($token, $table, $field)
{
    if ($token != '') {
        $isExist = DB::table($table)->where($field, $token)->count();
        if ($isExist > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function get_fancy_box_html($path = "", $class = "img_75")
{
    return '<a data-fancybox="gallery" href="' . $path . '"><img class="' . $class . '" src="' . $path . '" alt="image" width=40 height=40></a>';
}

function general_date($date)
{
    return date('Y-m-d', strtotime($date));
}

function current_route_is_same($name = "")
{
    return $name == request()->route()->getName();
}

function is_selected_blade($id = 0, $id2 = "")
{
    return ($id == $id2) ? "selected" : "";
}

function clean_number($number)
{
    return preg_replace('/[^0-9]/', '', $number);
}

function print_query($builder)
{
    $addSlashes = str_replace('?', "'?'", $builder->toSql());
    return vsprintf(str_replace('?', '%s', $addSlashes), $builder->getBindings());
}

function user_status($status = "", $is_delete_at = false)
{
    if ($is_delete_at) {
        $status = "<span class='badge badge-danger'>Deleted</span>";
    } elseif ($status == "inactive") {
        $status = "<span class='badge badge-warning'>" . ucfirst($status) . "</span>";
    } else {
        $status = "<span class='badge badge-success'>" . ucfirst($status) . "</span>";
    }
    return $status;
}


function is_active_module($names = [])
{
    $current_route = request()->route()->getName();
    return in_array($current_route, $names) ? "kt-menu__item--active kt-menu__item--open" : "";
}

function echo_extra_for_site_setting($extra = "")
{
    $string = "";
    $extra = json_decode($extra);
    if (!empty($extra) && (is_array($extra) || is_object($extra))) {
        foreach ($extra as $key => $item) {
            $string .= $key . '="' . $item . '" ';
        }
    }
    return $string;
}


function upload_file($file_name = "", $path = null)
{
    $file = "";
    $request = \request();
    if ($request->hasFile($file_name) && $path) {
        $path = config('constants.upload_paths.' . $path);
        $file = $request->file($file_name)->store($path, config('constants.upload_type'));
    } else {
        echo 'Provide Valid Const from web controller';
        die();
    }
    return $file;
}

function upload_base_64_img($base64 = "", $path = "uploads/product/")
{
    $file = null;
    if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
        $data = substr($base64, strpos($base64, ',') + 1);
        $up_file = rtrim($path, '/') . '/' . md5(uniqid()) . '.png';
        $img = Storage::disk('local')->put($up_file, base64_decode($data));
        if ($img) {
            $file = $up_file;
        }
    }
    return $file;
}


function accountType($accountType)
{
    $types = [
        1 => '<span class="badge bg-warning" title="Distributor">D</span>',
        2 => '<span class="badge bg-danger" title="Wholesellor">W</span>',
        3 => '<span class="badge bg-info" Title="Other">R</span>',
    ];
    return $types[$accountType] ?? null;
}

function partyCalculateClosing($id, $from = null, $to = null)
{
    $account = \App\Models\BranchAccounts::where('account_id', $id)->first();
    $april1 = $_ENV['APP_YEAR'];
    $fromDate = $april1;
    $toDate = date('Y-m-d');

    if (isset($from, $to) && !empty($from) && !empty($to)) {
        $fromDate = $from;
        $toDate = $to;
    }

    $prevDate = date('Y-m-d', strtotime($fromDate . '-1 day'));
    $totalDebit = Payment::where('party_id', $id)
        ->where('txn_type', 'debit')
        ->where('status', 1)
        ->where('txn_date', '>=', $april1)
        ->where('txn_date', '<=', $toDate)
        ->sum('txn_amount');

    $totalCredit = Payment::where('party_id', $id)
        ->where('txn_type', 'credit')
        ->where('status', 1)
        ->where('txn_date', '>=', $april1)
        ->where('txn_date', '<=', $toDate)
        ->sum('txn_amount');
    $acc = \App\Models\BranchAccounts::where('account_id', $id)->select('opening_balance', 'opening_balance_type')->first();

    //=======New Calculation ====

    if (!empty($acc->opening_balance)) {
        $opBal = $acc->opening_balance;
    } else {
        $opBal = 0;
    }
    if ($acc->opening_balance_type == 'Dr') {
        $closingBalance_new = $opBal + ($totalDebit - $totalCredit);
    } else {
        $closingBalance_new = $opBal - ($totalDebit - $totalCredit);
    }

    if ($closingBalance_new >= 0) {
        $closingType = 'Cr';
    } else {
        $closingType = 'Dr';
    }

    $a['opening'] = $acc->opening_balance;
    $a['opening_type'] = $acc->opening_balance_type;
    $a['debitTotal'] = $totalDebit;
    $a['creditTotal'] = $totalCredit;
    $a['closing'] = $closingBalance_new;
    $a['closing_type'] = $closingType;
    $a['showbalance'] = accountBalanceView($closingBalance_new, $closingType);

    return $a;
}

function formatIndianCurrency($amount, $decimals = 2)
    {
        // Ensure amount is numeric and handle null/invalid cases
        $amount = floatval($amount);

        // Define thresholds
        $crore = 10000000; // 1 Crore = 10,000,000
        $lakh = 100000;    // 1 Lakh = 100,000

        if ($amount >= $crore) {
            // Format as Crore
            return number_format($amount / $crore, $decimals, '.', ',') . ' Cr';
        } elseif ($amount >= $lakh) {
            // Format as Lakh
            return number_format($amount / $lakh, $decimals, '.', ',') . ' Lac';
        } else {
            // Format with commas for thousands
            return number_format($amount, $decimals, '.', ',');
        }
    }

function accountBalanceView($amt, $type)
{
    $amt = number_to_dec($amt);
    if ($type == 'Dr') {
        $html = '<span class="text-success p-2 w-100">' . $amt . ' ' . $type . ' <span class="badge badge-success">&darr;<span> </span>';
    } else {
        $html = '<span class="text-danger p-2 w-100">' . $amt . ' ' . $type . ' <span class="badge badge-danger">&uarr;<span><span>';
    }
    return $html;
}

function myDateFormat($datestring)
{
    return date('d-M-Y', strtotime($datestring));
}

function getCurrentTimeOfDay()
{
    $hour = now()->hour;

    if ($hour >= 5 && $hour < 12) {
        return 'Morning';
    } elseif ($hour >= 12 && $hour < 17) {
        return 'Afternoon';
    } elseif ($hour >= 17 && $hour < 21) {
        return 'Evening';
    } else {
        return 'Night';
    }
}

function stockInfo($idqr)
{
    $st = DB::table('tbl_products_stock AS st')
        ->join('tbl_products_master AS p', function ($join) {
            $join->on('p.id', '=', 'st.product_id');
        })
        ->join('tbl_categories AS pc', function ($join) {
            $join->on('pc.id', '=', 'st.category_id');
        })
        ->join('tbl_color AS atr', function ($join) {
            $join->on('atr.id', '=', 'st.attribute_id');
        })
        ->where('id', $idqr)
        ->orWhere('qrcode', $idqr)
        ->select('st.*', 'p.code', 'p.name As prodName', 'atr.name as attributeName', 'pc.name AS catName')
        ->first();
}

function stockCatAllVariant($idqr)
{
    $st = DB::table('tbl_products_stock AS st')
        ->where('id', $idqr)
        ->orWhere('qrcode', $idqr)
        ->first();
    $allcat = '';
    if ($st) {
        $allcat = DB::table('tbl_products_stock AS st')
            ->join('tbl_products_master AS p', function ($join) {
                $join->on('p.id', '=', 'st.product_id');
            })
            ->join('tbl_categories AS pc', function ($join) {
                $join->on('pc.id', '=', 'st.category_id');
            })
            ->join('tbl_color AS atr', function ($join) {
                $join->on('atr.id', '=', 'st.attribute_id');
            })
            ->where('st.product_id', $st->product_id)
            ->where('st.category_id', $st->category_id)
            ->where('st.status', '1')
            ->select('st.*', 'p.code', 'p.name As prodName', 'atr.name as attributeName', 'pc.name AS catName')
            ->get();

    }
    return $allcat;
}

//===== Sale Order/Purchase Order  Item Color Marking=====
function OrderQtyStockStatus($stock, $sQty, $totalPoQty) {
    if ($stock >= $sQty) {
        // Stock Available & Ready
        return 'text-success';
    } elseif ($stock < $sQty && $totalPoQty > 0) {
        // On Order (Stock not enough but Purchase Order exists)
        return 'text-info';
    } elseif ($stock < $sQty && $totalPoQty <= 0) {
        // Not Available (Stock not enough and no Purchase Order)
        return 'text-danger';
    }
    return '';
}

//====Qrcode function =====
function createQrcodeForStock($stockId, $qrcode)
{
    // Create a new record in the tbl_stock_qrcode table
    \App\Models\QrModel::create([
        'stock_id' => $stockId,
        'qrcode' => $qrcode,
    ]);
}

function getQrcodeByStockId($stockId)
{
    // Retrieve a QR code by its stock_id
    $qrcode = QrModel::where('stock_id', $stockId)->first();

    if ($qrcode) {
        return $qrcode->qrcode;  // Return the QR code string
    }

    return null;  // Return null if no QR code is found
}

function generateQRCodesForStock($data)
{
    $stockid = $data['stockid'] ?? null;
    $reqType = $data['reqType'] ?? 'opening_stock';
    $qty = $data['qty'] ?? 0;
    $st = \App\Models\BranchStocks::with('stock')
        ->where('stock_id', $stockid)
        ->first();

    //return $reqType;
    if (!$st || $qty <= 0) {
        \Log::warning("No valid stock found for stock_id: $stockid, reqType: $reqType, qty: $qty");
        return false;
    }

    // Generate a unique QR code for each stock item
    for ($n = 1; $n <= $qty; $n++) {
        $forQr = "$n|{$st->stock->product_id}|{$st->stock->category_id}|{$st->stock->attribute_id}|" . time();
        $qrcodeString = generateUniqueQRCode($forQr, $reqType = 'opening_stock'); // Ensure this function exists

        // Store the unique QR code string in the database
        $q = new QrModel();
        $q->bsid = $st->id;
        $q->stock_id = $st->stock_id;
        $q->qrcode = $qrcodeString;
        $q->branch_id = Auth::user()->branch_id;
        $q->user_id = Auth::user()->id;
        $q->fyid = Session::get('fyear.id');
        if ($reqType == 'purchase') {
            $q->purchase_id = $data['itemid'] ?? 1;
        }
        if ($reqType == 'stockup') {
            $q->stockup_id = $data['itemid'] ?? null;
        }

        $q->save();
    }

    // Mark the product as having its QR code generated
    $st->update(['osqr_generated' => 1]);

    return true;
}


function base62_encode($number)
{
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $base = strlen($characters);
    $result = '';

    while ($number > 0) {
        $remainder = $number % $base;
        $result = $characters[$remainder] . $result;
        $number = intdiv($number, $base);
    }

    return $result;
}

/**
 * Generate a unique QR code string based on product attributes and a unique stock ID.
 */
function generateUniqueQRCode($stock)
{
    // Combine the key fields into a single string. Include stock_id, product_id, category_id, attribute_id, and timestamp.
    /*
    $data = implode('|', [
        $stock->id,               // stock_id
        $stock->product_id,       // product_id
        $stock->category_id,      // category_id
        $stock->attribute_id,     // attribute_id
        time()                       // Add current timestamp for uniqueness
    ]); */
    $data = $stock;
    // Create a unique hash of the combined string using crc32 (or any other hash function)
    $hash = crc32($data);

    // Convert the hash to a Base62 string for a compact representation
    $encodedString = strtoupper(base62_encode($hash));

    // Ensure the generated QR code is unique in the database
    while (QrModel::where('qrcode', $encodedString)->exists()) {
        // If a collision occurs, re-hash with an additional random number and re-encode
        $hash = crc32($data . rand());
        $encodedString = base62_encode($hash);
    }

    return $encodedString;
}

//====== Generate Qr Image =====
function QrsvgImg($qrstring, $width)
{
    return QrCode::format('svg')->size($width)->generate($qrstring);
}

function AccountAssocProduct($acid, $stockid)
{
    // Check if the record exists
    $exists = DB::table('tbl_product_assoc_account')
        ->where('account_id', $acid)
        ->where('stock_id', $stockid)
        ->exists();

    // If it doesn't exist, insert a new record
    if (!$exists) {
        DB::table('tbl_product_assoc_account')->insert([
            'account_id' => $acid,
            'stock_id' => $stockid,
        ]);
    }
}

function shareBill($id)
{
    // Fetch bill details from the database
    $bill = \App\Models\Sales::findOrFail($id);

    // Set recipient phone number (make dynamic if needed)
    $recipientPhoneNumber = '1234567890';

    // Create the message template
    $messageTemplate = "Hello, I would like to share the following bill details with you:";
    $billDetails = "Bill #{$bill->invoice_No}\nAmount: {$bill->bill_amount}\nDue Date: {$bill->billDate}";
    $billLink = url("/print/sale/{$bill->id}"); // Link to the bill details

    // Combine all into a WhatsApp message
    $fullMessage = $messageTemplate . "\n\n" . $billDetails . "\n\nView the bill here: $billLink";

    // URL encode the message
    $encodedMessage = urlencode($fullMessage);

    // Generate WhatsApp URL
    $whatsappUrl = "https://wa.me/{$recipientPhoneNumber}?text={$encodedMessage}";

    return $whatsappUrl;
}

function billOffer($actype = null)
{

    $status = 'false';
    $sofData = '';
    $today = date('Y-m-d');
    if ($actype > 0) {

    }
}


function productOffer($stockId, $accountType = null, $offlineOnline = null)
{
    $status = 'false';
    $offerData = '';
    $offerRate = '';
    $offerType = '';
    $offerId = '';
    $ofrIndicator = 'NA';
    $offerSalePrice = '';
    $oflineOnline = 'offline';
    $isOffer = '';
    $today = now()->format('Y-m-d'); // Use Laravel's Carbon for date handling

    // Fetch stock with the given stock ID and ensure it's not currently on offer
    $stock = \App\Models\BranchStocks::where('id', $stockId)->first();

    if ($stock) {

        if ($stock->is_offer == 1) {

            $status = 'true';
            $offerId = '0';
            $offerType = 'Offer';
            $offerRate = 0;
            $offerSalePrice = $stock->offer_sale_price;
            $ofrIndicator = '<span class="badge bg-danger rounded-pill" title="Stock">' . $offerType . '</span>';
            $isOffer = 1;

        } else {

            // Prepare base queries for offers
            $billOfferQuery = \App\Models\Offer::where('startdate', '<=', $today)
                ->where('enddate', '>=', $today)
                ->where('status', 1)
                ->where('offer_type', 'bill');

            $productOfferQuery = \App\Models\Offer::where('startdate', '<=', $today)
                ->where('enddate', '>=', $today)
                ->where('status', 1)
                ->where('offer_type', 'product');

            // Filter by account type (customer type) if provided
            if (!is_null($accountType)) {
                $billOfferQuery->where(function ($query) use ($accountType) {
                    $query->where('customer_type', 0)
                        ->orWhere('customer_type', $accountType);
                });

                $productOfferQuery->where(function ($query) use ($accountType) {
                    $query->where('customer_type', 0)
                        ->orWhere('customer_type', $accountType);
                });
            }

            $categoryId = $stock->category_id;
            $productId = $stock->product_id;

            // Filter product-specific offers
            $productOfferQuery->whereHas('offerdetail', function ($query) use ($categoryId, $productId) {
                $query->where('product_id', $productId)
                    ->where('category_id', $categoryId);
            });

            // Fetch the first applicable bill and product offers
            $billOffer = $billOfferQuery->first();
            $productOffer = $productOfferQuery->first();

            if ($productOffer) {
                $status = 'true';
                $offerId = $productOffer->id;
                $offerType = $productOffer->offer_type;
                $offerData = $productOffer;
                if (!is_null($offlineOnline)) {
                    $offerRate = $productOffer->rate2;
                    $oflineOnline = 'Online';
                } else {
                    $offerRate = $productOffer->rate;
                }

                $offerOnlineRate = $productOffer->rate2;
                $ofrIndicator = '<span class="badge bg-success rounded-pill" title="' . $oflineOnline . ' | ' . $productOffer->code . ' | Product">' . $productOffer->code . '</span>';
            } elseif ($billOffer) {
                $status = 'true';
                $offerId = $billOffer->id;
                $offerType = $billOffer->offer_type;
                $offerData = $billOffer;
                if (!is_null($offlineOnline)) {
                    $offerRate = $billOffer->rate2;
                    $oflineOnline = 'Online';
                } else {
                    $offerRate = $billOffer->rate;
                }
                $ofrIndicator = '<span class="badge bg-warning rounded-pill" title="' . $oflineOnline . ' | ' . $billOffer->code . ' | Bill">' . $billOffer->code . '</span>';

            }
        }
    }

    return [
        'status' => $status,
        'offerId' => $offerId,
        'offerType' => $offerType,
        'offerData' => $offerData,
        'offerRate' => $offerRate > 0 ? number_format($offerRate) : '',
        'offerIndicator' => $ofrIndicator,
        'offerSalePrice' => $offerSalePrice,
        'oflineOnline' => $oflineOnline,
        'isOffer' => $isOffer,
    ];
}

function stockPendingOrder($id)
{
    $a = \App\Models\StockModel::where('id', $id)->with('product', 'category', 'attr', 'psod', 'ppod')->first();
}


function customerTypeName($typeid)
{
    $partyTypeNames = [1 => 'Distributor', 2 => 'Whole Seller', 3 => 'Other'];
    return $partyTypeNames[$typeid];
}


function cartdata($type)
{
    return $data['OpenSaleCart'] = Cart::where('type', $type)
        ->where('status', 1)
        ->orderBy('id', 'desc')
        ->get();

}

function hasPermission($permission)
{
    $user = Auth::user();
    if (!$user || !$user->can($permission)) {
        session()->flash('error', 'You do not have permission to perform this action.');
        return false;
    }
    return true;
}

function TakeawayDispatchNotification()
{
    $data = \App\Models\Dispatch::where('dispatch_type', '1')
        ->where('status', '0')
        ->with('transporter', 'saleorder.account', 'user', 'pickedby')
        ->get();
    return $data;
}

function newDispatchNotification()
{
    $data = \App\Models\Dispatch::where('status', '0')
        ->with('transporter', 'saleorder.account', 'user', 'pickedby')
        ->get();
    return $data;
}

function myPickedDispatch()
{
    $data = \App\Models\Dispatch::where('status', '1')
        ->where('picked_by', Auth::user()->id)
        ->with('transporter', 'saleorder.account', 'user', 'pickedby')
        ->get();
    return $data;
}

function lpd($lastpurchase)
{
    if (!empty($lastpurchase)) {
        $d = explode(' | ', $lastpurchase);
        if (date('Y-m-d', strtotime($d[1])) <= date('Y-m-d', strtotime('- 150 days'))) {
            $lpd = '<span class="badge bg-secondary">' . date('d-M-y', strtotime($d[1])) . '</span>';
        } else {
            $lpd = date('d-M-y', strtotime($d[1]));
        }
        $lpdTitle = "Last Purchase :: Qty/Date/Supplier :" . $lastpurchase;
    } else {
        $lpd = '';
        $lpdTitle = '';
    }
    $a['lpd'] = $lpd;
    $a['lpdTitle'] = $lpdTitle;
    return $a;
}
