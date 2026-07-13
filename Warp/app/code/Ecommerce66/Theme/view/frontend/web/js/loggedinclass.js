require(['jquery', 'jquery/ui'], function($){
    jQuery(document).ready( function() {
        var isLoggedIn = jQuery('.authorization-link > a').attr('href').indexOf('/login')<0;
        if(isLoggedIn){
            jQuery("body").addClass("logged-in");
        } else {
            jQuery("body").addClass("guest");
        }
    });
});
