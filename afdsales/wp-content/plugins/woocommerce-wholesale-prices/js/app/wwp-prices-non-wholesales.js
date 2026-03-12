jQuery(document).ready(function ($) {
    /**-----------------------------------------------------------------------------------------------------------------
     * Variables
     -----------------------------------------------------------------------------------------------------------------*/
    var popover_title =
        '<h4 class="custom-title"><i class="fa fa-info-circle"></i> ' +
        wwp_non_wholesale_var.popover_header_title +
        "</h4>";
    /**-----------------------------------------------------------------------------------------------------------------
     * Events
     -----------------------------------------------------------------------------------------------------------------*/
    // Prevent all anchor tag with an attribute of "[data-toggle=popover]"  event to execute its default action, since we will not be needing it

    $("body").popover({
        title: popover_title,
        container: 'body',
        content: function() {
            var wholesale_price_box_data = $(this).data('wholesale_price_box');

            // create a placeholder id
            var tmp_id = 'tmp-id-'+ $.now();

            decode_html(wholesale_price_box_data).then(function(data){
                $('#'+ tmp_id).removeClass('loading spinner').html(data);
            });

            // generate temporary content for the placeholder to show the user while we wait
            return $('<div>').attr('id', tmp_id).addClass('loading spinner');

        },
        html: true,
        selector: '[rel=popover]',
        trigger: 'focus',   
    });



    $(document).on("click", "[data-toggle=popover]", function (e) {
        e.preventDefault();
    });

    // Disable Anchor tag for registration link if wwlc is not active
    if (wwp_non_wholesale_var.is_wwlc_active == false) {
        $(".register-link").prop("disable", true);
    }

    /**-----------------------------------------------------------------------------------------------------------------
     * Functions
     -----------------------------------------------------------------------------------------------------------------*/

    /**
     * This function gets encoded utf-8 data from html data attribute and pass the decoded html string to popover
     * 
     * @since 1.15.0
     * @since 1.15.1 change this function to async fetching of data from html data attribute
     * @param {*} str 
     * @returns html string
     */
    async function decode_html(str){
        let result;

        try{
            result = await htmlentities.decode(str.replace(/\\/g, ''));
        }catch(error){
            console.log(error);
        }

        return result;

    }

});

(function(window){
    window.htmlentities = {
        /**
         * Converts a string to its html characters completely.
         *
         * @param {String} str String with unescaped HTML characters
         **/
        encode : function(str) {
            var buf = [];
            
            for (var i=str.length-1;i>=0;i--) {
                buf.unshift(['&#', str[i].charCodeAt(), ';'].join(''));
            }
            
            return buf.join('');
        },
        /**
         * Converts an html characterSet into its original character.
         *
         * @param {String} str htmlSet entities
         **/
        decode : function(str) {
            return str.replace(/&#(\d+);/g, function(match, dec) {
                return String.fromCharCode(dec);
            });
        }
    };
})(window);
