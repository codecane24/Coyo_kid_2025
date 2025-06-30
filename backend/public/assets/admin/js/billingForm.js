function editIt(e){
	var rate =$(e).closest('tr').find(".apRate").val();
	var Qty =$(e).closest('tr').find(".apQty").val();
    var taxrate = $(e).closest('tr').find('input[name="AdTaxRate[]"]').val();
    var itemAmt=rate*Qty;
    $(e).closest('tr').find(".apAmt").val(rate*Qty);

    if(taxrate >0){
        taxamt= itemAmt *  taxrate /100;
        $(e).closest('tr').find('input[name="AdTaxAmt[]"]').val(taxamt);
    }
	
	calculate();
}

/**==== Date Picker ===== */
$('.datepicker').datepicker({
    toDisplay: 'dd-mm-yyyy',
     toValue: 'yyyy-mm-dd',
     format: 'dd-M-yyyy'
});

/**=====Account Search Form ====== */
$('#clientSearchForm').on('keyup', function(){
    var nameKey = $(this).val();
    var type= '*';
    $('.regCustomer').html("");
    $('.PrevInq').html("");
    if(nameKey.length>=4){
    $('.customerSearchlist').show(500);
    $.get("/admin/search-account-inquiry/" +type+'/'+nameKey , function(data){

        $('.regCustomer').html(data.acc);
        $('.PrevInq').html(data.inq);
      
    });
    }else{
        $('.customerSearchlist').hide();
    }
});
/*===== Product Search  By name ====== */
let currentIndex = -1;

$('#prodData').on('input', function (e) {
    var query = $(this).val();
    var accountid = $('#account_id').val();
    
    if (query.length < 3 ) {
        $('#suggestions').empty().hide();
        return;
    }else if(accountid==null){
        //
    }

    $.ajax({
        url: "/admin/search-prod-name/"+query,
        method: 'GET',
        //data: { name },
        success: function (data) {
            var filteredProducts = data.filter(product => 
                product.name.toLowerCase().includes(query.toLowerCase())
            );

            if (filteredProducts.length > 0) {
                var suggestionList = filteredProducts.map((product, index) => `
                    <div class="row productRow" data-prodid="${product.id}" data-catid="${product.catID}">
                        <div class="col-2 spCode">${product.code}</div>
                        <div class="col-5 spName">${product.name}</div>
                        <div class="col-5 spCatname">${product.catName}</div>
                    </div>
                `).join('');

                $('#suggestions').html(suggestionList).show();
                currentIndex = -1; // Reset the current index
            } else {
                $('#suggestions').empty().hide();
            }
        },
        error: function () {
            $('#suggestions').empty().show();
            console.error('Error fetching data');
        }
    });
});

$('#prodData').on('keydown', function (e) {
    const items = $('.productRow');

    if (e.key === 'ArrowDown') {
        currentIndex = Math.min(currentIndex + 1, items.length - 1);
    } else if (e.key === 'ArrowUp') {
        currentIndex = Math.max(currentIndex - 1, -1);
    } else if (e.key === 'Enter' && currentIndex >= 0) {
        const selectedId = $(items[currentIndex]).data('id');
        alert('Selected Product ID: ' + selectedId); // Handle selection
        $('#suggestions').empty().hide();
        return;
    }

    items.removeClass('highlight');
    if (currentIndex >= 0) {
        $(items[currentIndex]).addClass('highlight');
    }
});

$(document).on('click', '.productRow', function () {
    
    const ProdId = $(this).data('prodid');
    const CatId = $(this).data('catid');
    const AccId= $('#account_id').val();
    if(AccId==null){
        toastr.error('Please Select the Account/ledger');
        return false;
    }
    var prodCode=$(this).find('.spCode').text();
    var prodName=$(this).find('.spName').text();
    var prodCatName=$(this).find('.spCatname').text();
    var ProdInfo='<strong class="h5 text-danger">'+prodName+'</strong> ('+prodCode+')<br>'+prodCatName;
    $('#selectedProd').html(ProdInfo);
    $('#suggestions').empty().hide();
    $('#seletedProdName').val(prodName);
    $('#seletedCatName').val(prodCatName);
    $.ajax({
        url: "/admin/product-all-active-variants/" + ProdId+'/'+CatId+'/inquiry/'+AccId,
        method: 'GET',
        //data: { name },
        success: function (data) {
            $('#prodAllVariants').html(data);
        },error: function () {
            $('#prodAllVariants').empty();
            console.error('Error fetching data');
        }
    });
 
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('.input-group').length) {
        $('#suggestions').empty().hide();
    }
    
    // if (!$(e.target).closest('.cstbtn').length) {
    //     $('.dropdown-menu').removeClass('show');
    //   }
});


 $('#prodAllVariants').on('input', '.AdQty , .AdSprice', function () {
        const $row = $(this).closest('tr');
        QtyRateCalculate($row);
    });

 function QtyRateCalculate(e){
        
    const qty=parseFloat($(e).find('.AdQty').val());
    const rate=parseFloat($(e).find('.AdSprice').val());
    const stock=parseFloat($(e).find('.itemAstock').text());
    
        // Validate input
        if (isNaN(rate) || isNaN(qty)) {
            $(e).find('.AdNet').val(''); // Clear total if input is invalid
            $(e).find('.itmStatus').html('');
            $('#addItemBtn').addClass('hide');
            return;
        }
        // Calculate total
        const total = rate * qty;
        $(e).find('.AdNet').val(total.toFixed(2));
        $('#addItemBtn').removeClass('hide');
        if(stock<qty){
            $(e).find('.itmStatus').html('<i class="fa fa-check-circle text-danger"></i>');
        }else{
            $(e).find('.itmStatus').html('<i class="fa fa-check-circle text-success"></i>');
        }

       
 }


/*========SET Serial No. to added product List=======*/
function RowSno(){
    $( "#addTblBody tr" ).each(function( index ) {
        $(this).find('td').first().text(index+1);
    });
}

 /*==== Bill amount calculation function ==== */
 function calculate(){
    var SumQty =0;SumDis=0; sumNetAmt=0; billDis=0;otherCharge=0;freight=0;SumtaxAmt=0; offerItemAmt=0; OfrDiscount=0; acDiscount=0; genDiscount=0;TotalBillDiscount=0;calDis=0;

 /*=== Account Discount Rate ===*/
  disRate=parseFloat($('#DisRate').val());
 /*====*/

     $("input[name*='AdProdQty']").each( function(){ SumQty += parseFloat($(this).val());	});
     $("input[name*='AdNetAmt']").each( function(){ sumNetAmt += parseFloat($(this).val());	});
     $("input[name*='AdTaxAmt']").each( function(){ SumtaxAmt += parseFloat($(this).val());	});
     $("input[name*='AdIsOffer']").each( function(){ 
        if(this.value>0){
            offerItemAmt+=parseFloat($(this).closest('tr').find('.apAmt').val());
        }
    });

     otherCharge+=$('input[name ="otherCharges"]').val();
     freight+=$('input[name ="freight"]').val();
     genDiscount=parseFloat($('#gen_discount').val()) || 0;
     
     DisTotal=parseFloat(billDis);
     
     if(disRate>0){
        acDiscount=(sumNetAmt-offerItemAmt)*(disRate/100);
        calDis=acDiscount+genDiscount;
        $('.AcDisRate').text('('+disRate+'%)');
    }
    DisTotal=acDiscount + genDiscount;

     $('input[name ="sumQtyTotal"]').val(SumQty.toFixed(2));
     $('input[name ="sumDisTotal"]').val(SumDis.toFixed(2));
     $('input[name ="totalBillDiscount"]').val(DisTotal);
     $('input[name ="sumNetTotal"]').val(sumNetAmt.toFixed(2));
     $('input[name ="sumTaxAmount"]').val(SumtaxAmt.toFixed(2));


    
     
    console.log(sumNetAmt,offerItemAmt,disRate,calDis,otherCharge,freight);
    
    
     var bill_amount=(sumNetAmt
                        +SumtaxAmt+parseFloat(otherCharge)
                        +parseFloat(freight)
                        -DisTotal
                    ).toFixed(2);
     $('#acc_discount').val(acDiscount.toFixed(2));
     $('#offer_discount').val(OfrDiscount.toFixed(2));
                   
     $('#bill_amount').val(bill_amount);
     $('.grandTotal').text(bill_amount);
     $('#showTotalDis').text(DisTotal.toFixed(2));
     $('#showTax').text(SumtaxAmt.toFixed(2));
     $('#showAccDiscount').text(acDiscount.toFixed(2));
     $('#showOfferDiscount').text(OfrDiscount.toFixed(2));

}

/*=====Show  Account Detail  by Clicking  searched Account List=== */
$(document).delegate(".accinfo","click",function(e){
    var acid=$(this).attr('acid');
    $.ajax({
            url: "/admin/getAccountDetail/" + acid,
            method: 'GET',
            //data: { name },
            success: function (d) {
                 // Get form values
                    const name = d.name;
                    const email = d.email;
                    const phone = d.phone;
                    const phone2 = d.phone2;
                    const customerType=d.type;
                    const statename= d.statedata.name;
                    const contactPerson = d.contactPerson;
                    const address = d.address;
                    const blockStatus = d.block_status;

                    if (d.citydata === null || d.citydata === undefined )
                        {
                            city='';
                        }else{
                            city =d.citydata.name;
                        }

                    if(d.type == '1'){
                        var actype='<span class="badge bg-warning">D</span>';
                     }else if(d.type == '2'){
                        var actype='<span class="badge bg-danger">W</span>';
                     }else{
                        var actype='<span class="badge bg-success">R</span>';
                     }

                     if (d.discount_rate === null || d.discount_rate === undefined || d.discount_rate.trim() === "")
                     {
                        d.discount_rate=0;
                     }
                   
                    $('#account_id').val(d.id);
                    // Create result display
                    $('#resultDisplay').html(`
                        <p><strong clas="h5"> ${name} <small>${actype}</small></strong></p>
                        <p>Phone: ${phone}, ${phone2}<br>
                        ${email}<br>
                        <strong>Address:</strong>${address}, ${city} , ${statename}<br>
                        <strong>Contact Person:</strong> ${contactPerson}</p>
                    `);
                    $('.customerSearchlist').hide();
                    $('#DisRate').val(d.discount_rate);


                    if(blockStatus==0){
                        //=== forn unblocked / active account
                        
                        $('input[name="inqfor"]').closest('.row').show();
                        $('#pendSaleOdr').html('');
                    }else{
                        //===== For Blocked Account ======
                        blockremar='<h2 class="text-danger">Blocked</h2>';
                        blockremar+='<span>Note : '+d.block_remark+'</span>';
                        $('#pendSaleOdr').html(blockremar);
                        $('input[name="inqfor"]').closest('.row').hide();
                        $('#prodData').prop('disabled',true);
                        $('#prodQrData').prop('disabled',true);

                    }
            },
            error: function (){
                $('#prodAllVariants').empty();
                console.error('Error fetching data');
            }
        });
})


/* ===== Product By QR Code ===== */
$('#prodQrData').on('keyup', function(){
    qrInputProdData();
});
function qrInputProdData(){
var qrcode=$('#prodQrData').val();
    if(qrcode.length>=3)
    {
        if($('#catall').is(":checked")){
            catall='all';
        }else{
            catall='';     
        }

        $.ajax({
                url: "/admin/search-prod-qr/" + qrcode+'/'+catall,
                method: 'GET',
                //data: { name },
                success: function (d) {
                    $('#prodAllVariants').html(d.data);
                    var prodCode=d.prodinfo.product.code;
                    var prodName=d.prodinfo.product.name;
                    var prodCatName=d.prodinfo.category.name;

                    var ProdInfo='<strong class="h5 text-danger">'+prodName+'</strong> ('+prodCode+')<br>'+prodCatName;
                    $('#selectedProd').html(ProdInfo);
                    $('#seletedProdName').val(prodName);
                    $('#seletedCatName').val(prodCatName);
                },
                error: function (){
                    $('#prodAllVariants').empty();
                    console.error('Error fetching data');
                }
            });
    }
};



/*==========QR Scanner ==== */
    const scanButton = document.getElementById('scan-btn');
    const qrResultInput = document.getElementById('prodQrData');
    const qrModal = document.getElementById('qr-modal');
    const closeModal = document.querySelector('.close');
    const qrReaderContainer = document.getElementById('qr-reader');

    // Initialize HTML5 QR code reader
    const qrReader = new Html5Qrcode("qr-reader");

    // Function to handle successful QR code scanning
    function onScanSuccess(decodedText) {
      qrResultInput.value = decodedText;  // Fill the input field with scan result
      qrModal.style.display = "none";  // Close the modal after successful scan
      qrInputProdData();
      qrReader.stop().catch(err => console.error("Failed to stop scanner:", err));  // Stop the scanner
    }

    // Function to start the scanner inside the popup
    function startScanner() {
      qrModal.style.display = "block";  // Show the modal with scanner
      qrReaderContainer.style.display = "block";  // Show the QR reader preview

      const cameraConfig = { facingMode: "environment" };  // Use the back camera
      qrReader.start(cameraConfig, { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess,  // Success callback
        (error) => { console.error("QR Code scan error:", error); }  // Error callback
      ).catch(err => {
        console.error("Failed to start QR scanner:", err);
        alert("Failed to start scanner: " + err.message);
      });
    }

    // Open the modal and start scanner when the button is clicked
    scanButton.addEventListener('click', function () {
      startScanner();
    });

    // Close the modal when the "x" button is clicked
    closeModal.addEventListener('click', function () {
      qrModal.style.display = "none";  // Hide the modal
      qrReader.stop().catch(err => console.error("Failed to stop scanner:", err));  // Stop the scanner
    });

    // Close the modal when clicking outside the modal
    window.addEventListener('click', function (event) {
      if (event.target === qrModal) {
        qrModal.style.display = "none";  // Close modal if clicked outside
        qrReader.stop().catch(err => console.error("Failed to stop scanner:", err));  // Stop the scanner
      }
    });



//====Item Add to Temp Memory ==========
function addToTemp(type){

    if ($('#tempid').val() == undefined || $('#tempid').val() == null) {
        return false;
    }
      
    var srchPdIDs=$('#allData').val();
    var allId=srchPdIDs.split("|"); //(stock_id|Prodduct_id|category_id|attribute_id);
    var account= $('input[name ="supplier_id"]').val();
    //var retData='';
    tmpAry={};
    tmpAry['accountid'] =account;
    tmpAry['userid'] ='{{Auth::user()->id}}';
    tmpAry['stockID'] = allId[0];
    tmpAry['prodID'] = allId[1];
    tmpAry['catID'] = allId[2];
    tmpAry['attrID'] = allId[3];
    tmpAry['pQty'] = $('#pQty').val();
    tmpAry['stMrp'] = $('#Mrp').val();
    tmpAry['pRate'] = $('#pRate').val();
    tmpAry['stDiscount']=$('#pDiscount').val();
    tmpAry['stNetAmt'] = $('#pNetAmount').val();

    tmpAry['stTaxRate'] = $('#pTaxRate').val()
    tmpAry['stTaxAmt']=($('#pNetAmount').val() * $('#pTaxRate').val() / 100);
    tmpAry['tempid']=$('#tempid').val();

     
$.ajax({
    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    type: 'POST',
    url: AdminUrl+'/add-to-temptable/'+type,
    data: tmpAry,
    //contentType: "application/json",
   // dataType: 'json',
    success: function(e) { 
        if(e.status=='success'){
            toastr.success("item saved to temperory memory33333");
            $('#addTblBody:last tr td:last-child').append('<input type="hidden" class="oldTempID" name="oldTempID[]" value="'+e.newid+'">');
        }else{
            toastr.error("unable to add item  to temperory memory");
        } 
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) { 
        alert("Status: " + textStatus); alert("Error: " + errorThrown); 
    }
    
}).fail(function (jqXHR, textStatus, error) {
    // Handle error here
    console.log(jqXHR.responseText);
    //$('#editor-container').modal('show');
});

};

function removeTempItem(obj){
    var tempItemId=$(obj).closest('tr').find('.oldTempID').val();
    if (tempItemId == undefined || tempItemId == null) {
        return false;
    }
    $.getJSON(AdminUrl+"/removetempitem/"+tempItemId, function(e){
        if(e.status=='success'){
            toastr.warning("Item Removed from temp memory successfully");
        }
                   
    });
}

function updateTempItemData(obj){
    e=$(obj).closest('tr');
    var tempItemId=$(e).find('.oldTempID').val();
    if (tempItemId == undefined || tempItemId == null) {
        return false;
    }
    tmpAry={};
    tmpAry['tempItemId'] = tempItemId;
    tmpAry['pQty'] = $(e).find('.apQty').val();
    tmpAry['stMrp'] = $(e).find('input[name ="AdpMrp"]').val();
    tmpAry['pRate'] = $(e).find('.apRate').val();
    tmpAry['stDiscount']= $(e).find('input[name ="AdDiscount"]').val();
    tmpAry['stNetAmt'] = $(e).find('.apAmt').val();
    tmpAry['stTaxRate'] =  $(e).find('input[name ="AdTaxRate"]').val();
    tmpAry['stTaxAmt']=($(e).find('.apAmt').val() * $(e).find('input[name ="AdTaxRate"]').val() / 100);

    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: 'POST',
        url: AdminUrl+'/update-tempitemdata',
        data: tmpAry,
        // contentType: "application/json",
        // dataType: 'json',
        success: function(e) { 
            if(e.status=='success'){
                toastr.success("item updated to temperory memory");
            }else{
                toastr.error("unable to add item  to temperory memory");
            } 
        },
        error: function(XMLHttpRequest, textStatus, errorThrown) { 
            alert("Status: " + textStatus); alert("Error: " + errorThrown); 
        }
        
    }).fail(function (jqXHR, textStatus, error) {
        // Handle error here
        console.log(jqXHR.responseText);
        //$('#editor-container').modal('show');
    });;

}


function showAccountTempbill(type,acid){
    var listData = $('.tblBodyTemp').html("");
    $('.tempAcName').text('');
	var i=1;
    $.getJSON(AdminUrl+"/get-acwise-tempbills/"+type+'/'+acid, function(e){
        
        if(e.tmpdata.length>=1){
            console.log(e.tmpdata); 
            $.each(e.tmpdata, function(key, value){
                var billdate=value.dateTime.split(' ');
                var func="loadTempData('"+value.tempNo+"')";
                var delFun="deleteTempBill('"+value.tempNo+"')";
                var list = '<tr><td>'+i+'</td><td>'+value.dateTime+'</td><td class="text-danger" onclick="'+func+'">'+value.tempNo+'</td><td>'+value.user.name+'</td><td>'+value.ItemCount+'<span class="float-right badge badge-danger" onclick="'+delFun+'">Del</span></td></tr>';
                $('.tempAcName').text(value.account.name+' ('+value.account.acCode+')');
                i++;
               listData.append(list);
           });

            $('#pendingTempbill').modal('show');  
        }
           
    });
}


    //====DELETE Temp Bill Data===== 
    function deleteTempBill(tempNo){
        var confirmDel = confirm('Are you sure you want to Delete temp- data PERMANENTALY ?');
		if (!confirmDel==true)
		{
			return false;
		}else{
            $.ajax({
                type: 'GET',
                url: AdminUrl+'/delete-tempbills/'+tempNo,
                success: function(e) { 
                    if(e.status=='success'){               
                       // toastr.success(tempNo+": Deleted successfully");
                        location.reload();
                    }else{
                        toastr.error("unable to deleted the order");
                    } 
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) { 
                    alert("Status: " + textStatus); alert("Error: " + errorThrown);                     
                }                
            }).fail(function (jqXHR, textStatus, error) {
                console.log(jqXHR.responseText);
            });
        }
    }
    //======Get Temperory bill date ===
    function loadTempData(tempid){

        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type: 'GET',
            url: AdminUrl+'/get-tempbills-detail/'+tempid,
           // data: tmpAry,
            //contentType: "application/json",
           // dataType: 'json',
            success: function(e) { 
                if(e.status=='success'){
                    $("#addTblBody").html(e.tempdata);
                    $('.tempid').text('TempNo :'+tempid);
                    $('#tempid').val(tempid);
                    $('#pendingTempbill').modal('hide');  
                    RowSno();
                    calculate();
                    
                    toastr.success("data loaded successfully");
                }else{
                    toastr.error("unable to add item  to temperory memory");
                } 
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) { 
                alert("Status: " + textStatus); alert("Error: " + errorThrown); 
                ///console.log("Status: " + textStatus);
            }
            
        }).fail(function (jqXHR, textStatus, error) {
            // Handle error here
            console.log(jqXHR.responseText);
            //$('#editor-container').modal('show');
        });
    }
