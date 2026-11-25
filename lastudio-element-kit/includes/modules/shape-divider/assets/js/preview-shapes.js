(function ($) {
    'use strict';

    var config = window.lakitShapeDividerPreview || {};
    var placeholders = (config.placeholders || []).filter(Boolean);
    var svgCache = new Map();

    function isPlaceholderRequest(url) {
        if (!url) {
            return false;
        }
        var clean = url.split('?')[0];
        return placeholders.some(function (placeholder) {
            return clean === placeholder || clean.indexOf(placeholder) !== -1;
        });
    }

    function prepareSvgMarkup(rawSvg) {
        var doc = new DOMParser().parseFromString(rawSvg, 'image/svg+xml');
        var svgEl = doc.querySelector('svg');

        if (!svgEl || doc.querySelector('parsererror')) {
            return '';
        }

        if (!svgEl.hasAttribute('preserveAspectRatio')) {
            svgEl.setAttribute('preserveAspectRatio', 'none');
        }

        svgEl.querySelectorAll('path, polygon, rect, circle, ellipse, g').forEach(function (node) {
            var fill = node.getAttribute('fill');

            if (fill && fill.toLowerCase() === 'none') {
                return;
            }

            node.removeAttribute('fill');
            node.classList.add('elementor-shape-fill');
        });

        return new XMLSerializer().serializeToString(svgEl);
    }

    function loadSvgMarkup(url) {
        if (svgCache.has(url)) {
            return svgCache.get(url);
        }

        var promise = fetch(url, { credentials: 'same-origin' })
            .then(function (res) {
                return res.ok ? res.text() : '';
            })
            .then(function (text) {
                return text ? prepareSvgMarkup(text) : '';
            })
            .catch(function () {
                return '';
            });

        svgCache.set(url, promise);

        return promise;
    }

    $.ajaxPrefilter(function (options, originalOptions, jqXHR) {
        if (isPlaceholderRequest(options.url)) {
            jqXHR.abort();
        }
    });

    function getShapeWrapper($element, side) {
        return $element.find('> .elementor-shape-' + side + ', > .e-con-inner > .elementor-shape-' + side).first();
    }

    function syncSide($element, settings, side) {
        var $wrapper = getShapeWrapper($element, side);

        if (!$wrapper.length || settings['shape_divider_' + side] !== 'custom_svg') {
            return;
        }

        var media = settings['shape_divider_' + side + '_custom_svg'];
        var url = media && media.url ? media.url : '';

        if (!url) {
            $wrapper.empty().removeData('lakitShapeUrl');
            return;
        }

        Promise.resolve(loadSvgMarkup(url)).then(function (markup) {
            var $current = getShapeWrapper($element, side);

            if (!$current.length || !markup) {
                return;
            }

            $current.html(markup).data('lakitShapeUrl', url);
        });
    }

    $(window).on('elementor/frontend/init', function () {
        if (!window.elementorFrontend || !elementorFrontend.isEditMode() || !window.elementorModules) {
            return;
        }

        var CustomShapeUploadHandler = class extends elementorModules.frontend.handlers.Base {
            onInit() {
                super.onInit();
                this.syncShapes();
            }

            onElementChange(propertyName) {
                if (propertyName && propertyName.indexOf('shape_divider_') === 0) {
                    this.syncShapes();
                }
            }

            syncShapes() {
                var settings = this.getElementSettings();

                ['top', 'bottom'].forEach(function (side) {
                    syncSide(this.$element, settings, side);
                }, this);
            }
        };

        var register = function ($element) {
            elementorFrontend.elementsHandler.addHandler(CustomShapeUploadHandler, { $element: $element });
        };

        elementorFrontend.hooks.addAction('frontend/element_ready/container', register, 999);
        elementorFrontend.hooks.addAction('frontend/element_ready/section', register, 999);
    });
})(jQuery);
