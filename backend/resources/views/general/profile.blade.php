@extends('layouts.master')

@section('content')
@section('title')
    @lang('translation.Profile_Overview')
@endsection

@include('components.breadcum')

<div class="row">
    <div class="col-xl-12">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary bg-gradient py-3">
                <h4 class="card-title mb-0 text-white">Profile Details</h4>
            </div>
            <div class="card-body p-4">
                <form name="main_form" id="main_form" method="post" enctype="multipart/form-data"
                    action="{{ route('admin.post_profile') }}">
                    {!! get_error_html($errors) !!}
                    {!! success_error_view_generator() !!}
                    @csrf

                    <!-- Profile Image Section -->
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <img src="{{ $user->profile_image }}" alt="Profile Image"
                                class="rounded-circle border shadow-sm" height="120" width="120"
                                id="preview_image">
                            <div class="position-absolute bottom-0 end-0">
                                <label for="profile_image" class="btn btn-sm btn-primary rounded-circle">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" accept="image/*" id="profile_image" class="d-none"
                                    name="profile_image"
                                    onchange="document.getElementById('preview_image').src = window.URL.createObjectURL(this.files[0])">
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">

                        <!-- Name Section -->
                        <div class="col-md-12">
                            <div class="form-floating">
                                <input type="text" name="name" id="name"
                                    class="form-control bg-light border-0" value="{{ $user->name }}"
                                    placeholder="Enter Full Name">
                                <label for="name">
                                    <i class="fas fa-user me-2"></i>Full Name
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        <!-- Email Section -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" name="email" id="email"
                                    class="form-control bg-light border-0" value="{{ $user->email }}"
                                    placeholder="Enter Email Address">
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>Email Address
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        <!-- Mobile Number Section -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" name="mobile" id="mobile"
                                    class="form-control bg-light border-0" value="{{ $user->mobile }}"
                                    placeholder="Enter Mobile Number">
                                <label for="mobile">
                                    <i class="fas fa-mobile-alt me-2"></i>Mobile Number
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="text" readonly id="type"
                                    class="form-control bg-light border-0" value="{{ $user->type }}"
                                    placeholder="Enter Email Address">
                                <label for="email">
                                    <i class="fas fa-list me-2"></i>User Type
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" name="text" readonly id="email"
                                    class="form-control bg-light  border-0" value="{{ $user->status }}"
                                    placeholder="Enter Email Address">
                                <label for="email">
                                    <i class="fas fa-flag me-2"></i>Status
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" name="text" readonly id="email"
                                    class="form-control bg-light  border-0"
                                    value="{{ $branch->name ?? 'Not Selected' }}" placeholder="Enter Email Address">
                                <label for="email">
                                    <i class="fas fa-flag me-2"></i>Branch Name
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" name="text" readonly id="email"
                                    class="form-control bg-light  border-0"
                                    value="{{ $department->name ?? 'Not Selected' }}"
                                    placeholder="Enter Email Address">
                                <label for="email">
                                    <i class="fas fa-flag me-2"></i>Department Name
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating">
                                <input type="text" name="text" readonly id="email"
                                    class="form-control bg-light  border-0"
                                    value="{{ $userRole->name ?? 'Not Selected' }}" placeholder="Enter Email Address">
                                <label for="email">
                                    <i class="fas fa-flag me-2"></i>Assigned Role
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    @if ($user->hasRole('Super-Admin'))
                        @if (isset($groupedPermissions) && $groupedPermissions->count() > 0)
                            <div class="mt-5">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="text-primary mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>Permissions
                                    </h5>
                                    <div>
                                        <button type="button" id="select_all" class="btn btn-primary btn-sm me-2">
                                            <i class="fas fa-check-double me-1"></i>Select All
                                        </button>
                                        <button type="button" id="deselect_all" class="btn btn-light btn-sm">
                                            <i class="fas fa-times me-1"></i>Deselect All
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive border rounded">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="py-3 px-4">Category</th>
                                                <th class="py-3 px-4">Permissions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($groupedPermissions as $parentId => $permissions)
                                                <tr>
                                                    <td class="py-3 px-4 fw-bold text-primary">
                                                        {{ $permissions->first()->name }}
                                                    </td>
                                                    <td class="py-3 px-4">
                                                        <div class="row g-3">
                                                            @foreach ($permissions as $permission)
                                                                <div class="col-lg-4 col-md-6">
                                                                    <div class="form-check">
                                                                        <input type="checkbox"
                                                                            class="form-check-input permission-checkbox"
                                                                            name="permissions[]"
                                                                            value="{{ $permission->name }}"
                                                                            id="permission_{{ $permission->id }}"
                                                                            {{ in_array($permission->name, $userPermissions) ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="permission_{{ $permission->id }}">
                                                                            {{ $permission->name }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Action Buttons -->
                    <div class="text-center mt-5">
                        <button type="submit" class="btn btn-primary btn-lg px-5 waves-effect waves-light"
                            id="save_changes">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                        <a href="{{ route(getDashboardRouteName()) }}" class="ms-3">
                            <button type="button" class="btn btn-light btn-lg px-5 waves-effect">
                                <i class="fas fa-times me-2"></i>Close
                            </button>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
    .form-floating>.form-control {
        padding: 1rem 1rem;
        height: 3.5rem;
        line-height: 1.25;
    }

    .form-floating>label {
        padding: 1rem 1rem;
    }

    .form-control:focus {
        box-shadow: none;
        border-color: var(--primary);
    }

    .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .btn {
        border-radius: 8px;
        padding: 0.6rem 1.2rem;
    }

    .form-check-input:checked {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .table> :not(:first-child) {
        border-top: none;
    }

    .permission-checkbox:focus {
        box-shadow: none;
    }
</style>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        // Select/Deselect all permissions
        $('#select_all').click(function() {
            $('.permission-checkbox').prop('checked', true);
        });

        $('#deselect_all').click(function() {
            $('.permission-checkbox').prop('checked', false);
        });

        // Initialize form validation
        $("#main_form").validate({
            rules: {
                mobile: {
                    required: true,
                    minlength: 10,
                    maxlength: 15,
                    remote: {
                        type: 'get',
                        url: "{{ route('front.availability_checker') }}",
                        data: {
                            'type': "mobile",
                            'val': function() {
                                return $('#mobile').val();
                            }
                        }
                    }
                },
                name: {
                    required: true
                },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        type: 'get',
                        url: "{{ route('front.availability_checker') }}",
                        data: {
                            'type': "email",
                            'val': function() {
                                return $('#email').val();
                            }
                        }
                    }
                }
            },
            messages: {
                mobile: {
                    required: 'Please enter mobile number',
                    minlength: 'Please enter valid mobile number',
                    remote: "This mobile number is already registered"
                },
                email: {
                    required: 'Please enter email address',
                    remote: "This email is already registered"
                },
                name: {
                    required: 'Please enter full name'
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-floating').append(error);
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                form.submit();
            }
        });
    });
</script>
@endsection
