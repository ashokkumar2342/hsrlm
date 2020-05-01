<?php 
namespace App\Modules\Litigation\Controllers\AdvanceFilter;
use App\Http\Controllers\Controller;				
use App\Modules\Litigation\Models\AdvanceFilterMaster; 
use App\Modules\Litigation\Models\AdvanceFilterMasterCase; 
use Illuminate\Support\Facades\Log;					
use Illuminate\Http\Request;					
use Validator;
use advanceSearch;
use Illuminate\Support\Facades\Crypt;
class AdvanceFilterController extends Controller
{
	/*
	* index() is used to show the login page
	* @param request data
	* @return to the view
	*/
	public function saveNotice(Request $request){
		try{
			$rules=[
				'f_id' => 'required|numeric',
				'filter_name' => 'required',
				'advanceselect' => 'required',
				'filter_by_date' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if($validator->fails()){
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);
				// response as json
			}
			$insArr=array();
			$insArr["filter_id"]=$request->f_id;
			$insArr["user_type"]=getUserType();
			$insArr["filter_name"]=$request->filter_name;
			$insArr["filter_select"]=implode(",",$request->advanceselect);
			
			$insArr["filter_by_date"]=$request->filter_by_date;
			if($request->filter_by_date==5){
				$insArr["filter_date_range"]=$request->filter_date_range;
			}
			$insArr["user_id"]=getUserId();
			$insArr["legal_type_id"] = 2;
			foreach($request->all() as $va=>$key){
				if($key){
					if(in_array($va,array('notice_type','criticality'))){
						$insArr[$va] = implode(",",$key);	
					}elseif(in_array($va,array('location','notice_status','department','legal_category','lawyer','owner','party_type'))){
						$carray=array();
						foreach($key as $vv){
							$carray[]=Crypt::decrypt($vv);
						}
						$insArr[$va]=implode(",",$carray);
					}
				}
			}
			$AdvanceFilterMaster= new AdvanceFilterMaster();
			$filterId=$AdvanceFilterMaster->addFilter($insArr);
			$response=array();
			$response["status"]=1;
			$response["msg"]="Filter Saved Successfully";
			return response()->json($response);
		}catch(\Exception $e){	
			Log::error('AdvanceFilterController-saveNotice: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}

	public function saveCase(Request $request){
		try{
			$rules=[
				'f_id' => 'required|numeric',
				'filter_name' => 'required',
				'advanceselect' => 'required',
				'filter_by_date' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if($validator->fails()){
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);
				// response as json
			}
			$insArr=array();
			$insArr["filter_id"]=$request->f_id;
			$insArr["filter_name"]=$request->filter_name;
			$insArr["filter_select"]=implode(",",$request->advanceselect);
			
			$insArr["filter_by_date"]=$request->filter_by_date;
			if($request->filter_by_date==5){
				$insArr["filter_date_range"]=$request->filter_date_range;
			}
			$insArr["user_id"]=getUserId();
			foreach($request->all() as $va=>$key){
				if($key){
					if(in_array($va,array('potential'))){
						$insArr[$va] = $key;	
					}elseif(in_array($va,array('criticality'))){
						$insArr[$va] = implode(',',$key);	
					}elseif(in_array($va,array('kmp_involved'))){
						$insArr[$va]=Crypt::decrypt($key);
					}elseif(in_array($va,array('case_position','court_type','case_status','legal_category','case_year','owner'))){
						$carray=array();
						foreach($key as $vv){
							$carray[]=Crypt::decrypt($vv);
						}
						$insArr[$va]=implode(",",$carray);
					}
				}
			}
			$AdvanceFilterMasterCase = new AdvanceFilterMasterCase();
			$filterId = $AdvanceFilterMasterCase->addFilter($insArr);
			$response=array();
			$response["status"]=1;
			$response["msg"]="Filter Saved Successfully";
			return response()->json($response);
		}catch(\Exception $e){	
			Log::error('AdvanceFilterController-saveCase: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}
}

