<?php 
namespace App\Modules\Litigation\Controllers\LawyerMaster;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\City;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\LawFirm;
use App\Modules\Litigation\Models\CountryState;
use App\Modules\Litigation\Models\LawyerMaster;
use App\Modules\Litigation\Models\LawyerExperience;
use Auth;
use Response;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
class LawyerMasterController extends Controller
{
	public function index(Request $request){
		try{
			return view('Litigation::LawyerMaster.view');
		}catch(\Exception $e){
			Log::error('LawyerMasterController-index: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function show(Request $request){
		try{
			$data=array();
			$LawyerMaster = new LawyerMaster();
			$data["lawyerList"]=$LawyerMaster->getLawyers(getSetCompanyId());
			return view('Litigation::LawyerMaster.lawyer_table',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-show: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}
	public function lawyerPopupList(Request $request,$name){
		try{ 
			$data=array();
			$LM = new LawFirm();
			$LawFirm = $LM->getLawFirmByNmae($name);
			$LawyerMaster = new LawyerMaster();
			$data["lawyerList"]=$LawyerMaster->getLawyersByFirmId($LawFirm->id);
			$data["lawFirmName"]=$name;
			return view('Litigation::LawyerMaster.lawyer_popup_list',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-show: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function showfirm(Request $request){
		try{
			$data=array();
			$LawFirm = new LawFirm();
			$data["firmList"]=$LawFirm->getFirm(getSetCompanyId());
			return view('Litigation::LawyerMaster.firm_table',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-showfirm: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function addpopup(Request $request){
		try{
			$data=array();
			$LawFirm = new LawFirm();
			$LawyerExperience = new LawyerExperience();
			$data["firmList"]=$LawFirm->getFirm(getSetCompanyId());
			$data["experienceList"]=$LawyerExperience->getExperience();
			return view('Litigation::LawyerMaster.addpopup',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-addpopup: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function addpopupfirm(Request $request){
		try{
			$data=array();
			$Country = new Country();
			$data["countryList"]=$Country->getCountry();
			return view('Litigation::LawyerMaster.addpopupfirm',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-addpopupfirm: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function state(Request $request){
		try{
			$id = Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data=array();
			$CountryState = new CountryState();
			$data["stateList"]=$CountryState->getState($id);
			return view('Litigation::LawyerMaster.state',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-state: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function city(Request $request){
		try{
			$id = Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data=array();
			$City = new City();
			$data["cityList"]=$City->getCity($id);
			return view('Litigation::LawyerMaster.city',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-city: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function firmsubmit(Request $request){
		try{
			$rules=[
				'law_firm_name' => 'required|string|unique:lawfirm,name,NULL,id,company_id,' . getSetCompanyId(),
				'country' => 'required',
				'state' => 'required',
				'city' => 'required',
				'website' => 'nullable|url',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$insArr=array();
			$LawFirm = new LawFirm();
			
			$insArr['website'] = $request->website;
			$insArr['address'] = $request->address;
			$insArr['company_id'] = getSetCompanyId();
			$insArr['name'] = $request->law_firm_name;
			$insArr['city'] = Crypt::decrypt($request->city);
			$insArr['state'] = Crypt::decrypt($request->state);
			$insArr['country'] = Crypt::decrypt($request->country);
			$LawFirm->insArr($insArr);

			$response["status"]=1;
			$response["msg"]="Law firm added successfully!";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('LawyerMasterController-firmsubmit: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function lawyersubmit(Request $request){
		try{
			$rules=[
				'law_firm' => 'required',
				'lawyer_name' => 'required|string',
				'lawyer_email' => 'required|email|unique:lawyer_master,email,NULL,id,company_id,' . getSetCompanyId(),
				'mobile' => 'required|digits_between:10,12',
				'landline' => 'nullable|digits_between:10,12',
				'linkedin' => 'nullable|url',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$insArr=array();
			$LawyerMaster = new LawyerMaster();
			
			$insArr['mobile'] = $request->mobile;
			$insArr['name'] = $request->lawyer_name;
			$insArr['linkedin'] = $request->linkedin;
			$insArr['landline'] = $request->landline;
			$insArr['company_id'] = getSetCompanyId();
			$insArr['email'] = $request->lawyer_email;
			$insArr['designation'] = $request->designation;
			$insArr['law_firm_id'] = Crypt::decrypt($request->law_firm);
			$insArr['experience'] = !empty($request->experience)?Crypt::decrypt($request->experience):'';
			$LawyerMaster->insArr($insArr);

			$response["status"]=1;
			$response["msg"]="Lawyer added successfully!";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('LawyerMasterController-lawyersubmit: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function editpopupfirm(Request $request){
		try{
			$id = Crypt::decrypt($request->firm_id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$data=array();
			$LawFirm = new LawFirm();
			$Country = new Country();

			$data['firm_id'] = $id;
			$data["countryList"]=$Country->getCountry();
			$data['result'] = $LawFirm->getListById($id);
			return view('Litigation::LawyerMaster.editpopupfirm',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-editpopupfirm: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function stateedit(Request $request){
		try{
			if(!empty($request->id)){
				$id = Crypt::decrypt($request->c_id);
				$firm_id = Crypt::decrypt($request->ref_id);
				$rules=[
					$id => 'numeric',
					$firm_id => 'numeric',
				];
				$validator = Validator::make($request->all(),$rules);
				if ($validator->fails()) {
					$errors = $validator->errors()->all();
					$response=array();
					$response["status"]=0;
					$response["msg"]=$errors[0];
					return response()->json($response);// response as json
				}
				$data=array();
				$LawFirm = new LawFirm();
				$CountryState = new CountryState();

				$data['firm_id'] = $firm_id;
				$data["stateList"]=$CountryState->getState($id);
				$data['result'] = $LawFirm->getListById($firm_id);
				return view('Litigation::LawyerMaster.stateedit',$data);
			}else{
				return view('Litigation::LawyerMaster.stateedit');
			}
		}catch(\Exception $e){
			Log::error('LawyerMasterController-stateedit: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function cityedit(Request $request){
		try{
			if(!empty($request->id)){
				$id = Crypt::decrypt($request->s_id);
				$firm_id = Crypt::decrypt($request->ref_id);
				$rules=[
					$id => 'numeric',
					$firm_id => 'numeric',
				];
				$validator = Validator::make($request->all(),$rules);
				if ($validator->fails()) {
					$errors = $validator->errors()->all();
					$response=array();
					$response["status"]=0;
					$response["msg"]=$errors[0];
					return response()->json($response);// response as json
				}
				$data=array();
				$City = new City();
				$LawFirm = new LawFirm();
				$data["cityList"]=$City->getCity($id);
				$data['result'] = $LawFirm->getListById($firm_id);
				return view('Litigation::LawyerMaster.cityedit',$data);	
			}else{
				return view('Litigation::LawyerMaster.cityedit');
			}
		}catch(\Exception $e){
			Log::error('LawyerMasterController-cityedit: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function firmupdate(Request $request){
		try{
			$firm_id = Crypt::decrypt($request->firm_id);
			$rules=[
				'law_firm_name' => 'required|string|unique:lawfirm,name,'.$firm_id.',id,company_id,' . getSetCompanyId(),
				'country' => 'required',
				'state' => 'required',
				'city' => 'required',
				'website' => 'nullable|url',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$updArr=array();
			$LawFirm = new LawFirm();
		
			$updArr['website'] = $request->website;	
			$updArr['address'] = $request->address;
			$updArr['name'] = $request->law_firm_name;
			$updArr['city'] = Crypt::decrypt($request->city);
			$updArr['state'] = Crypt::decrypt($request->state);
			$updArr['country'] = Crypt::decrypt($request->country);
			$LawFirm->updateFirm($updArr,$firm_id);

			$response["status"]=1;
			$response["msg"]="Law firm updated successfully!";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('LawyerMasterController-firmupdate: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function editpopup(Request $request){
		try{
			$id = Crypt::decrypt($request->lawyer_id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$data=array();
			$LawFirm = new LawFirm();
			$LawyerMaster = new LawyerMaster();
			$LawyerExperience = new LawyerExperience();

			$data['lawyer_id'] = $id;
			$data['result'] = $LawyerMaster->getListById($id);
			$data["firmList"]=$LawFirm->getFirm(getSetCompanyId());
			$data["experienceList"]=$LawyerExperience->getExperience();
			return view('Litigation::LawyerMaster.editpopup',$data);
		}catch(\Exception $e){
			Log::error('LawyerMasterController-editpopup: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function lawyerupdate(Request $request){
		try{
			$lawyer_id = Crypt::decrypt($request->lawyer_id);
			$rules=[
				'law_firm' => 'required',
				'lawyer_name' => 'required|string',
				'lawyer_email' => 'required|email|unique:lawyer_master,email,'.$lawyer_id.',id,company_id,' . getSetCompanyId(),
				'mobile' => 'required|digits_between:10,12',
				'landline' => 'nullable|digits_between:10,12',
				'linkedin' => 'nullable|url',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$updArr=array();
			$LawyerMaster = new LawyerMaster();
			
			$updArr['mobile'] = $request->mobile;
			$updArr['name'] = $request->lawyer_name;
			$updArr['linkedin'] = $request->linkedin;
			$updArr['landline'] = $request->landline;
			$updArr['email'] = $request->lawyer_email;
			$updArr['designation'] = $request->designation;
			$updArr['law_firm_id'] = Crypt::decrypt($request->law_firm);
			$updArr['experience'] = !empty($request->experience)?Crypt::decrypt($request->experience):'';
			$LawyerMaster->updateLawyer($updArr,$lawyer_id);

			$response["status"]=1;
			$response["msg"]="Lawyer updated successfully!";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('LawyerMasterController-lawyerupdate: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

}

