( function( $, elementorFrontend ) {
    "use strict";

    class NestedCarousel extends elementorModules.frontend.handlers.Base {

        getDefaultSettings() {
            return {
                selectors: {
                    swiperContainer: `.swiper-container-${this.getID()}`,
                    swiperWrapper: `.swiper-wrapper-${this.getID()}`,
                    slideContent: `.swiper-slide`,
                    swiperArrow: `.lakit-carousel__prev-arrow-${this.getID()}`,
                    paginationBullet: '.swiper-pagination-bullet',
                    paginationBulletWrapper: `.lakit-carousel__dots_${this.getID()}`
                }
            }
        }

        getDefaultElements() {
            const selectors = this.getSettings('selectors'),
                elements = {
                    $swiperContainer: this.$element.find(selectors.swiperContainer),
                    $swiperWrapper: this.$element.find(selectors.swiperWrapper),
                    $swiperArrows: this.$element.find(selectors.swiperArrow),
                    $paginationBullets: this.$element.find(selectors.paginationBullet),
                    $paginationBulletWrapper: this.$element.find(selectors.paginationBulletWrapper),
                    $carousel: this.$element.find('.lakit-carousel').first()
                };
            elements.$slides = elements.$swiperContainer.find(selectors.slideContent);
            return elements;
        }

        wrapSlideContent() {
            if (!elementorFrontend.isEditMode()) {
                return;
            }
            const settings = this.getSettings(),
                slideContentClass = settings.selectors.slideContent.replace('.', ''),
                $widget = this.$element;

            let index = 1;
            this.findElement(`${settings.selectors.swiperWrapper} > .e-con`).each(function () {
                const $currentContainer = jQuery(this),
                    hasSwiperSlideWrapper = $currentContainer.closest('div').hasClass(slideContentClass),
                    $currentSlide = $widget.find(`${settings.selectors.swiperWrapper} > .${slideContentClass}:nth-child(${index})`);
                if (!hasSwiperSlideWrapper) {
                    $currentSlide.append($currentContainer);
                }
                index++;
            });
        }

        async initSwiper() {
            const $widget = this.$element;
            await LaStudioKits.initCarousel($widget)
            const settings = this.getSettings();
            const $swiperContainer = $(settings.selectors.swiperContainer)
            this.swiper = $swiperContainer.data('swiper')
        }

        async onInit() {
            this.wrapSlideContent();
            super.onInit(...arguments);
            await this.initSwiper();
        }

        onEditSettingsChange(propertyName) {
            if ('activeItemIndex' === propertyName) {
                this.swiper.slideToLoop(this.getEditSettings('activeItemIndex') - 1);
            }
        }

        updateListeners() {
            this.swiper.initialized = false;
            this.swiper.init();
        }

        async linkContainer(event) {
            const {
                    container,
                    index,
                    targetContainer,
                    action: {
                        type
                    }
                } = event.detail,
                view = container.view.$el,
                id = container.model.get('id'),
                currentId = this.$element.data('id');

            if (id === currentId) {
                const {
                    $slides
                } = this.getDefaultElements();
                let carouselItemWrapper, contentContainer;
                switch (type) {
                    case 'move':
                        [carouselItemWrapper, contentContainer] = this.move(view, index, targetContainer, $slides);
                        break;
                    case 'duplicate':
                        [carouselItemWrapper, contentContainer] = this.duplicate(view, index, targetContainer, $slides);
                        break;
                    default:
                        break;
                }
                if (undefined !== carouselItemWrapper) {
                    carouselItemWrapper.appendChild(contentContainer);
                }
                this.updateIndexValues($slides);
                const isSwiperActive = this.swiper && !this.swiper.destroyed,
                    hasMultipleSlides = $slides.length > 1;
                if (!isSwiperActive && hasMultipleSlides) {
                    await this.initSwiper();
                } else if (isSwiperActive && !hasMultipleSlides) {
                    this.swiper.destroy(true);
                }
                this.updateListeners();
            }
        }

        move(view, index, targetContainer, slides) {
            return [slides[index], targetContainer.view.$el[0]];
        }
        duplicate(view, index, targetContainer, slides) {
            return [slides[index + 1], targetContainer.view.$el[0]];
        }
        updateIndexValues($slides) {
            $slides.each((index, element) => {
                const newIndex = index + 1;
                element.setAttribute('data-slide', newIndex);
            });
        }

        bindEvents() {
            super.bindEvents();
            elementorFrontend.elements.$window.on('elementor/nested-container/atomic-repeater', this.linkContainer.bind(this));
        }

        getResponsiveValue(_key, _settings){
            const _bpkArr = Object.keys(elementorFrontend.config.responsive.activeBreakpoints);
            _bpkArr.push('desktop')
            return _bpkArr.reduce(function(acc, cur) {
                acc[cur] = elementorFrontend.getDeviceSetting(cur, _settings, _key);
                return acc;
            }, {})
        }

        destroySwiper(){
            this.elements.$carousel.removeClass('inited')
            this.elements.$swiperWrapper.removeAttr('aria-live')
            this.swiper.destroy(true)
        }

        async onElementChange(propertyName) {
            if(propertyName.startsWith('carousel_columns') || propertyName.startsWith('carousel_to_scroll') || propertyName.startsWith('carousel_rows')){
                const dbSettings = this.getElementSettings()
                const slidesToShow = this.getResponsiveValue('carousel_columns', dbSettings)
                const slidesToScroll = this.getResponsiveValue('carousel_to_scroll', dbSettings)
                const rows = this.getResponsiveValue('carousel_rows', dbSettings)
                let oldSettings = this.elements.$carousel.data('slider_options')
                const newSettings = {
                    ...oldSettings,
                    slidesToScroll,
                    slidesToShow,
                    rows,
                }
                this.elements.$carousel.data('slider_options', newSettings)
                this.destroySwiper()
                await this.initSwiper();
            }
        }
    }

    $( window ).on( 'elementor/frontend/init', () => {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/lakit-nested-carousel.default', ( $element ) => {
            elementorFrontend.elementsHandler.addHandler( NestedCarousel, { $element } );
        } );
    } );

}( jQuery, window.elementorFrontend ) );