(function($) {
    "use strict";

    $( function() {
      $( "#htpm_accordion" ).accordion({
      	active: false ,
      	collapsible: true,
      	heightStyle: 'content'
      });
    } );

    // Reapeter Field Increase
    $( '.htpm-add-row' ).on('click', function() {
        var row = $(this).parent().closest('tr').clone(true);
        row.removeClass( 'htpm-empty-row screen-reader-text' );
        $(this).parent().closest('tr').after(row);
        return false;
    });

    // Reapeter Field Decrease
    $( '.htpm-remove-row' ).on('click', function() {
        $(this).parent().parent().remove();
        return false;
    });


    $('.htpm_single_accordion .htpm_uri_type').on('change', function(){
    	var select_val = $(this).val();

    	if(select_val == 'page'){
    		$(this).parent().parent().attr('data-htpm_uri_type', 'page');
    	} else if(select_val == 'post'){
    		$(this).parent().parent().attr('data-htpm_uri_type', 'post');
    	} else if(select_val == 'page_post'){
    		$(this).parent().parent().attr('data-htpm_uri_type', 'page_post');
    	} else if(select_val == 'custom'){
    		$(this).parent().parent().attr('data-htpm_uri_type', 'custom');
    	}
    });

    // select2 activation
    $(document).ready(function() {
        $('.htpm_select2_active').select2();
    });

    // pro popup
    $('.htpm_accordion .htpm_repeater').on('click', function(e){
    	$( "#htpm_pro_notice" ).dialog({
    		'dialogClass': 'wp-dialog',
    		title: 'Pro Version is Required!',
    		modal: true,
    	}); return;
    });

    $('.htpm_accordion .htpm_uri_type').on('change', function(e){
        var value = $(this).val();
        if(value == 'page_post_cpt'){
            $( "#htpm_pro_notice" ).dialog({
                'dialogClass': 'wp-dialog',
                title: 'Pro Version is Required!',
                modal: true,
            }); return;
        }
    });

    $( document ).on('click','.htmp-nav.nav-tab', function() {

        // Check for active
        $('.htmp-nav.nav-tab').removeClass('nav-tab-active');
        $('.htpm-tab-group').removeClass('htmp-active-tab');
        $(this).addClass('nav-tab-active');

        // Display active tab
        let currentTab = $(this).attr('href');
        $(currentTab).addClass('htmp-active-tab');

        return false;
        
    });

})(jQuery);