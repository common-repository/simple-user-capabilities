var submit_url_user_access = 'admin.php?page=user_admin_menus&showonly_data=1';
var modal = document.getElementById("myModal");

jQuery( document ).ready( function( $ ) {
    $('[data-toggle="tooltip"]').tooltip();   
    $("body").append('<div class="user_access_loader"></div>');

    $( '.manage_user_capabilities' ).on( 'click', function( e ) {
        //$('.selectpicker').selectpicker();
        jQuery(".user_access_loader").show();
        $('.userAccess-modal-body').html("");
        
        e.preventDefault();
        submit_url_user_access = jQuery(this).attr('href');
        // Delay the AJAX call by 1 second.
        $.ajax({
            url: submit_url_user_access,
            type: 'POST',
            data: {
                user_id: $(this).attr('user_id'),
                action:'user_capabilities'
            },
            success: function( response ) {
                modal.style.display = "block";
                $('.userAccess-modal-body').html(response);

                jQuery('.selectpicker').selectpicker(); 
                jQuery('.selectpicker').on('changed.bs.select', function() {});

                jQuery(".user_access_loader").hide();
            },
            complete: function (data) {
                //
            },
            error: function( response ) {
                console.log( 'Error: ' + response.statusText );
            }
        });

    });

     $( '.userAccess-modal-content .close' ).on( 'click', function( e ) {
        modal.style.display = "none";
    });

});

jQuery(document).on('change', '#targeted_user', function(e){
    jQuery(".user_access_loader").show();
    jQuery.ajax({
        url: submit_url_user_access,
        type: 'POST',
        data: {
            user_id: jQuery(this).val(),
            action:'user_capabilities'
        },
        success: function( response ) {
            jQuery(".user_access_loader").hide();
            jQuery('.userAccess-modal-body').html(response);
        },
        error: function( response ) {
            console.log( 'Error: ' + response.statusText );
        }
    });
});

jQuery(document).on('submit', '#submit_user_cap', function(e) {
    jQuery(".user_access_loader").show();
    
    var submit_url_user_access = 'admin.php?page=user_admin_menus&usr_access=1';
    submit_url_user_access = jQuery(this).attr('href');

    e.preventDefault();
    var formData = jQuery(this).serialize()+"&action=submit_capabilities";

    jQuery.ajax({
        type:'POST',
        url: submit_url_user_access,
        data: formData,
        success: function(response){
            response = jQuery.parseJSON(response);
            jQuery(".user_access_loader").hide();
            if(response.error){
                jQuery('#modal_alert').modal('show');
            }
            else{
               // modal.style.display = "none";
               // jQuery('#modal_alert').modal('show');

               modal.style.display = "none";
               jQuery('#success-popup #success-view-message').text(response.message);
               jQuery('#success-popup').slideDown('slow', function() { });
               setTimeout(function() { 
                   jQuery('#success-popup').slideUp('slow', function() { });
               }, 2000);

            }
        }

    });
});

jQuery(document).on('click', '.reset_restricts', function(e){
    e.preventDefault();
    var confirmReset = confirm("Are you sure to reset modified permission?");
    if(confirmReset){
        jQuery(".user_access_loader").show();
        submit_url_user_access = jQuery(this).attr('href');
    
        jQuery.ajax({
            url:submit_url_user_access,
            type:'POST',
            data:{
                user_id: jQuery(this).attr("user_id"),
                action:'reset_capability'
            },
            success: function(response){
                jQuery(".user_access_loader").hide();
                jQuery('#success-popup #success-view-message').text(response.message);
                jQuery('#success-popup').slideDown('slow', function() { });
                setTimeout(function() { 
                    jQuery('#success-popup').slideUp('slow', function() { });
                }, 2000);
            }
        });
    }
});


