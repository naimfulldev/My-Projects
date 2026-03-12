jQuery( document ).ready( function ( $ ) {

    var $wws_settings_wwlc      = $( "#wws_settings_wwlc" ),
        $wws_wwlc_license_email = $wws_settings_wwlc.find( "#wws_wwlc_license_email" ),
        $wws_wwlc_license_key   = $wws_settings_wwlc.find( "#wws_wwlc_license_key" ),
        $wws_save_btn           = $wws_settings_wwlc.find( "#wws_save_btn" ),
        errorMessageDuration    = '10000',
        successMessageDuration  = '5000';

    $wws_save_btn.click( function () {

        var $this = $( this );

        $this
            .attr( "disabled" , "disabled" )
            .siblings( ".spinner" )
                .css( {
                    display : 'inline-block',
                    visibility : 'visible'
                } );

        var $licenseDetails = {
            'license_email' : $.trim( $wws_wwlc_license_email.val() ),
            'license_key'   : $.trim( $wws_wwlc_license_key.val() ),
            'nonce'         : WWSLicenseSettingsVars.nonce_activate_license
        };

        wwlcBackEndAjaxServices.saveWWLCLicenseDetails( $licenseDetails )
            .done( function ( data , textStatus , jqXHR ) {

                if ( data.status == "success" ) {

                    toastr.success( '' , data.success_msg , { "closeButton" : true , "showDuration" : successMessageDuration } );

                } else {

                    toastr.error( '' , data.error_msg , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                    if ( data.expired_date ) {

                        var $expired_notice = $( "#wws_wwlc_license_expired_notice" );
                        
                        $expired_notice.find( '#wwlc-license-expiration-date' ).text( data.expired_date );
                        $expired_notice.css( { 'display' : 'table-row' } );

                    }

                    console.log( data.error_msg );
                    console.log( data );
                    console.log( '----------' );

                }

            } )
            .fail( function ( jqXHR , textStatus , errorThrown ) {

                toastr.error( jqXHR.responseText , WWSLicenseSettingsVars.failed_save_message , { "closeButton" : true , "showDuration" : errorMessageDuration } );

                console.log( WWSLicenseSettingsVars.failed_save_message );
                console.log( jqXHR );
                console.log( '----------' );

            } )
            .always( function () {

                $this
                    .removeAttr( "disabled" )
                    .siblings( ".spinner" )
                        .css( {
                            display : 'none',
                            visibility : 'hidden'
                        } );

            } );

    } );

} );
