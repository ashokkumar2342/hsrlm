var full_url_js=(("https:"==document.location.protocol?"https://":"http://")+window.location.hostname+("8000"==document.location.port?":8000":""));

function callAjax(obj,url,divId,callback){
	$('#'+divId).html('Please Wait......'); 
    $.get(url,
    {
        id: obj.value
    },
    function(data, status){
        if(status=="success")
		{
			$('#'+divId).html(data);
			if(obj.getAttribute('no-add-form')!="true")
				callJqueryDefault(divId);
			if(obj.getAttribute('data-table-scroll')!=""){
				$("."+obj.getAttribute('data-table-scroll')).DataTable().destroy();
					$("."+obj.getAttribute('data-table-scroll')).DataTable({
					"scrollY":        "400px",
					colReorder: true,
			        "scrollCollapse": true,
			        "paging":         false
						});
				}
			if(obj.getAttribute('data-table-export'))
				{
					if ( ! $.fn.DataTable.isDataTable("#"+obj.getAttribute('data-table-export')))
					{
						$("#"+obj.getAttribute('data-table-export')).DataTable({
							'iDisplayLength': 10,
							colReorder: true,
							dom: 'Bfrtip',
							buttons: ['csv', 'excel', 'pdf', 'print'],
							  exportOptions: {
							    modifer: {
							      page: 'all',
							      search: 'none'    }
							  }
						});
					}
					
				}
			if(obj.getAttribute('toast-msg')=="true"){
			    if(data.status==0)
			    {
					ToastErrorMsg(data.msg);
			    }
			    else if (data.status==1)
			    {
					ToastSuccessMsg(data.msg);  
			
			    }
				
			}	
			if(obj.getAttribute('select2')!=""){
				$("."+obj.getAttribute('select2')).select2({
				   	width: 'resolve' 
				   });
				}	
			if(obj.getAttribute('multiselect-form')=="true")
				$("#"+divId).find('.multiselect').selectpicker();

			if(obj.getAttribute('success-content-id')){
				$('#'+obj.getAttribute('success-content-id')).html(data.data);
			}
			
			if(callback){
				callback();
			}
			if(obj.getAttribute('data-table'))
				{
				$("#"+obj.getAttribute('data-table')).DataTable({
					'iDisplayLength': 10,
					colReorder: true,
				});
				}
			else if(obj.getAttribute('child-table'))
			{
				var table = $("#"+obj.getAttribute('child-table')).DataTable({colReorder: true,});

				  // Add event listener for opening and closing details
				  $("#"+obj.getAttribute('child-table')).on('click', 'td.details-control', function () {
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
			if(obj.getAttribute('data-table-class'))
				{
					if ( ! $.fn.DataTable.isDataTable("."+obj.getAttribute('data-table-class')))
					{
						$("."+obj.getAttribute('data-table-class')).DataTable({
						'iDisplayLength': 10,
						colReorder: true,
					});
					}
					
				}

			if(obj.getAttribute('button-click'))
			{	
				var myStr = obj.getAttribute('button-click');
	        	var strArray = myStr.split(",");
	        
	        	for(var i = 0; i < strArray.length; i++){
	        		$("#"+strArray[i]).click();
	       		 }
			}
			if(obj.getAttribute('trigger'))
			{	
				var myStr = obj.getAttribute('trigger');
	        	var strArray = myStr.split(",");
	        
	        	for(var i = 0; i < strArray.length; i++){
	        		$("#"+strArray[i]).trigger('change');
	       		 }
			 
	        	 
			}

			if(obj.getAttribute('call-back')){
					var callback=obj.getAttribute('call-back');
					//console.log(typeof window[callback]);
					if(typeof window[callback] == "function")
		                    window[callback].call();
				}

			if(obj.getAttribute('text-editor')!=""){
				$('.'+obj.getAttribute('text-editor')).summernote({
					toolbar: [
					     
					     ['font', ['bold', 'italic', 'underline']], 
					     ['fontsize', ['fontsize']],
					     ['color', ['color']],
					     ['para', ['ul', 'ol', 'paragraph']],
					     ['height', ['height']], 		      
					     ['insert', ['link', 'picture','video']],
					  
					   ],

					 placeholder: 'write here...',
					 height: 100
							}); 
				
			}

			if(obj.getAttribute('upload-modal')=="true")
			{
				var urls = obj.getAttribute('upload-url');
				var token = obj.getAttribute('upload-token');
				var key = obj.getAttribute('upload-key');
				var limit = obj.getAttribute('upload-size');
				var mime = obj.getAttribute('upload-file');
				 $("#sequentialupload").uploadFile({
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
			
	
		}
		else
		{
			if(obj.getAttribute('toast-msg')=="true"){
				ToastErrorMsg(data.msg);
			}
		}
    });

}
function callAjaxUrl(url,divId,callback){
	$('#'+divId).html('Please Wait......'); 
    $.get(url,{},
    function(data, status){
        if(status=="success")
		{
			$('#'+divId).html(data);	
			callJqueryDefault(divId);
			if(callback)
				callback();
		}
    });

}
function callDataTable(url,divId,tableId){
	$('#'+divId).html('Please Wait......'); 
    $.get(url,{},
    function(data, status){
        if(status=="success")
		{
			$('#'+divId).html(data);
			if ( ! $.fn.DataTable.isDataTable("#"+tableId) ) 
			{
			$("#"+tableId).DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			}
			if ( ! $.fn.DataTable.isDataTable("."+tableId) ) 
			{
			$("."+tableId).DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			}

			callJqueryDefault(divId);
		}
    });

}

function callSuccessPopup(msg){
	$('#success-popup').modal("show"); 
	$('#success-popup-content-id').html(msg); 
}

function callPopupLarge(obj,url){
	$('#ModalLargeId').modal("show"); 
	var divId='ModalLargeContentId';
	$('#'+divId).html('<div align="center"><img src="'+full_url_js+'/img/loader.gif" align="center"></div>'); 
    $.get(url,{},
    function(response, status){
        if(status=="success"){
			$('#'+divId).html(response);
			callJqueryDefault(divId);
			$('#'+divId).css('width','90%');
			if(obj.getAttribute('datatable-view-without-pagination')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'paging':   false,
				colReorder: true,
				});
			}

			if(obj.getAttribute('child-table'))
				{
					var table = $("."+obj.getAttribute('child-table')).DataTable({colReorder: true,});

						  // Add event listener for opening and closing details
						  $("."+obj.getAttribute('child-table')).on('click', 'td.details-control', function () {
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

				if(obj.getAttribute('multi-select')=="true")
				{
					$("#"+divId).find('.multiselect').selectpicker();
				}

				if(obj.getAttribute('select2')!=""){
				$("."+obj.getAttribute('select2')).select2({
				   	width: 'resolve' 
				   });
				}

				if(obj.getAttribute('call-back')){
					var callback=obj.getAttribute('call-back');
					//console.log(typeof window[callback]);
					if(typeof window[callback] == "function")
		                    window[callback].call();
				}

			if(obj.getAttribute('datatable-view')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			}
			if(obj.getAttribute('popup-width')!="")
				{
					$('#'+divId).css('width',obj.getAttribute('popup-width'));
				}	

			if(obj.getAttribute('textarea-expand')){
					setTimeout(function(){
						$('#'+divId).find('textarea').each(function() {
					        $(this).height($(this).prop('scrollHeight'));
					    })
					},600);
				}

			if(obj.getAttribute('multiselect-form')=="true")
				$("#"+divId).find('.multiselect').selectpicker();
			}

			
    });

}

function callPopupDatatable(url){
	$('#ModalLargeId').modal("show"); 
	var divId='ModalLargeContentId';
	$('#'+divId).html('<div align="center"><img src="'+full_url_js+'/img/loader.gif" align="center"></div>'); 
    $.get(url,{},
    function(response, status){
        if(status=="success"){
			$('#'+divId).html(response);
			$("#"+divId).find('.datatablepopup').DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			callJqueryDefault(divId);
			$('#'+divId).css('width','90%');
			if($(document).height() > 650 && $(document).height() < 800){
					var $height = $(document).height()-150;
				}
				else{
					var $height=600;
				}
				$(".modal-body").css('max-height',$height);
			if(obj.getAttribute('popup-width')!="")
				{
					$('#'+divId).css('width',obj.getAttribute('popup-width'));
				}	
			}
    });

}

function callPopupsm(obj,url){
	$('#ModalSmId').modal("show"); 
	var divId='ModalSmContentId';
	$('#'+divId).html('<div align="center"><img src="'+full_url_js+'/img/loader.gif" align="center"></div>'); 
    $.get(url,{},
    function(response, status){
        if(status=="success"){
			$('#'+divId).html(response);
			callJqueryDefault(divId);
			$('#'+divId).css('width','30%');
			if(obj.getAttribute('datatable-view-without-pagination')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'paging':   false,
				colReorder: true,
				});
			}
			if(obj.getAttribute('datatable-view')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			}

			if(obj.getAttribute('call-back'))
				{
					var callback=obj.getAttribute('call-back');
					console.log(typeof window[callback]);
					if(typeof window[callback] == "function")
		                    window[callback].call(); //wi
					//console.log(formObj.getAttribute('call-back'));
					
					//eval(callback());
				}
				if(obj.getAttribute('popup-width')!="")
				{
					$('#'+divId).css('width',obj.getAttribute('popup-width'));
				}
				if(obj.getAttribute('multiselect-form')=="true")
					$("#"+divId).find('.multiselect').selectpicker();
			}
    });

}


function callPopupLevel2(obj,url){
	$('#Modallevel2').modal("show"); 
	var divId='Modallevel2ContentId';
	$('#'+divId).html('<div align="center"><img src="'+full_url_js+'/img/loader.gif" align="center"></div>'); 
    $.get(url,{},
    function(response, status){
        if(status=="success"){
			$('#'+divId).html(response);
			callJqueryDefault(divId);
			$('#'+divId).css('width','90%');
			if(obj.getAttribute('datatable-view-without-pagination')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'paging':   false,
				colReorder: true,
				});
			}
			if(obj.getAttribute('datatable-view')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			}
			if(obj.getAttribute('multi-select')=="true")
			{
				$("#"+divId).find('.multiselect').selectpicker();
			}

			if(obj.getAttribute('select2')!=""){
				$("."+obj.getAttribute('select2')).select2({
				   	width: 'resolve' 
				   });
				}
			if(obj.getAttribute('call-back'))
				{
					var callback=obj.getAttribute('call-back');
					console.log(typeof window[callback]);
					if(typeof window[callback] == "function")
		                    window[callback].call(); //wi
					//console.log(formObj.getAttribute('call-back'));
					
					//eval(callback());
				}
				if(obj.getAttribute('popup-width')!="")
				{
					$('#'+divId).css('width',obj.getAttribute('popup-width'));
				}
				if(obj.getAttribute('multiselect-form')=="true")
					$("#"+divId).find('.multiselect').selectpicker();
			}
    });

}

function callPopupLevel3(obj,url){
	$('#Modallevel3').modal("show"); 
	var divId='Modallevel3ContentId';
	$('#'+divId).html('<div align="center"><img src="'+full_url_js+'/img/loader.gif" align="center"></div>'); 
    $.get(url,{},
    function(response, status){
        if(status=="success"){
			$('#'+divId).html(response);
			callJqueryDefault(divId);
			$('#'+divId).css('width','90%');
			if(obj.getAttribute('datatable-view-without-pagination')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'paging':   false,
				colReorder: true,
				});
			}
			if(obj.getAttribute('datatable-view')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			}
			if(obj.getAttribute('multi-select')=="true")
			{
				$("#"+divId).find('.multiselect').selectpicker();
			}

			if(obj.getAttribute('select2')!=""){
			$("."+obj.getAttribute('select2')).select2({
			   	width: 'resolve' 
			   });
			}
			if(obj.getAttribute('call-back'))
				{
					var callback=obj.getAttribute('call-back');
					console.log(typeof window[callback]);
					if(typeof window[callback] == "function")
		                    window[callback].call(); //wi
					//console.log(formObj.getAttribute('call-back'));
					
					//eval(callback());
				}
				if(obj.getAttribute('popup-width')!="")
				{
					$('#'+divId).css('width',obj.getAttribute('popup-width'));
				}
				if(obj.getAttribute('multiselect-form')=="true")
					$("#"+divId).find('.multiselect').selectpicker();
			}
    });

}

function callPopupLevel4(obj,url){
	$('#Modallevel4').modal("show"); 
	var divId='Modallevel4ContentId';
	$('#'+divId).html('<div align="center"><img src="'+full_url_js+'/img/loader.gif" align="center"></div>'); 
    $.get(url,{},
    function(response, status){
        if(status=="success"){
			$('#'+divId).html(response);
			callJqueryDefault(divId);
			$('#'+divId).css('width','90%');
			if(obj.getAttribute('datatable-view-without-pagination')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'paging':   false,
				colReorder: true,
				});
			}
			if(obj.getAttribute('datatable-view')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			}
			if(obj.getAttribute('multi-select')=="true")
			{
				$("#"+divId).find('.multiselect').selectpicker();
			}

			if(obj.getAttribute('select2')!=""){
			$("."+obj.getAttribute('select2')).select2({
			   	width: 'resolve' 
			   });
			}
			if(obj.getAttribute('call-back'))
				{
					var callback=obj.getAttribute('call-back');
					console.log(typeof window[callback]);
					if(typeof window[callback] == "function")
		                    window[callback].call(); //wi
					//console.log(formObj.getAttribute('call-back'));
					
					//eval(callback());
				}
				if(obj.getAttribute('popup-width')!="")
				{
					$('#'+divId).css('width',obj.getAttribute('popup-width'));
				}
				if(obj.getAttribute('multiselect-form')=="true")
					$("#"+divId).find('.multiselect').selectpicker();
			}
    });

}

function callPopupLevel5(obj,url){
	$('#Modallevel5').modal("show"); 
	var divId='Modallevel5ContentId';
	$('#'+divId).html('<div align="center"><img src="'+full_url_js+'/img/loader.gif" align="center"></div>'); 
    $.get(url,{},
    function(response, status){
        if(status=="success"){
			$('#'+divId).html(response);
			callJqueryDefault(divId);
			$('#'+divId).css('width','90%');
			if(obj.getAttribute('datatable-view-without-pagination')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'paging':   false,
				colReorder: true,
				});
			}
			if(obj.getAttribute('datatable-view')=="true")
				if ( ! $.fn.DataTable.isDataTable('.datatablepopup') ) 
			{
				$("#"+divId).find('.datatablepopup').DataTable({
				'iDisplayLength': 10,
				colReorder: true,
			});
			}
			if(obj.getAttribute('call-back'))
				{
					var callback=obj.getAttribute('call-back');
					console.log(typeof window[callback]);
					if(typeof window[callback] == "function")
		                    window[callback].call(); //wi
					//console.log(formObj.getAttribute('call-back'));
					
					//eval(callback());
				}

			if(obj.getAttribute('upload-modal')=="true")
			{
				var urls = obj.getAttribute('upload-url');
				var token = obj.getAttribute('upload-token');
				var key = obj.getAttribute('upload-key');
				var limit = obj.getAttribute('upload-size');
				var mime = obj.getAttribute('upload-file');
				 $("#sequentialupload").uploadFile({
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
				if(obj.getAttribute('popup-width')!="")
				{
					$('#'+divId).css('width',obj.getAttribute('popup-width'));
				}

				if(obj.getAttribute('multiselect-form')=="true")
					$("#"+divId).find('.multiselect').selectpicker();
				
			}
    });

}


function changeUserType(obj,url){
	
    $.get(url,{
        id: obj.value
    },
    function(data, status){
        if(status=="success")
		{
			if(data.status==1)
				window.location.reload();
		}
    });

}
function changeJurisdiction(obj){
	if(obj.value=="national")
		$('#city_id_div').hide();
	if(obj.value=="state" || obj.value=="muncipal")
		$('#city_id_div').show();
	
  

}


function callchildTable(url,divId,tableId){
	$('#'+divId).html('Please Wait......'); 
    $.get(url,{},
    function(data, status){
        if(status=="success")
		{
			$('#'+divId).html(data);
			if ( ! $.fn.DataTable.isDataTable("#"+tableId) ) 
			{
				var table = $("#"+tableId).DataTable({colReorder: true,});

						  // Add event listener for opening and closing details
						  $("#"+tableId).on('click', 'td.details-control', function () {
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
			
			callJqueryDefault(divId);
		}
    });

}

function data_table_by_click(table_id)
{
	if ( ! $.fn.DataTable.isDataTable("#"+table_id) ) 
			{
				$("#"+table_id).DataTable({
					'iDisplayLength': 10,
					colReorder: true,
				});
			}
}



function changeAt(obj){  
	$('.pre-disable').attr("readonly","readonly");
		if (obj==null) {
				console.log('empty')
		}else{
				$.each(obj, function( index, value ) {  	
			    $('#'+value).removeAttr("readonly"); 
			    $('#edit_remark').removeAttr("readonly"); 
				}); 
		}

	}

function storageadd(key,value)
{
	if (typeof(Storage) !== "undefined") {
  		localStorage.setItem(key, value);
	} else {
	  // Sorry! No Web Storage support..
	}
}	

function storageget(key)
{
	if (typeof(Storage) !== "undefined") {
  		return localStorage.getItem(key);
	} else {
	  // Sorry! No Web Storage support..
	}
}

function changeJurisdiction(obj){ 
	if(obj=="National"){
	$('#state').prop('disabled', 'disabled');
	$('#city').prop('disabled', 'disabled'); 
	}
	if(obj=="State"){
		$('#state').prop('disabled', false); 
		$('#city').prop('disabled', 'disabled'); 
	}
	if(obj=="Municipal"){
		$('#state').prop('disabled', false); 
		$('#city').prop('disabled', false); 
	}

}

function textAreaAdjust(o) {
 $(o).height(0).height(o.scrollHeight);
}

function showDiv(div_id) {
	var myStr = div_id;
	var strArray = myStr.split(","); 
	for(var i = 0; i < strArray.length; i++){
		$("#"+strArray[i]).show();
		 } 
}function hideDiv(div_id) {
	var myStr = div_id;
	var strArray = myStr.split(","); 
	for(var i = 0; i < strArray.length; i++){
		$("#"+strArray[i]).hide();
	} 
}function showHide(div_show_id,div_hide_id) {
	var myStrShow = div_show_id;
	var myStrHide = div_hide_id;
	var strArrayShow = myStrShow.split(","); 
	var strArrayHide = myStrHide.split(","); 
	for(var i = 0; i < strArrayShow.length; i++){
		$("#"+strArrayShow[i]).show();
	 }
	 for(var i = 0; i < strArrayHide.length; i++){
	 	$("#"+strArrayHide[i]).hide();
	  }  

}function disabledId(disabled_id) {
	var myStrShow = disabled_id; 
	var strArrayShow = myStrShow.split(",");  
	for(var i = 0; i < strArrayShow.length; i++){
		$("#"+strArrayShow[i]).attr('disabled', true);
	 } 
}function enabledId(enable_id) { 
	var myStrShow = enable_id; 
	var strArrayShow = myStrShow.split(",");  
	for(var i = 0; i < strArrayShow.length; i++){
		$("#"+strArrayShow[i]).attr('disabled', false);
	 } 
}

function callErrorPopup(msg){
	$('#error-popup').modal("show"); 
	$('#error-popup-content-id').html(msg); 
}

function fetchNotifications() { 
    var page = $('.endless-pagination').data('next-page'); 
    
    if(page !== null) {
    	$('#notification_wait').show();

        clearTimeout( $.data( this, "scrollCheck" ) );

        $.data( this, "scrollCheck", setTimeout(function() {

            var scroll_position_for_posts_load = $('#notification_list').height() + $('#notification_list').scrollTop() + 100;

            if(scroll_position_for_posts_load >= $('#notification_list').height()) {            	
                $.get(page, function(data){
                    $('.notifications').append(data.notifications);
                    $('.endless-pagination').data('next-page', data.next_page);
                    $('#notification_wait').hide();
                });
            }
        }, 500))

    }
} 
//scroll on window
$('#notification_list').scroll(fetchNotificationsAll);

function fetchNotificationsAll() {
	 
    var page = $('.endless-pagination2').data('next-page');
    var hasPage = $('.endless-pagination2').data('has-page');
    
 
    if(page !== null && hasPage == 1) {
	$('#message_wait').show();
        clearTimeout( $.data( this, "scrollCheck" ) );

        $.data( this, "scrollCheck", setTimeout(function() {
            var scroll_position_for_posts_load = $(window).height() + $(window).scrollTop() + 100;

            if(scroll_position_for_posts_load >= $(document).height()) {
            	       
                $.get(page, function(data){
                                       	 
                    $('.notificationItmes').append(data.notifications);
                    
                    $('.endless-pagination2').data('next-page', data.next_page);
                     $('#message_wait').hide();	 

                });
            }
        }, 350))

    }
}