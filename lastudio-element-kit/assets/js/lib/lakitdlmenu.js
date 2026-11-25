(function ($) {
    'use strict';

    $.LakitDLMenu = function (options, element) {
        this.$el = $(element);
        this._init(options);
    };

    $.LakitDLMenu.defaults = {
        animationClasses: {
            classin: 'lakitdl-animate-in-1',
            classout: 'lakitdl-animate-out-1'
        },
        onLevelClick: () => { return false; },
        backLabel: '',
        triggerIcon: '>',
        useActiveItemAsBackLabel: false,
        useActiveItemAsLink: false,
        resetOnClose: true,
        supportAnimations: true,
        timing: 300
    };

    $.LakitDLMenu.prototype = {
        _init: function (options) {
            this.options = $.extend(true, {}, $.LakitDLMenu.defaults, options);
            this.eId = this.$el.closest('.elementor-element').data('id') || '';
            this.animEndEventName = 'animationend.lakitdlmenu';
            this.transEndEventName = 'transitionend.lakitdlmenu';
            this.supportAnimations = this.options.supportAnimations;
            this.isAnimating = false;

            this._config();
            this._initEvents();
        },

        _config: function () {
            const opts = this.options;
            const eId = this.eId;

            this.open = false;
            this.$menu = this.$el.children('.lakit-nav.lakitdl-menu');
            this.$menuitems = this.$menu.find('.menu-item:not(.lakitdl-back)');

            this.$menu.find('.lakitdl-back, .trigger-dlmenu, .lakitdl-parent').remove();

            const $submenus = this.$el.find('.lakitdl-submenu');

            $submenus.prepend(
                `<div class="menu-item lakit-nav__item lakit-nav-id-${eId} lakitdl-back">` +
                `<button type="button" class="menu-item-link menu-item-link-sub">${opts.backLabel}</button>` +
                `</div>`
            );

            $submenus.parent().append(
                `<button type="button" class="trigger-dlmenu" aria-label="Open submenu">${opts.triggerIcon}</button>`
            );

            this.$back = this.$menu.find('.menu-item.lakitdl-back');
            this.$itemtrigger = this.$menuitems.find('.trigger-dlmenu');

            if (opts.useActiveItemAsBackLabel) {
                this.$back.each(function () {
                    const $this = $(this);
                    const parentLabel = $this.closest('.menu-item:not(.lakitdl-back)').children('a:first').text();
                    $this.children('button').text(parentLabel);
                });
            }

            if (opts.useActiveItemAsLink) {
                this.$el.find('.lakit-nav__sub.lakitdl-submenu').prepend(function () {
                    const $parentLink = $(this).closest('.menu-item:not(.lakitdl-back)').children('a:first');
                    return `<div class="menu-item lakit-nav__item lakitdl-parent">` +
                        `<a class="menu-item-link" href="${$parentLink.attr('href') || '#'}">${$parentLink.text()}</a>` +
                        `</div>`;
                });
            }
        },

        _initEvents: function () {
            const self = this;

            this.$menu.off('.lakitdlmenu');

            this.$menu.on('click.lakitdlmenu', '.trigger-dlmenu', function (e) {
                e.preventDefault();
                if (self.isAnimating) return;

                const $item = $(this).parent();
                const $submenu = $item.children('.lakit-nav__sub.lakitdl-submenu');

                if ($submenu.length && !$item.hasClass('lakitdl-subviewopen')) {
                    self.isAnimating = true;
                    const $clone = self._createClone($submenu).css('opacity', 0);

                    self._runAnimation($clone, self.options.animationClasses.classin, self.options.animationClasses.classout, function () {
                        self.$menu.addClass('lakitdl-subview');
                        $item.addClass('lakitdl-subviewopen')
                            .parents('.lakitdl-subviewopen:first')
                            .removeClass('lakitdl-subviewopen')
                            .addClass('lakitdl-subview');
                        self.isAnimating = false;
                    });

                    self.options.onLevelClick($item, $item.children('a:first').text());
                }
            });

            this.$menu.on('click.lakitdlmenu', '.lakitdl-back', function (e) {
                e.preventDefault();
                if (self.isAnimating) return;

                self.isAnimating = true;
                const $this = $(this);
                const $submenu = $this.closest('.lakit-nav__sub.lakitdl-submenu');
                const $item = $submenu.parent();
                const $clone = self._createClone($submenu);

                requestAnimationFrame(function () {
                    self._runAnimation($clone, self.options.animationClasses.classout, self.options.animationClasses.classin, function () {
                        self.isAnimating = false;
                    });

                    $item.removeClass('lakitdl-subviewopen');

                    const $subview = $this.closest('.lakitdl-subview');
                    if ($subview.is('.menu-item')) {
                        $subview.addClass('lakitdl-subviewopen');
                    }
                    $subview.removeClass('lakitdl-subview');
                });
            });
        },

        _createClone: function ($submenu) {
            return $('<div/>')
                .addClass(`lakit-nav--clone lakit-nav lakit-nav-${this.eId} lakit-nav-id-${this.eId}`)
                .append($submenu.clone())
                .insertAfter(this.$menu);
        },

        _runAnimation: function ($clone, cloneClass, menuClass, callback) {
            const self = this;

            const onEnd = function () {
                self.$menu.off(self.animEndEventName).removeClass(menuClass);
                $clone.remove();
                if (typeof callback === 'function') callback();
            };

            $clone.addClass(cloneClass);
            this.$menu.addClass(menuClass);

            if (this.supportAnimations) {
                this.$menu.one(this.animEndEventName, onEnd);
            } else {
                setTimeout(onEnd, this.options.timing);
            }
        },

        _resetMenu: function () {
            this.$menu.removeClass('lakitdl-subview');
            this.$menuitems.removeClass('lakitdl-subview lakitdl-subviewopen');
        },

        destroyMenu: function () {
            this.$el.removeClass('lakitdl-menuwrapper');
            this.$menu.removeClass('lakitdl-menu lakitdl-menuopen lakitdl-subview').off('.lakitdlmenu');
            this.$menuitems.removeClass('lakitdl-subview lakitdl-subviewopen');
            this.$menu.find('.lakitdl-back, .trigger-dlmenu, .lakitdl-parent').remove();
            this.$el.find('> .lakit-nav__sub, > .lakit-nav--clone').remove();
            this.$el.removeData('lakitdlmenu');
        }
    };

    $.fn.lakitdlmenu = function (options) {
        return this.each(function () {
            const instance = $.data(this, 'lakitdlmenu');
            if (instance) {
                instance._init(options);
            } else {
                $.data(this, 'lakitdlmenu', new $.LakitDLMenu(options, this));
            }
        });
    };

})(jQuery);