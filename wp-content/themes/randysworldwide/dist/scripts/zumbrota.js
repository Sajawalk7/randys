jQuery(document).ready( function($) {
var ajax_url="https://www.randysworldwide.com/wp-admin/admin-ajax.php"
	function generate_select(years){
		var str='';
		var totalYear=years.length;
		for(i=0;i<totalYear;i++){
			str+='<option value="'+years[i]+'">'+years[i]+'</option>';	
		}
		return str;
	}
	jQuery(function($){
		
		var $loader=$(".zumbrota-search .ajax-loader");
		$("#zumbrota_category").on("change",function(e){
			var $this=$(this);
			var cat_id=$this.val();
			$.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					'action': 'get_year_by_category',
					cat_id: cat_id,
				},
				beforeSend: function() {
			        // setting a timeout
			        $loader.show();
			    }
			})
			.done(function(data) {
				//console.log(data);
				$r='<option value="">Year</option>';
				if(data.result=='1'){
					$r+=generate_select(data.year);
					$("#zumbrota_year").attr("disabled",false).html($r);
				}else{
					$("#zumbrota_year").attr("disabled",true).html($r);
				}
				$loader.hide();
			})
			.fail(function() {
				console.log("error");
			});
		});
		// YEAR CHNAGE EVENT
		$("#zumbrota_year").on("change",function(e){
			var $this=$(this);
			var zyear=$this.val();
			var cat_id=$("#zumbrota_category").val();
			$.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					'action': 'get_make_by_year',
					zyear: zyear,
					cat_id: cat_id,
				},
				beforeSend: function() {
			        // setting a timeout
			        $loader.show();
			    }
			})
			.done(function(data) {
				//console.log(data);
				$r='<option value="">Make</option>';
				if(data.result=='1'){
					$r+=generate_select(data.make);
					$("#zumbrota_make").attr("disabled",false).html($r);
				}else{
					$("#zumbrota_make").attr("disabled",true).html($r);
				}
				$loader.hide();
			})
			.fail(function() {
				console.log("error");
			});
			
		});

		// MAKE CHNAGE EVENT
		$("#zumbrota_make").on("change",function(e){
			var $this=$(this);
			var zmake=$this.val();
			var zyear=$("#zumbrota_year").val();
			var cat_id=$("#zumbrota_category").val();
			$.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					'action': 'get_model_by_make',
					zmake: zmake,
					zyear: zyear,
					cat_id: cat_id,
				},
				beforeSend: function() {
			        // setting a timeout
			        $loader.show();
			    }
			})
			.done(function(data) {
				//console.log(data);
				$r='<option value="">Model</option>';
				if(data.result=='1'){
					$r+=generate_select(data.model);
					$("#zumbrota_model").attr("disabled",false).html($r);
				}else{
					$("#zumbrota_model").attr("disabled",true).html($r);
				}
				$loader.hide();
			})
			.fail(function() {
				console.log("error");
			});
			
		});

		// MODEL CHNAGE EVENT
		$("#zumbrota_model").on("change",function(e){
			var $this=$(this);
			var zmodel=$this.val();
			var zmake=$("#zumbrota_make").val();
			var zyear=$("#zumbrota_year").val();
			var cat_id=$("#zumbrota_category").val();
			$.ajax({
				url: ajax_url,
				type: 'POST',
				dataType: 'json',
				data: {
					'action': 'get_unit_model_by_model',
					zmodel: zmodel,
					zmake: zmake,
					zyear: zyear,
					cat_id: cat_id,
				},
				beforeSend: function() {
			        // setting a timeout
			        $loader.show();
			    }
			})
			.done(function(data) {
				//console.log(data);
				$r='<option value="">Unit Model Name</option>';
				if(data.result=='1'){
					$r+=generate_select(data.unit_model);
					$("#zumbrota_unit_model_name").attr("disabled",false).html($r);
				}else{
					$("#zumbrota_unit_model_name").attr("disabled",true).html($r);
				}
				$loader.hide();
			})
			.fail(function() {
				console.log("error");
			});
			
		});

		
		// PAGE LIMIT CHANGE SEARCH
		$("body").on("change",".order-by-change",function(e){
			var $this=$(this);
			var order_by=$this.val();
			window.location.href =order_by;
			return true;
			
		});
		// PAGE LIMIT CHANGE SEARCH
		$("body").on("change",".per-page-limit",function(e){
			var $this=$(this);
			var per_page=$this.val();
			window.location.href =per_page;
			return true;
		});
		
	});
});