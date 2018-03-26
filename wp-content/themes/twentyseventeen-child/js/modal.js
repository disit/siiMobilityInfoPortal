function openModal(img)
{
    jQuery('#modal').css('display', 'block');
    jQuery("#img").attr('src', jQuery(img).attr("src"));
    jQuery("#caption").html(jQuery(img).attr("alt"));
}

jQuery(document).ready(function($) {
    var imageModal = ['#first-img', '#second-img', '#third-img', '#fourth-img'];

    imageModal.forEach(function(image) {
        $(image).click(function(){
            openModal(this);
        });
    });
    
    $('#modal').on('click', function(){
        $(this).css('display', "none");
    });
});