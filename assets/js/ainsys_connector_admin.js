jQuery( function ( $ ) {

	const ainsys_settings = {

		$settingsWrap: $( '.ainsys-settings-wrap' ),

		activeLastTab: function () {
			const lastTab = localStorage.getItem( 'lastTab' );

			if ( lastTab ) {
				$( '.nav-tab' ).removeClass( 'nav-tab-active' );
				$( '.tab-target' ).removeClass( 'tab-target-active' );
				$( 'a[href="' + lastTab + '"]' ).addClass( 'nav-tab-active' )
				$( lastTab ).addClass( 'tab-target-active' );
			} else {
				$( 'a[href="#setting-section-general"]' ).addClass( 'nav-tab-active' )
				$( '#setting-section-general' ).addClass( 'tab-target-active' );
			}
		},

		toggleTabs: function ( event ) {
			event.preventDefault();
			localStorage.setItem( 'lastTab', $( event.target ).attr( 'href' ) );
			const target = $( event.target ).data( 'target' );

			$( '.nav-tab' ).removeClass( 'nav-tab-active' );
			$( '.tab-target' ).removeClass( 'tab-target-active' );
			$( event.target ).addClass( 'nav-tab-active' );
			$( '#' + target ).addClass( 'tab-target-active' );
		},

		animationLogo: function () {
			setTimeout( () => {
				$( '.ainsys-logo' ).css( 'opacity', 1 )
			}, 500 )
		},

		buttonsEach: function () {
			$( '.ainsys-email-btn.ainsys-plus' ).each( function ( e ) {
				let btnPlus = $( this );
				let target  = $( this ).data( 'target' );

				$( '.ainsys-email' ).each( function () {
					if ( $( this ).data( 'block-id' ) === target && $( this ).hasClass( 'ainsys-email-show' ) ) {
						btnPlus.addClass( 'ainsys-email-btn-disabled' );
					}
				} );
			} );
		},

		togglePlus: function ( event ) {
			const target = $( event.target ).data( 'target' );
			$( event.target ).addClass( 'ainsys-email-btn-disabled' );
			$( '.ainsys-email' ).each( function () {
				if ( $( this ).data( 'block-id' ) === target ) {
					$( this ).addClass( 'ainsys-email-show' );
				}
			} );
		},

		toggleMinus: function ( event ) {
			$( event.target ).closest( '.ainsys-email' ).removeClass( 'ainsys-email-show' );
			$( event.target ).closest( '.ainsys-email' ).find( 'input' ).val( '' );
			const blockId = $( event.target ).closest( '.ainsys-email' ).data( 'block-id' );
			$( '.ainsys-email-btn.ainsys-plus' ).each( function () {
				if ( $( this ).data( 'target' ) === blockId ) {
					$( this ).removeClass( 'ainsys-email-btn-disabled' );
				}
			} );
		},

		reload: function () {
			window.location.reload();
		},


		removeAinsysIntegration: function ( event ) {
			const data = {
				action:    'remove_ainsys_integration',
				flush_all: $( event.target ).closest( '#setting-section-general' ).find( '#full-uninstall-checkbox' ).val()
			};

			const isConfirm = confirm( ainsys_connector_params.remove_ainsys_integration );

			if ( false === isConfirm ) {
				return;
			}

			$( event.target ).addClass( 'ainsys-loading' );

			$.ajax( {
				url:     ainsys_connector_params.ajax_url,
				data:    data,
				type:    'POST',
				success: function ( response ) {
					$( '#remove_ainsys_integration' ).removeClass( 'ainsys-loading' );
					location.reload();
				},
				error:   function ( response ) {
					$( '#remove_ainsys_integration' ).removeClass( 'ainsys-loading' );
					console.log( response );
				}
			} );
		},


		checkAinsysIntegration: function ( event ) {
			const data = {
				action:            'check_ainsys_integration',
				check_integration: true
			};

			$( event.target ).addClass( 'ainsys-loading' );

			let lastOperation = $(event.target).closest('.ainsys-settings-block').find('.ainsys-settings-block--connect-status--last-operation a span');

			$.ajax( {
				url:      ainsys_connector_params.ajax_url,
				data:     data,
				type:     'POST',
				dataType: 'json',
				success:  function ( response ) {
					$( '#check_ainsys_integration' ).removeClass( 'ainsys-loading' );

					lastOperation.text(response.data.result.time);
				},
				error:    function ( e ) {
					$( '#check_ainsys_integration' ).removeClass( 'ainsys-loading' );
					console.log( 'Error: ' + e.message );
				}
			} );
		},

		init: function () {

			$( '#connection_log .ainsys-table' ).DataTable( {
				"sPaginationType": "full_numbers",
				"aaSorting":       [ [ 0, "asc" ] ],
				"iDisplayLength":  50,
				"aLengthMenu":     [ [ 5, 10, 25, 50, 100, -1 ], [ 5, 10, 25, 50, 100, "All" ] ]
			} );

			this.animationLogo();

			this.activeLastTab();

			this.buttonsEach();

			this.$settingsWrap
				.on(
					'click',
					'.nav-tab',
					function ( event ) {
						ainsys_settings.toggleTabs( event );
					}
				)
				.on(
					'click',
					'.ainsys-plus',
					function ( event ) {
						ainsys_settings.togglePlus( event );
					}
				)
				.on(
					'click',
					'.ainsys-minus',
					function ( event ) {
						ainsys_settings.toggleMinus( event );
					}
				)
				.on(
					'click',
					'#remove_ainsys_integration',
					function ( event ) {
						ainsys_settings.removeAinsysIntegration( event );

					}
				)
				.on(
					'click',
					'#check_ainsys_integration',
					function ( event ) {
						ainsys_settings.checkAinsysIntegration( event );
					}
				)
		},

	};

	ainsys_settings.init();

	/////////////////////////////////
	////////////   Test tab   ///////

	//////// Ajax test btns ////////
	$( '#setting-section-test' ).on( 'click', '.ainsys-check', function ( e ) {
		e.preventDefault();

		if ( $( this ).hasClass( 'ainsys-loading' ) ) {
			return;
		}
		const entity          = $( this ).data( 'entity-name' );
		const requestTdShort  = $( this ).closest( 'tr' ).find( '.ainsys-table-table__cell-outgoing' ).find( '.ainsys-response-short' );
		const requestTdFull   = $( this ).closest( 'tr' ).find( '.ainsys-table-table__cell-outgoing' ).find( '.ainsys-response-full pre' );
		const responseTdShort = $( this ).closest( 'tr' ).find( '.ainsys-table-table__cell-server_response' ).find( '.ainsys-response-short' );
		const responseTdFull  = $( this ).closest( 'tr' ).find( '.ainsys-table-table__cell-server_response' ).find( '.ainsys-response-full pre' );
		const responseTime    = $( this ).closest( 'tr' ).find( '.ainsys-table-table__cell--time--inside' );
		const responseStatus  = $( this ).closest( 'tr' ).find( '.ainsys-table-table__cell--status--inside' );

		$( this ).addClass( 'ainsys-loading' );

		var data = {
			action: 'test_entity_connection',
			entity: entity,
			nonce:  ainsys_connector_params.nonce
		};

		$.ajax( {
			url:     ainsys_connector_params.ajax_url,
			type:    'POST',
			data:    data,
			success: function ( response ) {
				$( '.ainsys-check' ).removeClass( 'ainsys-loading' );

				let result       = response.data.result;
				let resultEntity = result[ entity ];

				responseTime.text( resultEntity.time )
				requestTdShort.text( resultEntity.short_request );
				responseTdShort.text( resultEntity.short_response );
				requestTdFull.html( resultEntity.full_request );
				responseTdFull.html( resultEntity.full_response );

				if ( resultEntity.status ) {
					responseStatus.html( '<span class="ainsys-status--ok ainsys-status--state"><svg fill="none" viewBox="0 0 24 24"><g clip-path="url(#a)"><path fill="#37B34A"'
					                     + ' d="M16.59 7.58 10 14.17l-3.59-3.58L5 12l5 5 8-8-1.41-1.42ZM12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/></g><defs><clipPath id="a"><path fill="#fff" d="M0 0h24v24H0z"/></clipPath></defs></svg>'
					                     + ainsys_connector_params.check_connection_entity_connect + '</span>' );

				} else {
					responseStatus.html( '<span class="ainsys-status--error ainsys-status--state"><svg class="ainsys-icon ainsys-icon--error"fill=none viewBox="0 0 24 24"><g clip-path=url(#a) fill=#D5031E><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z"/><path d="m17 8-1-1-4 4-4-4-1 1 4 4-4 4 1 1 4-4 4 4 1-1-4-4 4-4Z"stroke=#D5031E stroke-width=.5 /></g><defs><clipPath id=a><path d="M0 0h24v24H0z"fill=#fff /></clipPath></defs></svg>'
					                     + ainsys_connector_params.check_connection_entity_no_connect + '</span>' );
				}


			},
			error:   function () {
				$( '.ainsys-check' ).removeClass( 'ainsys-loading' );
				requestTdShort.text( 'Error!' );
				responseTdShort.text( 'Error!' );
			}
		} );
	} );


	let timeout;
	$( '#setting-section-entities' ).on( 'change', '.ainsys-table--controlling-entities .toggle-checkbox', function ( event ) {
		$( event.target ).addClass( 'ainsys-loading' );
		$( event.target ).attr( 'disabled', true );

		const responseTime = $( event.target ).closest( 'tr' ).find( '.ainsys-table-table__cell--last_exchange--inside' );

		if ( $( event.target ).is( ':checked' ) ) {
			$( event.target ).val( 1 )
		} else {
			$( event.target ).val( 0 )
		}

		let valueEntity = $( event.target ).val();

		let entity = $( event.target ).data( 'toggle-checkbox-entity-id' );
		let column = $( event.target ).data( 'toggle-checkbox-column-id' );


		if ( column === 'on_off' ) {
			let allCheckboxTr = $( event.target ).closest( 'tr' ).find( '.toggle-checkbox' );

			if ( ! $( event.target ).is( ':checked' ) ) {
				$.each( allCheckboxTr, function ( index, element ) {
					if ( index === 0 ) {
						return true;
					}

					$( element ).attr( 'disabled', true )
					//$(element).prop('checked', false)
				} );
			} else {
				$.each( allCheckboxTr, function ( index, element ) {
					if ( index === 0 ) {
						return true;
					}

					$( element ).attr( 'disabled', false )
				} );
			}
		}

		if ( timeout !== undefined ) {
			clearTimeout( timeout );
		}

		const data = {
			action: 'save_entities_controlling',
			entity: entity,
			column: column,
			value:  valueEntity,
		};
		timeout    = setTimeout( function () {
			$.ajax( {
				url:     ainsys_connector_params.ajax_url,
				type:    'POST',
				data:    data,
				success: function ( response ) {
					console.log( response );

					$( event.target ).removeClass( 'ainsys-loading' );
					$( event.target ).attr( 'disabled', false )
					let result       = response.result;
					let resultEntity = result[ entity ].general;

					responseTime.text( resultEntity.time )
				},
				error:   function ( e ) {
					console.log( 'Error: ' + e.message );
				}
			} );
		}, 100 );

	} );

	/////////////////////////////////
	////////////   Log tab   ///////

	//////// Ajax clear log ////////
	$( '#setting-section-log' ).on( 'click', '#clear_log', function ( e ) {
		$.ajax( {
			url:        ainsys_connector_params.ajax_url,
			type:       'POST',
			data:       {
				action: "clear_log",
				nonce:  ainsys_connector_params.nonce
			},
			beforeSend: function ( xhr ) {
				$( e.target ).addClass( 'disabled' );
			},
			success:    function ( value ) {
				$( e.target ).removeClass( 'disabled' );
				if ( value ) {
					$( '#connection_log' ).html( value );
				}
			}
		} )
	} );



	//////// Ajax start/stop loging  ////////
	function checkToDisableLogging() {

		let timerId;
		const endTime  = parseInt( $( '.ainsys-log-time' ).text() );
		const now      = Date.now() / 1000;
		const nowTime  = now.toFixed();
		const timeLeft = endTime - nowTime;

		if ( endTime > 0 ) {
			if ( timeLeft > 0 ) {
				timerId = setTimeout( checkToDisableLogging, 1000 );
			} else {
				clearTimeout( timerId );
				$( '#stop_loging' ).trigger( 'click' );
			}
		}
	}


	$( document ).ready( function () {
		checkToDisableLogging();
	} );

	//////// Ajax start/stop loging btns  ////////
	$( '#setting-section-log' ).on( 'click', '.ainsys-log-control', function ( e ) {
		e.preventDefault();

		if ( $( this ).hasClass( 'disabled' ) ) {
			return;
		}
		const id      = $( this ).attr( 'id' );
		const time    = $( '#start_loging_timeinterval' ).val();
		const date    = new Date(); // new Date().toLocaleString();
		const min     = date.getMinutes() >= 10 ? date.getMinutes() : '0' + date.getMinutes();
		const sec     = date.getSeconds() >= 10 ? date.getSeconds() : '0' + date.getSeconds();
		const startAt = date.getDate().toString() + '.' + (
		                date.getMonth() + 1
		).toString() + '.' + date.getFullYear().toString() + ' ' + date.getHours() + ':' + min + ':' + sec;

		$( '.ainsys-log-status' ).addClass( 'ainsys-loading' );

		$( '.ainsys-log-control' ).addClass( 'disabled' );
		$( '#start_loging_timeinterval' ).addClass( 'disabled' ).prop( 'disabled', true );

		var data = {
			action:  'toggle_logging',
			command: id,
			time:    time,
			startat: startAt,
			nonce:   ainsys_connector_params.nonce
		};
		jQuery.post( ainsys_connector_params.ajax_url, data, function ( response ) {
			$( '.ainsys-log-status' ).removeClass( 'ainsys-loading' );

			if ( response.logging_since ) {
				$( '#stop_loging' ).removeClass( 'disabled' );
				$( '#start_loging_timeinterval' ).addClass( 'disabled' ).prop( 'disabled', true );
				$( '.ainsys-log-time' ).text( response.logging_time );
				$( '.ainsys-log-status-ok' ).show();
				$( '.ainsys-log-status-no' ).hide();
				$( '.ainsys-log-since' ).text( response.logging_since );
				checkToDisableLogging();
			} else {
				$( '#start_loging' ).removeClass( 'disabled' );
				$( '#start_loging_timeinterval' ).removeClass( 'disabled' ).prop( 'disabled', false ).val( time);
				$( '.ainsys-log-time' ).text( '-1' );
				$( '.ainsys-log-status-ok' ).hide();
				$( '.ainsys-log-status-no' ).show();
			}
		} );
	} );

	////////  Ajax reload log HTML ////////
	$( '#setting-section-log' ).on( 'click', '#reload_log', function ( e ) {

		$.ajax( {
			url:        ainsys_connector_params.ajax_url,
			type:       'POST',
			data:       {
				action: 'reload_log_html',
				nonce:  ainsys_connector_params.nonce
			},
			beforeSend: function ( xhr ) {
				$( e.target ).addClass( 'disabled' );
			},
			success:    function ( msg ) {
				$( e.target ).removeClass( 'disabled' );

				if ( msg ) {
					$( '#connection_log' ).html( msg );
					$( '#connection_log .ainsys-table' ).DataTable( {
						"sPaginationType": "full_numbers",
						"aaSorting":       [ [ 0, "asc" ] ],
						"iDisplayLength":  50,
						"aLengthMenu": [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]]
					});
					//location.reload();
				}
			}
		} )
	} );


	$( '.ainsys-settings-wrap' ).on( 'click', '.ainsys-response-short', function ( e ) {
		const fullResponse = $( this ).siblings( '.ainsys-response-full' ).html();
		$( 'body' ).append( '<div class="ainsys-overlay"><div class="ainsys-popup"><div class="ainsys-popup-body"><div class="ainsys-popup-response">' + fullResponse
		                    + '</div></div><div class="ainsys-popup-btns"><span class="btn btn-primary ainsys-popup-copy" data-clipboard-target=".ainsys-overlay'
		                    + ' .ainsys-popup-response pre">Copy to Clipboard</span><span class="btn btn-tertiary'
		                    + ' ainsys-popup-close">Close</span></div></div></div>' );
		const respHeight = $( '.ainsys-popup' ).height() - $( '.ainsys-popup-btns' ).outerHeight() - 40;
		$( '.ainsys-popup-response' ).css( 'height', respHeight );

		new ClipboardJS( '.ainsys-popup-copy' ).on( 'success', function ( e ) {
			console.log( e.trigger );
			$( e.trigger ).text( 'Copied!' );
			setTimeout( function () {
				$( e.trigger ).text( 'Copy to Clipboard' );
			}, 1000 );
			e.clearSelection();
		} );

	} );

	$( window ).on( 'resize', function () {
		if ( $( '.ainsys-popup-response' ).length > 0 ) {
			const respHeight = $( '.ainsys-popup' ).height() - $( '.ainsys-popup-btns' ).outerHeight() - 40;
			$( '.ainsys-popup-response' ).css( 'height', respHeight );
		}
	} );
	$( document ).on( 'click', '.ainsys-popup-close', function ( e ) {
		$( '.ainsys-overlay' ).remove();
	} );

} );
