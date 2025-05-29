;(function( $ ) {
    var $window = $(window);

    $window.on('elementor/frontend/init', function() {
        $('body').on('click.onWrapperLink', '[data-lakit-element-link]', function() {
            var $wrapper = $(this),
                data     = $wrapper.data('lakit-element-link'),
                id       = $wrapper.data('id'),
                anchor   = document.createElement('a'),
                anchorReal;

            const allowProtocols = ['http:', 'https:', 'mailto:','tel:','webcal:', 'skype:', 'viber:']

            try {
                const safeURL = new URL(data.url, window.location.href);
                if (!allowProtocols.includes(safeURL.protocol)) {
                    return;
                }
                anchor.href          = safeURL.href;
                anchor.id            = 'lakit-wrapper-link-' + id;
                anchor.target        = data.is_external ? '_blank' : '_self';
                anchor.rel           = data.nofollow ? 'nofollow noreferer' : '';
                anchor.style.display = 'none';
                document.body.appendChild(anchor);

                anchorReal = document.getElementById(anchor.id);
                anchorReal.click();
                anchorReal.remove();
            } catch (err) {}
        });
    });

}( jQuery ));
