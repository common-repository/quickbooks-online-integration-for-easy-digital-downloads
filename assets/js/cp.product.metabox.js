jQuery( function ( $ ) {
	$( '#qbo-product-data' )
	.on( 'click', 'a.sync[name="sync-from"]', function() {
		$( '#qbo-product-data' ).block({
			message: null,
			overlayCSS: {
				background: '#fff url(' + cp_product_meta_box.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
				opacity: 0.6
			}
		});
		
		var data = {
			action:    'cp_sync_product',
			post_id:   cp_product_meta_box.post_id,
			security:  cp_product_meta_box.sync_item_nonce,
		};

		$.post( cp_product_meta_box.ajax_url, data, function( response ) {
				$( '#qbo-product-data' ).unblock();	  
				window.location.reload;
		});
		
		return false;

	});
	$( '#qbo-product-data' )
	.on( 'click', 'a.sync[name="sync-to"]', function() {
		$( '#qbo-product-data' ).block({
			message: null,
			overlayCSS: {
				background: '#fff url(' + cp_product_meta_box.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
				opacity: 0.6
			}
		});
		var data = {
			action:    'cp_update_product',
			post_id:   	cp_product_meta_box.post_id,
			qb_id:   	$('input[name="qbo_product_id"]').val(),
			qb_price:   $('input[name="qbo_product_price"]').val(),
			qb_name:	$('input[name="qbo_product_name"]').val(),
			qb_desc:	$('input[name="qbo_product_description"]').val(),
			qb_status:	$('select[name="qbo_product_active"]').val(),
			qb_taxable:	$('select[name="qbo_product_taxable"]').val(),
			qb_type:	$('select[name="qbo_product_type"]').val(),
			security:  	cp_product_meta_box.sync_item_nonce,
		};
		$.post( cp_product_meta_box.ajax_url, data, function( response ) {
			$( '#qbo-product-data' ).unblock();
			console.log(response);
		});
		return false;
	});
	$( '#qbo-product-data' )
	.on( 'change', '#qb_live_edit', function() {
		if ($(this).attr("checked")) {
			$(".qb_content :input.can_edit").prop("disabled", false);
			$('a[name="sync-to"]').show('slow');
			$('a[name="sync-from"]').hide('slow');
		}else{
			$(".qb_content :input.can_edit").prop("disabled", true);
			$('a[name="sync-to"]').hide('slow');
			$('a[name="sync-from"]').show('slow');
		}
		return false;
	});
	$( '#qbo-product-data' )
	.on( 'click', 'a.button[name="break-sync"]', function() {
		
		$( '#qbo-product-data' ).block({
			message: null,
			overlayCSS: {
				background: '#fff url(' + cp_product_meta_box.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center',
				opacity: 0.6
			}
		});
		var data = {
			action:    'cp_break_sync',
			post_id:   	cp_product_meta_box.post_id,
			security:  	cp_product_meta_box.sync_item_nonce,
		};
		$.post( cp_product_meta_box.ajax_url, data, function( response ) {
			$( '#qbo-product-data' ).unblock();
			console.log(response);
			window.location.reload();
		});
		return false;
	});
	$('.sync_box').each(function(){
		$(this).insertAfter('#qbo-product-data h3.hndle span');
	});
	$('i.cp-logo').each(function(){
		$(this).insertBefore('#qbo-product-data h3.hndle span:first-child');
	});
	
});