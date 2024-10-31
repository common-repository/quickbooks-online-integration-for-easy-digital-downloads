jQuery( function ( $ ) {
	$('[data-dependency]').each(function(){
		var dependency 	= $(this).data('dependency');
		var value 		= $(this).data('value');
		var c_value 	= $('[name="' + dependency + '"]').val();
		
		if(value == c_value){
			$(this).show();
		}else{
			$(this).hide();
		}
		
	});
	$('select[name="qbo[order_type]"]').on('change', function() {
		$('[data-dependency]').each(function(){
			var dependency 	= $(this).data('dependency');
			
			if(dependency == 'qbo[order_type]'){//(this).attr('name')){
				 var value 		= $(this).data('value');
				 var c_value 	= $('[name="' + dependency + '"]').val();
				 
				 if(value == c_value){
					$(this).show('slow');
				}else{
					$(this).hide('slow');
				}
			}
		});
  		
	});
	$( '#qbo-order-data' )
	.on( 'click', 'a.transfer-to.button', function() {
		$( '#qbo-order-data' ).block({
			message: null,
			overlayCSS: {
				background: '#fff url(' + cp_order_meta_box.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
				opacity: 0.6
			}
		});
		$payment_id = $('#edd-add-payment-note').attr('data-payment-id');	
		var data = {
			action:    'cp_transfer_single_order',
			post_id:   $payment_id,
			security:  cp_order_meta_box.transfer_order_nonce,
		};

		$.post( cp_order_meta_box.ajax_url, data, function( response ) {
			$( '#qbo-order-data' ).unblock();
			console.log(response);
			//location.reload;
		});

		
		return false;
	});
	$('a.button.transfer').on('click', function(e) {
		var url 	= $(this).attr('href');
		var vars 	= [], hash;
		$this 		= $(this);
		if(url){
			
			var parent 	= $(this).parent().parent().parent(); 
			    var q 	= url.split('?')[1];
			    if(	q != undefined	){
			        q = q.split('&');
			        for(var i = 0; i < q.length; i++){
			            hash = q[i].split('=');
			            vars.push(hash[1]);
			            vars[hash[0]] = hash[1];
			        }
			}
			if(vars['sent']){
				var data = {
				action:	    vars['action'],
				message:   'Order #' + vars['order_id'] + ' has already been sent to QuickBooks.',
				security:	vars['payment-transfer-nonce'],
				};
				// $.post( cp_order_meta_box.ajax_url, data, function( response ) {
					// console.log(vars['action']); 
					// window.location.reload();
				// });	
			}else{
				parent.toggleClass("queued");
				var data = {
					action:	    vars['action'],
					order_id:   vars['payment_id'],
					security:	vars['payment-transfer-nonce'],
					trigger: 	vars['order_trigger']
				};
				 $.post( cp_order_meta_box.ajax_url, data, function( response ) {
					console.log(data); 
					// parent.toggleClass("queued");
					window.location.reload();
				 });
			}
		}else{
			
		}
		return false;
	});
	$( '#qbo-order-data' )
	.on( 'change', '#qb_resend', function() {
		
		if ($(this).attr("checked")) {
			$('a.transfer-to').removeClass('hide');
			$('a.transfer-to').show('slow');
			
		}else{
			$('a.transfer-to').hide('slow');
			
		}
		return false;
	});
	$('i.cp-logo').each(function(){
		$(this).insertBefore('#qbo-order-data h3.hndle span:first-child');
	});
	// TABS
	$('ul.wc-tabs').show();
	$('div.panel-wrap').each(function(){
		$(this).find('div.panel:not(:first)').hide();
	});
	$('ul.wc-tabs a').click(function(){
		var panel_wrap =  $(this).closest('div.panel-wrap');
		$('ul.wc-tabs li', panel_wrap).removeClass('active');
		$(this).parent().addClass('active');
		$('div.panel', panel_wrap).hide();
		$( $(this).attr('href') ).show();
		return false;
	});
});