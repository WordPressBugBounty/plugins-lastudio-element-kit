/**
 * LA-Studio Kit - Loop Builder frontend handlers.
 *
 */
( function( $ ) {
	'use strict';

	const __ = ( window.wp && window.wp.i18n ) ? window.wp.i18n.__ : ( text ) => text;

	const SKINS = [ 'post', 'product', 'post_taxonomy', 'product_taxonomy' ];
	const DOC_TYPE = 'lakit-loop-item';
	const AFTER_INSERT_EVENT = 'lastudio-kit/loop-builder/after-insert-posts';

	function runElementHandlers( elements ) {
		[ ...elements ]
			.flatMap( ( el ) => [ ...el.querySelectorAll( '.elementor-element' ) ] )
			.forEach( ( el ) => elementorFrontend.elementsHandler.runReadyTrigger( el ) );
	}

	function maybeObserveLazyLoad() {
		const proConfig = window.ElementorProFrontendConfig;
		if ( proConfig && proConfig.settings && proConfig.settings.lazy_load_background_images ) {
			document.dispatchEvent( new Event( 'elementor/lazyload/observe' ) );
		}
	}

	function getResponsiveControlValue( settings, key, subKey = '' ) {
		return elementorFrontend.utils.controls.getResponsiveControlValue( settings, key, subKey );
	}

	/* ------------------------------------------------------------------
	 * Document handles (Edit template / Save & Back) - editor only.
	 * ---------------------------------------------------------------- */
	const EDIT_HANDLE_CLASS_NAME = 'elementor-document-handle';
	const EDIT_MODE_CLASS_NAME = 'elementor-edit-mode';
	const EDIT_CONTEXT = 'edit';
	const SAVE_HANDLE_CLASS_NAME = 'elementor-document-save-back-handle';
	const SAVE_CONTEXT = 'save';

	function createElement( { tag, classNames = [], children = [] } ) {
		const element = document.createElement( tag );
		element.classList.add( ...classNames );
		children.forEach( ( child ) => element.appendChild( child ) );
		return element;
	}

	function getHandleIcon( context ) {
		if ( SAVE_CONTEXT === context ) {
			return elementorFrontend.config.is_rtl ? 'eicon-arrow-right' : 'eicon-arrow-left';
		}
		return 'eicon-edit';
	}

	function createHandleElement( { title, onClick }, context ) {
		const text = EDIT_CONTEXT === context
			? __( 'Edit %s', 'lastudio-kit' ).replace( '%s', title )
			: __( 'Save %s', 'lastudio-kit' ).replace( '%s', title );

		const innerElement = createElement( {
			tag: 'div',
			classNames: [ `${ EDIT_HANDLE_CLASS_NAME }__inner` ],
			children: [
				createElement( { tag: 'i', classNames: [ getHandleIcon( context ) ] } ),
				createElement( {
					tag: 'div',
					classNames: [ `${ EDIT_CONTEXT === context ? EDIT_HANDLE_CLASS_NAME : SAVE_HANDLE_CLASS_NAME }__title` ],
					children: [ document.createTextNode( text ) ],
				} ),
			],
		} );

		const classNames = [ EDIT_HANDLE_CLASS_NAME, 'lakit-loop-document-handle' ];
		if ( EDIT_CONTEXT !== context ) {
			classNames.push( SAVE_HANDLE_CLASS_NAME );
		}

		const containerElement = createElement( { tag: 'div', classNames, children: [ innerElement ] } );
		containerElement.addEventListener( 'click', onClick );
		return containerElement;
	}

	async function onDocumentClick( id, context, onCloseDocument = null, selector = null ) {
		const topWindow = window.top;

		if ( EDIT_CONTEXT === context ) {
			topWindow.$e.internal( 'panel/state-loading' );
			await topWindow.$e.run( 'editor/documents/switch', {
				id: parseInt( id ),
				onClose: onCloseDocument,
				selector,
			} );
			topWindow.$e.internal( 'panel/state-ready' );
		} else {
			topWindow.$e.internal( 'panel/state-loading' );
			topWindow.$e.run( 'editor/documents/switch', {
				id: topWindow.elementor.config.initial_document.id,
				mode: 'save',
				shouldScroll: false,
				selector,
			} ).finally( () => topWindow.$e.internal( 'panel/state-ready' ) );
		}
	}

	function addDocumentHandle( { element, id, title = __( 'Template', 'lastudio-kit' ) }, context = EDIT_CONTEXT, onCloseDocument = null, selector = null ) {
		if ( EDIT_CONTEXT === context ) {
			if ( ! id || ! element ) {
				return;
			}

			if ( element.classList.contains( EDIT_MODE_CLASS_NAME ) || element.querySelector( `:scope > .${ EDIT_HANDLE_CLASS_NAME }` ) ) {
				return;
			}
		}

		const handleElement = createHandleElement( {
			title,
			onClick: () => onDocumentClick( id, context, onCloseDocument, selector ),
		}, context );

		element.prepend( handleElement );

		if ( EDIT_CONTEXT === context ) {
			element.dataset.editableElementorDocument = id;
		}
	}

	window.LaStudioKitLoopDocumentHandle = { addDocumentHandle, EDIT_CONTEXT, SAVE_CONTEXT };

	/* ------------------------------------------------------------------
	 * Posts handler (masonry) - port of Pro posts.js
	 * ---------------------------------------------------------------- */
	const PostsHandler = elementorModules.frontend.handlers.Base.extend( {
		getSkinPrefix() {
			return '';
		},

		bindEvents() {
			elementorFrontend.addListenerOnce( this.getModelCID(), 'resize', this.onWindowResize );
		},

		unbindEvents() {
			elementorFrontend.removeListeners( this.getModelCID(), 'resize', this.onWindowResize );
		},

		getClosureMethodsNames() {
			return elementorModules.frontend.handlers.Base.prototype.getClosureMethodsNames.apply( this, arguments ).concat( [ 'fitImages', 'onWindowResize', 'runMasonry' ] );
		},

		getDefaultSettings() {
			return {
				classes: {
					fitHeight: 'elementor-fit-height',
					hasItemRatio: 'elementor-has-item-ratio',
				},
				selectors: {
					postsContainer: '.elementor-posts-container',
					post: '.elementor-post',
					postThumbnail: '.elementor-post__thumbnail',
					postThumbnailImage: '.elementor-post__thumbnail img',
				},
			};
		},

		getDefaultElements() {
			const selectors = this.getSettings( 'selectors' );
			return {
				$postsContainer: this.$element.find( selectors.postsContainer ),
				$posts: this.$element.find( selectors.post ),
			};
		},

		fitImages() {},

		setColsCountSettings() {
			const settings = this.getElementSettings();
			const colsCount = getResponsiveControlValue( settings, `${ this.getSkinPrefix() }columns` );
			this.setSettings( 'colsCount', colsCount );
		},

		isMasonryEnabled() {
			return !! this.getElementSettings( this.getSkinPrefix() + 'masonry' );
		},

		initMasonry() {
			if ( typeof window.imagesLoaded === 'function' ) {
				window.imagesLoaded( this.elements.$posts, this.runMasonry );
			} else {
				this.runMasonry();
			}
		},

		getVerticalSpaceBetween() {
			return getResponsiveControlValue( this.getElementSettings(), `${ this.getSkinPrefix() }row_gap`, 'size' );
		},

		runMasonry() {
			const elements = this.elements;

			elements.$posts.css( { marginTop: '', transitionDuration: '' } );

			this.setColsCountSettings();

			const colsCount = this.getSettings( 'colsCount' );
			const hasMasonry = this.isMasonryEnabled() && colsCount >= 2;

			elements.$postsContainer.toggleClass( 'elementor-posts-masonry', hasMasonry );

			if ( ! hasMasonry ) {
				elements.$postsContainer.height( '' );
				return;
			}

			new elementorModules.utils.Masonry( {
				container: elements.$postsContainer,
				items: elements.$posts.filter( ':visible' ),
				columnsCount: colsCount,
				verticalSpaceBetween: this.getVerticalSpaceBetween() || 0,
			} ).run();
		},

		run() {
			setTimeout( this.fitImages, 0 );
			this.initMasonry();
		},

		onInit() {
			elementorModules.frontend.handlers.Base.prototype.onInit.apply( this, arguments );
			this.bindEvents();
			this.run();
		},

		onWindowResize() {
			this.fitImages();
			this.runMasonry();
		},

		onElementChange() {
			this.fitImages();
			setTimeout( this.runMasonry );
		},
	} );

	/* ------------------------------------------------------------------
	 * Loop handler - port of Pro loop.js
	 * ---------------------------------------------------------------- */
	class LoopHandler extends PostsHandler {
		getDefaultSettings() {
			const defaultSettings = super.getDefaultSettings();
			defaultSettings.selectors.post = '.elementor-loop-container .elementor';
			defaultSettings.selectors.postsContainer = '.elementor-loop-container';
			defaultSettings.classes.inPlaceTemplateEditable = 'elementor-in-place-template-editable';
			return defaultSettings;
		}

		getVerticalSpaceBetween() {
			return getResponsiveControlValue( this.getElementSettings(), 'row_gap', 'size' );
		}

		onInPlaceEditTemplate() {
			this.$element.addClass( this.getDefaultSettings().classes.inPlaceTemplateEditable );
			this.elementsToRemove = [];
			this.handleSwiper();

			const templateID = this.getElementSettings( 'template_id' );

			this.elementsToRemove = [
				...this.elementsToRemove,
				'style#loop-' + templateID,
				'link#font-loop-' + templateID,
				'style#loop-dynamic-' + templateID,
			];

			this.elementsToRemove.forEach( ( elementToRemove ) => {
				this.$element.find( elementToRemove ).remove();
			} );
		}

		handleSwiper() {
			const swiper = this.elements.$postsContainer.data( 'swiper' );

			if ( ! swiper ) {
				return;
			}

			swiper.slideTo( 0 );
			if ( swiper.autoplay ) {
				swiper.autoplay.pause();
			}
			swiper.allowTouchMove = false;
			if ( swiper.params.autoplay ) {
				swiper.params.autoplay.delay = 1000000;
			}
			swiper.update();

			this.elementsToRemove = [
				...this.elementsToRemove,
				'.swiper-pagination',
				'.elementor-swiper-button',
				'.elementor-document-handle',
			];
		}

		attachEditDocumentHandle() {
			const templateId = this.getElementSettings( 'template_id' );

			if ( ! templateId ) {
				return;
			}

			const defaultSelector = `[data-elementor-type="${ DOC_TYPE }"]`;
			const handleSelector = this.getElementSettings( 'edit_handle_selector' ) || defaultSelector;
			const widgetSelector = `.elementor-element-${ this.getID() }`;
			const editHandleSelector = handleSelector + ( defaultSelector === handleSelector ? `.elementor-${ templateId }` : '' );
			const editHandleElement = this.$element.find( editHandleSelector ).first()[ 0 ];

			if ( ! editHandleElement ) {
				return;
			}

			if ( this.isFirstEdit() ) {
				this.$element.find( '.elementor-swiper-button' ).remove();
				return;
			}

			addDocumentHandle( {
				element: editHandleElement,
				title: __( 'Template', 'lastudio-kit' ),
				id: templateId,
			}, EDIT_CONTEXT, () => this.onInPlaceEditTemplate(), `${ widgetSelector } .elementor-${ templateId }` );
		}

		isFirstEdit() {
			return this.$element.has( '.e-loop-first-edit' ).length;
		}

		getEditorModule() {
			return window.top && window.top.LaStudioKitLoopBuilder ? window.top.LaStudioKitLoopBuilder : null;
		}

		handleCTA() {
			const emptyViewContainer = document.querySelector( `[data-id="${ this.getID() }"] .e-loop-empty-view__wrapper` );
			const editorModule = this.getEditorModule();

			if ( ! emptyViewContainer || ! editorModule || emptyViewContainer.shadowRoot ) {
				return;
			}

			const shadowRoot = emptyViewContainer.attachShadow( { mode: 'open' } );

			shadowRoot.appendChild( editorModule.getCtaStyles() );
			shadowRoot.appendChild( editorModule.getCtaContent( this.getWidgetType() ) );

			const cta = shadowRoot.querySelector( '.e-loop-empty-view__box-cta' );

			if ( cta ) {
				cta.addEventListener( 'click', ( event ) => {
					event.preventDefault();
					editorModule.createTemplate();
				} );
			}
		}

		doEditorInitAction() {
			if ( window.elementor && window.elementor.hooks ) {
				window.elementor.hooks.doAction( 'editor/widgets/lakit-loop-grid/on-init', this );
			}
		}

		onElementChange( control ) {
			const editorModule = this.getEditorModule();

			if ( '_skin' === control && editorModule ) {
				editorModule.onApplySkinChange();
			}

			PostsHandler.prototype.onElementChange.apply( this, arguments );
		}

		bindEvents() {
			super.bindEvents();
			this.onAfterInsertPosts = this.reInitMasonry.bind( this );
			elementorFrontend.elements.$window.on( AFTER_INSERT_EVENT, this.onAfterInsertPosts );
		}

		reInitMasonry() {
			const selectors = this.getSettings( 'selectors' );
			this.elements.$posts = $( `.elementor-element-${ this.getID() } ${ selectors.post }` );
			this.runMasonry();
		}

		unbindEvents() {
			super.unbindEvents();
			if ( this.onAfterInsertPosts ) {
				elementorFrontend.elements.$window.off( AFTER_INSERT_EVENT, this.onAfterInsertPosts );
			}
		}

		onInit( ...args ) {
			super.onInit( ...args );

			if ( elementorFrontend.isEditMode() ) {
				this.doEditorInitAction();
				this.attachEditDocumentHandle();
				this.handleCTA();
			}
		}
	}

	/* ------------------------------------------------------------------
	 * Load more - port of Pro posts load-more.js + loop load-more.js
	 * ---------------------------------------------------------------- */
	class LoadMoreHandler extends elementorModules.frontend.handlers.Base {
		getDefaultSettings() {
			return {
				selectors: {
					postsContainer: '.elementor-loop-container',
					postWrapperTag: '.e-loop-item',
					loadMoreButton: '.e-loop__load-more .elementor-button',
					loadMoreSpinnerWrapper: '.e-load-more-spinner',
					loadMoreSpinner: '.e-load-more-spinner i, .e-load-more-spinner svg',
					loadMoreAnchor: '.e-load-more-anchor',
					dynamicStyleElement: 'style[id^="loop-dynamic"]',
				},
				classes: {
					loadMoreSpin: 'eicon-animation-spin',
					loadMoreIsLoading: 'e-load-more-pagination-loading',
					loadMorePaginationEnd: 'e-load-more-pagination-end',
					loadMoreNoSpinner: 'e-load-more-no-spinner',
				},
			};
		}

		getDefaultElements() {
			const selectors = this.getSettings( 'selectors' );
			const element = this.$element[ 0 ];

			return {
				postsWidgetWrapper: element,
				postsContainer: element.querySelector( selectors.postsContainer ),
				loadMoreButton: element.querySelector( selectors.loadMoreButton ),
				loadMoreSpinnerWrapper: element.querySelector( selectors.loadMoreSpinnerWrapper ),
				loadMoreSpinner: element.querySelector( selectors.loadMoreSpinner ),
				loadMoreAnchor: element.querySelector( selectors.loadMoreAnchor ),
			};
		}

		bindEvents() {
			super.bindEvents();

			if ( ! this.elements.loadMoreButton ) {
				return;
			}

			this.elements.loadMoreButton.addEventListener( 'click', ( event ) => {
				if ( this.isLoading ) {
					return;
				}

				event.preventDefault();
				this.handlePostsQuery();
			} );
		}

		onInit() {
			super.onInit();

			this.classes = this.getSettings( 'classes' );
			this.isLoading = false;

			const paginationType = this.getElementSettings( 'pagination_type' );

			if ( 'load_more_on_click' !== paginationType && 'load_more_infinite_scroll' !== paginationType ) {
				return;
			}

			this.isInfinteScroll = 'load_more_infinite_scroll' === paginationType;

			const spinner = this.getElementSettings( 'load_more_spinner' );
			this.isSpinnerAvailable = spinner && spinner.value;

			if ( ! this.isSpinnerAvailable ) {
				this.elements.postsWidgetWrapper.classList.add( this.classes.loadMoreNoSpinner );
			}

			if ( this.isInfinteScroll ) {
				this.handleInfiniteScroll();
			} else if ( this.elements.loadMoreSpinnerWrapper && this.elements.loadMoreButton ) {
				this.elements.loadMoreButton.insertAdjacentElement( 'beforeEnd', this.elements.loadMoreSpinnerWrapper );
			}

			this.elementId = this.getID();

			if ( this.elements.loadMoreAnchor ) {
				this.currentPage = parseInt( this.elements.loadMoreAnchor.getAttribute( 'data-page' ) );
				this.maxPage = parseInt( this.elements.loadMoreAnchor.getAttribute( 'data-max-page' ) );

				if ( this.currentPage === this.maxPage || ! this.currentPage ) {
					this.handleUiWhenNoPosts();
				}
			}
		}

		handleInfiniteScroll() {
			if ( this.isEdit || ! this.elements.loadMoreAnchor ) {
				return;
			}

			this.observer = elementorModules.utils.Scroll.scrollObserver( {
				callback: ( event ) => {
					if ( ! event.isInViewport || this.isLoading ) {
						return;
					}

					this.observer.unobserve( this.elements.loadMoreAnchor );

					this.handlePostsQuery().then( () => {
						if ( this.currentPage !== this.maxPage ) {
							this.observer.observe( this.elements.loadMoreAnchor );
						}
					} );
				},
			} );

			this.observer.observe( this.elements.loadMoreAnchor );
		}

		handleUiBeforeLoading() {
			this.isLoading = true;

			if ( this.elements.loadMoreSpinner ) {
				this.elements.loadMoreSpinner.classList.add( this.classes.loadMoreSpin );
			}

			this.elements.postsWidgetWrapper.classList.add( this.classes.loadMoreIsLoading );
		}

		handleUiAfterLoading() {
			this.isLoading = false;

			if ( this.elements.loadMoreSpinner ) {
				this.elements.loadMoreSpinner.classList.remove( this.classes.loadMoreSpin );
			}

			if ( this.isInfinteScroll && this.elements.loadMoreSpinnerWrapper && this.elements.loadMoreAnchor ) {
				this.elements.loadMoreAnchor.insertAdjacentElement( 'afterend', this.elements.loadMoreSpinnerWrapper );
			}

			this.elements.postsWidgetWrapper.classList.remove( this.classes.loadMoreIsLoading );
		}

		handleUiWhenNoPosts() {
			this.elements.postsWidgetWrapper.classList.add( this.classes.loadMorePaginationEnd );
		}

		handleSuccessFetch( result ) {
			this.handleUiAfterLoading();

			const selectors = this.getSettings( 'selectors' );
			const postsElements = result.querySelectorAll( `[data-id="${ this.elementId }"] ${ selectors.postsContainer } > ${ selectors.postWrapperTag }` );
			const nextAnchor = result.querySelector( `[data-id="${ this.elementId }"] .e-load-more-anchor` );
			const nextPageUrl = nextAnchor ? nextAnchor.getAttribute( 'data-next-page' ) : '';

			postsElements.forEach( ( element ) => this.elements.postsContainer.append( element ) );

			this.elements.loadMoreAnchor.setAttribute( 'data-page', this.currentPage );
			this.elements.loadMoreAnchor.setAttribute( 'data-next-page', nextPageUrl );

			if ( this.currentPage === this.maxPage ) {
				this.handleUiWhenNoPosts();
			}

			this.afterInsertPosts( postsElements, result );
		}

		afterInsertPosts( postsElements, result ) {
			maybeObserveLazyLoad();
			this.handleDynamicStyleElements( result );
			runElementHandlers( postsElements );
			elementorFrontend.elements.$window.trigger( AFTER_INSERT_EVENT );
		}

		handleDynamicStyleElements( result ) {
			const selectors = this.getSettings( 'selectors' );
			const dynamicStyleElements = result.querySelectorAll( `[data-id="${ this.elementId }"] ${ selectors.dynamicStyleElement }` );
			this.$element.append( dynamicStyleElements );
		}

		handlePostsQuery() {
			this.handleUiBeforeLoading();
			this.currentPage++;

			const nextPageUrl = this.elements.loadMoreAnchor.getAttribute( 'data-next-page' );

			return fetch( nextPageUrl )
				.then( ( response ) => response.text() )
				.then( ( html ) => {
					const doc = new DOMParser().parseFromString( html, 'text/html' );
					this.handleSuccessFetch( doc );
				} );
		}
	}

	/* ------------------------------------------------------------------
	 * Ajax pagination - port of Pro ajax-pagination.js
	 * ---------------------------------------------------------------- */
	class AjaxPaginationHandler extends elementorModules.frontend.handlers.Base {
		getDefaultSettings() {
			return {
				selectors: {
					links: 'a.page-numbers:not(.current)',
					widgetContainer: '.elementor-widget-container',
					postWrapperTag: '.e-loop-item',
				},
			};
		}

		getDefaultElements() {
			const selectors = this.getSettings( 'selectors' );

			return {
				links: this.$element[ 0 ].querySelectorAll( selectors.links ),
				widgetContainer: this.$element[ 0 ].querySelector( selectors.widgetContainer ),
			};
		}

		bindEvents() {
			super.bindEvents();
			this.linksEventListeners();
		}

		linksEventListeners() {
			if ( ! this.elements.links.length || 'ajax' !== this.getElementSettings( 'pagination_load_type' ) ) {
				return;
			}

			this.onLinkClick = this.handleLinkClick.bind( this );

			this.elements.links.forEach( ( link ) => {
				link.addEventListener( 'click', this.onLinkClick );
			} );
		}

		handleLinkClick( event ) {
			event.preventDefault();

			if ( this.isLoading ) {
				return;
			}

			this.removeLinksListeners();
			this.handleUiBeforeLoading();

			const nextPageUrl = event.currentTarget.getAttribute( 'href' );

			this.updateURLQueryString( nextPageUrl );

			return fetch( nextPageUrl )
				.then( ( response ) => response.text() )
				.then( ( html ) => {
					const doc = new DOMParser().parseFromString( html, 'text/html' );
					this.handleSuccessFetch( doc );
				} );
		}

		removeLinksListeners() {
			if ( ! this.elements.links.length || ! this.onLinkClick ) {
				return;
			}

			this.elements.links.forEach( ( link ) => {
				link.removeEventListener( 'click', this.onLinkClick );
			} );
		}

		updateURLQueryString( nextPageUrl ) {
			const currentUrl = new URL( window.location.href );
			const currentParams = currentUrl.searchParams;
			const targetUrl = new URL( nextPageUrl, window.location.href );
			const targetParams = targetUrl.searchParams;

			targetParams.forEach( ( value, key ) => {
				currentParams.set( key, value );
			} );

			if ( ! targetParams.has( 'e-page-' + this.elementId ) ) {
				currentParams.delete( 'e-page-' + this.elementId );
			}

			// Pretty permalinks: keep the target path (e.g. /page/2/).
			if ( targetUrl.pathname !== currentUrl.pathname && ! targetParams.has( 'e-page-' + this.elementId ) ) {
				currentUrl.pathname = targetUrl.pathname;
			}

			history.pushState( null, '', currentUrl.href );
		}

		handleUiBeforeLoading() {
			this.isLoading = true;
			this.$element[ 0 ].classList.add( 'e-loading-overlay' );
			this.maybeScrollToTop();
		}

		maybeScrollToTop() {
			if ( 'yes' !== this.getElementSettings( 'auto_scroll' ) ) {
				return;
			}

			this.$element[ 0 ].scrollIntoView( { behavior: 'smooth' } );
		}

		handleUiAfterLoading() {
			this.isLoading = false;
			this.$element[ 0 ].classList.remove( 'e-loading-overlay' );
		}

		handleSuccessFetch( result ) {
			this.handleUiAfterLoading();

			const selectors = this.getSettings( 'selectors' );
			const newWidgetContainer = result.querySelector( `[data-id="${ this.elementId }"] ${ selectors.widgetContainer }` );
			const existingWidgetContainer = this.elements.widgetContainer;

			if ( ! newWidgetContainer || ! existingWidgetContainer ) {
				return;
			}

			existingWidgetContainer.parentNode.replaceChild( newWidgetContainer, existingWidgetContainer );

			this.afterInsertPosts();
		}

		afterInsertPosts() {
			const selectors = this.getSettings( 'selectors' );
			const postsElements = document.querySelectorAll( `[data-id="${ this.elementId }"] ${ selectors.postWrapperTag }` );

			elementorFrontend.elementsHandler.runReadyTrigger( this.$element[ 0 ] );
			runElementHandlers( postsElements );
			maybeObserveLazyLoad();
		}

		onInit() {
			super.onInit();
			this.isLoading = false;
			this.elementId = this.getID();
		}
	}

	/* ------------------------------------------------------------------
	 * Loop carousel - port of Pro loop-carousel.js
	 * ---------------------------------------------------------------- */
	function createLoopCarouselHandler() {
		const CarouselBase = elementorModules.frontend.handlers.CarouselBase;

		if ( ! CarouselBase ) {
			return null;
		}

		return class LoopCarouselHandler extends CarouselBase {
			getDefaultSettings() {
				const defaultSettings = super.getDefaultSettings();
				defaultSettings.selectors.carousel = '.elementor-loop-container';
				return defaultSettings;
			}

			getSwiperSettings() {
				const swiperOptions = super.getSwiperSettings();
				const elementSettings = this.getElementSettings();
				const isRtl = elementorFrontend.config.is_rtl;
				const widgetSelector = `.elementor-element-${ this.getID() }`;

				if ( 'yes' === elementSettings.arrows ) {
					swiperOptions.navigation = {
						prevEl: isRtl ? `${ widgetSelector } .elementor-swiper-button-next` : `${ widgetSelector } .elementor-swiper-button-prev`,
						nextEl: isRtl ? `${ widgetSelector } .elementor-swiper-button-prev` : `${ widgetSelector } .elementor-swiper-button-next`,
					};
				}

				swiperOptions.on = swiperOptions.on || {};
				swiperOptions.on.beforeInit = () => {
					this.a11ySetSlidesAriaLabels();
				};

				return swiperOptions;
			}

			async onInit( ...args ) {
				super.onInit( ...args );
				this.ranElementHandlers = false;
			}

			handleElementHandlers() {
				if ( this.ranElementHandlers || ! this.swiper ) {
					return;
				}

				runElementHandlers( Array.from( this.swiper.slides ).slice( this.swiper.activeIndex - 1, this.swiper.slides.length ) );
				this.ranElementHandlers = true;
			}

			a11ySetSlidesAriaLabels() {
				const slides = Array.from( this.elements.$slides );

				slides.forEach( ( slide, index ) => {
					slide.setAttribute( 'aria-label', `${ parseInt( index + 1 ) } ${ __( 'of', 'lastudio-kit' ) } ${ slides.length }` );
				} );
			}
		};
	}


	/* ------------------------------------------------------------------
	 * Loop filter - port of Pro loop-filter frontend module
	 * ---------------------------------------------------------------- */
	const queryConstants = {
		AND: { separator: { decoded: '+', fromBrowser: ' ', encoded: '%2B' }, operator: 'AND' },
		OR: { separator: { decoded: '~', fromBrowser: '~', encoded: '%7C' }, operator: 'IN' },
		NOT: { separator: { decoded: '!', fromBrowser: '!', encoded: '%21' }, operator: 'NOT IN' },
		DISABLED: { separator: { decoded: '', fromBrowser: '', encoded: '' }, operator: 'AND' },
	};

	class LoopWidgetsStore {
		constructor() {
			this.widgets = {};
		}

		get() {
			return this.widgets;
		}

		getWidget( widgetId ) {
			return this.widgets[ widgetId ];
		}

		addWidget( widgetId ) {
			this.widgets[ widgetId ] = { filters: {}, consolidatedFilters: {} };
		}

		getFilters( widgetId ) {
			return this.getWidget( widgetId ).filters;
		}

		getFilter( widgetId, filterId ) {
			return this.getWidget( widgetId ).filters[ filterId ];
		}

		setFilter( widgetId, filterId, filterData ) {
			this.getWidget( widgetId ).filters[ filterId ] = filterData;
		}

		unsetFilter( widgetId, filterId ) {
			delete this.getWidget( widgetId ).filters[ filterId ];
		}

		getFilterTerms( widgetId, filterId ) {
			const filter = this.getFilter( widgetId, filterId );
			return filter && filter.filterData.terms ? filter.filterData.terms : [];
		}

		setFilterTerms( widgetId, filterId, terms ) {
			this.getFilter( widgetId, filterId ).filterData.terms = terms;
		}

		getConsolidatedFilters( widgetId ) {
			return this.getWidget( widgetId ).consolidatedFilters;
		}

		maybeInitializeWidget( widgetId ) {
			if ( ! this.getWidget( widgetId ) ) {
				this.addWidget( widgetId );
			}
		}

		maybeInitializeFilter( widgetId, filterId ) {
			if ( ! this.getFilter( widgetId, filterId ) ) {
				this.setFilter( widgetId, filterId, { filterData: { terms: [] } } );
			}
		}

		consolidateFilters( widgetId ) {
			const filters = this.getFilters( widgetId );
			const consolidated = {};

			for ( const filterId in filters ) {
				const { filterType, filterData } = filters[ filterId ];

				if ( ! filterData.terms || 0 === filterData.terms.length ) {
					continue;
				}

				consolidated[ filterType ] = consolidated[ filterType ] || {};
				consolidated[ filterType ][ filterData.selectedTaxonomy ] = {
					terms: 'string' === typeof filterData.terms ? [ filterData.terms ] : filterData.terms,
					logicalJoin: filterData.logicalJoin || 'AND',
				};
			}

			this.getWidget( widgetId ).consolidatedFilters = consolidated;
		}
	}

	class LoopFilterModule {
		constructor() {
			this.loopWidgetsStore = new LoopWidgetsStore();
		}

		getConfig() {
			return window.LaStudioKitLoopBuilderFrontendConfig || {};
		}

		removeFilterFromLoopWidget( widgetId, filterId, filterTerm = '', defaultFilter = '' ) {
			if ( ! this.loopWidgetsStore.getWidget( widgetId ) ) {
				this.loopWidgetsStore.addWidget( widgetId );
				this.refreshLoopWidget( widgetId, filterId );
				return;
			}

			if ( filterTerm === defaultFilter ) {
				this.loopWidgetsStore.unsetFilter( widgetId, filterId );
			} else if ( this.loopWidgetsStore.getFilter( widgetId, filterId ) ) {
				const newTerms = this.loopWidgetsStore.getFilterTerms( widgetId, filterId ).filter( ( term ) => term !== filterTerm );
				this.loopWidgetsStore.setFilterTerms( widgetId, filterId, newTerms );
			}

			this.refreshLoopWidget( widgetId, filterId );
		}

		setFilterDataForLoopWidget( widgetId, filterId, filter, refresh = true, multipleFiltersLogicalJoin = 'DISABLED' ) {
			this.loopWidgetsStore.maybeInitializeWidget( widgetId );
			this.loopWidgetsStore.maybeInitializeFilter( widgetId, filterId );

			const logicalJoin = [ 'AND', 'OR' ].includes( multipleFiltersLogicalJoin ) ? multipleFiltersLogicalJoin : 'DISABLED';

			if ( 'DISABLED' !== logicalJoin ) {
				const existingTerms = this.loopWidgetsStore.getFilterTerms( widgetId, filterId );
				filter.filterData.terms = [ ...new Set( [ ...existingTerms, ...filter.filterData.terms ] ) ];
				filter.filterData.logicalJoin = logicalJoin;
			}

			this.loopWidgetsStore.setFilter( widgetId, filterId, filter );

			if ( refresh ) {
				this.refreshLoopWidget( widgetId, filterId );
				return;
			}

			this.loopWidgetsStore.consolidateFilters( widgetId );
		}

		getQueryStringInObjectForm() {
			const queryString = {};

			for ( const widgetId in this.loopWidgetsStore.get() ) {
				const loopWidget = this.loopWidgetsStore.getWidget( widgetId );

				for ( const filterType in loopWidget.consolidatedFilters ) {
					const filterData = loopWidget.consolidatedFilters[ filterType ];

					for ( const filterName in filterData ) {
						const separator = queryConstants[ filterData[ filterName ].logicalJoin || 'AND' ].separator.decoded;
						queryString[ `e-filter-${ widgetId }-${ filterName }` ] = Object.values( filterData[ filterName ].terms ).join( separator );
					}
				}
			}

			return queryString;
		}

		updateURLQueryString( widgetId, filterId ) {
			const existingQueryString = new URL( window.location.href ).searchParams;
			const queryStringObject = this.getQueryStringInObjectForm();
			const updatedParams = new URLSearchParams();

			existingQueryString.forEach( ( value, key ) => {
				if ( ! key.startsWith( 'e-filter' ) && ! key.startsWith( 'e-page-' + widgetId ) ) {
					updatedParams.append( key, value );
				}
			} );

			for ( const key in queryStringObject ) {
				updatedParams.set( key, queryStringObject[ key ] );
			}

			let queryString = updatedParams.toString();
			queryString = queryString.replace( new RegExp( queryConstants.AND.separator.encoded, 'g' ), queryConstants.AND.separator.decoded );
			queryString = queryString.replace( new RegExp( queryConstants.OR.separator.encoded, 'g' ), queryConstants.OR.separator.decoded );

			const helpers = this.getFilterHelperAttributes( filterId );

			if ( helpers.pageNum > 1 ) {
				queryString = queryString ? this.formatQueryString( helpers.baseUrl, queryString ) : helpers.baseUrl;
			} else {
				queryString = queryString ? `?${ queryString }` : location.pathname;
			}

			history.pushState( null, null, queryString );
		}

		formatQueryString( baseURL, queryString ) {
			const baseURLParams = baseURL.includes( '?' ) ? new URLSearchParams( baseURL.split( '?' )[ 1 ] ) : new URLSearchParams();
			const inputParams = new URLSearchParams( queryString );

			for ( const param of baseURLParams.keys() ) {
				if ( inputParams.has( param ) ) {
					inputParams.delete( param );
				}
			}

			for ( const excludedVar of [ 'page', 'paged' ] ) {
				baseURLParams.delete( excludedVar );
				inputParams.delete( excludedVar );
			}

			const mergedParams = new URLSearchParams( baseURLParams.toString() );

			for ( const [ param, value ] of inputParams.entries() ) {
				mergedParams.append( param, value );
			}

			return baseURL.split( '?' )[ 0 ] + ( mergedParams.toString() ? `?${ mergedParams.toString() }` : '' );
		}

		getFilterHelperAttributes( filterId ) {
			const filterWidget = document.querySelector( '[data-id="' + filterId + '"]' );
			const filterBar = filterWidget ? filterWidget.querySelector( '.e-filter' ) : null;

			if ( ! filterBar ) {
				return { baseUrl: location.href, pageNum: 1 };
			}

			return filterBar.dataset;
		}

		getClosestDataElementorId( element ) {
			const closestParent = element ? element.closest( '[data-elementor-id]' ) : null;
			return closestParent ? closestParent.getAttribute( 'data-elementor-id' ) : null;
		}

		prepareLoopUpdateRequestData( widgetId, filterId ) {
			const helpers = this.getFilterHelperAttributes( filterId );
			const data = {
				post_id: this.getClosestDataElementorId( document.querySelector( `.elementor-element-${ widgetId }` ) ) || elementorFrontend.config.post.id,
				widget_filters: this.loopWidgetsStore.getConsolidatedFilters( widgetId ),
				widget_id: widgetId,
				pagination_base_url: helpers.baseUrl,
			};

			if ( elementorFrontend.isEditMode() ) {
				const container = window.top.$e.components.get( 'document' ).utils.findContainerById( widgetId );

				if ( container ) {
					data.widget_model = container.model.toJSON( { remove: [ 'default', 'editSettings', 'defaultEditSettings' ] } );
				}
			}

			return data;
		}

		fetchUpdatedLoopWidgetMarkup( widgetId, filterId ) {
			const config = this.getConfig();
			const headers = { 'Content-Type': 'application/json' };

			if ( config.nonce ) {
				headers[ 'X-WP-Nonce' ] = config.nonce;
			}

			return fetch( config.refreshUrl, {
				method: 'POST',
				headers,
				credentials: 'same-origin',
				body: JSON.stringify( this.prepareLoopUpdateRequestData( widgetId, filterId ) ),
			} );
		}

		createElementFromHTMLString( html ) {
			const div = document.createElement( 'div' );

			if ( ! html ) {
				div.classList.add( 'elementor-widget-container' );
				return div;
			}

			div.innerHTML = html.trim();
			return div.firstElementChild;
		}

		refreshLoopWidget( widgetId, filterId ) {
			this.loopWidgetsStore.consolidateFilters( widgetId );
			this.updateURLQueryString( widgetId, filterId );

			const widget = document.querySelector( `.elementor-element-${ widgetId }` );

			if ( ! widget ) {
				return;
			}

			widget.classList.add( 'e-loading-overlay' );

			return this.fetchUpdatedLoopWidgetMarkup( widgetId, filterId )
				.then( ( response ) => ( response && response.ok ? response.json() : {} ) )
				.catch( () => ( {} ) )
				.then( ( response ) => {
					if ( ! response || ( ! response.data && '' !== response.data ) ) {
						return;
					}

					const existingWidgetContainer = widget.querySelector( '.elementor-widget-container' );
					const newWidgetContainer = this.createElementFromHTMLString( response.data );

					if ( existingWidgetContainer ) {
						existingWidgetContainer.parentNode.replaceChild( newWidgetContainer, existingWidgetContainer );
					} else {
						// Optimized markup: no widget container, replace the widget content.
						widget.innerHTML = '';
						widget.appendChild( newWidgetContainer );
					}

					runElementHandlers( widget.querySelectorAll( '.e-loop-item' ) );
					maybeObserveLazyLoad();
					elementorFrontend.elementsHandler.runReadyTrigger( widget );
					widget.classList.remove( 'e-loading' );
				} )
				.finally( () => {
					widget.classList.remove( 'e-loading-overlay' );
				} );
		}
	}

	window.LaStudioKitLoopFilter = window.LaStudioKitLoopFilter || new LoopFilterModule();

	/* -------- horizontal scroll helpers (port of Pro flex-horizontal-scroll.js) -------- */
	function changeScrollStatus( element, event ) {
		if ( 'mousedown' === event.type ) {
			element.classList.add( 'e-scroll' );
			element.dataset.pageX = event.pageX;
		} else {
			element.classList.remove( 'e-scroll', 'e-scroll-active' );
			element.dataset.pageX = '';
		}
	}

	function setHorizontalTitleScrollValues( element, horizontalScrollStatus, event ) {
		const isActiveScroll = element.classList.contains( 'e-scroll' );
		const headingContentIsWiderThanWrapper = element.scrollWidth > element.clientWidth;

		if ( ! isActiveScroll || 'enable' !== horizontalScrollStatus || ! headingContentIsWiderThanWrapper ) {
			return;
		}

		event.preventDefault();

		const mouseMoveX = event.pageX - parseFloat( element.dataset.pageX );
		let toScrollDistanceX = mouseMoveX;

		if ( 20 < mouseMoveX ) {
			toScrollDistanceX = 5;
		} else if ( -20 > mouseMoveX ) {
			toScrollDistanceX = -5;
		}

		element.scrollLeft = element.scrollLeft - toScrollDistanceX;
		element.classList.add( 'e-scroll-active' );
	}

	function getChildrenWidth( children ) {
		if ( ! children.length ) {
			return 0;
		}

		const gap = parseFloat( getComputedStyle( children[ 0 ].parentNode ).gap ) || 0;
		let totalWidth = 0;

		for ( let i = 0; i < children.length; i++ ) {
			totalWidth += children[ i ].offsetWidth + gap;
		}

		return totalWidth;
	}

	function setHorizontalScrollAlignment( { element, direction, justifyCSSVariable, horizontalScrollStatus } ) {
		if ( ! element ) {
			return;
		}

		const isScroll = element.clientWidth < getChildrenWidth( element.children ) && 'enable' === horizontalScrollStatus;

		if ( ! isScroll ) {
			element.style.setProperty( justifyCSSVariable, '' );
			return;
		}

		element.style.setProperty( justifyCSSVariable, 'start' );

		if ( 'end' === direction ) {
			element.scrollLeft = elementorFrontend.config.is_rtl ? -1 * getChildrenWidth( element.children ) : getChildrenWidth( element.children );
		} else {
			element.scrollLeft = 0;
		}
	}

	/* -------- Taxonomy filter handler -------- */
	class TaxonomyFilterHandler extends elementorModules.frontend.handlers.Base {
		getDefaultSettings() {
			return {
				selectors: {
					item: '.e-filter-item',
					container: '.e-filter',
				},
				filterValues: {
					default: '__all',
				},
			};
		}

		getDefaultElements() {
			return {
				$filterButtons: this.$element.find( this.getSettings( 'selectors.item' ) ),
				$container: this.$element.find( this.getSettings( 'selectors.container' ) ),
			};
		}

		getModule() {
			return window.LaStudioKitLoopFilter;
		}

		getHeadingEvents() {
			const container = this.elements.$container[ 0 ];

			return {
				mousedown: changeScrollStatus.bind( this, container ),
				mouseup: changeScrollStatus.bind( this, container ),
				mouseleave: changeScrollStatus.bind( this, container ),
				mousemove: setHorizontalTitleScrollValues.bind( this, container, this.getHorizontalScrollSetting() ),
			};
		}

		getScrollSettingsObject() {
			return {
				element: this.elements.$container[ 0 ],
				direction: this.getItemsAlignment(),
				justifyCSSVariable: '--e-filter-justify-content',
				horizontalScrollStatus: this.getHorizontalScrollSetting(),
			};
		}

		bindEvents() {
			this.elements.$filterButtons.on( 'click', this.onFilterButtonClick.bind( this ) );

			if ( this.elements.$container.length ) {
				this.elements.$container.on( this.getHeadingEvents() );
			}

			this.resizeListener = () => setHorizontalScrollAlignment( this.getScrollSettingsObject() );
			elementorFrontend.elements.$window.on( 'resize', this.resizeListener );
		}

		unbindEvents() {
			if ( this.resizeListener ) {
				elementorFrontend.elements.$window.off( 'resize', this.resizeListener );
			}
		}

		onElementChange( propertyName ) {
			if ( 0 === propertyName.indexOf( 'horizontal_scroll' ) || 0 === propertyName.indexOf( 'item_alignment_horizontal' ) ) {
				setHorizontalScrollAlignment( this.getScrollSettingsObject() );
			}
		}

		getFilterButtonElements() {
			if ( this.elements && this.elements.$filterButtons.length ) {
				return this.elements.$filterButtons;
			}

			this.elements = this.getDefaultElements();
			this.bindEvents();

			return this.elements.$filterButtons;
		}

		getActiveFilterButtonElements() {
			return this.getFilterButtonElements().filter( '[aria-pressed="true"]' );
		}

		getCurrentlyActiveFilter() {
			return this.getActiveFilterButtonElements().toArray().map( ( button ) => button.dataset.filter );
		}

		isMultipleSelection() {
			return 'yes' === this.getElementSettings( 'multiple_selection' );
		}

		activateFilterButton( selectedTermSlug ) {
			const $filterButtons = this.getFilterButtonElements();
			const defaultFilter = this.getSettings( 'filterValues.default' );

			if ( ! $filterButtons.length ) {
				return;
			}

			if ( ! this.isMultipleSelection() || defaultFilter === selectedTermSlug ) {
				$filterButtons.attr( 'aria-pressed', false );
			}

			$filterButtons.filter( '[data-filter="' + selectedTermSlug + '"]' ).attr( 'aria-pressed', true );

			const active = this.getCurrentlyActiveFilter();

			if ( active.includes( defaultFilter ) && defaultFilter !== selectedTermSlug ) {
				this.deactivateDefaultFilterButton();
			}
		}

		deactivateFilterButton( clickedFilter ) {
			const $filterButtons = this.getFilterButtonElements();
			const defaultFilter = this.getSettings( 'filterValues.default' );

			if ( ! $filterButtons.length ) {
				return;
			}

			const currentlyActive = this.getCurrentlyActiveFilter();
			const needDefault = ! this.isMultipleSelection() || ( ! currentlyActive.includes( defaultFilter ) && 1 === currentlyActive.length );

			$filterButtons.filter( '[data-filter="' + clickedFilter + '"]' ).attr( 'aria-pressed', false );

			if ( needDefault ) {
				this.activateDefaultFilterButton();
			}

			this.getModule().removeFilterFromLoopWidget( this.getElementSettings( 'selected_element' ), this.getID(), clickedFilter, defaultFilter );
		}

		activateDefaultFilterButton() {
			const $filterButtons = this.getFilterButtonElements();
			$filterButtons.attr( 'aria-pressed', false );
			$filterButtons.filter( '[data-filter="' + this.getSettings( 'filterValues.default' ) + '"]' ).attr( 'aria-pressed', true );
		}

		deactivateDefaultFilterButton() {
			this.getFilterButtonElements().filter( '[data-filter="' + this.getSettings( 'filterValues.default' ) + '"]' ).attr( 'aria-pressed', false );
		}

		getFilterOperator() {
			const settings = this.getElementSettings();

			if ( ! settings.multiple_selection || ! [ 'AND', 'OR' ].includes( settings.logical_combination ) ) {
				return 'DISABLED';
			}

			return settings.logical_combination;
		}

		filterItems( selectedTermSlug ) {
			const settings = this.getElementSettings();
			const defaultFilterValue = this.getSettings( 'filterValues.default' );

			if ( defaultFilterValue === selectedTermSlug ) {
				this.getModule().removeFilterFromLoopWidget( settings.selected_element, this.getID(), selectedTermSlug, defaultFilterValue );
				return;
			}

			this.getModule().setFilterDataForLoopWidget( settings.selected_element, this.getID(), {
				filterType: 'taxonomy',
				filterData: {
					selectedTaxonomy: settings.taxonomy,
					terms: [ selectedTermSlug ],
				},
			}, true, this.getFilterOperator() );
		}

		setFilter( filter = this.getSettings( 'filterValues.default' ) ) {
			this.filterItems( filter );
			this.activateFilterButton( filter );
		}

		onFilterButtonClick( event ) {
			const loopWidget = document.querySelector( '.elementor-element-' + this.getElementSettings( 'selected_element' ) );

			if ( loopWidget ) {
				loopWidget.classList.remove( 'e-load-more-pagination-end' );
			}

			const currentlyActive = this.getCurrentlyActiveFilter();
			const clickedFilter = event.currentTarget && event.currentTarget.dataset ? event.currentTarget.dataset.filter : undefined;

			if ( currentlyActive.includes( clickedFilter ) && clickedFilter === this.getSettings( 'filterValues.default' ) ) {
				return;
			}

			if ( currentlyActive.includes( clickedFilter ) ) {
				this.deactivateFilterButton( clickedFilter );
				return;
			}

			this.setFilter( clickedFilter );
		}

		onDestroy() {
			const selectedElementId = this.getElementSettings( 'selected_element' );

			if ( selectedElementId && this.getElementSettings( 'taxonomy' ) && elementorFrontend.isEditMode() ) {
				this.getModule().removeFilterFromLoopWidget( selectedElementId, this.getID(), '' );
			}

			super.onDestroy();
		}

		populateLoopWidgetsStoreOnInitialPageLoad() {
			const settings = this.getElementSettings();
			let selectedTermSlugs = new URLSearchParams( window.location.search ).get( 'e-filter-' + settings.selected_element + '-' + settings.taxonomy );

			if ( ! selectedTermSlugs ) {
				return;
			}

			let separator = queryConstants.AND.separator.fromBrowser;

			if ( selectedTermSlugs.includes( queryConstants.OR.separator.fromBrowser ) ) {
				separator = queryConstants.OR.separator.fromBrowser;
			}

			selectedTermSlugs = selectedTermSlugs.split( separator );

			this.getModule().setFilterDataForLoopWidget( settings.selected_element, this.getID(), {
				filterType: 'taxonomy',
				filterData: {
					selectedTaxonomy: settings.taxonomy,
					terms: selectedTermSlugs,
				},
			}, false, this.getFilterOperator() );
		}

		onInit() {
			super.onInit();
			this.populateLoopWidgetsStoreOnInitialPageLoad();
			setHorizontalScrollAlignment( this.getScrollSettingsObject() );
		}

		getHorizontalScrollSetting() {
			return elementorFrontend.utils.controls.getResponsiveControlValue( this.getElementSettings(), 'horizontal_scroll', '', elementorFrontend.getCurrentDeviceMode() );
		}

		getItemsAlignment() {
			return elementorFrontend.utils.controls.getResponsiveControlValue( this.getElementSettings(), 'item_alignment_horizontal', '', elementorFrontend.getCurrentDeviceMode() );
		}
	}

	$( window ).on( 'elementor/frontend/init', () => {
		const LoopCarouselHandler = createLoopCarouselHandler();

		elementorFrontend.elementsHandler.attachHandler( 'lakit-taxonomy-filter', TaxonomyFilterHandler );

		SKINS.forEach( ( skinName ) => {
			elementorFrontend.elementsHandler.attachHandler( 'lakit-loop-grid', LoadMoreHandler, skinName );
			elementorFrontend.elementsHandler.attachHandler( 'lakit-loop-grid', LoopHandler, skinName );
			elementorFrontend.elementsHandler.attachHandler( 'lakit-loop-grid', AjaxPaginationHandler, skinName );
			elementorFrontend.elementsHandler.attachHandler( 'lakit-loop-carousel', LoopHandler, skinName );

			if ( LoopCarouselHandler ) {
				elementorFrontend.elementsHandler.attachHandler( 'lakit-loop-carousel', LoopCarouselHandler, skinName );
			}
		} );
	} );
}( jQuery ) );
