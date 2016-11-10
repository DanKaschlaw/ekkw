jQuery(document).ready(function($) {

	var su_msgs = SU.messages;

	$('#su-wrapper .js-su-user').on('click', function(event) {
		event.preventDefault();

		if ( ! $('#su-wrapper').hasClass('working') ) {

			$('#su-wrapper').addClass('working');
			$('#su-wrapper').removeClass('open');

			var user_id = $(this).attr('data-user-id');
			var su_security = $('#su-wrapper').find('#su-change-user-security').val();

			$.ajax({
				url: SU.ajaxurl,
				type: 'POST',
				data: {
					action: 'switch_user_change_user',
					user_id: user_id,
					su_nonce: su_security
				},
			})
			.done(function(data) {

				if ( data.status == 'ok' ) {
					alert( su_msgs.change_success );
					window.location.reload(true);
				} else if ( data.msg != '' ) {
					alert( data.msg);
				} else {
					alert( su_msgs.change_error );
				}

			})
			.fail(function() {
				alert( su_msgs.connection_error );
			});
		}

	});

	$('.su-wrapper-toggle').on('click', function(event) {
		event.preventDefault();
		$('#su-wrapper').toggleClass('open');
	});

});
