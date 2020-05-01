function callJqueryDefault(divId){
	$("#"+divId).find(".add_form").submit(function(event){
    event.preventDefault(); //prevent default action 
	$("#"+divId).find(".alert").remove();
	var formObj=this;
	if(formObj.getAttribute('success-content-id')){
				$('#'+formObj.getAttribute('success-content-id')).html('Loading.....');
			}
	var pleaseWait=$("<div>please wait.......</div>");
	var uploadProgress=$("<div id='upload-progress'><div class='progress-bar'></div></div>");
	pleaseWait.insertAfter(this);
    var post_url = this.action; //get form action url
    var request_method = 'POST'; //get form GET/POST method
    var form_data = new FormData(this); //Encode form elements for submission
    $('button[type=button],button[type=submit], input[type=submit]').prop('disabled',true);
    $.ajax({
        url : post_url,
        type: request_method,
        data : form_data,
        contentType: false,
        processData:false,
        xhr: function(){
        //upload Progress
        var xhr = $.ajaxSettings.xhr();
        if (xhr.upload) {
			
			pleaseWait.remove();
			//update progressbar
			uploadProgress.insertAfter(formObj);
			//console.log(5);
            xhr.upload.addEventListener('progress', function(event) {
                var percent = 0;
                var position = event.loaded || event.position;
                var total = event.total;
                if (event.lengthComputable) {
                    percent = Math.ceil(position / total * 100);
                }
				//console.log(2);
				$("#upload-progress .progress-bar").css("width", + percent +"%");
				//console.log(3);
            }, true);
        }
        return xhr;
    }
    }).done(function(response){ //
	
	pleaseWait.remove();
	uploadProgress.remove();
	
	if(response.status==0){
		if(formObj.getAttribute('import')=="true"){
			$('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button> <strong>'+response.msg+'</strong>'+response.data+'</div>').insertAfter(formObj);
			formObj.reset();
		}else if(formObj.getAttribute('toast-msg')=="true")
		{
			ToastErrorMsg(response.msg);
		}
		else if(formObj.getAttribute('error-popup')){
			
			callErrorPopup(response.msg);	
			//successMsg(response.msg)
		}
		else{
			if(formObj.getAttribute('error-id')){
				$('#'+formObj.getAttribute('error-id')).html(response.msg);
			}else{
				$(formObj).append($('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Warning!</strong> '+response.msg+'</div>'));

			}
		}
		if(formObj.getAttribute('success-content-id')){
				$('#'+formObj.getAttribute('success-content-id')).html('');
			}
			
	}else if(response.status==1){
		if(formObj.getAttribute('success-id')){
				$('#'+formObj.getAttribute('success-id')).html(response.msg);
		}else if(formObj.getAttribute('toast-msg')=="true")
		{
			ToastSuccessMsg(response.msg);
		}
		else if(formObj.getAttribute('success-popup')){
			console.log(response.msg);
			callSuccessPopup(response.msg);
		}else if(formObj.getAttribute('profile-pic')){
			$('.'+formObj.getAttribute('profile-pic')).attr( 'src', response.msg + '?dt=' + (+new Date()) );
		}else if(formObj.getAttribute('success-content-id')){
				$('#'+formObj.getAttribute('success-content-id')).html(response.data);
				if(formObj.getAttribute('data-table'))
				{
				$("#"+formObj.getAttribute('data-table')).DataTable({
					'iDisplayLength': 10,
					colReorder: true,
				});
				}
				else if(formObj.getAttribute('data-table-scroll'))
				{
				$("#"+formObj.getAttribute('data-table-scroll')).DataTable({
					'paging':   false,
					colReorder: true,
				});
				}

				else if(formObj.getAttribute('data-table-without-pagination'))
				{
				$("#"+formObj.getAttribute('data-table-without-pagination')).DataTable({
					'paging':   false,
					colReorder: true,
					dom: 'Bfrtip',
						buttons: [
							'csv', 'excel', 'pdf', 'print'
						]
				});
				}
				else if(formObj.getAttribute('child-table'))
				{
									var table = $("#"+formObj.getAttribute('child-table')).DataTable({colReorder: true,});

						  // Add event listener for opening and closing details
						  $("#"+formObj.getAttribute('child-table')).on('click', 'td.details-control', function () {
							  var tr = $(this).closest('tr');
							  var row = table.row(tr);

							  if (row.child.isShown()) {
								  // This row is already open - close it
								  row.child.hide();
								  tr.removeClass('shown');
							  } else {
								  // Open this row
								  row.child(format(tr.data('child-value'))).show();
								  tr.addClass('shown');
							  }
						  });
				}
		}else{
			$(formObj).append($('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Success!</strong> '+response.msg+'</div>'));
		}
		
		if(formObj.getAttribute('call-back'))
		{
			var callback=formObj.getAttribute('call-back');
			console.log(typeof window[callback]);
			if(typeof window[callback] == "function")
                    window[callback].call(); //wi
			//console.log(formObj.getAttribute('call-back'));
			
			//eval(callback());
		}
		if(formObj.getAttribute('redirect-to'))
		{
			var redirect=formObj.getAttribute('redirect-to');
			setTimeout(window.location.replace(redirect), 3000);
			;
		}

		if(formObj.getAttribute('display-url') && formObj.getAttribute('display-div'))
		{	
			var url=formObj.getAttribute('display-url');
			var div=formObj.getAttribute('display-div');
			 setTimeout(function() {
                  callAjaxUrl(url,div,'');
            }, 1000);
			
		}

		if(formObj.getAttribute('button-click') && response.status==1)
		{	
			$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
			var myStr = formObj.getAttribute('button-click');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).click();
       		 }
		}

		if(formObj.getAttribute('button-click-save') && response.status==1)
		{	
			$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
			var myStr = formObj.getAttribute('button-click-save');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).click();
       		 }
		}

		if(formObj.getAttribute('content-refresh') && response.status==1)
		{	
			var myStr = formObj.getAttribute('content-refresh');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).load(location.href + ' #'+strArray[i]);
       		 }
		}
		
		if(formObj.getAttribute('no-reset')!="true"){
			formObj.reset();
			$(formObj).find('.multiselect').selectpicker("refresh");
		}

		if(formObj.getAttribute('no-reset-summernote')!="true"){ 
			$(formObj).find('.summernote').summernote("reset");
		}
		
		if(formObj.getAttribute('call-jquery-default')!=""){
			callJqueryDefault(formObj.getAttribute('call-jquery-default'));
			
		}

		if(formObj.getAttribute('select2')!=""){
				$("."+formObj.getAttribute('select2')).select2({
				   	width: 'resolve' 
				   });
		}

		if(formObj.getAttribute('reset-select2')=="true"){
			$('.select2').val('').trigger('change');
		}
		if(formObj.getAttribute('form-reset-select2')=="true"){
			$('.reset_me').val('').trigger('change');
		}
		if(formObj.getAttribute('trigger'))
		{	
			var myStr = formObj.getAttribute('trigger');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).trigger('change');
       		 }
		 
        	 
		}

		if(formObj.getAttribute('text-editor')!=""){
			$('.'+formObj.getAttribute('text-editor')).summernote({
				  toolbar: [
				    // [groupName, [list of button]]
				    ['style', ['bold', 'italic', 'underline', 'clear']],
				    ['fontname', ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New']],
				    ['fontsize', ['fontsize']],
				    ['color', ['color']],
				    ['para', ['ul', 'ol', 'paragraph']],
				    ['height', ['height']]
				  ],
				   placeholder: 'write here...',
				   height: 100
				});
			
		}

		if(formObj.getAttribute('window-open')){
			var myStr = formObj.getAttribute('window-open')+'/'+response.data;
			window.open(myStr);
		}

		if(formObj.getAttribute('upload-form')!="" && typeof response.valuee !== 'undefined'){
				var obj = response.valuee;
				var urls = obj.url;
				var token = obj.token;
				var key = obj.key;
				var limit = obj.limit;
				var mime = obj.mime;
				 $('.'+formObj.getAttribute('upload-form')).uploadFile({
					url:""+urls,
					method:"POST",
					fileName:"myfile",
					maxFileSize:limit*1024,
					acceptFiles:mime,
					dynamicFormData: function()
					{
					    var data ={"_token":token,"id":key};
					    return data;        
					},
					sequential:true,
					sequentialCount:1,
					onSuccess:function(files,data,xhr,pd)
						{
						    if(data.status==0)
						    {
								$("#eventsmessage").html($("#eventsmessage").html()+"<br/>Warning for: "+data.msg);
						    }
						    else (data.status==1)
						    {
								$("#eventsmessage").html($("#eventsmessage").html()+"<br/>Success for: "+data.msg);
						    }
						    	
						}	
					}); 
			}

		if(formObj.getAttribute('error-id')){
				$('#'+formObj.getAttribute('error-id')).html('');
			}
			
	}
	$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
    }).fail(function (jqXHR, textStatus) {
	  $('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false); 
	});
});
}
callJqueryDefault('body_id');

function ToastSuccessMsg(msg){  
 // toastr.success(msg); 
 	$.toast({
 		heading: msg, 
 		position: 'top-right',
 		loaderBg:'#f0c541',
 		icon: 'success',
 		hideAfter: 3500, 
 		stack: 6
 	}); 
}

function ToastErrorMsg(msg){  
 // toastr.success(msg); 
 	$.toast({
 		heading: msg, 
 		position: 'top-right',
 		loaderBg:'#f0c541',
 		icon: 'error',
 		hideAfter: 3500, 
 		stack: 6
 	}); 
}

function format(value) {
     return '<div>' + value + '</div>';
}		

  
function child_table_by_click(table_id){
	  if ( ! $.fn.DataTable.isDataTable("#"+table_id) ) 
			{
				var table = $("#"+table_id).DataTable({colReorder: true,});

						  // Add event listener for opening and closing details
						  $("#"+table_id).on('click', 'td.details-control', function () {
							  var tr = $(this).closest('tr');
							  var row = table.row(tr);

							  if (row.child.isShown()) {
								  // This row is already open - close it
								  row.child.hide();
								  tr.removeClass('shown');
							  } else {
								  // Open this row
								  row.child(format(tr.data('child-value'))).show();
								  tr.addClass('shown');
							  }
						  });
			}
									
				
  } 

function advSrchMatch(value) {
	if(value==2){
		$(".adv_srch_cond").val(0);
	}else if(value==1){
		$(".adv_srch_cond").val(1);
	}
}
function showDatePicker(obj){
  	console.log(obj);
  	if(typeof(obj)!="undefined" && obj){
		var id=obj.getAttribute("picker_id");
		if(obj.value==7){
			$("#"+id+"-content").show();
		}else{
			$("#"+id+"-content").hide();
		}
  	}
}
function advanceSearchNotice(init){
	
	$('#public-methods').multiSelect();
	$('#select-all').click(function(){
		$('#public-methods').multiSelect('select_all');
		return false;
	});
	$('#deselect-all').click(function(){
		$('#public-methods').multiSelect('deselect_all');
		return false;
	});
	searchDatepicker('adv-search-datepicker');
	showDatePicker(document.getElementById('adv_search_date_by'));
	if(init==1)
	searchDatepicker('basic-search-datepicker');
	$('#adv_search_content_tab .multiselect').selectpicker();
	
	$('#adv_search_content_tab .multiselect').on('changed.bs.select', function(e) {
		var obj=$(e.currentTarget);
		if(typeof(obj.val()) != "undefined" && obj.val()){
			console.log(obj.val());
			var url=$(this).attr('load_url');
			var loadId=$(this).attr('load_id');
			var data={};
			data["id"]=obj.val();
			var url_data;
			if(url_data=$(this).attr('send_data')){
				var url_data_arr= url_data.split(",");
				$.each(url_data_arr, function( index, value ) {
				  data[value]= $('#'+value).val();
				});
				
			}
			loadSelectBoxData(data,url,loadId);
		}
	});
}

function advanceSearchCase(init){
	
	$('#public-methods-case').multiSelect();
	$('#select-all-case').click(function(){
		$('#public-methods-case').multiSelect('select_all');
		return false;
	});
	$('#deselect-all-case').click(function(){
		$('#public-methods-case').multiSelect('deselect_all');
		return false;
	});
	searchDatepicker('adv-search-datepicker');
	showDatePicker(document.getElementById('adv_search_date_by'));
	if(init==1)
	searchDatepicker('basic-search-datepicker-case');
	$('#adv_search_content_tab .multiselect').selectpicker();
	
	$('#adv_search_content_tab .multiselect').on('changed.bs.select', function(e) {
		var obj=$(e.currentTarget);
		if(typeof(obj.val()) != "undefined" && obj.val()){
			console.log(obj.val());
			var url=$(this).attr('load_url');
			var loadId=$(this).attr('load_id');
			var data={};
			data["id"]=obj.val();
			var url_data;
			if(url_data=$(this).attr('send_data')){
				var url_data_arr= url_data.split(",");
				$.each(url_data_arr, function( index, value ) {
				  data[value]= $('#'+value).val();
				});
				
			}
			loadSelectBoxData(data,url,loadId);
		}
	});
}
function searchData(){
	advanceSearchNotice();
}
function searchDataCase(){
	advanceSearchCase();
}
function searchDatepicker(datepickerId){
	$('#'+datepickerId).daterangepicker({
		buttonClasses: ['btn', 'btn-sm'],
		applyClass: 'btn-info',
		cancelClass: 'btn-default',
		format: 'MM/DD/YY',
	});
	$("#"+datepickerId+"-content").hide();
}
function loadSelectBoxData(dataToSend,url,selectBoxId){
	var selectBoxObj=$('#'+selectBoxId);
	 $.get(url,dataToSend,
    function(data, status){
        if(data.status==1)
		{
			selectBoxObj.find('option').remove();
			$.each(data.data, function (key, entry) {
				selectBoxObj.append($('<option></option>').attr('value', entry.value).text(entry.text));
			});
			if(selectBoxObj.attr('multiple')=="multiple")
				selectBoxObj.selectpicker('refresh');
		}
    });
}
function loadData(obj){
	var data={};
	data["id"]=obj.value;
	if(url_data=obj.getAttribute('send_data')){
		var url_data_arr= url_data.split(",");
		$.each(url_data_arr, function( index, value ) {
		  data[value]= $('#'+value).val();
		});
		
	}
	var url=obj.getAttribute('load_url');
	var loadId=obj.getAttribute('load_id');
	loadSelectBoxData(data,url,loadId);	
}
function getSelectedOptions(sel) {
  var opts = [],
    opt;
  var len = sel.options.length;
  for (var i = 0; i < len; i++) {
    opt = sel.options[i];

    if (opt.selected) {
      opts.push(opt);
    }
  }

  return opts;
}

function searchForm(formObj){
	var pleaseWait=$("<div>please wait.......</div>");
	var uploadProgress=$("<div class='upload-progress'><div class='progress-bar'></div></div>");
	pleaseWait.insertAfter(formObj);
    var post_url = formObj.getAttribute('search-url'); //get form action url
    var request_method = 'POST'; //get form GET/POST method
    var form_data = new FormData(formObj); //Encode form elements for submission
    $(formObj).find(".alert").remove();
    $('button[type=button],button[type=submit], input[type=submit]').prop('disabled',true);
    $.ajax({
        url : post_url,
        type: request_method,
        data : form_data,
        contentType: false,
        processData:false,
        xhr: function(){
        //upload Progress
        var xhr = $.ajaxSettings.xhr();
        if (xhr.upload) {
			
			pleaseWait.remove();
			//update progressbar
			uploadProgress.insertAfter(formObj);
			//console.log(5);
            xhr.upload.addEventListener('progress', function(event) {
                var percent = 0;
                var position = event.loaded || event.position;
                var total = event.total;
                if (event.lengthComputable) {
                    percent = Math.ceil(position / total * 100);
                }
				//console.log(2);
				uploadProgress.css("width", + percent +"%");
				//console.log(3);
            }, true);
        }
        return xhr;
    }
    }).done(function(response){ //
	
	pleaseWait.remove();
	uploadProgress.remove();
	
	if(response.status==0){
		if(formObj.getAttribute('import')=="true"){
			$('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button> <strong>'+response.msg+'</strong>'+response.data+'</div>').insertAfter(formObj);
			formObj.reset();
		}else if(formObj.getAttribute('toast-msg')=="true")
		{
			ToastErrorMsg(response.msg);
		}
		else{
			if(formObj.getAttribute('error-id')){
				$('#'+formObj.getAttribute('error-id')).html(response.msg);
			}else{
				
				$(formObj).append($('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Warning!</strong> '+response.msg+'</div>'));
			}
		}
	}else if(response){
		console.log(response);
		$('#adv_filter_content').html(response);
		if(formObj.getAttribute('data-table-without-pagination')){
			$('#'+formObj.getAttribute('data-table-without-pagination')).DataTable({
				'paging':   false,
				colReorder: true,
				dom: 'Bfrtip',
				buttons: [
					 'csv', 'excel', 'pdf', 'print'
				]
			});
		}else if(formObj.getAttribute('toast-msg')=="true"){
			ToastSuccessMsg(response.msg);
		}else{
			$(formObj).append($('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Success!</strong> '+response.msg+'</div>'));
		}

		if(formObj.getAttribute('content-refresh') && response.status==1)
		{	
			var myStr = formObj.getAttribute('content-refresh');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).load(location.href + ' #'+strArray[i]);
       		 }
		}

		if(formObj.getAttribute('button-click') && response.status==1)
		{	
			$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
			var myStr = formObj.getAttribute('button-click');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).click();
       		 }
		}

		if(formObj.getAttribute('window-open')){
			var myStr = formObj.getAttribute('window-open')+'/'+response.data;
			window.open(myStr);
		}

		if(formObj.getAttribute('window-open-without-obj')){
			var myStr = formObj.getAttribute('window-open-without-obj');
			window.open(myStr);
		}

		if(formObj.getAttribute('no-reset')!="true"){
			formObj.reset();
			$(formObj).find('.multiselect').selectpicker("refresh");
			$(formObj).find('.summernote').shouldInitialize = function () {
				$(formObj).find('.summernote').summernote("reset");
				};
		}
			
	}
	$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
    });
}

function thirdurl(formObj){
	var pleaseWait=$("<div>please wait.......</div>");
	var uploadProgress=$("<div class='upload-progress'><div class='progress-bar'></div></div>");
	pleaseWait.insertAfter(formObj);
    var post_url = formObj.getAttribute('third-url'); //get form action url
    var request_method = 'POST'; //get form GET/POST method
    var form_data = new FormData(formObj); //Encode form elements for submission
    $(formObj).find(".alert").remove();
    $('button[type=button],button[type=submit], input[type=submit]').prop('disabled',true);
    $.ajax({
        url : post_url,
        type: request_method,
        data : form_data,
        contentType: false,
        processData:false,
        xhr: function(){
        //upload Progress
        var xhr = $.ajaxSettings.xhr();
        if (xhr.upload) {
			
			pleaseWait.remove();
			//update progressbar
			uploadProgress.insertAfter(formObj);
			//console.log(5);
            xhr.upload.addEventListener('progress', function(event) {
                var percent = 0;
                var position = event.loaded || event.position;
                var total = event.total;
                if (event.lengthComputable) {
                    percent = Math.ceil(position / total * 100);
                }
				//console.log(2);
				uploadProgress.css("width", + percent +"%");
				//console.log(3);
            }, true);
        }
        return xhr;
    }
    }).done(function(response){ //
	
	pleaseWait.remove();
	uploadProgress.remove();
	
	if(response.status==0){
		if(formObj.getAttribute('import')=="true"){
			$('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button> <strong>'+response.msg+'</strong>'+response.data+'</div>').insertAfter(formObj);
			formObj.reset();
		}else if(formObj.getAttribute('toast-msg')=="true")
		{
			ToastErrorMsg(response.msg);
		}
		else{
			if(formObj.getAttribute('error-id')){
				$('#'+formObj.getAttribute('error-id')).html(response.msg);
			}else{
				
				$(formObj).append($('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Warning!</strong> '+response.msg+'</div>'));
			}
		}
	}else if(response){
		console.log(response);
		$('#adv_filter_content').html(response);
		if(formObj.getAttribute('data-table-without-pagination')){
			$('#'+formObj.getAttribute('data-table-without-pagination')).DataTable({
				'paging':   false,
				colReorder: true,
				dom: 'Bfrtip',
					buttons: [
						 'csv', 'excel', 'pdf', 'print'
					]
			});
		}else if(formObj.getAttribute('toast-msg')=="true"){
			ToastSuccessMsg(response.msg);
		}else{
			$(formObj).append($('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Success!</strong> '+response.msg+'</div>'));
		}

		if(formObj.getAttribute('content-refresh') && response.status==1)
		{	
			var myStr = formObj.getAttribute('content-refresh');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).load(location.href + ' #'+strArray[i]);
       		 }
		}

		if(formObj.getAttribute('button-click') && response.status==1)
		{	
			$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
			var myStr = formObj.getAttribute('button-click');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).click();
       		 }
		}

		if(formObj.getAttribute('no-reset')!="true"){
			formObj.reset();
			$(formObj).find('.multiselect').selectpicker("refresh");
			$(formObj).find('.summernote').shouldInitialize = function () {
				$(formObj).find('.summernote').summernote("reset");
				};
		}

		if(formObj.getAttribute('pivot-view')=="true"){

			var renderers = $.extend(
            $.pivotUtilities.renderers,
            $.pivotUtilities.plotly_renderers,
            $.pivotUtilities.d3_renderers,
            $.pivotUtilities.export_renderers
            );

			$("#output").pivotUI($("#input-table"), {
                renderers: renderers,
            });
		}
			
	}
	$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
    });
}

function fourthurl(formObj){
	var pleaseWait=$("<div>please wait.......</div>");
	var uploadProgress=$("<div class='upload-progress'><div class='progress-bar'></div></div>");
	pleaseWait.insertAfter(formObj);
    var post_url = formObj.getAttribute('fourth-url'); //get form action url
    var request_method = 'POST'; //get form GET/POST method
    var form_data = new FormData(formObj); //Encode form elements for submission
    $('button[type=button],button[type=submit], input[type=submit]').prop('disabled',true);
    $(formObj).find(".alert").remove();
    $.ajax({
        url : post_url,
        type: request_method,
        data : form_data,
        contentType: false,
        processData:false,
        xhr: function(){
        //upload Progress
        var xhr = $.ajaxSettings.xhr();
        if (xhr.upload) {
			
			pleaseWait.remove();
			//update progressbar
			uploadProgress.insertAfter(formObj);
			//console.log(5);
            xhr.upload.addEventListener('progress', function(event) {
                var percent = 0;
                var position = event.loaded || event.position;
                var total = event.total;
                if (event.lengthComputable) {
                    percent = Math.ceil(position / total * 100);
                }
				//console.log(2);
				uploadProgress.css("width", + percent +"%");
				//console.log(3);
            }, true);
        }
        return xhr;
    }
    }).done(function(response){ //
	
	pleaseWait.remove();
	uploadProgress.remove();
	
	if(response.status==0){
		if(formObj.getAttribute('import')=="true"){
			$('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button> <strong>'+response.msg+'</strong>'+response.data+'</div>').insertAfter(formObj);
			formObj.reset();
		}
		else{
			if(formObj.getAttribute('error-id')){
				$('#'+formObj.getAttribute('error-id')).html(response.msg);
			}else{
				
				$(formObj).append($('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Warning!</strong> '+response.msg+'</div>'));
			}
		}
	}else if(response.status==1){
		//console.log(response);
		$('#adv_filter_content').html(response.data);
		if(formObj.getAttribute('data-table-without-pagination'))
				{
					$('#'+formObj.getAttribute('data-table-without-pagination')).DataTable({
					'paging':   false,
					colReorder: true,
					dom: 'Bfrtip',
						buttons: [
							'csv', 'excel', 'pdf', 'print'
						]
				});
				}
				
		
		else{
			$(formObj).append($('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Success!</strong> '+response.msg+'</div>'));
		}

		if(formObj.getAttribute('content-refresh') && response.status==1)
		{	
			var myStr = formObj.getAttribute('content-refresh');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).load(location.href + ' #'+strArray[i]);
       		 }
		}

		if(formObj.getAttribute('button-click') && response.status==1)
		{	
			$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
			var myStr = formObj.getAttribute('button-click');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).click();
       		 }
		}

		if(formObj.getAttribute('no-reset')!="true"){
			formObj.reset();
			$(formObj).find('.multiselect').selectpicker("refresh");
			$(formObj).find('.summernote').shouldInitialize = function () {
				$(formObj).find('.summernote').summernote("reset");
				};
		}
		if(formObj.getAttribute('redirect-to'))
		{
			var redirect=formObj.getAttribute('redirect-to');
			setTimeout(window.location.replace(redirect), 3000);
		}

		if(formObj.getAttribute('window-open')){
			var myStr = formObj.getAttribute('window-open')+'/'+response.data;
			window.open(myStr);
		}

		if(formObj.getAttribute('redirect-url'))
		{
			var redirect=formObj.getAttribute('redirect-url');
			setTimeout(window.location.replace(redirect), 3000);
			;
		}
			
	}
	$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
    });
}

function fifthurl(formObj){
	var pleaseWait=$("<div>please wait.......</div>");
	var uploadProgress=$("<div class='upload-progress'><div class='progress-bar'></div></div>");
	pleaseWait.insertAfter(formObj);
    var post_url = formObj.getAttribute('fifth-url'); //get form action url
    var request_method = 'POST'; //get form GET/POST method
    var form_data = new FormData(formObj); //Encode form elements for submission
    $(formObj).find(".alert").remove();
    $('button[type=button],button[type=submit], input[type=submit]').prop('disabled',true);
    $.ajax({
        url : post_url,
        type: request_method,
        data : form_data,
        contentType: false,
        processData:false,
        xhr: function(){
        //upload Progress
        var xhr = $.ajaxSettings.xhr();
        if (xhr.upload) {
			
			pleaseWait.remove();
			//update progressbar
			uploadProgress.insertAfter(formObj);
			//console.log(5);
            xhr.upload.addEventListener('progress', function(event) {
                var percent = 0;
                var position = event.loaded || event.position;
                var total = event.total;
                if (event.lengthComputable) {
                    percent = Math.ceil(position / total * 100);
                }
				//console.log(2);
				uploadProgress.css("width", + percent +"%");
				//console.log(3);
            }, true);
        }
        return xhr;
    }
    }).done(function(response){ //
	
	pleaseWait.remove();
	uploadProgress.remove();
	
	if(response.status==0){
		if(formObj.getAttribute('import')=="true"){
			$('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button> <strong>'+response.msg+'</strong>'+response.data+'</div>').insertAfter(formObj);
			formObj.reset();
		}else if(formObj.getAttribute('toast-msg')=="true")
		{
			ToastErrorMsg(response.msg);
		}
		else{
			if(formObj.getAttribute('error-id')){
				$('#'+formObj.getAttribute('error-id')).html(response.msg);
			}else{
				
				$(formObj).append($('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Warning!</strong> '+response.msg+'</div>'));
			}
		}
	}else if(response){
		console.log(response);
		$('#adv_filter_content').html(response.data);
		if(formObj.getAttribute('data-table-without-pagination')){
			$('#'+formObj.getAttribute('data-table-without-pagination')).DataTable({
				'paging':   false,
				colReorder: true,
				dom: 'Bfrtip',
					buttons: [
						 'csv', 'excel', 'pdf', 'print'
					]
			});
		}else if(formObj.getAttribute('toast-msg')=="true"){
			ToastSuccessMsg(response.msg);
		}else{
			$(formObj).append($('<div class="alert alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button><strong>Success!</strong> '+response.msg+'</div>'));
		}

		if(formObj.getAttribute('content-refresh') && response.status==1)
		{	
			var myStr = formObj.getAttribute('content-refresh');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).load(location.href + ' #'+strArray[i]);
       		 }
		}

		if(formObj.getAttribute('button-click') && response.status==1)
		{	
			$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
			var myStr = formObj.getAttribute('button-click');
        	var strArray = myStr.split(",");
        
        	for(var i = 0; i < strArray.length; i++){
        		$("#"+strArray[i]).click();
       		 }
		}

		if(formObj.getAttribute('no-reset')!="true"){
			formObj.reset();
			$(formObj).find('.multiselect').selectpicker("refresh");
			$(formObj).find('.summernote').shouldInitialize = function () {
				$(formObj).find('.summernote').summernote("reset");
				};
		}

		if(formObj.getAttribute('pivot-view')=="true"){

			var renderers = $.extend(
            $.pivotUtilities.renderers,
            $.pivotUtilities.plotly_renderers,
            $.pivotUtilities.d3_renderers,
            $.pivotUtilities.export_renderers
            );

			$("#output").pivotUI($("#input-table"), {
                renderers: renderers,
            });
		}
			
	}
	$('button[type=button],button[type=submit], input[type=submit]').prop('disabled',false);
    });
}