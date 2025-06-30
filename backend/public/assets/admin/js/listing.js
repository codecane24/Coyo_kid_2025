/* ====== ACtion Button Dropdown menu Open close====== */
$(document).on("click",".dropdown-toggle",function(e){
    $('.dropdown-menu').removeClass('show');
    if( $(this).find('i').hasClass('mdi-chevron-up')){
        $(this).closest('.btn-group').find('.dropdown-menu').removeClass('show');
        
    }else{
        $(this).closest('.btn-group').find('.dropdown-menu').addClass('show');
    }
    
    $(this).find('i').toggleClass('mdi-chevron-up','mdi-chevron-down');
});  


//=====Bill Print ====
$("#printit").click(function(){
    $("#printit").hide();
    window.print();
  });