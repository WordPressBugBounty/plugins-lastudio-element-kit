;(function( $ ) {
    "use strict";
    var $window = $(window);

    $window.on('elementor/frontend/init', function() {

        var ModuleHandler = elementorModules.frontend.handlers.Base,
            TOCHandler;

        TOCHandler = ModuleHandler.extend({
            getDefaultSettings: function () {
                const elementSettings = this.getElementSettings(),
                    listWrapperTag = 'numbers' === elementSettings.marker_view ? 'ol' : 'ul';
                return {
                    selectors: {
                        widgetContainer: '.elementor-widget-container',
                        postContentContainer: '.elementor:not([data-elementor-type="header"]):not([data-elementor-type="footer"]):not([data-elementor-type="popup"])',
                        expandButton: '.lakit-toc__toggle-button--expand',
                        collapseButton: '.lakit-toc__toggle-button--collapse',
                        body: '.lakit-toc__body',
                        headerTitle: '.lakit-toc__header-title'
                    },
                    classes: {
                        anchor: 'elementor-menu-anchor',
                        listWrapper: 'lakit-toc__list-wrapper',
                        listItem: 'lakit-toc__list-item',
                        listTextWrapper: 'lakit-toc__list-item-text-wrapper',
                        firstLevelListItem: 'lakit-toc__top-level',
                        listItemText: 'lakit-toc__list-item-text',
                        activeItem: 'elementor-item-active',
                        headingAnchor: 'lakit-toc__heading-anchor',
                        collapsed: 'lakit-toc--collapsed'
                    },
                    listWrapperTag
                };
            },

            getDefaultElements: function () {
                const settings = this.getSettings();
                return {
                    $pageContainer: this.getContainer(),
                    $widgetContainer: this.$element.find(settings.selectors.widgetContainer),
                    $expandButton: this.$element.find(settings.selectors.expandButton),
                    $collapseButton: this.$element.find(settings.selectors.collapseButton),
                    $tocBody: this.$element.find(settings.selectors.body),
                    $listItems: this.$element.find('.' + settings.classes.listItem)
                };
            },
            getContainer: function() {
                const elementSettings = this.getElementSettings();

                // If there is a custom container defined by the user, use it as the headings-scan container
                if (elementSettings.container) {
                    return jQuery(elementSettings.container);
                }

                // Get the document wrapper element in which the TOC is located
                const $documentWrapper = this.$element.parents('.elementor');

                // If the TOC container is a popup, only scan the popup for headings
                if ('popup' === $documentWrapper.attr('data-elementor-type')) {
                    return $documentWrapper;
                }

                // If the TOC container is anything other than a popup, scan only the post/page content for headings
                const settings = this.getSettings();
                return jQuery(settings.selectors.postContentContainer);
            },
            bindEvents: function() {
                const settings = this.getSettings(),
                    elementSettings = this.getElementSettings();

                console.log({
                    settings,
                    elementSettings
                })

                if (elementSettings.minimize_box) {
                    this.elements.$expandButton.on('click', () => this.expandBox()).on('keyup', event => this.triggerClickOnEnterSpace(event));
                    this.elements.$collapseButton.on('click', () => this.collapseBox()).on('keyup', event => this.triggerClickOnEnterSpace(event));
                }
                if (elementSettings.collapse_subitems) {
                    console.log(this.$element)
                    console.log(this.getDefaultElements())
                    this.elements.$listItems.on('hover', event => jQuery(event.target).slideToggle());
                }
            },
            getHeadings: function() {
                // Get all headings from document by user-selected tags
                const elementSettings = this.getElementSettings(),
                    tags = elementSettings.headings_by_tags.join(','),
                    selectors = this.getSettings('selectors'),
                    excludedSelectors = elementSettings.exclude_headings_by_selector;
                return this.elements.$pageContainer.find(tags).not(selectors.headerTitle).filter((index, heading) => {
                    return !jQuery(heading).closest(excludedSelectors).length; // Handle excluded selectors if there are any
                });
            },
            addAnchorsBeforeHeadings: function() {
                const classes = this.getSettings('classes');

                // Add an anchor element right before each TOC heading to create anchors for TOC links
                this.elements.$headings.before(index => {
                    // Check if the heading element itself has an ID, or if it is a widget which includes a main heading element, whether the widget wrapper has an ID
                    if (jQuery(this.elements.$headings[index]).data('hasOwnID')) {
                        return;
                    }
                    return `<span id="${classes.headingAnchor}-${index}" class="${classes.anchor} "></span>`;
                });
            },
            activateItem: function($listItem) {
                const classes = this.getSettings('classes');
                this.deactivateActiveItem($listItem);
                $listItem.addClass(classes.activeItem);
                this.$activeItem = $listItem;
                if (!this.getElementSettings('collapse_subitems')) {
                    return;
                }
                let $activeList;
                if ($listItem.hasClass(classes.firstLevelListItem)) {
                    $activeList = $listItem.parent().next();
                } else {
                    $activeList = $listItem.parents('.' + classes.listWrapper).eq(-2);
                }
                if (!$activeList.length) {
                    delete this.$activeList;
                    return;
                }
                this.$activeList = $activeList;
                this.$activeList.stop().slideDown();
            },
            deactivateActiveItem: function($activeToBe) {
                if (!this.$activeItem || this.$activeItem.is($activeToBe)) {
                    return;
                }
                const {
                    classes
                } = this.getSettings();
                this.$activeItem.removeClass(classes.activeItem);
                if (this.$activeList && (!$activeToBe || !this.$activeList[0].contains($activeToBe[0]))) {
                    this.$activeList.slideUp();
                }
            },
            followAnchor: function($element, index) {
                const anchorSelector = $element[0].hash;
                let $anchor;
                try {
                    // `decodeURIComponent` for UTF8 characters in the hash.
                    $anchor = jQuery(decodeURIComponent(anchorSelector));
                } catch (e) {
                    return;
                }
                const observerOptions = {
                    rootMargin: '0px',
                    threshold: 0
                };
                const observer = this.createObserver(anchorSelector, $anchor, observerOptions, $element, index);
                observer.observe($anchor[0]);
            },
            createObserver: function(anchorSelector, $anchor, options, $element, index) {
                let lastScrollTop = 0;
                return new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        const scrollTop = document.documentElement.scrollTop,
                            isScrollingDown = scrollTop > lastScrollTop,
                            id = $anchor.attr('id');
                        if (entry.isIntersecting && !this.itemClicked) {
                            this.viewportItems[id] = true;
                            this.activateItem($element);
                        } else if (entry.isIntersecting && isScrollingDown) {
                            delete this.viewportItems[id];
                            if (Object.keys(this.viewportItems).length) {
                                this.activateItem(this.$listItemTexts.eq(index + 1));
                            }
                        } else if (!isScrollingDown) {
                            delete this.viewportItems[id];
                            this.activateItem(this.$listItemTexts.eq(index - 1));
                        }
                        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                    });
                }, options);
            },
            followAnchors: function() {
                this.$listItemTexts.each((index, element) => this.followAnchor(jQuery(element), index));
            },
            populateTOC: function() {
                this.listItemPointer = 0;
                const elementSettings = this.getElementSettings();
                if (elementSettings.hierarchical_view) {
                    this.createNestedList();
                } else {
                    this.createFlatList();
                }
                this.$listItemTexts = this.$element.find('.lakit-toc__list-item-text');
                this.$listItemTexts.on('click', this.onListItemClick.bind(this));
                if (!elementorFrontend.isEditMode()) {
                    this.followAnchors();
                }
            },
            createNestedList: function() {
                this.headingsData.forEach((heading, index) => {
                    heading.level = 0;
                    for (let i = index - 1; i >= 0; i--) {
                        const currentOrderedItem = this.headingsData[i];
                        if (currentOrderedItem.tag <= heading.tag) {
                            heading.level = currentOrderedItem.level;
                            if (currentOrderedItem.tag < heading.tag) {
                                heading.level++;
                            }
                            break;
                        }
                    }
                });
                this.elements.$tocBody.html(this.getNestedLevel(0));
            },
            createFlatList: function() {
                this.elements.$tocBody.html(this.getNestedLevel());
            },
            getNestedLevel: function(level) {
                const settings = this.getSettings(),
                    elementSettings = this.getElementSettings(),
                    icon = this.getElementSettings('icon');
                let renderedIcon;
                if (icon) {
                    // We generate the icon markup in PHP and make it available via get_frontend_settings(). As a result, the
                    // rendered icon is not available in the editor, so in the editor we use the regular <i> tag.
                    if (elementorFrontend.config.experimentalFeatures.e_font_icon_svg && !elementorFrontend.isEditMode()) {
                        renderedIcon = typeof icon.rendered_tag !== 'undefined' ? icon.rendered_tag : '';
                    } else {
                        renderedIcon = icon.value ? `<i class="${this.sanitizeAttributeValue(icon.value)}"></i>` : '';
                    }
                }

                // Open new list/nested list
                let html = `<${settings.listWrapperTag} class="${this.sanitizeAttributeValue(settings.classes.listWrapper)}">`;

                // For each list item, build its markup.
                while (this.listItemPointer < this.headingsData.length) {
                    const currentItem = this.headingsData[this.listItemPointer];
                    let listItemTextClasses = settings.classes.listItemText;
                    if (0 === currentItem.level) {
                        // If the current list item is a top level item, give it the first level class
                        listItemTextClasses += ' ' + settings.classes.firstLevelListItem;
                    }
                    if (level > currentItem.level) {
                        break;
                    }
                    if (level === currentItem.level) {
                        html += `<li class="${settings.classes.listItem}">`;
                        html += `<div class="${settings.classes.listTextWrapper}">`;
                        let liContent = `<a href="#${this.sanitizeAttributeValue(currentItem.anchorLink)}" class="${this.sanitizeAttributeValue(listItemTextClasses)}">${this.sanitizeHTML(currentItem.text)}</a>`;

                        // If list type is bullets, add the bullet icon as an <i> tag
                        if ('bullets' === elementSettings.marker_view && icon) {
                            liContent = `${renderedIcon}${liContent}`;
                        }
                        // liContent = _dompurify.default.sanitize(liContent);
                        html += liContent;
                        html += '</div>';
                        this.listItemPointer++;
                        const nextItem = this.headingsData[this.listItemPointer];
                        if (nextItem && level < nextItem.level) {
                            // If a new nested list has to be created under the current item,
                            // this entire method is called recursively (outside the while loop, a list wrapper is created)
                            html += this.getNestedLevel(nextItem.level);
                        }
                        html += '</li>';
                    }
                }
                html += `</${settings.listWrapperTag}>`;
                return html;
            },
            handleNoHeadingsFound: function() {
                const noHeadingsText = 'No headings were found on this page.';
                return this.elements.$tocBody.html(noHeadingsText);
            },
            collapseBodyListener: function() {
                const activeBreakpoints = elementorFrontend.breakpoints.getActiveBreakpointsList({
                    withDesktop: true
                });
                const minimizedOn = this.getElementSettings('minimized_on'),
                    currentDeviceMode = elementorFrontend.getCurrentDeviceMode(),
                    isCollapsed = this.$element.hasClass(this.getSettings('classes.collapsed'));

                // If minimizedOn value is set to desktop, it applies for widescreen as well.
                if ('desktop' === minimizedOn || activeBreakpoints.indexOf(minimizedOn) >= activeBreakpoints.indexOf(currentDeviceMode)) {
                    if (!isCollapsed) {
                        this.collapseBox(false);
                    }
                } else if (isCollapsed) {
                    this.expandBox(false);
                }
            },
            onElementChange: function(settings) {
                if ('minimized_on' === settings) {
                    this.collapseBodyListener();
                }
            },
            getHeadingAnchorLink: function(index, classes) {
                const headingID = this.elements.$headings[index].id,
                    wrapperID = this.elements.$headings[index].closest('.elementor-widget').id;
                let anchorLink = '';
                if (headingID) {
                    anchorLink = headingID;
                } else if (wrapperID) {
                    // If the heading itself has an ID, we don't want to overwrite it
                    anchorLink = wrapperID;
                }

                // If there is no existing ID, use the heading text to create a semantic ID
                if (headingID || wrapperID) {
                    jQuery(this.elements.$headings[index]).data('hasOwnID', true);
                } else {
                    anchorLink = `${classes.headingAnchor}-${index}`;
                }
                return anchorLink;
            },
            setHeadingsData: function() {
                this.headingsData = [];
                const classes = this.getSettings('classes');

                // Create an array for simplifying TOC list creation
                this.elements.$headings.each((index, element) => {
                    const anchorLink = this.getHeadingAnchorLink(index, classes);
                    this.headingsData.push({
                        tag: +element.nodeName.slice(1),
                        text: element.textContent,
                        anchorLink
                    });
                });
            },
            run: function() {
                this.elements.$headings = this.getHeadings();
                if (!this.elements.$headings.length) {
                    return this.handleNoHeadingsFound();
                }
                this.setHeadingsData();
                if (!elementorFrontend.isEditMode()) {
                    this.addAnchorsBeforeHeadings();
                }
                this.populateTOC();
                if (this.getElementSettings('minimize_box')) {
                    this.collapseBodyListener();
                }
            },
            expandBox: function() {
                let changeFocus = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
                const boxHeight = this.getCurrentDeviceSetting('min_height');
                this.$element.removeClass(this.getSettings('classes.collapsed'));
                this.elements.$tocBody.slideDown();
                this.elements.$expandButton.attr('aria-expanded', 'true');
                this.elements.$collapseButton.attr('aria-expanded', 'true');

                // Return container to the full height in case a min-height is defined by the user
                this.elements.$widgetContainer.css('min-height', boxHeight.size + boxHeight.unit);
                if (changeFocus) {
                    this.elements.$collapseButton.trigger('focus');
                }
            },
            collapseBox: function() {
                let changeFocus = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
                this.$element.addClass(this.getSettings('classes.collapsed'));
                this.elements.$tocBody.slideUp();
                this.elements.$expandButton.attr('aria-expanded', 'false');
                this.elements.$collapseButton.attr('aria-expanded', 'false');

                // Close container in case a min-height is defined by the user
                this.elements.$widgetContainer.css('min-height', '0px');
                if (changeFocus) {
                    this.elements.$expandButton.trigger('focus');
                }
            },
            triggerClickOnEnterSpace: function(event) {
                const ENTER_KEY = 13,
                    SPACE_KEY = 32;
                if (ENTER_KEY === event.keyCode || SPACE_KEY === event.keyCode) {
                    event.currentTarget.click();
                    event.stopPropagation();
                }
            },
            onInit: function() {
                ModuleHandler.prototype.onInit.apply(this, arguments);
                this.viewportItems = [];
                jQuery(() => this.run());
            },
            onListItemClick: function(event) {
                this.itemClicked = true;
                setTimeout(() => this.itemClicked = false, 2000);
                const $clickedItem = jQuery(event.target),
                    $list = $clickedItem.parent().next(),
                    collapseNestedList = this.getElementSettings('collapse_subitems');
                let listIsActive;
                if (collapseNestedList && $clickedItem.hasClass(this.getSettings('classes.firstLevelListItem'))) {
                    if ($list.is(':visible')) {
                        listIsActive = true;
                    }
                }
                this.activateItem($clickedItem);
                if (collapseNestedList && listIsActive) {
                    $list.slideUp();
                }
            },
            sanitizeHTMLAttributes: function ( html ){
                return html.replace(/on[a-z]+=("|).*?.*("|)([^>]|\s+)/gi, '');
            },
            sanitizeHTML: function (str){
                return str.replace(/[^\w. ]/gi, function (c) {
                    return '&#' + c.charCodeAt(0) + ';';
                });
            },
            sanitizeAttributeValue: function ( value ){
                return value.replace(/[^\w\s-:]/gi, '');
            }
        })

        elementorFrontend.elementsHandler.attachHandler( 'lakit-table-of-contents', TOCHandler );
    });

}( jQuery ));