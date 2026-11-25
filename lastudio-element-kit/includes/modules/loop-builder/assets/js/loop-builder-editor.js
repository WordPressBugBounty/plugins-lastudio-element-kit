/**
 * LA-Studio Kit - Loop Builder editor module.
 *
 *  - `lastudiokit-template-query` control (choose / create / edit a Loop Item template in place)
 *  - "Save & Back" handles when editing a loop item inside a Loop widget
 *  - Loop Item document: "Apply" source type, "Apply & Preview", dynamic `preview_id` autocomplete
 *  - Taxonomy skins: keep the include/exclude term autocomplete in sync with the chosen taxonomy
 */
( function( $ ) {
	'use strict';

	const config = window.LaStudioKitLoopBuilderConfig || {};
	const DOC_TYPE = config.documentType || 'lakit-loop-item';
	const LOOP_WIDGETS = config.widgets || [ 'lakit-loop-grid', 'lakit-loop-carousel' ];
	const __ = ( window.wp && window.wp.i18n ) ? window.wp.i18n.__ : ( text ) => text;

	const TAXONOMY_SKINS = [ 'post_taxonomy', 'product_taxonomy' ];
	const TAXONOMY_SOURCE_CONTROLS = [ 'post_taxonomy_query_post_type', 'product_taxonomy_query_post_type' ];

	function isLoopDocument( document ) {
		return !! ( document && document.config && DOC_TYPE === document.config.type );
	}

	function getPreviewWindow() {
		return elementor.$preview && elementor.$preview[ 0 ] ? elementor.$preview[ 0 ].contentWindow : null;
	}

	/* ------------------------------------------------------------------
	 * Template query control
	 * ---------------------------------------------------------------- */
	function registerTemplateQueryControl() {
		const BaseQueryView = elementor.modules.controls[ elementorCommon.helpers.upperCaseWords( 'lastudiokit-query' ) ] || elementor.modules.controls.Select2;

		const TemplateQueryControl = BaseQueryView.extend( {
			ui() {
				const ui = BaseQueryView.prototype.ui.apply( this, arguments );
				ui.newButton = 'button[data-action="new"]';
				ui.editButton = 'button[data-action="edit"]';
				return ui;
			},

			events() {
				const events = BaseQueryView.prototype.events.apply( this, arguments );
				events[ 'click @ui.newButton' ] = 'onNewButtonClicked';
				events[ 'click @ui.editButton' ] = 'onEditButtonClicked';
				return events;
			},

			onRender() {
				BaseQueryView.prototype.onRender.apply( this, arguments );
				this.toggleButtons( this.getControlValue() );
			},

			onBaseInputChange( event ) {
				BaseQueryView.prototype.onBaseInputChange.apply( this, arguments );
				this.toggleButtons( this.getInputValue( event.currentTarget ) );
			},

			getSelect2Placeholder() {
				return {
					id: '',
					text: __( 'Start typing its name', 'lastudio-kit' ),
				};
			},

			toggleButtons( templateID ) {
				const newButton = this.ui.newButton && this.ui.newButton.get ? this.ui.newButton.get( 0 ) : null;
				const editButton = this.ui.editButton && this.ui.editButton.get ? this.ui.editButton.get( 0 ) : null;

				if ( newButton ) {
					newButton.style.display = templateID ? 'none' : 'block';
				}

				if ( editButton ) {
					editButton.style.display = templateID ? 'block' : 'none';
				}
			},

			onNewButtonClicked() {
				this.createTemplate();
			},

			onEditButtonClicked() {
				this.afterAction( 'edit', this.getControlValue() );
			},

			/**
			 * Show a confirm dialog (the current document is saved), create a new template via the REST API
			 * and switch the editor to it.
			 */
			createTemplate() {
				if ( ! this.confirmSaveBeforeTemplateCreateDialog ) {
					this.confirmSaveBeforeTemplateCreateDialog = elementorCommon.dialogsManager.createWidget( 'confirm', {
						id: 'lakit-confirm-save-before-template-create',
						headerMessage: __( 'Save Changes', 'lastudio-kit' ),
						message: __( 'Would you like to save the changes you\'ve made?', 'lastudio-kit' ),
						position: {
							my: 'center center',
							at: 'center center',
						},
						strings: {
							confirm: __( 'Save', 'lastudio-kit' ),
							cancel: __( 'Discard', 'lastudio-kit' ),
						},
						onConfirm: () => this.onConfirmCreateTemplate(),
					} );
				}

				this.confirmSaveBeforeTemplateCreateDialog.show();
			},

			async onConfirmCreateTemplate() {
				$e.internal( 'panel/state-loading' );

				try {
					const templateID = await this.createAndSetTemplate();
					await this.afterAction( 'new', templateID );
				} finally {
					$e.internal( 'panel/state-ready' );
				}
			},

			async createAndSetTemplate() {
				const controlId = this.model.get( 'name' );
				const controls = this.options.container.controls || {};
				const controlConfig = controls[ controlId ] || this.model.attributes;
				const newTemplateType = controlConfig.actions.new.document_config.type;
				const newTemplateSource = this.getTemplateSourceTypeValue();

				const newTemplate = await $e.data.create( 'library/templates', {
					type: newTemplateType,
					page_settings: {
						source: newTemplateSource,
					},
				} );

				const templateID = parseInt( newTemplate.data.template_id );

				this.setValue( templateID );

				return templateID;
			},

			getTemplateSourceTypeValue() {
				const container = this.options.container;

				if ( container && container.args && 'repeater' === container.args.type ) {
					return container.renderer.args.settings.attributes._skin || undefined;
				}

				if ( container && container.controls && container.controls._skin ) {
					const skinView = container.panel.getControlView( '_skin' );
					return skinView ? skinView.getControlValue() : container.settings.get( '_skin' );
				}

				return undefined;
			},

			async switchDocument( id ) {
				await $e.run( 'editor/documents/switch', {
					id: parseInt( id ),
					mode: 'save',
				} );

				const document = elementor.documents.getCurrent();

				if ( document.config.container_attributes && document.config.container_attributes.class ) {
					document.$element.addClass( document.config.container_attributes.class );
				}
			},

			async afterAction( context, templateID ) {
				const button = 'new' === context ? this.ui.newButton[ 0 ] : this.ui.editButton[ 0 ];
				const afterAction = button ? button.getAttribute( 'data-after-action' ) : 'switch_document';

				if ( 'switch_document' === afterAction ) {
					await this.switchDocument( templateID );
				} else {
					window.open( this.getThemeBuilderURL( templateID ), '_blank' );
				}
			},

			getThemeBuilderURL( templateID ) {
				return `${ config.adminUrl || elementor.config.admin_url || '' }post.php?post=${ templateID }&action=elementor`;
			},
		} );

		elementor.addControlView( 'lastudiokit-template-query', TemplateQueryControl );
	}

	/* ------------------------------------------------------------------
	 * Behavior for the taxonomy skin "Source" control
	 * ---------------------------------------------------------------- */
	const LoopBuilderBehavior = Marionette.Behavior.extend( {
		ui() {
			return {
				postSourceControlSelector: '[data-setting="post_taxonomy_query_post_type"]',
				productSourceControlSelector: '[data-setting="product_taxonomy_query_post_type"]',
			};
		},

		events() {
			return {
				'change @ui.postSourceControlSelector': 'onApplySourceChange',
				'change @ui.productSourceControlSelector': 'onApplySourceChange',
			};
		},

		onApplySourceChange( event ) {
			const sourceType = ( event.target && event.target.value ) || this.getOption( 'getDefaultSourceType' )( this.getOption( 'getSkinType' )() );
			this.getOption( 'updateTaxonomyTabsIdControls' )( sourceType, true );
		},

		onRender() {
			const postType = this.getOption( 'getSourceControlValue' )();
			this.getOption( 'updateTaxonomyTabsIdControls' )( postType );
		},
	} );

	/* ------------------------------------------------------------------
	 * Hook: reset preview_id when preview_type changes (Loop Item docs)
	 * ---------------------------------------------------------------- */
	function registerHooks() {
		class LakitLoopUpdatePreviewOptions extends $e.modules.hookData.After {
			getCommand() {
				return 'document/elements/settings';
			}

			getId() {
				return 'lastudio-kit-loop-builder-update-preview-options';
			}

			getContainerType() {
				return 'document';
			}

			getConditions( args ) {
				return args.settings && args.settings.preview_type && isLoopDocument( elementor.documents.getCurrent() );
			}

			apply( args ) {
				const { containers = [ args.container ] } = args;

				$e.run( 'document/elements/settings', {
					containers,
					settings: {
						preview_id: '',
					},
				} );

				if ( $e.routes.is( 'panel/page-settings/settings' ) ) {
					window.LaStudioKitLoopBuilder.updatePreviewIdOptions( true );
				}
			}
		}

		$e.hooks.registerDataAfter( new LakitLoopUpdatePreviewOptions() );
	}

	/* ------------------------------------------------------------------
	 * Module
	 * ---------------------------------------------------------------- */
	class LoopBuilderModule extends elementorModules.editor.utils.Module {
		onInit() {
			super.onInit && super.onInit();

			this.registerControlBehavior = this.registerControlBehavior.bind( this );
			this.onDocumentLoaded = this.onDocumentLoaded.bind( this );
			this.onDocumentUnloaded = this.onDocumentUnloaded.bind( this );
			this.onApplySourceChange = this.onApplySourceChange.bind( this );
			this.onApplyPreview = this.onApplyPreview.bind( this );
			this.onSectionPreviewSettingsActive = this.onSectionPreviewSettingsActive.bind( this );
			this.updatePreviewIdOptions = this.updatePreviewIdOptions.bind( this );
			this.getSourceControlValue = this.getSourceControlValue.bind( this );
			this.getSkinType = this.getSkinType.bind( this );
			this.getDefaultSourceType = this.getDefaultSourceType.bind( this );
			this.updateTaxonomyTabsIdControls = this.updateTaxonomyTabsIdControls.bind( this );
			this.createDocumentSaveHandles = this.createDocumentSaveHandles.bind( this );
		}

		onElementorInit() {
			registerTemplateQueryControl();
			elementor.hooks.addFilter( 'controls/base/behaviors', this.registerControlBehavior );
		}

		onElementorLoaded() {
			// `document:loaded` is bound to `onDocumentLoaded()` by the base editor module.
			registerHooks();
			elementor.on( 'document:unload', this.onDocumentUnloaded );
			elementor.on( 'document:loaded', this.createDocumentSaveHandles );
		}

		onElementorFrontendInit() {
			this.createDocumentSaveHandles();
		}

		registerControlBehavior( behaviors = {}, view ) {
			if ( ! TAXONOMY_SOURCE_CONTROLS.includes( view.options.model.get( 'name' ) ) ) {
				return behaviors;
			}

			const container = view.options.container;
			const widgetType = container && container.settings ? container.settings.get( 'widgetType' ) : '';

			if ( widgetType && ! LOOP_WIDGETS.includes( widgetType ) ) {
				return behaviors;
			}

			behaviors.lakitLoopBuilder = {
				behaviorClass: LoopBuilderBehavior,
				getSourceControlValue: this.getSourceControlValue,
				updateTaxonomyTabsIdControls: this.updateTaxonomyTabsIdControls,
				getSkinType: this.getSkinType,
				getDefaultSourceType: this.getDefaultSourceType,
			};

			return behaviors;
		}

		onDocumentLoaded( document ) {
			if ( ! isLoopDocument( document ) || this.loopDocumentEventsBound ) {
				return;
			}

			elementor.channels.editor.on( 'lakitLoopBuilder:ApplySourceChange', this.onApplySourceChange );
			elementor.channels.editor.on( 'lakitLoopBuilder:ApplyPreview', this.onApplyPreview );
			elementor.channels.editor.on( 'page_settings:preview_settings:activated', this.onSectionPreviewSettingsActive );
			elementor.getPanelView().on( 'set:page:page_settings', this.updatePreviewIdOptions );
			this.loopDocumentEventsBound = true;

			if ( 'product' === this.getDocumentSource( document ) && elementor.$previewContents ) {
				const item = elementor.$previewContents[ 0 ].querySelector( '.e-loop-item' );
				if ( item ) {
					item.classList.add( 'woocommerce' );
				}
			}
		}

		onDocumentUnloaded( document ) {
			if ( ! isLoopDocument( document ) ) {
				return;
			}

			elementor.channels.editor.off( 'lakitLoopBuilder:ApplySourceChange', this.onApplySourceChange );
			elementor.channels.editor.off( 'lakitLoopBuilder:ApplyPreview', this.onApplyPreview );
			elementor.channels.editor.off( 'page_settings:preview_settings:activated', this.onSectionPreviewSettingsActive );
			elementor.getPanelView().off( 'set:page:page_settings', this.updatePreviewIdOptions );
			this.loopDocumentEventsBound = false;
		}

		getDocumentSource( document ) {
			const settings = document && document.config && document.config.settings && document.config.settings.settings;
			return settings && settings.source ? settings.source : 'post';
		}

		async onApplySourceChange() {
			await $e.run( 'document/save/update', { force: true } );
			location.reload();
		}

		onApplyPreview() {
			$e.run( 'document/save/auto', {
				force: true,
				onSuccess: () => {
					elementor.dynamicTags.cleanCache();

					if ( elementor.config.initial_document.id === elementor.documents.getCurrentId() ) {
						elementor.reloadPreview();
					} else {
						$e.internal( 'editor/documents/attach-preview' );
					}
				},
			} );
		}

		onSectionPreviewSettingsActive() {
			this.updatePreviewIdOptions( true );
		}

		updatePreviewIdOptions( render ) {
			let previewType = elementor.settings.page.model.get( 'preview_type' );

			if ( ! previewType ) {
				return;
			}

			previewType = previewType.split( '/' );

			const currentView = elementor.getPanelView().getCurrentPageView();

			if ( ! currentView || ! currentView.collection ) {
				return;
			}

			const controlModel = currentView.collection.findWhere( { name: 'preview_id' } );

			if ( ! controlModel ) {
				return;
			}

			const templateType = previewType[ 0 ];
			const sourceType = previewType[ 1 ];

			if ( TAXONOMY_SKINS.includes( templateType ) ) {
				controlModel.set( { autocomplete: { object: 'tax', query: { taxonomy: sourceType } } } );
			} else if ( 'single' === templateType ) {
				controlModel.set( { autocomplete: { object: 'post', query: { post_type: sourceType } } } );
			} else {
				controlModel.set( { autocomplete: { object: '' } } );
			}

			if ( true === render && currentView.children ) {
				const controlView = currentView.children.findByModel( controlModel );

				if ( controlView ) {
					controlView.render();
					controlView.$el.toggle( !! controlModel.get( 'autocomplete' ).object );
				}
			}
		}

		/* -------- Empty view CTA (rendered inside the preview's shadow DOM) -------- */

		getCtaStyles() {
			const previewDocument = getPreviewWindow() ? getPreviewWindow().document : document;
			const ctaStyle = previewDocument.createElement( 'link' );
			ctaStyle.setAttribute( 'rel', 'stylesheet' );
			ctaStyle.setAttribute( 'href', config.ctaStyleUrl );
			return ctaStyle;
		}

		getCtaContent( widgetName ) {
			const previewDocument = getPreviewWindow() ? getPreviewWindow().document : document;
			const ctaContent = previewDocument.createElement( 'div' );
			ctaContent.classList.add( 'e-loop-empty-view__container', 'elementor-grid', widgetName );
			ctaContent.innerHTML = Marionette.Renderer.render( '#tmpl-' + widgetName + '-cta' );
			return ctaContent;
		}

		createTemplate() {
			setTimeout( () => {
				const pageView = elementor.getPanelView().getCurrentPageView();

				if ( pageView && pageView.activateSection ) {
					pageView.activateSection( 'section_layout' )._renderChildren();
				}

				const controlView = this.getEditorControlView( 'template_id' );

				if ( controlView && controlView.createTemplate ) {
					controlView.createTemplate();
				}
			} );
		}

		/* -------- Taxonomy skins -------- */

		getSkinType() {
			const sectionView = this.getEditorControlView( 'section_layout' );

			if ( ! sectionView ) {
				return '';
			}

			return sectionView.options.container.settings.get( '_skin' );
		}

		getSourceControlValue() {
			const skinType = this.getSkinType();
			const controlView = this.getEditorControlView( `${ skinType }_query_post_type` );

			if ( ! controlView ) {
				return skinType && skinType.includes( 'product' ) ? 'product_cat' : 'category';
			}

			return controlView.getControlValue();
		}

		getDefaultSourceType( skinType ) {
			return {
				post: 'post',
				product: 'product',
				post_taxonomy: 'category',
				product_taxonomy: 'product_cat',
			}[ skinType ];
		}

		onApplySkinChange() {
			const skinType = this.getSkinType();

			if ( ! TAXONOMY_SKINS.includes( skinType ) ) {
				return;
			}

			this.updateTaxonomyTabsIdControls( this.getDefaultSourceType( skinType ), true );
		}

		updateTaxonomyTabsIdControls( postType, shouldResetControlValues = false ) {
			const skinType = this.getSkinType();

			if ( ! TAXONOMY_SKINS.includes( skinType ) ) {
				return;
			}

			const querySectionView = this.getEditorControlView( 'section_query' );

			if ( ! querySectionView ) {
				return;
			}

			[
				querySectionView.model.collection.findWhere( { name: `${ skinType }_posts_ids` } ),
				querySectionView.model.collection.findWhere( { name: `${ skinType }_exclude_ids` } ),
			].forEach( ( control ) => {
				if ( ! control ) {
					return;
				}

				const pageView = elementor.getPanelView().getCurrentPageView();
				const controlView = pageView && pageView.children ? pageView.children.findByModel( control ) : null;

				control.set( {
					autocomplete: {
						object: 'tax',
						query: {
							taxonomy: postType,
						},
					},
				} );

				if ( controlView && shouldResetControlValues ) {
					controlView.setValue( [] );
					controlView.applySavedValue();
				}
			} );
		}

		/* -------- "Save & Back" handles -------- */

		createDocumentSaveHandles() {
			const previewWindow = getPreviewWindow();
			const handleApi = previewWindow ? previewWindow.LaStudioKitLoopDocumentHandle : null;
			const frontend = previewWindow ? previewWindow.elementorFrontend : null;

			if ( ! handleApi || ! frontend || ! frontend.config || ! frontend.config.elements ) {
				return;
			}

			Object.entries( frontend.config.elements.data || {} ).forEach( ( [ cid, element ] ) => {
				const widgetType = element.attributes ? element.attributes.widgetType : '';

				if ( ! LOOP_WIDGETS.includes( widgetType ) ) {
					return;
				}

				const templateId = element.attributes.template_id;

				if ( ! templateId ) {
					return;
				}

				const widgetSelector = `.elementor-element[data-model-cid="${ cid }"]`;
				const editHandleSelector = `[data-elementor-type="${ DOC_TYPE }"].elementor-${ templateId }`;
				const editHandleElement = frontend.elements.$body.find( `${ widgetSelector } ${ editHandleSelector }` ).first()[ 0 ];

				if ( editHandleElement && ! editHandleElement.querySelector( ':scope > .elementor-document-save-back-handle' ) ) {
					handleApi.addDocumentHandle( {
						element: editHandleElement,
						id: 0,
						title: __( '& Back', 'lastudio-kit' ),
					}, handleApi.SAVE_CONTEXT, null, '.elementor-' + elementor.config.initial_document.id );
				}
			} );
		}
	}

	window.LaStudioKitLoopBuilder = new LoopBuilderModule();

	/* ------------------------------------------------------------------
	 * Taxonomy filter (editor) - port of Pro loop-filter editor module
	 * ---------------------------------------------------------------- */
	const TaxonomyFilterEditor = elementorModules.editor.utils.Module.extend( {
		cache: {},

		onElementorInit() {
			this.cache = {};
			this.onTaxonomyFilterSectionActive = this.onTaxonomyFilterSectionActive.bind( this );
			elementor.channels.editor.on( 'editor:widget:lakit-taxonomy-filter:section_taxonomy_filter:activated', this.onTaxonomyFilterSectionActive );
			elementor.channels.editor.on( 'change:lakit-taxonomy-filter', ( controlView ) => {
				this.onElementChange( controlView.model.get( 'name' ), controlView );
			} );
		},

		updateOptions( name, options ) {
			const controlView = this.getEditorControlView( name );

			if ( controlView ) {
				this.getEditorControlModel( name ).set( 'options', options );
				controlView.render();
			}
		},

		onTaxonomyFilterSectionActive() {
			this.updateSelectedElementOptions();

			const controlView = this.getEditorControlView( 'selected_element' );
			const value = controlView ? controlView.getControlValue() : '';

			if ( ! value || this.handleInvalidSetup( value ) ) {
				return;
			}

			this.updateTaxonomyOptions( value );
		},

		updateSelectedElementOptions() {
			const controlView = this.getEditorControlView( 'selected_element' );

			if ( ! controlView ) {
				return;
			}

			const value = controlView.getControlValue();
			const docSelector = `[data-elementor-id="${ elementor.config.document.id }"]`;
			const preview = elementor.$previewContents[ 0 ];

			if ( value && ! preview.querySelector( `${ docSelector } .elementor-element-${ value }` ) ) {
				controlView.setValue( '' );
			}

			const loopWidgets = preview.querySelectorAll( `${ docSelector } .elementor-widget-lakit-loop-grid` );
			const options = { '': __( 'Select a widget', 'lastudio-kit' ) };

			let index = 1;

			for ( const loopWidget of loopWidgets ) {
				options[ loopWidget.dataset.id ] = `${ __( 'LaStudioKit Loop Grid', 'lastudio-kit' ) } ${ index++ }`;
			}

			if ( ! loopWidgets.length ) {
				controlView.setValue( '' );
			}

			this.updateOptions( 'selected_element', options );
		},

		onElementChange( setting, controlView ) {
			if ( 'selected_element' !== setting ) {
				return;
			}

			const value = controlView.getControlValue();

			if ( this.handleInvalidSetup( value ) ) {
				return;
			}

			if ( value ) {
				this.updateTaxonomyOptions( value );
			} else {
				this.updateOptions( 'taxonomy', { '': __( 'Select a taxonomy', 'lastudio-kit' ) } );
			}
		},

		getLoopQueryPostType( loopWidgetId ) {
			const container = elementor.getContainer( loopWidgetId );

			if ( ! container ) {
				return 'post';
			}

			const skin = container.settings.get( '_skin' ) || 'post';

			if ( 'post' !== skin ) {
				return 'product';
			}

			const querySource = container.settings.get( 'post_query_post_type' );

			switch ( querySource ) {
				case 'current_query':
					return config.mainQueryPostType || 'post';
				case 'by_id':
				case 'related':
				case undefined:
				case '':
					return 'post';
				default:
					return querySource;
			}
		},

		updateTaxonomyOptions( loopWidgetId ) {
			const postType = this.getLoopQueryPostType( loopWidgetId );

			return this.getPostTypeTaxonomies( postType )
				.then( ( response ) => {
					if ( ! ( response instanceof Response ) ) {
						return response;
					}

					if ( ! response.ok ) {
						this.displayErrorDialog();
						return {};
					}

					return response.json();
				} )
				.catch( () => {
					this.displayErrorDialog();
					return {};
				} )
				.then( ( response ) => {
					let data = ( response && response.data ) || response || {};

					if ( ! Object.keys( data ).length ) {
						this.updateOptions( 'taxonomy', { '': __( 'No taxonomies found', 'lastudio-kit' ) } );
						return;
					}

					data = Object.assign( { '': __( 'Select a taxonomy', 'lastudio-kit' ) }, data );
					this.cache[ postType ] = data;
					this.updateOptions( 'taxonomy', data );
				} );
		},

		getPostTypeTaxonomies( postType ) {
			if ( this.cache[ postType ] && Object.keys( this.cache[ postType ] ).length ) {
				return Promise.resolve( this.cache[ postType ] );
			}

			return fetch( config.taxonomiesUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': config.restNonce,
				},
				body: JSON.stringify( { post_type: postType } ),
			} );
		},

		displayErrorDialog( options = {} ) {
			setTimeout( () => {
				elementorCommon.dialogsManager.createWidget( 'alert', {
					id: options.id || 'lakit-filter-error-message',
					className: 'e-filter__error-message',
					headerMessage: options.headerMessage || __( 'Something went wrong', 'lastudio-kit' ),
					message: options.message || __( 'We are experiencing technical difficulties on our end. Please try again to reconnect.', 'lastudio-kit' ),
					position: { my: 'center center', at: 'center center' },
					strings: { confirm: __( 'OK', 'lastudio-kit' ) },
				} ).show();
			}, 0 );
		},

		handleInvalidSetup( loopWidgetId ) {
			const container = elementor.getContainer( loopWidgetId );

			if ( ! container ) {
				return false;
			}

			if ( ! ( 'current_query' === container.settings.get( 'post_query_post_type' ) && 'single-post' === elementor.config.document.type ) ) {
				return false;
			}

			this.displayErrorDialog( {
				id: 'lakit-filter-invalid-setup-error',
				headerMessage: __( 'Invalid Setup', 'lastudio-kit' ),
				message: __( 'Cannot filter a single-item list, please change from "Current Query" to a different query setting.', 'lastudio-kit' ),
			} );

			return true;
		},
	} );

	window.LaStudioKitTaxonomyFilterEditor = new TaxonomyFilterEditor();
}( jQuery ) );
