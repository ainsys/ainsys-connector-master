// ////////////////////////////////////
jQuery(function($){

    setTimeout(() => {
        $('.ainsys-logo').css('opacity', 1)
    }, 500)

    $('.ainsys_settings_wrap').on('click', '.nav-tab', function(event){

        event.preventDefault();

        var targ = $(this).data('target');

        $('.nav-tab').removeClass('nav-tab-active');
        $('.tab-target').removeClass('tab-target-active');
        $(this).addClass('nav-tab-active');
        $('#'+targ).addClass('tab-target-active');

        var ref = $('.ainsys_settings_wrap input[name="_wp_http_referer"]').val();
        var new_ref = ref;
        var query_string = {};
        var url_vars = ref.split("?");
        if (url_vars.length > 1){
            new_ref = url_vars[0] + '?';
            var url_pairs = url_vars[1].split("&");
            for (var i=0;i<url_pairs.length;i++){

                var pair = url_pairs[i].split("=");

                if (pair[0] != 'setting_tab'){
                    new_ref = new_ref + url_pairs[i] + '&';
                }
            }
        } else {
            new_ref = new_ref + '?';
        }
        new_ref = new_ref + 'setting_tab=' + targ;
        $('.ainsys_settings_wrap input[name="_wp_http_referer"]').val(new_ref);
    });
	$('.ainsys-tabs').on('click', '.ainsys-nav-tab', function(event){

        event.preventDefault();

        const targ = $(this).data('target');

        $( this).closest('.ainsys-tabs').find('.ainsys-nav-tab').removeClass('ainsys-nav-tab-active');
		$( this).closest('.ainsys-tabs').find('.ainsys-tab-target').removeClass('ainsys-tab-target-active');
        $(this).addClass('ainsys-nav-tab-active');
        $('#'+targ).addClass('ainsys-tab-target-active');
	});

	$('.ainsys_settings_wrap').on('click', '.ainsys-plus', function(){
		const target = $( this ).data( 'target' );
		$( this ).addClass( 'ainsys-email-btn-disabled' );
		$( '.aisys-email' ).each( function() {
			if ( $(this).data( 'block-id' ) == target ) {
				$( this ).addClass( 'aisys-email-show' );
			}
		} );
	} );

	$('.ainsys_settings_wrap').on('click', '.ainsys-minus', function(){
		$( this ).closest( '.aisys-email' ).removeClass( 'aisys-email-show' );
		$( this ).closest( '.aisys-email' ).find( 'input' ).val('');
		const blockId = $( this ).closest( '.aisys-email' ).data( 'block-id' );
		$( '.ainsys-email-btn.ainsys-plus' ).each( function() {
			if ( $(this).data( 'target' ) == blockId ) {
				$( this ).removeClass( 'ainsys-email-btn-disabled' );
			}
		} );
	} );

	$( '.ainsys-email-btn.ainsys-plus' ).each( function() {
		const btnPlus = $( this );
		const target = $(this).data( 'target' );
		$( '.aisys-email' ).each( function() {
			if ( $(this).data( 'block-id' ) == target && $(this).hasClass( 'aisys-email-show' ) ) {
				btnPlus.addClass( 'ainsys-email-btn-disabled' );
			}
		} );
	} );


	/////////////////////////////////
    ////////////   Test tab   ///////

	//////// Ajax test btns ////////
    $( '#setting_section_test' ).on( 'click', '.ainsys-test', function(e) {
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
				const result = JSON.parse( response );
				requestTdShort.text( result.short_request );
				responceTdShort.text( result.short_responce );
				requestTdFull.html( result.full_request );
				responceTdFull.html( result.full_responce );
				if ( result.full_responce.indexOf( 'Error' ) !== -1 ) {
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
    $('#setting_section_log').on('click', '#clear_log', function (e){
        var data = {
            action: "clear_log",
            nonce: ainsys_connector_params.nonce
        };
        jQuery.post(ainsys_connector_params.ajax_url, data, function (value) {
            if(value){
                $('#connection_log').html(value);
            }
        });
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
    $( '#setting_section_log' ).on( 'click', '.ainsys-log-control', function(e) {
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
    $('#setting_section_log').on('click', '#reload_log', function (e){
        $.ajax({
            url: ainsys_connector_params.ajax_url,
            type: 'POST',
            data: {
                action: 'reload_log_html',
                // check
                nonce: ainsys_connector_params.nonce
            },
            success: function (msg) {
                if(msg){
                    $('#connection_log').html(msg);
                }
            }//,
            // error: function (jqXHR, exception) {
            //
            // }
        })
    });

	$('.ainsys_settings_wrap').on('click', '.ainsys-responce-short', function (e){
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
    $('#setting_section_general').on('click', '#remove_ainsys_integration', function (e){
        var data = {
            action: "remove_ainsys_integration",
            nonce: ainsys_connector_params.nonce
        };
        jQuery.post(ainsys_connector_params.ajax_url, data, function (value) {
            location.reload();
        });
    });

    //////// Expand/collapse data container ////////
    $('#setting_section_log').on('click', '.expand_data_container', function (e){
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
    $('#setting_entities_section .entities_field').on('click', '.fa.active', function (e){

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
    $('#setting_entities_section').on('click', ' .entities_field', function (e){
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
    $('#setting_entities_section').on('click', ' .expand_entiti_container', function (e){
        $(this).parent().parent().toggleClass('active');
        var text = $(this).text() == 'expand' ? 'collapse' : 'expand';
        $(this).text(text);
    });
});
