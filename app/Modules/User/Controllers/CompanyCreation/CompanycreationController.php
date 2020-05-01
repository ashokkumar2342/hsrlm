<?php 
namespace App\Modules\Litigation\Controllers\CompanyCreation;
use App\Http\Controllers\Controller;				// controller lib
use App\Modules\Litigation\Models\Company; 			// model of company table
use App\Modules\Litigation\Models\Country; 			// model of company table
use App\Modules\Litigation\Models\CompanyUser; 			// model of company table
use App\Modules\Login\Models\AppUsers; 				// model of company Director table
use App\Modules\Login\Models\AppUserType; 				// model of company Director table
use Illuminate\Http\Request;						// to handle the request data
use Auth;
use usersSessionHelper;
use Validator;
use App\Helpers\MailHelper;
use Illuminate\Support\Facades\Log;		
use Illuminate\Support\Facades\Crypt;
class CompanycreationController extends Controller
{
	 public function __construct(){
		 $this->middleware('superAdmin');
	}

	public function index(Request $request){
		try{
			$Country = new Country();
			$data['countryList'] = $Country->getCountry();
			return view('Litigation::CompanyCreation.view',$data);
		}catch(\Exception $e){
			Log::error('CompanycreationController-index: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function addsubmit(Request $request){
		try{
			$rules=[
				'name' => 'required|unique:company',
				'short_name' => 'required',
				'country_name' => 'required',
				'currency' => 'required',
				'logo_url' => 'max:200|mimes:png,jpg,jpeg',//max 10000kb,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$Company=new Company();
			$cmpnyArr=array();
			$cmpnyArr["name"]=$request->name;
			$cmpnyArr["short_name"]=$request->short_name;
			$cmpnyArr["country_id"]=implode(',',decrypt_array($request->country_name));
			$cmpnyArr['currency_code'] = $request->currency;
			$cmpnyArr["company_id"]=generateId();
			$cmpnyArr["group_id"]=getGroupId();
			if($file = $request->file('logo_url')){
				$file_name = $cmpnyArr["company_id"].'/'.time().'--'.$file->getClientOriginalName();
				$cmpnyArr["logo_url"] = $file->storeAs('company_logo', $file_name);
			}
			$cmpnyArr["status"]=1;
			$Company->addCompany($cmpnyArr);
			$response=array();
			$response["status"]=1;
			$response["msg"]="Company Added Successfully";
			return response()->json($response);// response as json
		}catch(\Exception $e){
			Log::error('CompanycreationController-addsubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}


	public function adminlist(Request $request){
		try{
			$group_id=getGroupId();
			$Company=new Company();
			$AppUsers = new AppUsers();
			$data=array();
			$data['list']=$Company->companylist($group_id);
			$data['userList'] = $AppUsers->getAdmins();
			return view('Litigation::CompanyCreation.companylist',$data);
		}catch(\Exception $e){
			Log::error('CompanycreationController-adminlist: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function addadmin(Request $request){
		try{
			$rules=[
			'admin_email' => 'required|email',
			'admin_name' => 'required',
			'admin_mobile' => 'required|digits_between:10,12',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}else{
				$AppUsers=new AppUsers();
				$userData=$AppUsers->checkUserExist($request->admin_email);
				$this->userId="";
				$error="";
				if(isset($userData)){
					if($userData["user_type"]==1){
						$this->userId=$userData["user_id"];
					}else{
						$error="User is an Internal User";
						$response=array();
						$response["status"]=0;
						$response["msg"]=$error;
						return response()->json($response);
					}
				}else{
					$userArr=array();
					$this->userId=$userArr["user_id"]=generateId();
					$userArr["name"]=$request->admin_name;
					$userArr["email"]=$request->admin_email;
					$userArr["mobile"]=$request->admin_mobile;
					$userArr["status"]=0;
					$AppUsers->addUser($userArr);
					$this->addUserType($request);
					
					$user_detail=$AppUsers->getdetailbyuserid($this->userId);
					if($user_detail['status']==0 && $user_detail['activation_mail']!=1)
					{
						$upa_user=array();
						$upa_user['activation_mail']=1;
						$MailHelper=new MailHelper();
						$MailHelper->activationmail($this->userId);
						$AppUsers->updateuserdetail($upa_user,$this->userId);
					}

					$response=array();
					$response["status"]=1;
					$cmpnyDirArr=array();
					$response["msg"]="Admin added successfully";
					return response()->json($response);// response as json	
				}
			}	
		}catch(\Exception $e){
			Log::error('CompanycreationController-addadmin: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function assignadmin(Request $request){
		try{
			$rules=[
			'company_name' => 'required',
			'admin_name' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}else{
				$cmpArr = decrypt_array($request->company_name);
				$id = Crypt::decrypt($request->admin_name);
				if(ctype_digit(join('',$cmpArr)) == false){
					$response=array();
					$response["status"]=0;
					$response["msg"]='The company id must be a number!';
					return response()->json($response);// response as json
				}
				if(ctype_digit(join('',(array)$id)) == false){
					$response=array();
					$response["status"]=0;
					$response["msg"]='The admin id must be a number!';
					return response()->json($response);// response as json
				}
					
				$cmpnyUserArr=$updArr=array();
				$CompanyUser=new CompanyUser();
				$AppUserType = new AppUserType();
				for($i=0;$i<count($cmpArr);$i++){
					$cmpnyUserArr["company_id"]=$cmpArr[$i];
					$cmpnyUserArr["user_id"]=$id;
					$cmpnyUserArr["status"]=1;
					$count = $AppUserType->getNullCompnayRaw($id);
					$CompanyUser->addCompanyUser($cmpnyUserArr);
					if($i==0 && $count>0){
						$updArr['company_id'] = $cmpArr[$i];
						$AppUserType->updateUserType($id,$updArr);
					}else{
						$updArr["user_id"]=$id;
						$updArr["user_type"]=2;
						$updArr['company_id'] = $cmpArr[$i];
						$updArr["product"]="Litigation";
						$updArr["status"]=1;
						$AppUserType->addUserType($updArr);
					}
				}


				$response=array();
				$response["status"]=1;
				$cmpnyDirArr=array();
				$response["msg"]="Admin assign successfully";
				return response()->json($response);// response as json
			}	
		}catch(\Exception $e){
			Log::error('CompanycreationController-assignadmin: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	private function addUserType($request){
		try{
			$userTypeArr=array();
			$AppUserType=new AppUserType();
			$userTypeArr["user_id"]=$this->userId;
			$userTypeArr["user_type"]=2;
			$userTypeArr["product"]="Litigation";
			$userTypeArr["status"]=1;
			$AppUserType->addUserType($userTypeArr);
			return true;
		}catch(\Exception $e){
			Log::error('CompanycreationController-addUserType: '.$e->getMessage()); 		// making log in file
			return view('error.home');				// throw the err
		}
	}

	public function refreshcontent(Request $request){
		try{
			$Company=new Company();
			$group_id = getGroupId();
			$data=array();
			$data['list']=$Company->listCompanyUnderGroup($group_id);
			return view('Litigation::CompanyCreation.refereshlist',$data);
		}catch(\Exception $e){
			Log::error('CompanycreationController-refreshcontent: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function companyedit(Request $request){
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
			$group_id=getGroupId();
			$data = array();
			$Company = new Company();
			$Country = new Country();
			$data['companyId'] = $id;
			$data['countryList'] = $Country->getCountry();
			$data['companyDetail'] = $Company->getDetail($id);
			return view('Litigation::CompanyCreation.viewcompany',$data);
		}catch(\Exception $e){
			Log::error('CompanycreationController-companyedit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function updatecompany(Request $request){
		try{
			$id = Crypt::decrypt($request->company_id);
			$rules=[
				'name' => 'required|unique:company,name,'.$id.',company_id',
				'short_name' => 'required',
				'country_name' => 'required',
				'currency' => 'required',
				'logo_url' => 'max:200|mimes:png,jpg,jpeg',//max 10000kb,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			if(ctype_digit(join('',(array)$id)) == false){
				$response=array();
				$response["status"]=0;
				$response["msg"]='The company id must be a number!';
				return response()->json($response);// response as json
			}
			$updArr=array();
			$Company=new Company();
			$updArr["name"]=$request->name;
			$updArr["short_name"]=$request->short_name;
			$updArr["country_id"]=implode(',',decrypt_array($request->country_name));
			$updArr["currency_code"]=$request->currency;
			if($file = $request->file('logo_url')){
				$file_name = $id.'/'.time().'--'.$file->getClientOriginalName();
				$updArr["logo_url"] = $file->storeAs('company_logo', $file_name);
			}
			$Company->updateCompany($updArr,$id);
			$response=array();
			$response["status"]=1;
			$response["msg"]="Company Updated Successfully";
			return response()->json($response);// response as json
		}catch(\Exception $e){
			Log::error('CompanycreationController-updatecompany: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function getcurrency(Request $request){
		try{
			if(!empty($request->company_id_array)){
				$id_arr = decrypt_array(explode(',',$request->company_id_array));
				$type = Crypt::decrypt($request->type);
				$data = $arr = array();
				$arr['idArr'] = $id_arr;
				if($type != 1){
					$Company = new Company();
					$data['currency_code'] = $Company->getDetail($type)->currency_code;
				}else{
					$data['currency_code'] = '';
				}
				$Country = new Country();
				$data['list'] = $Country->getResult($arr,3);
				return view('Litigation::CompanyCreation.currency',$data);	
			}else{
				return view('Litigation::CompanyCreation.currency',$data);	
			}
		}catch(\Exception $e){
			Log::error('CompanycreationController-getcurrency: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}
	
	
}

