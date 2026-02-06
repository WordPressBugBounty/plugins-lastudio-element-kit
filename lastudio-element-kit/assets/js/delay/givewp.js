(function ($) {
    'use strict';

    $.lakitGivePopup = function ( url, options = {} ){
        const settings = $.extend({
            onLoad: null,
            showLoading: false,
        }, options);

        // Remove previous popup if any
        $('.lakit-popup--wrap').remove();
        // Create DOM
        let ppHTML = `<div class="lakit-popup--content">
          <span class="lakit-popup--close">&times;</span>
          <div class="lakit-popup--iframe">
            <iframe src="${url}" frameborder="0"></iframe>
          </div>
          <div class="lakit-popup--overlay"></div>
        </div>`;
        if(settings.showLoading){
            ppHTML = `<div class="lakit-popup--wrap lakit-popup--givewp lakit-popup--show-loading lakit-popup--is-loading">${ppHTML}<div class="lakit-popup--loading"><div class="lakit-loader spinner3"><div class="dot1"></div><div class="dot2"></div><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="cube1"></div><div class="cube2"></div><div class="cube3"></div><div class="cube4"></div></div></div></div></div>`
        }
        else{
            ppHTML = `<div class="lakit-popup--wrap lakit-popup--givewp lakit-popup--is-loading">${ppHTML}</div>`
        }
        const popup = $(ppHTML);
        // Append to body
        $('body').append(popup);
        popup.fadeIn(200);
        function closePopup() {
            $('html').removeClass('lakit-no-scroll')
            popup.fadeOut(200, function () {
                popup.remove();
                $(document).off('keydown.lakitPopup'); // remove ESC listener
            });
        }

        // Close button
        popup.find('.lakit-popup--close, .lakit-popup--overlay').on('click', function () {
            closePopup()
        });

        $(document).on('keydown.lakitPopup', function (e) {
            if (e.key === 'Escape') {
                closePopup();
            }
        });
        popup.find('iframe').on('load', function () {
            $('html').addClass('lakit-no-scroll')
            popup.removeClass('lakit-popup--is-loading').addClass('lakit-popup--is-ready')
            let iframe = this;
            try {
                const $body = $(iframe.contentDocument).find('#root-givewp-donation-form');
                const designClass = $body
                    .attr('class')
                    .split(/\s+/)
                    .find(cls => cls.startsWith('givewp-donation-form-design--'));
                if(designClass){
                    $('.lakit-popup--iframe').addClass(`frame-${designClass?.split('--')[1] ?? '0'}`)
                }
                const updateHeight = () => {
                    const newHeight = $body[0].clientHeight;
                    const currentHeight = $(iframe).outerHeight();
                    if (newHeight !== currentHeight) {
                        $(iframe).height(newHeight);
                    }
                };
                updateHeight();
                const observer = new MutationObserver(updateHeight);
                observer.observe($body[0], {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
                const checkRemoved = new MutationObserver(() => {
                    if (!document.body.contains(iframe)) {
                        observer.disconnect();
                        checkRemoved.disconnect();
                    }
                });
                checkRemoved.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            } catch (e) {
                console.log(e)
            }
            if (typeof settings.onLoad === 'function') {
                settings.onLoad(this);
            }
        });
    }

    $(function () {
        $(document).on('click', '.lakit-posts__btn-donate', function (evt){
            evt.preventDefault();
            if( $('html').hasClass('elementor-html') || $(this).hasClass('loading') ){
                return;
            }
            let currentBtn = $(this);
            let isV3 = currentBtn.data('isv3') === true
            if(isV3){
                currentBtn.addClass('loading');
                $.lakitGivePopup(LaStudioKitSettings.giveRouter.replace('lakit_rp_lakit', currentBtn.data('id')), {
                    onLoad: function (iframe) {
                        currentBtn.removeClass('loading');
                    }
                });
                return false;
            }
            const frmID = $(".lakit-give-form-modal[data-id='"+currentBtn.data('id')+"']").first();
            $.magnificPopup.open({
                items: {
                    type: "inline",
                    src: frmID
                },
                fixedContentPos: true,
                fixedBgPos: true,
                closeBtnInside: true,
                midClick: true,
                removalDelay: 300,
                mainClass: "modal-fade-slide",
                callbacks: {
                    open: function (){
                        $('.mfp-content').addClass('lakit-mfp--content');
                    },
                    beforeOpen: function(){
                        const giveEmbed = $('.root-data-givewp-embed', frmID);
                        if(giveEmbed.length > 0 && $('>iframe', giveEmbed).length === 0){
                            $('<iframe>', {
                                src: giveEmbed.data('src'),
                                id: giveEmbed.data('givewp-embed-id'),
                                title: 'iframe',
                                scrolling: 'no'
                            }).appendTo(giveEmbed);
                            if( typeof iFrameResize !== "undefined"){
                                iFrameResize();
                            }
                        }
                    }
                }
            })
        })

        const showPopupDonateForm = (settings) => {
            const form_id = settings?.id
            if(form_id){
                $.lakitGivePopup(LaStudioKitSettings.giveRouter.replace('lakit_rp_lakit', form_id), {
                    showLoading: true
                });
            }
        }

        elementorFrontend.utils.urlActions.addAction('givewp_form:open', showPopupDonateForm);
    });

})(jQuery);