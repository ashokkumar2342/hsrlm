<?php 
namespace App\Modules\Litigation\Controllers\UserMasters;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\DefaultRole;
use App\Modules\Litigation\Models\SupportUserCompany;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\DefaultMenu;
use App\Modules\Litigation\Models\DefaultSubMenu;
use App\Modules\Litigation\Models\CompanyLocation;
use App\Modules\Litigation\Models\CompanyDepartment;
use App\Modules\Litigation\Models\Permission;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use App\Modules\Login\Models\AppUserType;
use App\Modules\Litigation\Models\AppUserProfile;
use App\Modules\Login\Models\AppUsers;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
class UserMasterController extends Controller
{
	public function __construct(){
		$this->middleware('admin');
	}
	
	public function index(Request $request){
		try{
			$data=array();

			$UserRole = new UserRole();
			$data['rolelist'] = $UserRole->getDetail();

			$Company = new Company();
			$data["companyList"] = $Company->listCompany();

			$DefaultRole = new DefaultRole();
			$data['roleTypeList'] = $DefaultRole->getRole();

			$AppUsers = new AppUsers();
			$CompanyUser = new CompanyUser();
			$arrayCompanyId=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$data['userList'] =$AppUsers->getAllCompanyUsers($arrayCompanyId);

			return view('Litigation::UserMasters.view',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-index: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}	

	//Role Master Functions Start Here

	public function addrole($company_id,$parent_id){
		try{
			$userRole = new UserRole();
			$id =  Crypt::decrypt($parent_id);
			$userRole = $userRole->getRoleById($id);
			$data=array();			 
			$data['company_id']=$company_id;
			$data['parent_id']=$parent_id; 
			$data['userRole']=$userRole; 
			return view('Litigation::UserMasters.Role.addrole',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-addrole: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function rolepopup(Request $request){
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
			$UserRole = new UserRole();
			$AppUserRole = new AppUserRole();
			$data['result'] = $AppUserRole->getRoleById($id);
			$data['roleList'] = $UserRole->getRoleAllByCompanyId($data['result']->company_id);
			return view('Litigation::UserMasters.Role.rolepopup',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-rolepopup: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function roletablerefresh(Request $request){
		try {
			$user_id = Crypt::decrypt($request->user_id); 
			$rules=[
				$user_id => 'numeric',
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
			$data['id'] = $arr['user_id'] = $user_id;

			$AppUsers = new AppUsers();
			$AppUserType = new AppUserType();
			$data['userType'] =  $AppUserType->getResult($arr,'getUserType');
			if($data['userType']->user_type == 3){
				$data['user'] = $AppUsers->getFullUsersDetailById($user_id);
			}else{
				$data['user'] = $AppUsers->getFullUsersDetailById($user_id,$data['userType']->user_type);
			}
			
			return view('Litigation::UserMasters.Role.role_table',$data);
			
		} catch (Exception $e) {
			Log::error('UserMasterController-roletablerefresh: '.$e->getMessage()); 		
			return view('error.home');  
		}
	}

	public function updateuserrole(Request $request){
		try {
			$id = Crypt::decrypt($request->role_id); 
			$rules=[
				$id => 'numeric',
				'role_name' => 'required',
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
			$AppUserRole = new AppUserRole();

			$updArr['user_role_id'] = Crypt::decrypt($request->role_name);
			$AppUserRole->updateById($updArr,$id);

			$response['status'] = 1; 
		 	$response['msg'] = "Role Successfully Updated";  
		 	return response()->json($response);
		} catch (Exception $e) {
			Log::error('UserMasterController-updateuserrole: '.$e->getMessage()); 		
			return view('error.home');  
		}
	}

	//Role tree show

	public function showTreeRole(Request $request){
		try{
			if(!empty($request->id)){
				$company_id = Crypt::decrypt($request->id);
				$data=array();
				$userRole = new UserRole();
				$userRoles = $userRole->getRoleByCompanyId($company_id);
				$data['userRoles']=$userRoles;
				$data['company_id']=$company_id;
				return view('Litigation::UserMasters.Role.role_tree',$data);	
			}else{
				return view('Litigation::UserMasters.Role.role_tree');
			}
		}catch(\Exception $e){
			Log::error('UserMasterController-showTreeRole: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function refviewrolelist(Request $request){
		try{
			$data=array();
			$UserRole = new UserRole();
			$data['roleList']=$UserRole->getDetail();
			return view('Litigation::UserMasters.Role.refview',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-refviewrolelist: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function submitrole(Request $request){
		try{  
			$company_id=Crypt::decrypt($request->company_id); 
			$parent_id=Crypt::decrypt($request->parent_id); 
			$rules=[
				'company_id' => 'required', 
				'parent_id' => 'required', 
				'role_name' => 'required|string|unique:user_role,name,NULL,id,company_id,'.$company_id,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			
			$response=$insArr=array();
			$UserRole = new UserRole(); 
			$insArr['company_id'] = $company_id;
			$insArr['parent_id'] = $parent_id;
			$insArr['name'] = $request->role_name; 
			$UserRole->insert($insArr);
			$response["status"]=1;
			$response["msg"]="Role added Successfully";
		    return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('UserMasterController-submitrole: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function editrole(Request $request){
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
			}
			$data=array();
			$UserRole = new UserRole();
			$role=$UserRole->getRoleById($id); 
			$companyRoles = $UserRole->getRoleAllByCompanyId($role->company_id);
			$data['list']=$role; 
		    $data['roleList']=$companyRoles; 
			
			return view('Litigation::UserMasters.Role.editrole',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-editrole: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function updaterole(Request $request){
		try{
			$id=Crypt::decrypt($request->role_id);
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				'role_name' => 'required|string|unique:user_role,name,'.$id.',id,company_id,' . $company_id,
				'role_report' => 'required',
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
			$response=$updArr=array();
			$UserRole = new UserRole();
			$updArr['name'] = $request->role_name;
			$updArr['parent_id'] = Crypt::decrypt($request->role_report);
			$UserRole->updateRole($updArr,$id);
			$response["status"]=1;
			$response["msg"]="Role updated Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('UserMasterController-updaterole: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	//Role Assign Functions  

	public function assignRoleForm(Request $request){	
		try{
			$company_id=Crypt::decrypt($request->company_id); 
			$id=Crypt::decrypt($request->id); 
			$UserRole = new UserRole();
			$AppUsers = new AppUsers(); 
			$AppUserRole = new AppUserRole();
			$AppUserRoles =$AppUserRole->getUserByCompanyId($company_id);
			$users=$AppUsers->getAllCompanyUsers($company_id,'byCompanyId'); 
			$data=array();
			$data['users'] = $users; 
			$data['userRole']=$UserRole->getRoleById($id);
			$data['AppUserRoles']=$AppUserRoles->pluck('user_id')->toArray();
			return view('Litigation::UserMasters.Role.assign_role',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-assignRoleForm: '.$e->getMessage()); 		
			return view('error.home');
		}	 
	}
	//User Master Functions Start Here

	public function userlist(Request $request){	
		try { 
			$data = $arr = array();
			$AppUsers = new AppUsers();
			$CompanyUser = new CompanyUser();
			$arrayCompanyId=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$data['users'] = $AppUsers->getAllCompanyUsers($arr,'byCompanyId'); 
			return view('Litigation::UserMasters.User.view',$data);
		} catch (Exception $e) {
			Log::error('UserMasterController-userlist: '.$e->getMessage()); 		
			return view('error.home');									
		}	 
	}

	public function selectuserlist(Request $request){	
		try { 
			$data =array();
			$AppUsers = new AppUsers();
			$CompanyUser = new CompanyUser();
			$arrayCompanyId=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$data['allUserList'] = $AppUsers->getAllCompanyUsers($arrayCompanyId,'byCompanyId'); 
			return view('Litigation::UserMasters.User.selectuser',$data);
		} catch (Exception $e) {
			Log::error('UserMasterController-selectuserlist: '.$e->getMessage()); 		
			return view('error.home');									
		}	 
	}

	public function supportuserlist(Request $request){	
		try { 
			$data =array();
			$AppUsers = new AppUsers();
			$CompanyUser = new CompanyUser();
			$arrayCompanyId=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$data['users'] =$AppUsers->getAllCompanyUsers($arrayCompanyId,4); 
			return view('Litigation::UserMasters.User.supportuserlist',$data);
		} catch (Exception $e) {
			Log::error('UserMasterController-supportuserlist: '.$e->getMessage()); 		
			return view('error.home');									
		}	 
	}

	public function addUserFormShow(){
		try {
			$data=array();
			$CompanyUser = new CompanyUser();
			$data['companyList']=$CompanyUser->getCompanyByUser(getUserId());
			return view('Litigation::UserMasters.User.user_model_form',$data);		
		} catch (Exception $e) {
			Log::error('UserMasterController-addUserFormShow: '.$e->getMessage()); 		
			return view('error.home');									
			
		}		
	}

	public function roleSearch(Request $request){ 
		try {
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
				$UserRole = new UserRole(); 
				$data['roleList'] = $UserRole->getRoleByCompanyId($id); 
				return view('Litigation::UserMasters.User.role_selectbox',$data);
			}else{
				return view('Litigation::UserMasters.User.role_selectbox');
			}
		} catch (Exception $e) {
			Log::error('UserMasterController-roleSearch: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}

	public function refUserlist(){
		try {
			$user = new AppUsers();
			$CompanyUser = new CompanyUser();
			$arrayCompanyId=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$users =$user->getAllCompanyUsers($arrayCompanyId); 
			$data =array();
			$data['users'] =$users; 
			return view('Litigation::UserMasters.User.user_table',$data);
			
		} catch (Exception $e) {
			Log::error('UserMasterController-refUserlist: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}

	public function updateUserDetails(Request $request){
		try {
			$rules=[
				'name' => 'required|string',
			 	'mobile' => 'required|numeric|digits_between:10,12', 
				'emp_id' => 'nullable|Numeric',
			];
			if(isset($request->company_name)){
				$rules['company_name'] = 'required';
			}
			if(isset($request->dept_id)){
				$rules['dept_id'] = 'required';
			}
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
		 	    return response()->json($response);// response as json
		 	}
		 	//stor app user
		 	$AppUsers=new AppUsers();
		 	$userId =Crypt::decrypt($request->user_id);
		 	$ins['name']=$request->name;	 	   
		 	$ins['mobile']=$request->mobile; 
		 	$AppUsers->updateuserdetail($ins,$userId);
	 	    //end app user store

		 	$AppUserProfile = new AppUserProfile();
		 	$inrArr['designation'] = $request->designation; 
		 	$inrArr['emp_id'] = $request->emp_id; 
		 	if(isset($request->company_name)){
		 		$inrArr['dept_id'] = Crypt::decrypt($request->dept_id); 
				$inrArr['company_id'] = Crypt::decrypt($request->company_name); 
				$inrArr['user_id'] = $userId; 
				$inrArr['created_by'] = getUserId(); 
			 	$AppUserProfile->createOrUpdateProfile($userId,$inrArr);	
		 	}else{
		 		$inrArr['updated_by'] = getUserId(); 
		 		$AppUserProfile->updateUser($userId,$inrArr);
		 	}

		 	$response['status'] = 1; 
		 	$response['msg'] = "User Update Successful";  
		 	return response()->json($response);
		}catch (Exception $e) {
		 	Log::error('UserMasterController-updateUserDetails: '.$e->getMessage()); 		
		 	return view('error.home');									
		}		
	}

	public function editUserDetails(Request $request){
		try {
			$user_id = Crypt::decrypt($request->id); 
			$rules=[
				$user_id => 'numeric',
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
			$data['id'] = $arr['user_id'] = $user_id;

			$Company = new Company();
			$data["companyList"] = $Company->listCompany();

			$AppUsers = new AppUsers();
			$AppUserType = new AppUserType();
			$data['userType'] =  $AppUserType->getResult($arr,'getUserType');
			if($data['userType']->user_type == 3){
				$data['user'] = $AppUsers->getFullUsersDetailById($user_id);
			}else{
				$data['user'] = $AppUsers->getFullUsersDetailById($user_id,$data['userType']->user_type);
			}
			$AppUserProfile = new AppUserProfile();
			$data['profile'] =  $AppUserProfile->getdetailbyuserid($user_id);
			
			return view('Litigation::UserMasters.User.user_edit',$data);
			
		} catch (Exception $e) {
			Log::error('UserMasterController-editUserDetails: '.$e->getMessage()); 		
			return view('error.home');  
		}
	}

	public function roleSearchEditUser(Request $request){ 
		try {
			$company_id =explode(',',$request->company_id);
		    $company_id =decrypt_array($company_id);		   
		    $user_id =Crypt::decrypt($request->user_id);		   
			$role = new UserRole(); 
			$appUserRole = new AppUserRole(); 
			$companyRoles = $role->getRoleByCompanyIdArray($company_id);
			$appUserRoles = $appUserRole->getAppUserRoleByCompanyIdUserId($company_id,$user_id); 
			$data=array();	
			$data['companyRoles']=$companyRoles;
			$data['appUserRoles']=$appUserRoles;
			return view('Litigation::UserMasters.User.role_selectbox',$data);
		} catch (Exception $e) {
			Log::error('UserMasterController-roleSearchEditUser: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}

	public function storeUserDetails(Request $request){
		try {
		 	$rules=[
			 	'name' => 'required|string',
			 	'email' => 'required|email|unique:app_user',
			 	'mobile' => 'required|numeric|digits_between:10,12	',
			 	'user_type' => 'required',
			 	'company_name' => 'required', 
				'emp_id' => 'nullable|Numeric',
		 	]; 

		 	if(isset($request->dept_id)){
		 		$rules['dept_id'] = 'required'; 
		 	}
		 	if(isset($request->location_id)){
		 		$rules['location_id'] = 'required'; 
		 	}

		 	$validator = Validator::make($request->all(),$rules);
		 	if ($validator->fails()) {
		 	    $errors = $validator->errors()->all();
		 	    $response=array();
		 	    $response["status"]=0;
		 	    $response["msg"]=$errors[0];
		 	    return response()->json($response);// response as json
		 	}
		 	//stor app user
	 	    if($this->insertUser($request->all()) == true){
				$response=array();
				$response["status"]=1;
				$response["msg"]="User Added Successfully";
				return response()->json($response);
			};		
		} catch (Exception $e) {
			Log::error('UserMasterController-storeUserDetails: '.$e->getMessage()); 		
			return view('error.home');									
			
		}		
	}

	public function blukUploadSubmit(Request $request){		
		try{
			$rules=[
				'company_name' => 'required',
				'dept_id' => 'required',
				'file' =>  'required|mimes:xlsx',
			];

			$validator = Validator::make($request->all(),$rules);
			if($validator->fails()){
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
                return response()->json($response);// response as json
            }
            $cid=Crypt::decrypt($request->company_name);
			$responseError = $errorArr = $customArr = array();
			$files = $request->file('file'); 
			$files->store('user_detail');
			$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
			$spreadsheet = $reader->load($request->file('file')->getRealPath());
			$sheetData = $spreadsheet->getActiveSheet()->toArray(); 
			$notImportedData=array();
			$count_all=$count_ins=0;
			$header_array=array("Name","Email","Mobile","Designation","Employee Id");
			$header_array = array_map('strtoupper', $header_array);
			$AppUsers = new AppUsers();
			$AppUserProfile = new AppUserProfile();
			$userList = implode(',',$AppUsers->getList()->pluck('email')->toarray());
			foreach($sheetData as $va=>$key){
				if($va == 0){
					foreach($key as $hc){ 
						if(!in_array(trim(strtoupper($hc)), $header_array) && $hc!=''){
							$response=array();
							$response["status"]=0;
							$response["msg"]="Sheet is invalid";
                         	return response()->json($response);// response as json
                         }
                     }
                     continue;
                }else{
                 	$count_all++; 
                 	$error="";
                 	$rules=[
                 		'0' => 'required',
                 		'1' => 'required|email|not_in:'.$userList,
                 		'2' => 'required|digits_between:10,12',
                 		'3' => 'nullable',
                 		'4' => 'nullable|numeric',
                 	];
                 	$messages = array();
					foreach ($header_array as $kk => $kkvalue) {
						$mgva = $va+1;
						$messages[$kk.".required"] = $kkvalue." required in line ".$mgva;
						$messages[$kk.".email"] = $kkvalue." must be in correct format in line ".$mgva;
						$messages[$kk.".not_in"] = $kkvalue." ID is already exist in line ".$mgva;
						$messages[$kk.".digits_between"] = $kkvalue." must be between 10 to 12 in line ".$mgva;
						$messages[$kk.".numeric"] = $kkvalue." must be numeric in line ".$mgva;
					}
                 	$validator = Validator::make($key,$rules,$messages);
                    if($validator->fails()){
                    	$errors = $validator->errors()->all(); 
                    	$responseError[$va]=$errors;
                    }
                }
            }
            if ($responseError!=null) {
	            $response['msg']= view('Litigation::ExcelErrors.view',compact('responseError'))->render();
	            $response["status"]=0;
	            return response()->json($response);// response as json
            }else{
            	foreach($sheetData as $va=>$key){
					if($va == 0){
						foreach($key as $hc){ 
							if(!in_array(trim(strtoupper($hc)), $header_array) && $hc!=''){
								$response=array();
								$response["status"]=0;
								$response["msg"]="Sheet is invalid";
	                         	return response()->json($response);// response as json
	                         }
	                     }
	                     continue;
	                }else{
						$ins['name'] =$key[0];
						$ins['email'] =$key[1];
						$ins['mobile'] =$key[2];
						$ins['emp_id'] = $key[4];
						$ins['designation'] =$key[3];
						$ins['dept_id'] = $request->dept_id;
						$ins['company_name'] = $request->company_name;
						if($this->insertUser($ins)==true){
							$count_ins++;
						}
	                }
	 			}
            }
            $response=array();
            $response["status"]=1;
            $response["msg"]= "User Detail Inserted = ".$count_ins.' Out Of = '.$count_all;
            return response()->json($response);// response as json 
        }catch(\Exception $e){
			Log::error('UserMasterController-blukUploadSubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	private function insertUser($request){
		try{
			$error = '';
			$inrArr = $userArr = array();
			$AppUsers=new AppUsers();
			$AppUserProfile = new AppUserProfile();
			$this->userId = $userArr["user_id"] = $inrArr['user_id'] = $supportArr['user_id'] = generateId();
			$userArr["name"]=$request['name'];
			$userArr["email"]=$request['email'];
			$userArr["mobile"]=$request['mobile'];
			$inrArr['designation'] = $request['designation']; 
			$inrArr['dept_id'] = isset($request['dept_id'])?Crypt::decrypt($request['dept_id']):NULL; 
			$inrArr['created_by'] = getUserId(); 
			if(!is_array($request['company_name'])){
				$inrArr['company_id'] = Crypt::decrypt($request['company_name']); 
			}else{
				$SupportUserCompany = new SupportUserCompany();
				$supportArr['company_id'] = implode(',',decrypt_array($request['company_name']));
				$SupportUserCompany->insArr($supportArr);
			}
			$inrArr['emp_id'] = $request['emp_id']; 
			$userArr["status"]=0;

			$AppUsers->addUser($userArr);
			$this->addUserType($request);
			$AppUserProfile->addUser($inrArr); 
			$user_detail=$AppUsers->getdetailbyuserid($userArr["user_id"]);
			$user_type = isset($request['user_type'])?Crypt::decrypt($request['user_type']):3;

			if($user_detail['status']==0 && $user_detail['activation_mail']!=1 && $user_type == 4){
				$upa_user=array();
				$upa_user['activation_mail']=1;
				$MailHelper=new MailHelper();
				$MailHelper->activationmail($user_detail->user_id);
				$AppUsers->updateuserdetail($upa_user,$user_detail->user_id);
			}
			
			return true;
		}catch(\Exception $e){
			Log::error('UserMasterController-insertUser: '.$e->getMessage()); 		// making log in file
			return view('error.home');					// throw the err
		}
	}

	private function addUserType($request){
		try{
			$userTypeArr=array();
			$userTypeArr["user_id"]=$this->userId;
			$userTypeArr["user_type"]=isset($request['user_type'])?Crypt::decrypt($request['user_type']):3;
			$userTypeArr["location"]=isset($request['location_id'])?Crypt::decrypt($request['location_id']):NULL;
			if(!is_array($request['company_name'])){
				$userTypeArr['company_id'] = Crypt::decrypt($request['company_name']); 
			}
			$userTypeArr["product"]="Litigation";
			$userTypeArr["status"]=1;
			$AppUserType=new AppUserType();
			$AppUserType->addUserType($userTypeArr);
			return true;
		}catch(\Exception $e){
			Log::error('UserMasterController-addUserType: '.$e->getMessage()); 		// making log in file
			return view('error.home');					// throw the err
		}
	}

	public function assignRole(Request $request){
		try{
			$data=array();
			$AppUsers = new AppUsers();
			$CompanyUser = new CompanyUser();
			$arrayCompanyId=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$users =$AppUsers->getAllCompanyUsers($arrayCompanyId);
			return view('Litigation::UserMasters.Permission.view',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-assignRole: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}

	public function roleSubmit(Request $request){
		try {  
		 	$rules=[
			 	'user' => 'required',
			 	'company_id' => 'required',
			 	'role_id' => 'required',
		 	]; 
		 	$validator = Validator::make($request->all(),$rules);
		 	if ($validator->fails()) {
		 	    $errors = $validator->errors()->all();
		 	    $response=array();
		 	    $response["status"]=0;
		 	    $response["msg"]=$errors[0];
		 	    return response()->json($response);// response as json
		 	}
		 	$arr_users = decrypt_array($request->user);	
		 	$role_id = Crypt::decrypt($request->role_id);	
		 	$company_id = Crypt::decrypt($request->company_id);
		 	$AppUsers = new AppUsers();
		 	$AppUserRole = new AppUserRole();
		  		
		 	foreach ($arr_users as  $user_id) {
		 		$count = $AppUserRole->countRoleByUserId($user_id);
		 		if($count == 0){
		 			$user_detail=$AppUsers->getdetailbyuserid($user_id);
		 			if($user_detail['status']==0 && $user_detail['activation_mail']!=1){
						$upa_user=array();
						$upa_user['activation_mail']=1;
						$MailHelper=new MailHelper();
						$MailHelper->activationmail($user_detail->user_id);
						$AppUsers->updateuserdetail($upa_user,$user_detail->user_id);
					}
		 		}
		 		$ins['user_id'] = $user_id;
		 	   	$ins['user_role_id'] = $role_id;
		 	   	$ins['company_id'] = $company_id;
		 	   	$AppUserRole->createOrUpdateAppUserRole($ins,$role_id,$user_id);
		 	} 
		 	 
		 	$response=array();
			$response["status"]=1;
			$response["msg"]="Role Assign Successfully";
			return response()->json($response);
		} catch (Exception $e) {
			Log::error('UserMasterController-roleSubmit: '.$e->getMessage()); 		
			return view('error.home');									
			
		}		
	}

	public function statuschange(Request $request){		
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
			$updArr=$data=$response=array();
			$AppUsers = new AppUsers();
			$AppUserProfile = new AppUserProfile();
			$updArr['status'] = Crypt::decrypt($request->status);
			$AppUsers->updateuserdetail($updArr,$id);
			$CompanyUser = new CompanyUser();
			$arrayCompanyId=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$users =$AppUsers->getAllCompanyUsers($arrayCompanyId); 
			$response['data'] = view('Litigation::UserMasters.User.view',compact('users'))->render();
			$response["status"]=1;
			$response['msg']='Status Change Successfully!';
			return response()->json($response);	
		}catch(\Exception $e){
			Log::error('UserMasterController-statuschange: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function supportuserstatuschange(Request $request){		
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
			$updArr=$data=$response=array();
			$AppUsers = new AppUsers();
			$AppUserProfile = new AppUserProfile();
			$updArr['status'] = Crypt::decrypt($request->status);
			$AppUsers->updateuserdetail($updArr,$id);
			$CompanyUser = new CompanyUser();
			$arrayCompanyId=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$users =$AppUsers->getAllCompanyUsers($arrayCompanyId,4); 
			$response['data'] = view('Litigation::UserMasters.User.supportuserlist',compact('users'))->render();
			$response["status"]=1;
			$response['msg']='Status Change Successfully!';
			return response()->json($response);	
		}catch(\Exception $e){
			Log::error('UserMasterController-supportuserstatuschange: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function resend(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'Numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);
			}
			$AppUsers=new AppUsers();
			$response=array();
			$updArr=array();

			$search=$AppUsers->getdetailbyuserid($id);
			if($search->status!=0 && $search->activation_mail!=1)
			{
				$response=array();
				$response["status"]=0;
				$response["msg"]="Something Went wrong";
				return response()->json($response);
			}

			$MailHelper=new MailHelper();

			
			$updArr['status']=0;
			$updArr['activation_mail']=1;
			$MailHelper->activationmail($id);
			$val=$AppUsers->updateuserdetail($updArr,$id);

			$response["status"]=1;
			$response["msg"]="Activation mail send.";

			return response()->json($response);
			
		}catch(\Exception $e){
			Log::error('UserMasterController-resend: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function getField(Request $request){
		try{
			if(!empty($request->id)){	
				$id=Crypt::decrypt($request->id);
				$rules=[
					$id => 'Numeric',
				];
				$validator = Validator::make($request->all(),$rules);
				if ($validator->fails()) {
					$errors = $validator->errors()->all();
					$response=array();
					$response["status"]=0;
					$response["msg"]=$errors[0];
					return response()->json($response);
				}

				$data['type'] = $id;
				$Company = new Company();
				$data["companyList"] = $Company->listCompany();
				
				return view('Litigation::UserMasters.User.company_dept_field',$data);
			}
		}catch(\Exception $e){
			Log::error('UserMasterController-getField: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	//User Master Functions End Here


	//Permission Function Start Here

	public function companyList(Request $request){
		try{
			$data=array();
			$CompanyUser = new CompanyUser();
			$data['companyList']=$CompanyUser->getCompanyByUser(getUserId());
			return view('Litigation::UserMasters.Permission.view',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-companyList: '.$e->getMessage()); 
			return view('error.home');
		}
	}

	public function roleMenuStore(Request $request){
		try{ 
			$rules=[
				'company' => 'required', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}  
			$company_id =Crypt::decrypt($request->company);   
			$UserRole = new UserRole();
			$Permission = new Permission();
			
			$roles = $UserRole->getRoleAllByCompanyId($company_id);
			foreach ($roles as $role) {
				$roleName='role'.$role->id;
				if (!empty($request->$roleName)) { 
					$updArr =array();
					$updArr['role_id'] = $role->id;
					$updArr['company_id'] = $company_id;
					$updArr['sub_menu_id'] = implode(',',decrypt_array($request->$roleName));
					$Permission->createOrUpdatePermission($updArr,$role->id);
				}
			}
			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;
		}catch(\Exception $e){
			Log::error('UserMasterController-roleMenuStore: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function roleMenu(Request $request){
		try{ 
			$company_id =Crypt::decrypt($request->id);
			$data=array(); 
			$role = new UserRole();
			$menu = new DefaultMenu();
			$subMenu = new DefaultSubMenu();			
		    $roles = $role->getRoleAllByCompanyId($company_id);		    
		    $subMenus = $subMenu->getSubMenu();
		    $menus = $menu->getMenu();
			$data['company_id']=$company_id;
			$data['roles']=$roles;
			$data['subMenus']=$subMenus;
			$data['menus']=$menus; 
			return view('Litigation::UserMasters.Permission.role_menu',$data);
		}catch(\Exception $e){
			Log::error('UserMasterController-roleMenu: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}		

	//Permission Function End Here


	//Department Function Start Here

	public function companyDept(Request $request){
		try{
			$data=array();
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
				$CompanyDepartment = new CompanyDepartment();
				$data['deptList']=$CompanyDepartment->getDepartmentlist($id);
				return view('Litigation::UserMasters.User.dept',$data);
			}else{
				return view('Litigation::UserMasters.User.dept');
			}
		}catch(\Exception $e){
			Log::error('UserMasterController-companyDept: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}

	public function companyLocation(Request $request){
		try{
			$data=array();
			if(empty($request->id)){
				return view('Litigation::UserMasters.User.location');
			}else{
				$ids = decrypt_array(explode(',',$request->company_id)); 
				$Company = new Company();
				$data['companyList']=$Company->getCompanyByArrId($ids);
				return view('Litigation::UserMasters.User.location',$data);
			}
		}catch(\Exception $e){
			Log::error('UserMasterController-companyLocation: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}

	public function companyDeptEdit(Request $request){
		try{
			$data=array();
			if(!empty($request->id)){
				$id = Crypt::decrypt($request->id); 
				$user_id = Crypt::decrypt($request->user_id); 
				$rules=[
					$id => 'numeric',
					$user_id => 'numeric',
				];
				$validator = Validator::make($request->all(),$rules);
				if ($validator->fails()) {
					$errors = $validator->errors()->all();
					$response=array();
					$response["status"]=0;
					$response["msg"]=$errors[0];
					return response()->json($response);// response as json
				}
				$data['type'] = 1;
				$CompanyDepartment = new CompanyDepartment();
				$data['deptList']=$CompanyDepartment->getDepartmentlist($id);
				$AppUserProfile = new AppUserProfile();
				$data['profile'] =  $AppUserProfile->getdetailbyuserid($user_id);
				return view('Litigation::UserMasters.User.dept',$data);
			}else{
				return view('Litigation::UserMasters.User.dept');
			}
		}catch(\Exception $e){
			Log::error('UserMasterController-companyDeptEdit: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}	

	//Department Function End Here

	public function roleDelete($id){ 
		try {   
		    $role_id =Crypt::decrypt($id);		   
			$role = new UserRole(); 
			$appUserRole = new AppUserRole(); 
			$appUserRoles = $appUserRole->checkRoleExits($role_id);
			$response=array();
			if (count($appUserRoles)==0) {
				$childRoles=parentTree($role_id);  
				if (empty($childRoles)) {
					$role->roleDeleteById($role_id);
					$response["status"]=1;
					$response["msg"]="Role Delete Successfully";
				}else{
					$response["status"]=0;
					$response["msg"]="Your attempt to delete the role  could not be completed.";
				}
				
			}else{
				$response["status"]=0;
				$response["msg"]="Your attempt to delete the role  could not be completed because users are currently assigned";

			}
			 
			
			 return $response;
		} catch (Exception $e) {
			Log::error('UserMasterController-roleSearchEditUser: '.$e->getMessage()); 		
			return view('error.home');									
			
		}
	}
}

