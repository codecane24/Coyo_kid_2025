
$('#addItemBtn').on('click', function(e) {
    e.preventDefault(); // This stops the default button behavior
});

function readFileInput(input, functions) {
    var content = false;
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            if (window[functions]) {
                window[functions](e.target.result);
            } else {
                toastr.error("Invalid function Provided", 'error');
            }
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        toastr.error("Sorry - you're browser doesn't support the FileReader API", 'error');
    }
    return content;
}

function addOverlay() {
    $('#loader_display_d').show();
    //$(`<div id="overlayDocument"><img src="${loader_img}" /></div>`).appendTo(document.body);

}

function removeOverlay() {
    $('#loader_display_d').hide();
    //$('#overlayDocument').remove();
}

function Get_Unique_String(length) {
    length = (length === undefined) ? 10 : length;
    var result = '';
    var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for (var i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return result;
}

function object_key_exits(obj, key = "") {
    return Object.keys(obj).indexOf(key) > -1;
}

function show_toastr_notification(msg = "", status = "200") {
    if (status == "200") {
        //toastr.success(msg);
        $.notify(msg,'success');
    } else if (status == "412") {
        $.notify(msg, "error");
       // toastr.error(msg);
    }
}

function ajax_maker(data) {
    // let ajax_demo = {
    //     url: "",
    //     type: "",
    //     data: "",
    //     success: "",
    //     error: "",
    // };
    let ajax_data = {
        url: (object_key_exits(data, 'url')) ? data.url : "",
        method: (object_key_exits(data, 'type')) ? data.type : "get",
        beforeSend: (object_key_exits(data, 'beforeSend')) ? data.beforeSend : addOverlay,
        complete: (object_key_exits(data, 'complete')) ? data.complete : removeOverlay,
        dataType: (object_key_exits(data, 'dataType')) ? data.dataType : 'JSON',
        success: (object_key_exits(data, 'success')) ? data.success : function () {
            alert('pass success');
        },
        error: function (err) {
            let json = err.responseJSON;
            if (json.status === 401) {
                window.location.assign("{{route('front.get_login')}}");
            } else if (json.status === 412) {
                show_toastr_notification(json.message, json.status);
            }

        }
    };
    if (object_key_exits(data, 'data')) {
        ajax_data.data = data.data;
    }
    if (object_key_exits(data, 'cache')) {
        ajax_data.cache = false;
    }
    if (object_key_exits(data, 'contentType')) {
        ajax_data.contentType = false;
    }
    if (object_key_exits(data, 'processData')) {
        ajax_data.processData = false;
    }


    if (object_key_exits(data, 'token')) {
        ajax_data.headers = {
            _token: "{{ csrf_token() }}"
        };
    }
    $.ajax(ajax_data);
}


    $(function () {
       
});

function loadDate(){
    $(".date").datepicker({
        autoclose: true,
        todayHighlight: true,
        clearBtn: true,
        templates: {
            leftArrow: '<i class="la la-angle-left"></i>',
            rightArrow: '<i class="la la-angle-right"></i>'
        }
       // dateFormat: 'dd-mm-yy'
    }).on('changeDate', function(ev) {
        $(this).valid();
     });

    $(".input-mask").inputmask();
}


function phoneNumberMethod(){
    jQuery.validator.addMethod("phone", function (phone_number, element) {
        phone_number = phone_number.replace(/\s+/g, "");
        return this.optional(element) || phone_number.length > 9 &&
              phone_number.match(/^\(?[\d\s]{3}-[\d\s]{3}-[\d\s]{4}$/);
    }, "Invalid phone number");
}

$(document).on('click','.general_edit_btn',function(){
    $(".input-mask").inputmask();
});

function preview_image(event){
    document.getElementById('blah').style.display='block';
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('blah');
        output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
 };

 function totalCalculate(val,pecentage,unit=0){
     if(unit == 0 || unit == 'undefined'){
         return val*pecentage;
     }else if(unit == "%"){
        return (val*pecentage) / 100;
     }else{
        return val*pecentage;
     }
 }

 function currencyFormate(values= 0){
     
     // Create our number formatter.
var formatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  
    // These options are needed to round to whole numbers if that's what you want.
    //minimumFractionDigits: 0, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
    //maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
  });

  return formatter.format(values); /* $2,500.00 */

 }

 function getTimezone(){
    return  Intl.DateTimeFormat().resolvedOptions().timeZone;
 }



 /*======= Open Image popup====== */
 $(document).ready(function () {
    // Click event for product image
    $(document).on('click', '.imgpp', function () {
        let prodid = $(this).attr('pd');
        let catid = $(this).attr('ct');
        
        if (prodid) {
            $.getJSON(AdminUrl+'/product-image/'+prodid+'/'+catid)
                .done(function (rd) {
                    if (rd.imgtype != '0') {
                        let img = rd.image;
                        
                        $('#pd01').text(rd.imgtype+'|'+rd.product_name);
                        $('#ImageSec').html('<img src="/storage/product/'+img+'" style="width:100%;">');
                        $('#modal_image_popup').modal('show');
                    } else {
                        console.error('No data found for the product.');
                    }
                })
                .fail(function () {
                    console.error('Failed to fetch product image.');
                });
        } else {
            console.error('Product ID not found.');
        }
    });

    // Preloader fade out effect
    $(".seq-preloader").fadeOut();
    $(".sequence").delay(500).fadeOut("slow");
});



/*========= */
function accountPreviousTxn(txntype){
    
}

	/*=======Search Account/customer Data=======*/
	$('#topnavsearch').on('keyup', function(){
		var nameKey = $(this).val();
		var type= 'sc';
		if(nameKey.length>=3){
		$('#srchAccountListDiv').show(500);
		$.getJSON(AdminUrl+'/get-account-list'+'/' +type+'/'+nameKey , function(e){
			if(e.status==true && e.data.length>0){
			var listData = $('#srchActList').empty();
			$.each(e.data, function(key, value){
                if(value.acGroup==4){
                typ='<i class="badge bg-success">C</i>';
                }else{
                typ='<i class="badge bg-danger">S</i>';
                }
				var list = '<p class="p-1">'+typ+' <a href="'+AdminUrl+'/cart/activate-cart/'+value.id+'" class="text-decoration-none">'+value.acCode+' '+value.name+'</a></p>';
				listData.append(list);
			});
			}else{
				$('#srchActList').html('<em>--- no data found---</em>');
			}
		});
		}else{
			$('#srchAccountListDiv').hide();
		}
	});


  $(document).ready(function() {
    $('.fychange').click(function() {
        var fyid = $(this).data('id');
        window.location.href = "admin/fy-change" + fyid;
    });
});


