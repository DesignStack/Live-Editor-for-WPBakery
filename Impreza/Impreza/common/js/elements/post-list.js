/**
 * UpSolution Element: Post List
 */
;( function( $, _undefined ) {
	"use strict";

	const DELETE_FILTER = null;

	/**
	 * @param {Node} container.
	 */
	function usPostList( container ) {
		const self = this;

		// Private "variables"
		self.data = {
			paged: 1,
			max_num_pages: 1,
			paginationBase: 'page',
			pagination: 'none',
			ajaxUrl: $us.ajaxUrl,
			ajaxData: {
				us_ajax_list_pagination: 1,
			},
			facetedFilter: {},
		};
		self.listResultCounterOpts = [];
		self.xhr;

		// Elements
		self.$container = $( container );
		self.$list = $( '.w-grid-list', container );
		self.$loadmore = $( '.g-loadmore', container );
		self.$pagination = $( 'nav.pagination', container );
		self.$none = self.$container.next( '.w-grid-none' );
		self.$listResultCounter = $( '.w-list-result-counter' );

		self.isCurrentQuery = self.$container.hasClass( 'for_current_wp_query' );

		// Get element settings
		const $opts = $( '.w-grid-list-json:first', container );
		if ( $opts.is( '[onclick]' ) ) {
			$.extend( self.data, $opts[0].onclick() || {} );
		}
		$opts.remove();

		self.paginationType = $ush.toString( self.data.pagination );
		self.hideIsEmpty = self.$container.closest( '.hide_if_inner_list_is_empty' ).length > 0;

		// Bondable events
		self._events = {
			addNextPage: self._addNextPage.bind( self ),
			initMagnificPopup: self._initMagnificPopup.bind( self ),

			usListOrder: self._usListOrder.bind( self ),
			usListSearch: self._usListSearch.bind( self ),
			usListFilter: self._usListFilter.bind( self ),

			usbReloadIsotopeLayout: self.usbReloadIsotopeLayout.bind( self ),
		};

		// List result counter settings
		if ( self.$listResultCounter.length && self.isCurrentQuery ) {
			self.$listResultCounter.each( ( _, node ) => {
				const $node = $( node );
				if ( $node.is( '[onclick]' ) ) {
					self.listResultCounterOpts.push( node.onclick() || {} );
					$node.removeAttr( 'onclick' );
				}
			} );

			// Count result on load if filter is active
			const numAjaxParams = Object.keys( $ush.urlManager( self.data.ajaxUrl ).toJson( false ) ).length;
			// Always count result for pagination type "none" on load for correct [upper] result
			if ( self.paginationType === 'none' ) {
				$.each( self.listResultCounterOpts, ( index, opts ) => {
					self.countResult( $( '> *', self.$list ).length, false, opts, index );
				} );
			}
			if ( self.paginationType === 'numbered' ) {
				if ( ! ( numAjaxParams < 1 ) ) {
					$.each( self.listResultCounterOpts, ( index, opts ) => {
						self.countResult( self.data.ajaxData.found_posts, false, opts, index );
					} );
				}

			} else if ( ! ( numAjaxParams <= 1 ) ) {
				const total = ( self.paginationType === 'none' )
					? $( '> *', self.$list ).length
					: self.data.ajaxData.found_posts;
				$.each( self.listResultCounterOpts, ( index, opts ) => {
					self.countResult( total, false, opts, index );
				} );
			}
		}

		// Load posts on button click or page scroll;
		if ( self.paginationType === 'load_on_btn' ) {
			self.$loadmore.on( 'mousedown', 'button', self._events.addNextPage );

		} else if ( self.paginationType === 'load_on_scroll' ) {
			$us.waypoints.add( self.$loadmore, /* offset */'-70%', self._events.addNextPage );
		}

		// Events
		self.$container
			.add( self.$none )
			.on( 'usListSearch', self._events.usListSearch )
			.on( 'usListOrder', self._events.usListOrder )
			.on( 'usListFilter', self._events.usListFilter );
		self.$list
			.on( 'click', '[ref=magnificPopup]', self._events.initMagnificPopup )

		// Open posts in popup
		if ( self.$container.hasClass( 'open_items_in_popup' ) ) {
			new $us.WPopup().popupPost( self.$container );
		}

		// Initialize Masonry.
		// @see https://isotope.metafizzy.co
		if ( self.$container.hasClass( 'type_masonry' ) ) {
			self.$list.imagesLoaded( () => {

				const isotopeOptions = {
					itemSelector: '.w-grid-item',
					layoutMode: ( self.$container.hasClass( 'isotope_fit_rows' ) ) ? 'fitRows' : 'masonry',
					isOriginLeft: ! $( '.l-body' ).hasClass( 'rtl' ),
					transitionDuration: 0
				};

				var columnWidth;
				if ( $( '.size_1x1', self.$list ).length > 0 ) {
					columnWidth = '.size_1x1';
				} else if ( $( '.size_1x2', self.$list ).length > 0 ) {
					columnWidth = '.size_1x2';
				} else if ( $( '.size_2x1', self.$list ).length >0 ) {
					columnWidth = '.size_2x1';
				} else if ( $( '.size_2x2', self.$list ).length > 0 ) {
					columnWidth = '.size_2x2';
				}
				if ( columnWidth ) {
					columnWidth = columnWidth || '.w-grid-item';
					isotopeOptions.masonry = { columnWidth: columnWidth };
				}

				// Run CSS animations locally after rendering elements in Isotope.
				self.$list.on( 'layoutComplete', () => {
					if ( _window.USAnimate ) {
						$( '.w-grid-item.off_autostart', self.$list ).removeClass( 'off_autostart' );
						new USAnimate( self.$list );
					}
					// Trigger scroll event to check positions for `$us.waypoints`.
					$us.$window.trigger( 'scroll.waypoints' );
				} );

				self.$list.isotope( isotopeOptions );

				$us.$canvas.on( 'contentChange', () => {
					self.$list.imagesLoaded( () => {
						self.$list.isotope( 'layout' );
					} );
				} );

			} );

			self.$container.on( 'usbReloadIsotopeLayout', self._events.usbReloadIsotopeLayout );
		}
	}

	// Post List API
	$.extend( usPostList.prototype, {

		/**
		 * Sets the search string from "List Search".
		 *
		 * @event handler
		 * @param {Event} e
		 * @param {String} name
		 * @param {String} value The search text.
		 */
		_usListSearch: function( e, name, value ) {
			this.applyFilter( name, value );
		},

		/**
		 * Sets orderby from "List Order".
		 *
		 * @event handler
		 * @param {Event} e
		 * @param {String} name
		 * @param {String} value The search text.
		 */
		_usListOrder: function( e, name, value ) {
			this.applyFilter( name, value );
		},

		/**
		 * Sets values from "List Filter".
		 *
		 * @event handler
		 * @param {Event} e
		 * @param {{}} values
		 */
		_usListFilter: function( e, values ) {
			const self = this;
			$.each( values, self.applyFilter.bind( self ) );
		},

		/**
		 * Adds next page.
		 *
		 * @event handler
		 */
		_addNextPage: function() {
			const self = this;
			if ( $ush.isUndefined( self.xhr ) && ! self.$none.is( ':visible' ) ) {
				self.addItems();
			}
		},

		/**
		 * Apply param to "Post/Product List".
		 *
		 * @param {String} name
		 * @param {String} value
		 */
		applyFilter: function( name, value ) {
			const self = this;
			if ( $ush.toString( value ) == '{}' ) {
				value = DELETE_FILTER;
			}

			// only save
			if ( name === 'list_filters' ) {
				$.extend( value, JSON.parse( self.data.ajaxData[ name ] || '{}' ) );
				self.data.ajaxData[ name ] = JSON.stringify( value );
				return;
			}

			// Reset pagination
			const pathname = location.pathname;
			const PAGINATION_PAGE_PATTERN = new RegExp( `\/${self.data.paginationBase}\/?([0-9]{1,})\/?$` );
			if ( PAGINATION_PAGE_PATTERN.test( pathname ) ) {
				history.pushState( {}, '', location.href.replace( pathname, pathname.replace( PAGINATION_PAGE_PATTERN, '' ) + '/' ) );
			}
			self.data.paged = 0;

			if ( self.isCurrentQuery ) {
				self.data.ajaxUrl = $ush
					.urlManager( self.data.ajaxUrl )
					.set( name, value )
					.toString();

			} else if ( value === DELETE_FILTER ) {
				delete self.data.ajaxData[ name ];

			} else {
				self.data.ajaxData[ name ] = value;
			}

			if ( ! $ush.isUndefined( self.xhr ) ) {
				self.xhr.abort();
			}
			self.addItems( /* apply filter */true );
		},

		/**
		 * Scrolls to the beginning of the list.
		 */
		scrollToList: function() {
			const self = this;

			if ( self.data.paged > 1 ) {
				return;
			}

			const offsetTop = $ush.parseInt( self.$container.offset().top );
			if ( ! offsetTop ) {
				return;
			}

			const scrollTop = $us.$window.scrollTop();

			if (
				! $ush.isNodeInViewport( self.$container[0] )
				|| offsetTop >= ( scrollTop + window.innerHeight )
				|| scrollTop >= offsetTop
			) {
				$us.$htmlBody
					.stop( true, false )
					.animate( { scrollTop: ( offsetTop - $us.header.getInitHeight() ) }, 500 );
			}
		},

		/**
		 * Adds items to element.
		 *
		 * @param {Boolean} applyFilter
		 */
		addItems: function( applyFilter ) {
			const self = this;

			self.data.paged += 1;
			if ( ! applyFilter && self.data.paged > self.data.max_num_pages ) {
				return;
			}

			if ( applyFilter ) {
				self.$container.addClass( 'filtering' );

				// Show spinner for filtering action only if set in options
				if ( self.$container.hasClass( 'preload_style_spinner' ) ) {
					self.$loadmore.removeClass( 'hidden' ).addClass( 'loading' );
				}

				// Always show spinner for pagination action
			} else {
				self.$loadmore.removeClass( 'hidden' ).addClass( 'loading' );
			}

			if ( ! self.hideIsEmpty ) {
				self.$container.removeClass( 'hidden' );
			}
			self.$pagination.addClass( 'hidden' );

			// Get request link and data
			var ajaxUrl = $ush.toString( self.data.ajaxUrl ),
				ajaxData = $ush.clone( self.data.ajaxData ),
				numPage = $ush.rawurlencode( '{num_page}' );

			if ( ajaxUrl.includes( numPage ) ) {
				ajaxUrl = ajaxUrl.replace( numPage, self.data.paged );

			} else if ( ajaxData.template_vars ) {
				ajaxData.template_vars = JSON.stringify( ajaxData.template_vars ); // convert for `us_get_HTTP_POST_json()`
				ajaxData.paged = self.data.paged;
			}

			self.xhr = $.ajax( {
				type: 'post',
				url: ajaxUrl,
				dataType: 'html',
				cache: false,
				data: ajaxData,
				success: ( html ) => {

					// Remove previous items when filtered
					if ( applyFilter ) {
						self.$list.html('');
						self.$none.addClass( 'hidden' );
					}

					// Reload element settings
					var $listJson = $( '.w-grid-list-json:first', html );
					if ( $listJson.is( '[onclick]' ) ) {
						$.extend( true, self.data, $listJson[0].onclick() || {} );
					}

					var $items = $( '.w-grid-list:first > *', html );
					const numAjaxParams = Object.keys( $ush.urlManager( self.data.ajaxUrl ).toJson( false ) ).length;

					var filterIsClear = numAjaxParams <= 1;
					if ( self.paginationType === 'numbered' ) {
						filterIsClear = numAjaxParams < 1;
					}

					// List items loaded
					$ush.timeout( () => {
						$us.$document.trigger( 'usPostList.itemsLoaded', [ $items, applyFilter ] );
					}, 50 );

					if ( self.hideIsEmpty ) {
						self.$container.toggleClass( 'hidden', $items.length === 0 );
					}

					// Case when there are no results
					if ( ! $items.length ) {
						if ( ! self.$none.length ) {
							self.$none = $( '.w-grid-none:first', html );
							if ( ! self.$none.length ) {
								self.$none = $( html ).filter( '.w-grid-none:first' );
							}
							self.$container.after( self.$none );
						}
						self.$container.removeClass( 'filtering' );
						self.$loadmore.addClass( 'hidden' );
						self.$pagination.addClass( 'hidden' );
						self.$none.removeClass( 'hidden' );
						$.each( self.listResultCounterOpts, ( index, opts ) => {
							self.countResult( $( '> *', self.$list ).length, false, opts, index );
						} );
						return;
					}

					// Output of results
					if ( self.$container.hasClass( 'type_masonry' ) ) {
						self.$list
							.isotope( 'insert', $items )
							.isotope( 'reloadItems' );
					} else {
						self.$list.append( $items );
					}

					// Init animation handler for new items
					if ( window.USAnimate && self.$container.hasClass( 'with_css_animation' ) ) {
						new USAnimate( self.$list );
						$us.$window.trigger( 'scroll.waypoints' );
					}

					// Case with numbered pagination
					if ( self.paginationType == 'numbered' ) {
						const $pagination = $( 'nav.pagination', html );
						if ( $pagination.length && ! self.$pagination.length ) {
							self.$list.after( $pagination.prop( 'outerHTML' ) );
							self.$pagination = self.$list.next( 'nav.pagination' );
						}
						if ( self.$pagination.length && $pagination.length ) {
							self.$pagination.html( $pagination.html() ).removeClass( 'hidden' );

						} else {
							self.$pagination.addClass( 'hidden' );
						}
					}

					// Case when the last page is loaded
					if ( self.data.paged >= self.data.max_num_pages ) {
						self.$loadmore.addClass( 'hidden' );
						self.$none.addClass( 'hidden' );

					} else {
						self.$loadmore.removeClass( 'hidden' );
					}

					// Adds point to load the next page
					if ( self.paginationType == 'load_on_scroll' ) {
						$us.waypoints.add( self.$loadmore, /* offset */'-70%', self._events.addNextPage );
					}
					$.each( self.listResultCounterOpts, ( index, opts ) => {
						const foundPosts = ( self.paginationType === 'none' )
							? $( '> *', self.$list ).length
							: self.data.ajaxData.found_posts;
						self.countResult( foundPosts, filterIsClear, opts, index );
					} );

					$us.$canvas.trigger( 'contentChange' );
				},
				complete: () => {
					self.$container.removeClass( 'filtering' );
					self.$loadmore.removeClass( 'loading' );
					if ( self.paginationType === 'load_on_scroll' ) {
						self.$loadmore.addClass( 'hidden' );
					}
					delete self.xhr;

					// Scroll to top of list
					self.scrollToList();
				}
			} );
		},

		/**
		 * Counts the number of results ( List Result Counter element )
		 *
		 * @param {Number} total
		 * @param {Boolean} filterIsClear
		 * @param {Object} opts
		 * @param {Number} index
		 */
		countResult: function( total, filterIsClear = false, opts, index ) {
			const self = this;
			if (
				! self.$listResultCounter.length
				|| ! self.isCurrentQuery
				|| $us.usbPreview()
			) {
				return;
			}

			const $listResultCounter = self.$listResultCounter.eq( index );
			const totalUnfiltered = opts.totalUnfiltered;
			const perPage = opts.perPage;

			$listResultCounter.show();

			total = filterIsClear ? totalUnfiltered : total;
			var lower = 1;
			if ( [ 'load_on_scroll', 'load_on_btn' ].includes( self.paginationType ) ) {
				lower = 1;
			} else {
				lower = ( self.data.paged - 1 ) * perPage + 1;
			}
			var upper = 1;
			if ( self.paginationType === 'none' ) {
				upper = $( '> *', self.$list ).length;
			} else {
				upper = Math.min( total, self.data.paged * perPage );
			}

			var formattedText = '';

			if ( total === 1 ) {
				formattedText = opts.textSingle;
			} else if ( total === 0 ) {
				if ( opts.textNoResults ) {
					formattedText = opts.textNoResults;
				} else {
					$listResultCounter.hide();
				}
			} else {
				formattedText = $ush.toString( opts.text )
					.replace( '[lower]', lower )
					.replace( '[upper]', upper )
					.replace( '[total]', total )
					.replace( '[total_unfiltered]', totalUnfiltered );
			}

			$listResultCounter.text( formattedText );
		},

		/**
		 * Reload layout in the Live Builder context.
		 *
		 * @event handler
		 */
		usbReloadIsotopeLayout: function() {
			const self = this;
			if ( self.$container.hasClass( 'with_isotope' ) ) {
				self.$list.isotope( 'layout' );
			}
		},
	} );

	// Popup window functionality
	$.extend( usPostList.prototype, {

		/**
		 * Initializing MagnificPopup for AJAX loaded items
		 *
		 * @param {Event} e
		 */
		_initMagnificPopup: function( e ) {
			e.stopPropagation();
			e.preventDefault();
			const $target = $( e.currentTarget );
			if ( $target.data( 'magnificPopup' ) === _undefined ) {
				$target.magnificPopup( {
					type: 'image',
					mainClass: 'mfp-fade'
				} );
				$target.trigger( 'click' );
			}
		},

	} );

	$.fn.usPostList = function() {
		return this.each( function() {
			$( this ).data( 'usPostList', new usPostList( this ) );
		} );
	};

	$( () => $( '.w-grid.us_post_list, .w-grid.us_product_list' ).usPostList() );

} )( jQuery );
