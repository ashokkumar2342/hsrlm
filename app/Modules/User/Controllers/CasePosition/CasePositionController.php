<?php 
namespace App\Modules\Litigation\Controllers\CasePosition;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CasePosition;
use Auth;
use Response;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
class CasePositionController extends Controller
{
	public function addpopup(Request $request){
		try{
			$case_id = Crypt::decrypt($request->case_id);
			$rules=[
				$case_id=>'numeric',  
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 

			$data = array();
			$data['case_id'] = $case_id;

			$Company = new Company();
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 
			return view('Litigation::CasePosition.addpopup',$data)->render();
		}catch (Exception $e) {
			Log::error('CasePositionController-addpopup: '.$e->getMessage()); 		
			return view('error.home');		
		}
	}

	public function editpopup(Request $request){
		try{
			$id = Crypt::decrypt($request->id);
			$rules=[
				$id=>'numeric', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 

			$data = array();
			$data['id'] = $id;

			$Company = new Company();
			$CasePosition = new CasePosition();
			$data['result'] = $CasePosition->getPositionById($id);
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 
			
			return view('Litigation::CasePosition.editpopup',$data)->render();
		}catch (Exception $e) {
			Log::error('CasePositionController-editpopup: '.$e->getMessage()); 		
			return view('error.home');		
		}
	}

	public function addposition(Request $request){
		try{  
			$case_id=Crypt::decrypt($request->case_id);
			$rules=[
				$case_id => 'numeric',
				'maximum_budget' => 'required|numeric',
				'applicable_from' => 'required|date|date_format:Y-m-d',
				'applicable_to' => 'required|date|date_format:Y-m-d',
				'our_position' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$CasePosition = new CasePosition();
			$insArr=array(); 
		    $insArr['case_id'] = $case_id;
		    $insArr['max_budget'] = $request->maximum_budget;
		    $insArr['applicable_from'] = $request->applicable_from;
		    $insArr['applicable_to'] = $request->applicable_to;
		    $insArr['our_position'] = $request->our_position;
		    $insArr['rationale'] = $request->rationale;
			$CasePosition->insert($insArr);

			$response=array();
			$response["status"]=1;
			$response["msg"]='Position Added';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('CasePositionController-addposition: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function editposition(Request $request){
		try{  
			$id=Crypt::decrypt($request->position_id);
			$rules=[
				$id => 'numeric',
				'maximum_budget' => 'required|numeric',
				'applicable_from' => 'required|date|date_format:Y-m-d',
				'applicable_to' => 'required|date|date_format:Y-m-d',
				'our_position' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$CasePosition = new CasePosition();

			$updArr=array(); 
			$updArr['max_budget'] = $request->maximum_budget;
		    $updArr['applicable_from'] = $request->applicable_from;
		    $updArr['applicable_to'] = $request->applicable_to;
		    $updArr['our_position'] = $request->our_position;
		    $updArr['rationale'] = $request->rationale;
			$CasePosition->updArr($updArr,$id);

			$response=array();
			$response["status"]=1;
			$response["msg"]='Position Updated';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('CasePositionController-editposition: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}
   	
}

