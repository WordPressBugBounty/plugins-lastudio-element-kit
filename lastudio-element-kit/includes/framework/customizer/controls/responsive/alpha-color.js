(function( $ ) {
    'use strict';

    $(function (){

        $('.lakit-customize-alpha-color-control').wpColorPicker({
            change: _.throttle( function() {
                $(this).trigger( 'change' );
            }, 1000 )
        })
    })

})( jQuery );