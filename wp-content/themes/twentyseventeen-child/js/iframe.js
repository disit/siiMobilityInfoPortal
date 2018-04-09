function resizeIframe()
{
    const maxWidth = 1560;

    var iframe = jQuery('iframe').first();

    if(iframe != undefined && iframe.data('height') != undefined) {
        var maxHeight = iframe.data('height');

        var iframeWidth = iframe.width();

        var iframeHeight = iframeWidth * maxHeight / maxWidth;

        iframe.height(iframeHeight + 'px');
    }
}

jQuery(window).resize(function() {
    resizeIframe();
});

jQuery(document).ready(function($) {
    $(window).resize();
});