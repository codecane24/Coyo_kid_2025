// Wait for document to be ready
$(document).ready(function() {
    initializePermissionManagement();
    initializeFormValidation();
    initializeImagePreview();
});

// Permission Management
function initializePermissionManagement() {
    // Select All Permissions
    $('#select_all').on('click', function() {
        $('.permission-checkbox').prop('checked', true);
    });

    // Deselect All Permissions
    $('#deselect_all').on('click', function() {
        $('.permission-checkbox').prop('checked', false);
    });

    // Select Category Permissions
    $('.select-category').on('click', function() {
        const category = $(this).data('category');
        $(`.permission-checkbox[data-category="${category}"]`).prop('checked', true);
    });

    // Deselect Category Permissions
    $('.deselect-category').on('click', function() {
        const category = $(this).data('category');
        $(`.permission-checkbox[data-category="${category}"]`).prop('checked', false);
    });
}

// Form Validation
function initializeFormValidation() {
    $('#userForm').validate({
        rules: {
            name: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            },
            mobile: {
                required: true,
                minlength: 10,
                maxlength: 15
            },
            password: {
                required: true,
                minlength: 8
            },
            confirm_password: {
                required: true,
                equalTo: "#password"
            },
            department: "required",
            role: "required",
            statusData: "required",
            branch: {
                required: function() {
                    return $('#branch').length > 0;
                }
            }
        },
        messages: {
            name: {
                required: "Please enter your full name",
                minlength: "Name must be at least 3 characters long"
            },
            email: {
                required: "Please enter your email address",
                email: "Please enter a valid email address"
            },
            mobile: {
                required: "Please enter your mobile number",
                minlength: "Mobile number must be at least 10 digits",
                maxlength: "Mobile number cannot be more than 15 digits"
            },
            department: "Please select a department",
            role: "Please select a role",
            statusData: "Please select an account status",
            branch: "Please select a branch"
        },
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            if (element.prop('type') === 'checkbox') {
                error.insertAfter(element.parent('div'));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function(element, errorClass, validClass) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).addClass('is-valid').removeClass('is-invalid');
        },
        submitHandler: function(form) {
            // Show loading state
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...').prop('disabled', true);

            // Submit form
            form.submit();
        }
    });
}

// Image Preview
function initializeImagePreview() {
    $('#profile_image').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            const preview = $('.image-preview');

            reader.onload = function(e) {
                preview.attr('src', e.target.result).show();
            };

            reader.readAsDataURL(file);
        }
    });
}

function initializeShowPassword() {
    $('#togglePassword').on('click', function () {
        let passwordField = $('#password');
        let icon = $(this).find('i');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
}

// Show toastr notifications for Laravel flash messages
$(document).ready(function() {
    // Configure toastr options
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };

    // Show success message
    if (typeof Session !== 'undefined' && Session.has('success')) {
        toastr.success(Session.get('success'));
    }

    // Show error message
    if (typeof Session !== 'undefined' && Session.has('error')) {
        toastr.error(Session.get('error'));
    }
});
