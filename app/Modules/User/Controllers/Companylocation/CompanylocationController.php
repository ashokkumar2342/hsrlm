<?php
namespace App\Modules\Litigation\Controllers\Companylocation;
use App\Http\Controllers\Controller;			// model of company table		// model of location table
use App\Modules\Litigation\Models\City; 				// controller lib
use App\Modules\Litigation\Models\Company; 			// model of company table		// model of location table
use App\Modules\Litigation\Models\Country; 							// model of company table		// model of location table
use App\Modules\Litigation\Models\AdminLog; 		// model of company table		// model of location table
use App\Modules\Litigation\Models\CountryState; 	// model of company table		// model of location table
use App\Modules\Litigation\Models\CompanyLocation; 						// to handle the request data
use App\Modules\Litigation\Models\CompanyDepartment; 						// to handle the request data
use App\Modules\Litigation\Models\Defaults\LegalCategory; 						// to handle the request data
use App\Modules\Litigation\Models\DefaultFileType; 			// model of company table
use App\Modules\Litigation\Models\DefaultDepartment;
use App\Modules\Litigation\Models\Defaults\MatterGroup;						// to handle the request data
use Auth;					// to handle the request data
use Validator;
use Illuminate\Http\Request;	
use Illuminate\Support\Facades\Log;			
use Illuminate\Support\Facades\Crypt;			// log for exception handling
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class CompanylocationController extends Controller
{

	 public function __construct(){
		 $this->middleware('admin');
	}

	public function viewlist(Request $request){
		try{
			$data=array();
			$Company=new Company();
			$data["companyList"]=$Company->listCompany();
			return view('Litigation::Companylocation.view',$data);
		}catch(\Exception $e){
			Log::error('CompanylocationController-viewlist: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function add(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
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
			}else{
				$data = array();
				$Country = new Country();
				$Company = new Company();
				$MatterGroup = new MatterGroup();
				$LegalCategory = new LegalCategory();
				$DefaultFileType = new DefaultFileType();
				$data["CompanyId"]=$id;
				$data["countryData"]=$Country->getCountry();
				$data["company"]=$Company->getCompanyById($id);
				$data["groupList"] = $MatterGroup->getMatterGroup();
				$data["file_type"] = $DefaultFileType->defaultfiletype();
				$data['categoryList'] = $LegalCategory->getLegalCategory();
				return view('Litigation::Companylocation.add',$data);
			}
		}catch(\Exception $e){
			Log::error('CompanylocationController-add: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function loadState(Request $request){
		try{
			if(!empty($request->id)){
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
				$CountryState=new CountryState();
				$data["StateData"]=$CountryState->getState($id);
				return view('Litigation::Companylocation.state',$data);	
			}else{
				return view('Litigation::Companylocation.state');
			}
		}catch(\Exception $e){
			Log::error('CompanylocationController-loadState: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function loadcity(Request $request){
		try{
			if(!empty($request->id)){
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
				$City=new City();
				$data["CityData"]=$City->getCity($id);
				return view('Litigation::Companylocation.city',$data);
			}else{
				return view('Litigation::Companylocation.city');
			}
		}catch(\Exception $e){
			Log::error('CompanylocationController-loadcity: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function locationsubmit(Request $request){
		try{
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				$company_id => 'numeric',
				'location_index' => 'required|unique:company_location,location_name,NULL,id,company_id,' . $company_id,
				'address' => 'required',
				'country' => 'required',
				'state' => 'required',
				'city' => 'required',
				'zipcode' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$CompanyLocation=new CompanyLocation();
			$cmpnyLocationArr=array();
			$cmpnyLocationArr["company_id"]=$company_id;
			$cmpnyLocationArr["location_name"]=$request->location_index;
			$cmpnyLocationArr["address"]=$request->address;
			$cmpnyLocationArr["country"]=Crypt::decrypt($request->country);
			$cmpnyLocationArr["city"]=Crypt::decrypt($request->city);
			$cmpnyLocationArr["state"]=Crypt::decrypt($request->state);
			$cmpnyLocationArr["pincode"]=$request->zipcode;
			$cmpnyLocationArr["sgst"]=$request->sgst;
			$cmpnyLocationArr["status"]=1;
			
			$CompanyLocation->addCompanyLocation($cmpnyLocationArr);
			
			$response=array();
			$response["status"]=1;
			$cmpnyDirArr=array();
			$response["msg"]="Company Location Added Successfully";
			return response()->json($response);// response as json
		}catch(\Exception $e){
			Log::error('CompanylocationController-locationsubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function locationlistshow(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}
			$data=array();
			$CompanyLocation=new CompanyLocation();
			$data["id"]=$id;
			$data["locList"]=$CompanyLocation->listLocationOfCompany($id);
			return view('Litigation::Companylocation.locationlistshow',$data);
		}catch(\Exception $e){
			Log::error('CompanylocationController-locationlistshow: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}


}

