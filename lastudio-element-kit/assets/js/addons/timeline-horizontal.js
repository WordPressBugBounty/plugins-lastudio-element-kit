(function ($) {
    "use strict";

    $(window).on('elementor/frontend/init', function () {

        window.elementorFrontend.hooks.addAction('frontend/element_ready/lakit-timeline-horizontal.default', function ($scope) {

            var $timeline = $scope.find('.lakit-htimeline'),
                $timelineTrack = $scope.find('.lakit-htimeline-track'),
                $items = $scope.find('.lakit-htimeline-item'),
                $arrows = $scope.find('.lakit-arrow'),
                $nextArrow = $scope.find('.next-arrow'),
                $prevArrow = $scope.find('.prev-arrow'),
                columns = $timeline.data('columns') || {},
                desktopColumns = columns.desktop || 3,
                tabletColumns = columns.tablet || desktopColumns,
                mobileColumns = columns.mobile || tabletColumns,
                firstMouseEvent = true,
                currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
                prevDeviceMode = currentDeviceMode,
                itemsCount = $scope.find('.lakit-htimeline-list--middle .lakit-htimeline-item').length,
                currentTransform = 0,
                currentPosition = 0,
                transform = {
                    desktop: 100 / desktopColumns,
                    tablet: 100 / tabletColumns,
                    mobile: 100 / mobileColumns
                },
                maxPosition = {
                    desktop: Math.max(0, (itemsCount - desktopColumns)),
                    tablet: Math.max(0, (itemsCount - tabletColumns)),
                    mobile: Math.max(0, (itemsCount - mobileColumns))
                };

            const $listTop = $('.lakit-htimeline-list--top', $scope);

            if ('ontouchstart' in window || 'ontouchend' in window) {
                $items.on('touchend.LaStudioKitsTimeLineHorizontal', function (event) {
                    var itemId = $(this).data('item-id');

                    $scope.find('.elementor-repeater-item-' + itemId).toggleClass('is-hover');
                });
            }
            else {
                $items.on('mouseenter.LaStudioKitsTimeLineHorizontal mouseleave.LaStudioKitsTimeLineHorizontal', function (event) {
                    if (firstMouseEvent && 'mouseleave' === event.type) {
                        return;
                    }
                    if (firstMouseEvent && 'mouseenter' === event.type) {
                        firstMouseEvent = false;
                    }
                    var itemId = $(this).data('item-id');
                    $scope.find('.elementor-repeater-item-' + itemId).toggleClass('is-hover');
                });
            }

            // Set Line Position
            setLinePosition();
            $(window).on('resize.LaStudioKitsTimeLineHorizontal orientationchange.LaStudioKitsTimeLineHorizontal', setLinePosition);

            function setLinePosition() {
                var $line = $scope.find('.lakit-htimeline__line')
                if($line.length === 0){
                    return;
                }
                let $firstPoint = $scope.find('.lakit-htimeline-item__point-content:first'),
                    $lastPoint = $scope.find('.lakit-htimeline-item__point-content:last'),
                    firstPointLeftPos = $firstPoint.position().left + parseInt($firstPoint.css('marginLeft')),
                    lastPointLeftPos = $lastPoint.position().left + parseInt($lastPoint.css('marginLeft')),
                    pointWidth = $firstPoint.outerWidth();

                $line.css({
                    'left': firstPointLeftPos + pointWidth / 2,
                    'width': lastPointLeftPos - firstPointLeftPos
                });

                $timeline.css({
                    '--lakit-htimeline-line-offset': ($line.offset().top - $timeline.offset().top) + 'px'
                });

                var $progressLine   = $scope.find( '.lakit-htimeline__line-progress' ),
                    $lastActiveItem = $scope.find( '.lakit-htimeline-list--middle .lakit-htimeline-item.is-active:last' );

                if ( $lastActiveItem[0] ) {
                    var $lastActiveItemPointWrap = $lastActiveItem.find( '.lakit-htimeline-item__point' ),
                        progressLineWidth        = $lastActiveItemPointWrap.position().left + $lastActiveItemPointWrap.outerWidth() - firstPointLeftPos - pointWidth / 2;

                    $progressLine.css( {
                        'width': progressLineWidth
                    } );
                }
            }

            // Arrows Navigation Type
            if ($nextArrow[0] && maxPosition[currentDeviceMode] === 0) {
                $nextArrow.addClass('arrow-disabled');
            }

            if ($arrows[0]) {
                $arrows.on('click.LaStudioKitsTimeLineHorizontal', function (event) {
                    var $this = $(this),
                        direction = $this.hasClass('next-arrow') ? 'next' : 'prev',
                        currentDeviceMode = elementorFrontend.getCurrentDeviceMode();

                    if ('next' === direction && currentPosition < maxPosition[currentDeviceMode]) {
                        currentTransform -= transform[currentDeviceMode];
                        currentPosition += 1;
                    }

                    if ('prev' === direction && currentPosition > 0) {
                        currentTransform += transform[currentDeviceMode];
                        currentPosition -= 1;
                    }

                    if (currentPosition > 0) {
                        $prevArrow.removeClass('arrow-disabled');
                    } else {
                        $prevArrow.addClass('arrow-disabled');
                    }

                    if (currentPosition === maxPosition[currentDeviceMode]) {
                        $nextArrow.addClass('arrow-disabled');
                    } else {
                        $nextArrow.removeClass('arrow-disabled');
                    }

                    if (currentPosition === 0) {
                        currentTransform = 0;
                    }

                    $timelineTrack.css({
                        'transform': 'translateX(' + currentTransform + '%)'
                    });

                });
            }

            setArrowPosition();
            $(window).on('resize.LaStudioKitsTimeLineHorizontal orientationchange.LaStudioKitsTimeLineHorizontal', setArrowPosition);
            $(window).on('resize.LaStudioKitsTimeLineHorizontal orientationchange.LaStudioKitsTimeLineHorizontal', timelineSliderResizeHandler);

            function setArrowPosition() {
                if (!$arrows[0]) {
                    return;
                }

                var $middleList = $scope.find('.lakit-htimeline-list--middle'),
                    middleListTopPosition = $middleList.position().top,
                    middleListHeight = $middleList.outerHeight();

                $arrows.css({
                    'top': middleListTopPosition + middleListHeight / 2
                });
            }

            function timelineSliderResizeHandler(event) {
                if (!$timeline.hasClass('lastudio-hor-timeline--arrows-nav')) {
                    return;
                }

                var currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
                    resetSlider = function () {
                        $prevArrow.addClass('arrow-disabled');

                        if ($nextArrow.hasClass('arrow-disabled')) {
                            $nextArrow.removeClass('arrow-disabled');
                        }

                        if (maxPosition[currentDeviceMode] === 0) {
                            $nextArrow.addClass('arrow-disabled');
                        }

                        currentTransform = 0;
                        currentPosition = 0;

                        $timelineTrack.css({
                            'transform': 'translateX(0%)'
                        });
                    };

                switch (currentDeviceMode) {
                    case 'desktop':
                        if ('desktop' !== prevDeviceMode) {
                            resetSlider();
                            prevDeviceMode = 'desktop';
                        }
                        break;

                    case 'tablet':
                        if ('tablet' !== prevDeviceMode) {
                            resetSlider();
                            prevDeviceMode = 'tablet';
                        }
                        break;

                    case 'mobile':
                        if ('mobile' !== prevDeviceMode) {
                            resetSlider();
                            prevDeviceMode = 'mobile';
                        }
                        break;
                }
            }

            function clamp(v){ return Math.max(0, Math.min(100, v)); }

            const $linebars = $scope.find('.lakit-htimeline-timelinebar')

            $timelineTrack.first().on('scroll', (evt) => {
                const elm = evt.currentTarget;
                let scrollLeft = Math.abs($(elm).scrollLeft());
                let scrollWidth = elm.scrollWidth;
                let clientWidth = $(elm).innerWidth();
                let percent = clamp((scrollLeft / (scrollWidth - clientWidth)) * 100);
                $('.lakit-htimeline-inner', $scope).css({
                    '--timeline-bar-width': percent.toFixed(3) + '%'
                })
            });
            function scrollToCenter(container, element, duration = 100) {
                const elementWidth = element.offsetWidth;
                const containerWidth = container.clientWidth;
                const elementOffset = element.offsetLeft;
                let scrollPosition = elementOffset - (containerWidth / 2) + (elementWidth / 2);
                $(container).animate({ scrollLeft: scrollPosition }, duration);
            }

            $items.on('click', function (evt){
                const element = evt.currentTarget;
                const container = element.closest('.lakit-htimeline-track');
                scrollToCenter(container, element, 100)
                if( $linebars.length > 0 ){
                    $(element).addClass('is-active is-current');
                    const refId = element.getAttribute('data-item-id');
                    const $refElm = $(`.lakit-htimeline-timelinebar[data-item-id="${refId}"]`);
                    $(element).siblings().removeClass('is-current');
                    $(element).prevAll().addClass('is-active');
                    $(element).nextAll().removeClass('is-active');
                    $refElm.addClass('is-active is-current')
                    $refElm.siblings().removeClass('is-current');
                    $refElm.prevAll().addClass('is-active');
                    $refElm.nextAll().removeClass('is-active');
                }
            })
            if( $linebars.length > 0 ){
                $linebars.on('click', (evt) => {
                    const eId = evt.currentTarget.getAttribute('data-item-id');
                    const element = evt.currentTarget;
                    const container = element.closest('.lakit-htimeline-track');
                    scrollToCenter(container, element, 100)
                    $(`.lakit-htimeline-item[data-item-id="${eId}"]`).trigger('click')
                })
            }
        });
    });

}(jQuery));