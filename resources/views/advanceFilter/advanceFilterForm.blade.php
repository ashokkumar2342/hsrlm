@php $selectArr = explode(',',$masterField->filter_select); @endphp
<div class="row">
	<div class="col-md-4">
		<label>&nbsp;</label>
		<select name="filter_by_date" onchange="showDatePicker(this)" picker_id="basic-search-datepicker-notice" class="form-control input-xs multisizesmall">
			@foreach ($dateby as $va => $key)
				<option value="{{$va}}" {{$masterField->filter_by_date == $va?'selected':''}}>{{$key}}</option>
			@endforeach
		</select>
	</div>
	<div class="col-md-8" id="basic-search-datepicker-notice-content" style="{{$masterField->filter_by_date != 5?'display:none':''}}">
		<div class="form-group mb-0">
			<label class="control-label mb-8 text-left">&nbsp;</label>
			<input class="form-control input-daterange-datepicker input-xs multisizesmall" id="basic-search-datepicker" name="filter_date_range" value="{{$masterField->filter_date_range}}" type="text">
		</div>
	</div>
</div>
<div class="row" style="margin-top: 20px;">
	<div class="col-md-8">
		<div class="row">
			<div class="col-md-4">
	            <div class="form-group">
	                <select class="form-control"  name="notice_type" id="notice_type" onchange="callAjax(this,'{{url('matrixreport/getnoticestatus')}}/{{Crypt::encrypt($masterField->id)}}/'+$(this).val(),'notice_status_div'),sendReceived(this.value)" select2="filter_select2">
	                    <option selected disabled>Select Notice Type</option>  
	                    {!! getSelectByDefault('NoticeType',$masterField->notice_type) !!}
	                </select>
	            </div> 
	        </div>
	        <div class="col-md-4">
                <div class="form-group">
                    <select class="form-control select2 filter_select2" name="location" id="location">
                        <option selected disabled value="">Select Location</option>   
                        @foreach ($locationList as $va => $key)
                           <option value="{{Crypt::encrypt($key->id)}}" {{$masterField->location == $key->id?'selected':''}}>{{$key->location_name}}</option>
                        @endforeach
                    </select>
                </div> 
            </div>
            <div class="col-md-4">
            	<div class="form-group" id="notice_status_div">
	                <select class="form-control select2 filter_select2"  name="notice_status" id="notice_status" >
	                    <option selected disabled>Select Status</option>  
	                </select>
            	</div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <select class="form-control select2 filter_select2"  name="department" id="department" >
                        <option selected disabled>Select Department</option>   
                        @foreach($deptList as $va => $key)
                            <option value="{{Crypt::encrypt($key->id)}}" {{$masterField->department == $key->id?'selected':''}}>{{$key->name.'-'.$key->short_name}}</option>
                        @endforeach
                    </select>
                </div> 
            </div>
            <div class="col-md-4">
            	<div class="form-group">
	                <select class="form-control select2 filter_select2"  name="legal_category" id="notice_legal_category" required onchange="callAjax(this,'{{url('matrixreport/getact')}}/'+this.value+'/{{Crypt::encrypt($masterField->id)}}','notice_act_list_change');" multiselect-form="true">
	                    <option selected disabled>Select Legal Category</option>
	                    @foreach ($categoryList as $va => $key)
	                        <option value="{{Crypt::encrypt($key->id)}}" {{$masterField->legal_category == $key->id?'selected':''}}>{{$key->name}}</option> 
	                    @endforeach
	                </select>
               	</div>
            </div>
            <div class="col-md-4">
            	<div class="form-group" id="notice_act_list_change">
	                @includeIf('Litigation::ActMaster.actList',['type' => 'noticewithoutlabel'])
	            </div>
            </div> 
	    </div>
	</div>
	<div class="col-sm-4">
		<select multiple id="public-methods" name="advanceselect[]">
			@foreach ($noticeField as $va => $key)
				<option value="{{$key->column_name}}" {{in_array($key->column_name,$selectArr)?'selected':''}} >{{$key->name}}</option>
			@endforeach
		</select>
		<div class="button-box"> 
			<a id="select-all" class="btn btn-danger btn-outline mr-10 mt-15" href="#">select all</a> 
			<a id="deselect-all" class="btn btn-info btn-outline mr-10 mt-15" href="#">deselect all</a> 
		</div>
	</div>
</div>
<div class="clearfix">&nbsp;</div>
<div class="row">
	<input type="hidden" name="f_id" value="1" />
	<div class="col-md-12">
		<?php if($filterData==0){?>
			<button type="submit" class="btn btn-success">Save</button>
		<?php } ?>
		<button id="adv_search_btn_id" onclick="fourthurl(this.form)" type="button" class="btn btn-primary">Search</button>
		<button id="adv_search_btn_id" onclick="fifthurl(this.form)" type="button" class="btn btn-primary">Pivot</button>
	
		<button type="reset" class="btn btn-danger">Clear</button>
	</div>
</div>
<script>
	$(document).ready(function() {
		advanceSearchNotice(1);
		$('#notice_type').trigger('change');
		$('#notice_legal_category').trigger('change');
	});
</script>