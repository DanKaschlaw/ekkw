;
jQuery(document).ready(function(){
	jQuery('.wpfts_instant_help').on('click', function(e){
		e.preventDefault();
		return false;
	});
	
	jQuery('.wpfts_submit').on('click', function(e){
		e.preventDefault();
		
		var formdata = wpftsiFormData(jQuery('#wpftsi_form'));
		wpftsiAction('wpftsi_submit_settings', formdata);
		
		return false;
	});
	
	jQuery('.wpfts_submit2').on('click', function(e){
		e.preventDefault();
		
		var confirm_text = jQuery(this).attr('data-confirm');
		var isallow = false;
		if ((confirm_text) && (confirm_text.length > 0)) {
			if (confirm(confirm_text)) {
				isallow = true;
			}
		} else {
			isallow = true;
		}
		
		if (isallow) {
			var formdata = wpftsiFormData(jQuery('#wpftsi_form2'));
			wpftsiAction('wpftsi_submit_settings2', formdata);
		}
		
		return false;
	});
	
	jQuery('.wpfts_btn_rebuild').on('click', function(e){
		e.preventDefault();
		
		var confirm_text = jQuery(this).attr('data-confirm');
		var isallow = false;
		if ((confirm_text) && (confirm_text.length > 0)) {
			if (confirm(confirm_text)) {
				isallow = true;
			}
		} else {
			isallow = true;
		}
		
		if (isallow) {
			
			jQuery('.wpfts_show_resetting').css('display', 'block');
			
			var formdata = wpftsiFormData(jQuery('#wpftsi_form'));
			wpftsiAction('wpftsi_submit_rebuild', formdata, function(jx){
				jQuery('.wpfts_show_resetting').css('display', 'none');
			});
		}
		
		return false;
	});
	
	jQuery('#wpfts_testbutton').on('click', function(){
		
		jQuery('#wpfts_sandbox_output').html('<hr><p>' + wpfts_test_waiter() + '</p>');
		
		var formdata = wpftsiFormData(jQuery('#wpftsi_form3'));
		wpftsiAction('wpftsi_submit_testpost', formdata, function(jx){
			
			jQuery('#wpfts_sandbox_output').html('');

			if (('code' in jx) && (jx['code'] === 0)) {
				jQuery('#wpfts_sandbox_output').html(jx['text']);
			}
		});
		
		return false;
	});
	
	jQuery('#wpfts_testquerybutton').on('click', function(){
		
		jQuery('#wpfts_sandbox_output').html('<hr><p>' + wpfts_test_waiter() + '</p>');
		
		var formdata = wpftsiFormData(jQuery('#wpftsi_form3'));
		wpftsiAction('wpftsi_submit_testsearch', formdata, function(jx){
			
			jQuery('#wpfts_sandbox_output').html('');
			
			if (('code' in jx) && (jx['code'] === 0)) {
				jQuery('#wpfts_sandbox_output').html(jx['text']);
			}
		});
		
		return false;
	});
	
	jQuery('#wpfts_sandbox_output').on('click', '.wpfts_tq_prevpage', function(){
		
		var i = parseInt(jQuery('#wpfts_tq_current_page').val());
		if (i > 1) {
			i --;
			wpfts_tqChangePage(i);
		}
		
		return false;
	});
	
	jQuery('#wpfts_sandbox_output').on('click', '.wpfts_tq_nextpage', function(){
		
		var i = parseInt(jQuery('#wpfts_tq_current_page').val());
		
		i ++;
		wpfts_tqChangePage(i);
		
		return false;
	});
	
	jQuery('#wpfts_sandbox_output').on('change', '#wpfts_tq_current_page', function(){
		
		var i = parseInt(jQuery('#wpfts_tq_current_page').val());
		wpfts_tqChangePage(i);
		
		return false;
	});
	
	jQuery('#wpfts_sandbox_output').on('change', '#wpfts_tq_n_perpage', function(){
		
		wpfts_tqChangePage(1);
		return false;
	});
	
	var pingprocessor = function(jx) {
		if (('code' in jx) && (jx['code'] === 0)) {
			wpftsShowIndexStatus(jx['status']);
			var result = jx['result'];
			switch (result) {
				case 5:
					// Start indexing of part
					wpftsiAction('wpftsi_rebuild_step', {'pid': wpfts_pid}, pingprocessor);
					break;
				case 10:
					// Indexing in progress (other process)
					setTimeout(pingtimer, wpfts_pingtimeout);
					break;
				case 0:
				default:
					// Nothing to index
					setTimeout(pingtimer, wpfts_pingtimeout);
			}
		}
	};
   
	// Start ping system
	var pingtimer = function () {
		wpftsiAction('wpftsi_ping', {'pid': wpfts_pid}, pingprocessor);
	};

	pingtimer();

});

function wpfts_tqChangePage(i) {
	
	var formdata = wpftsiFormData(jQuery('#wpftsi_form3'));
	
	formdata['wpfts_tq_current_page'] = i;
	formdata['wpfts_tq_n_perpage'] = jQuery('#wpfts_tq_n_perpage').val();
	
	wpftsiAction('wpftsi_submit_testsearch', formdata, function(jx){
			
		jQuery('#wpfts_sandbox_output').html('');
			
		if (('code' in jx) && (jx['code'] === 0)) {
			jQuery('#wpfts_sandbox_output').html(jx['text']);
		}
	});
}

function wpfts_test_waiter() {
	return '<img src="' + wpfts_root_url + '/style/waiting.gif" alt="">';
}

function wpftsShowIndexStatus(st) {
	jQuery('#wpfts_status_box .inside').html(st);
}

function wpftsiFormData(p) {
	var list = {};
	jQuery('input[name], select[name], textarea[name]', p).each(function(i, v){
		if (jQuery(v).is('input[type="radio"]')) {
			if (jQuery(v).is(':checked')) {
				list[jQuery(v).attr('name')] = jQuery(v).val();
			} else {
				// Not save value for unchecked radio
			}
		} else {
			if (jQuery(v).is('input[type="checkbox"]')) {
				list[jQuery(v).attr('name')] = jQuery(v).is(':checked') ? jQuery(v).val() : 0;
			} else {
				list[jQuery(v).attr('name')] = jQuery(v).val();
			}
		}
	});
	
	return list;
}

function wpftsiAction(action, data, cb) {
	jQuery.ajax({
		url: ajaxurl,
		method: 'POST',
		data: {'action': action, '__xr':1, 'z':JSON.stringify(data)},
		success: function(jx) {
			var ret = true;
			if ((typeof cb !== 'undefined') && (cb)) {
				var vars = {};
				for (var i = 0; i < jx.length; i ++) {
					switch (jx[i][0]) {
						case 'vr':
							vars[jx[i][1]] = jx[i][2];
							break;
					}
				}
				ret = cb(vars);
			}
			if ((ret) || (typeof ret === 'undefined')) {
				for (var i = 0; i < jx.length; i ++) {
					var jd = jx[i];
					switch (jd[0]) {
						case 'cn':
							console.log(jd[1]);
							break;						
						case 'al':
							alert(jd[1]);
							break;
						case 'as':
							if (jQuery(jd[1]).length > 0) {
								jQuery(jd[1]).html(jd[2]);
							}
							break;
						case 'js':
							eval(jd[1]);
							break;
						case 'rd':
							document.location.href(jd[1]);
							break;
						case 'rl':
							window.location.reload();
						break;
					}
				}
			}
		},
		error: function() {
			console.log('!!! Error !!!');
		},
		dataType: 'json'
	});

}
