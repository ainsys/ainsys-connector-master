jQuery(function($){

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
			$( '.ainsys-email-btn.ainsys-plus' ).each( function(e) {
				let btnPlus = $( this );
				let target = $(this).data( 'target' );

				$( '.ainsys-email' ).each( function() {
					if ( $(this).data( 'block-id' ) === target && $(this).hasClass( 'ainsys-email-show' ) ) {
						btnPlus.addClass( 'ainsys-email-btn-disabled' );
					}
				} );
			} );
		},

		togglePlus:   function ( event ) {
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

		reload: function ( ) {
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
				type:     'POST',
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
				action:    'check_ainsys_integration',
				check_integration: true
			};

			$( event.target ).addClass( 'ainsys-loading' );

			$.ajax( {
				url:     ainsys_connector_params.ajax_url,
				data:    data,
				type:     'POST',
				dataType: 'json',
				success: function ( response ) {
					$( '#check_ainsys_integration' ).removeClass( 'ainsys-loading' );
					console.log( response );
				},
				error:   function ( response ) {
					$( '#check_ainsys_integration' ).removeClass( 'ainsys-loading' );
					console.log( response );
				}
			} );
		},

		init:      function () {

			$( '#connection_log .ainsys-table' ).DataTable();

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



/*	$('.ainsys-tabs').on('click', '.ainsys-nav-tab', function(event){

        event.preventDefault();

        const targ = $(this).data('target');

        $( this).closest('.ainsys-tabs').find('.ainsys-nav-tab').removeClass('ainsys-nav-tab-active');
		$( this).closest('.ainsys-tabs').find('.ainsys-tab-target').removeClass('ainsys-tab-target-active');
        $(this).addClass('ainsys-nav-tab-active');
        $('#'+targ).addClass('ainsys-tab-target-active');
	});*/


	/////////////////////////////////
    ////////////   Test tab   ///////

	//////// Ajax test btns ////////
    $( '#setting-section-test' ).on( 'click', '.ainsys-test', function(e) {
        e.preventDefault();

        if ( $(this).hasClass( 'ainsys-loading' ) ){
            return;
        }
        const entity = $( this ).data( 'entity-name' );
		const requestTdShort = $( this ).closest( 'tr' ).find( '.ainsys-test-json' ).find( '.ainsys-responce-short' );
		const requestTdFull = $( this ).closest( 'tr' ).find( '.ainsys-test-json' ).find( '.ainsys-responce-full' );
		const responceTdShort = $( this ).closest( 'tr' ).find( '.ainsys-test-responce' ).find( '.ainsys-responce-short' );
		const responceTdFull = $( this ).closest( 'tr' ).find( '.ainsys-test-responce' ).find( '.ainsys-responce-full' );
		const testSuccess = $( this ).closest( 'tr' ).find( '.ainsys-success' );
		const testFailure = $( this ).closest( 'tr' ).find( '.ainsys-failure' );

		testFailure.removeClass( 'ainsys-test-finished' );
		testSuccess.removeClass( 'ainsys-test-finished' );

		$( this ).addClass( 'ainsys-loading' );

		var data = {
            action: 'test_entity_connection',
            entity: entity,
            nonce: ainsys_connector_params.nonce
        };

		$.ajax( {
            url: ainsys_connector_params.ajax_url,
            type: 'POST',
            data: data,
            success: function (response) {
				$( '.ainsys-test' ).removeClass( 'ainsys-loading' );
				console.log(response);
				//const result = JSON.parse( response );
				requestTdShort.text( response.short_request );
				responceTdShort.text( response.short_responce );
				requestTdFull.html( response.full_request );
				responceTdFull.html( response.full_responce );
				if ( response.full_responce.indexOf( 'Error' ) !== -1 ) {
					testFailure.addClass( 'ainsys-test-finished' );
				} else {
					testSuccess.addClass( 'ainsys-test-finished' );
				}
			},
            error: function () {
				$( '.ainsys-test' ).removeClass( 'ainsys-loading' );
				requestTdShort.text( 'Error!' );
				responceTdShort.text( 'Error!' );
				testFailure.addClass( 'ainsys-test-finished' );
            }
        } );
    } );

	/////////////////////////////////
    ////////////   Log tab   ///////

    //////// Ajax clear log ////////
    $('#setting-section-log').on('click', '#clear_log', function (e){
 	    $.ajax( {
		    url:        ainsys_connector_params.ajax_url,
		    type:       'POST',
		    data:{
			    action: "clear_log",
			    nonce: ainsys_connector_params.nonce
		    },
		    beforeSend: function ( xhr ) {
			    $( e.target ).addClass( 'disabled' );
		    },
		    success:    function ( value ) {
			    $( e.target ).removeClass( 'disabled' );
			    if(value){
				    $('#connection_log').html(value);
			    }
		    }
	    } )
    });

	//////// Ajax start/stop loging  ////////
	function checkToDisableLogging() {
		
		let timerId;
		const endTime = parseInt( $( '.ainsys-log-time' ).text() );
		const now = Date.now() / 1000;
		const nowTime = now.toFixed();
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
	$( document ).ready( function() {
		checkToDisableLogging();
	} );

    //////// Ajax start/stop loging btns  ////////
    $( '#setting-section-log' ).on( 'click', '.ainsys-log-control', function(e) {
        e.preventDefault();

        if ( $(this).hasClass( 'disabled' ) ){
            return;
        }
        const id = $( this ).attr( 'id' );
        const time = $( '#start_loging_timeinterval' ).val();
		const date = new Date(); // new Date().toLocaleString();
		const min = date.getMinutes() >= 10 ? date.getMinutes() : '0' + date.getMinutes();
		const sec = date.getSeconds() >= 10 ? date.getSeconds() : '0' + date.getSeconds();
		const startAt = date.getDate().toString() + '.' + ( date.getMonth() + 1 ).toString() + '.' + date.getFullYear().toString() + ' ' + date.getHours() + ':' + min + ':' + sec;

		$( '.ainsys-log-status' ).addClass( 'ainsys-loading' );

		$( '.ainsys-log-control' ).addClass( 'disabled' );
		$('#start_loging_timeinterval').addClass( 'disabled' ).prop( 'disabled', true );

		var data = {
            action: 'toggle_logging',
            command: id,
            time: time,
			startat: startAt,
            nonce: ainsys_connector_params.nonce
        };
        jQuery.post(ainsys_connector_params.ajax_url, data, function ( response ) {
			$( '.ainsys-log-status' ).removeClass( 'ainsys-loading' );
			const result = JSON.parse( response );

			if ( result.logging_since ) {
				$( '#stop_loging' ).removeClass( 'disabled' );
                $( '#start_loging_timeinterval' ).addClass( 'disabled' ).prop( 'disabled', true );
				$( '.ainsys-log-time' ).text( result.logging_time );
				$( '.ainsys-log-status-ok' ).show();
				$( '.ainsys-log-status-no' ).hide();
				$( '.ainsys-log-since' ).text( result.logging_since );
				checkToDisableLogging();
            } else {
				$( '#start_loging' ).removeClass( 'disabled' );
				$( '#start_loging_timeinterval' ).removeClass( 'disabled' ).prop( 'disabled', false ).val( -1 );
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
					$('#connection_log .ainsys-table').DataTable();
					//location.reload();
				}
			}
		} )
	} );

	$('.ainsys-settings-wrap').on('click', '.ainsys-responce-short', function (e){
		const fullResponse = $( this ).siblings( '.ainsys-responce-full' ).html();
		$( 'body' ).append('<div class="ainsys-overlay"><div class="ainsys-popup"><div class="ainsys-popup-body"><div class="ainsys-popup-response">' + fullResponse + '</div></div><div class="ainsys-popup-btns"><span class="btn btn-primary ainsys-popup-copy">Copy to Clipboard</span><span class="btn btn-tertiary ainsys-popup-close">Close</span></div></div></div>');
		const respHeight = $( '.ainsys-popup' ).height() - $( '.ainsys-popup-btns' ).outerHeight() - 40;
		$( '.ainsys-popup-response' ).css( 'height', respHeight );

	} );
	$( window ).on( 'resize', function(){
		if ( $( '.ainsys-popup-response' ).length > 0 ) {
			const respHeight = $( '.ainsys-popup' ).height() - $( '.ainsys-popup-btns' ).outerHeight() - 40;
			$( '.ainsys-popup-response' ).css( 'height', respHeight );
		}
	} );
	$( document ).on('click', '.ainsys-popup-close', function (e) {
		$( '.ainsys-overlay' ).remove();
	} );

	$( document ).on('click', '.ainsys-popup-copy', function (e) {
		var $temp = $( '<input>' );
  		$( 'body' ).append( $temp );
		$temp.val( $( '.ainsys-popup-response' ).text() ).select();
		document.execCommand('copy');
		$temp.remove();
	} );

    //////// Ajax remove ainsys integration ////////
	/*$( '#setting-section-general' ).on( 'click', '#remove_ainsys_integration', function ( e ) {
		const data = {
			action:    'remove_ainsys_integration',
			flush_all: $( e.target ).closest( '#setting-section-general' ).find( '#full-uninstall-checkbox' ).val()
		};

		const isConfirm = confirm( ainsys_connector_params.remove_ainsys_integration );

		if ( false === isConfirm ) {
			return;
		}

		$( this ).addClass( 'ainsys-loading' );

		$.ajax( {
			url:     ainsys_connector_params.ajax_url,
			data:    data,
			success: function ( response ) {
				$( '#remove_ainsys_integration' ).removeClass( 'ainsys-loading' );
				location.reload();
			},
			error:   function ( response ) {
				$( '#remove_ainsys_integration' ).removeClass( 'ainsys-loading' );
				console.log( response );
			}
		} );
	} );*/

    //////// Expand/collapse data container ////////
    $('#setting-section-log').on('click', '.expand_data_container', function (e){
        $(this).parent().toggleClass('expand_tab');

        var text = $(this).text() == 'less' ? 'more' : 'less';
        $(this).text(text);

    })

    /////////////////////////////////
    ////////////Settings tabs///////

    let auto_update = false;

    $('.entities_field .properties_field').on('change', '.entiti_settings_value', function () {
        if (!auto_update) {
            let setting_id = '#' + $(this).parent().parent().attr('id');
            let new_value = $(this).val();

            if ( $(this).attr("type") == 'checkbox' ){
                new_value = $(this).val() == 1 ? 'On' : 'Off';
            }

            $( setting_id + ' i').addClass('active');
            $( setting_id ).attr('data-' + $(this).attr("id"), $(this).val()).data($(this).attr("id"), $(this).val());
            if ( $(this).attr("id") == 'api' ){
                $(this).parent().find('div').attr('class', '');
                $(this).parent().find('div').addClass('entiti_settings_value').addClass(new_value);
            } else {
                $(this).parent().find('div').html( new_value );
            }
            //$('#save_entiti_properties').attr('disable', false);
        }
    })

    $('.entities_field .properties_field input').on('click', function () {
        if ( $(this).attr("type") == 'checkbox' ){
            let val = $(this).val() == 1 ? 0 : 1;
            $(this).attr('value', val);
        }
    });

    //////// Ajax clear log ////////
    $('#setting-section-entities .entities_field').on('click', '.fa.active', function (e){

        let setting_id = '#' + $(this).parent().attr('id');
        $(setting_id).toggleClass('loading');
        let data = {
            action: "save_entiti_settings",
            nonce: ainsys_connector_params.nonce,
        };
        // let temp = $(setting_id).data();

        $.each($(setting_id).data(), function(key,value) {
            data[key] = value;
        })

        jQuery.post(ainsys_connector_params.ajax_url, data, function (value) {
            if(value){
                //console.log($(setting_id + ' .fa'));
                $(setting_id + ' .fa').removeClass('active');
                $(setting_id + ' #id').val(value);
                $(setting_id + ' #id').parent().find('div').html(value);
            }
            $(setting_id).toggleClass('loading');
        });
    });

    ////////  ////////
    $('#setting-section-entities').on('click', ' .entities_field', function (e){
        $('.entities_field.active').each(function(){
            $(this).removeClass('active');
        })

        let obj_id = $(this).attr("id");
        $('.properties_data #setting_name').html($(this).data('seting_name'));
        $('.properties_data #setting_name').attr('data-seting_name', $(this).data('seting_name')).attr('data-entiti', $(this).data('entiti'));

        auto_update = true;
        $.each($(this).data(), function(key,value) {
            let input_obj = $('.properties_data .properties_field #' + key );
            let input_type = $('.properties_data .properties_field #' + key ).attr("type");
            if (input_obj.is("select")) input_type = 'select';
            switch (input_type){
                case 'text':
                    $(input_obj).val(value);
                    break;
                case 'checkbox':
                    $(input_obj).attr('value', value);
                    $(input_obj).prop('checked', Boolean(value));
                    break;
                case 'select':
                    $(input_obj).val(value).change();
                default:
                    $(input_obj).val(value);
                    break;
            }
        });
        auto_update = false;
        $(this).addClass('active');
    });
 
	//////// expand entity tab ////////
    $('#setting-section-entities').on('click', ' .expand_entity_container', function (e){
        $(this).parent().parent().toggleClass('active');
        var text = $(this).text() == 'expand' ? 'collapse' : 'expand';
        $(this).text(text);
    });
});
