<?php 
namespace App\Modules\Login\Controllers;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\DefaultSubMenu;
use App\Modules\Litigation\Models\Permission;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserAuth;
use App\Modules\Login\Models\AppUserRole;
use App\Modules\Login\Models\AppUserType;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Login\Models\LoginLog;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Redirect;
use Validator;					// log for exception handling
class LoginController extends Controller
{
	public function index(){		
		try{
			if(Auth::check())
				return redirect('dashboard');
			return view('Login::login');
		}catch(\Exception $e){
			Log::error('LoginController-index: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function loginSubmit(Request $request){
		$rules=[
		'email' => 'required|email',
		'password' => 'required',
		];
		$this->validate($request,$rules);
		try{
			$AppUsers=new AppUsers();
			$userData=$AppUsers->ValidateUser($request->email);
			$error=""; 
			if(isset($userData)){ 
				if(password_verify($request->password,$userData["password"])){ 
					if($userData["status"]==0) 
						$error="User Inactive Contact Admin";
					if($userData["user_status"]==0)
						$error="User Auth Inactive Contact Admin";
				}else{
					$error="Invalid User or Password";
				}
			}else
				$error="Invalid User";
			if($error)
				return Redirect::back()->withErrors(array($error));
			else{  
				Auth::loginUsingId($userData["id"]); 
				//Assigning User type for admin and super admin ---->starts
				$AppUserType=new AppUserType();
				$userTypeData=$AppUserType->getUserType($userData["user_id"],"");
				$userType=$AppUserType->getResult($userData,"getUserType");
				$AdminCheck=$AppUserType->getUserAdminandSuperadminType($userData["user_id"],""); 

				$sesData=array();
				$sesData["user_id"]=$userData["user_id"];
				$sesData["email"]=$userData["email"];
				$sesData["name"]=$userData["name"]; 
				 if(count($AdminCheck)>0){//User type for admin and super admin
				 	$sesData["product"]=$AdminCheck[0]->product;
				 	$sesData["user_type"]=$AdminCheck[0]->user_type;
				 	$sesData["group_id"]=$AdminCheck[0]->group_id;
				 	$sesData["company_id"]=$AdminCheck[0]->group_id ;
				 }elseif($userType->user_type == 4){
				 	$sesData["user_type"]=$userType->user_type;
				 	$sesData["company_id"]=$userType->company_id;
				 	$sesData["set_company_id"]=$userType->company_id;
				 }else{
				 	$AppUserRole = new AppUserRole();  
				 	$permission = new Permission();  
				 	$subMenu = new DefaultSubMenu();  
				 	$companyIdArr =$AppUserRole->getCompanyIdArrayByUserId($userData["user_id"]);
				 	if(empty($companyIdArr)){
				 		$this->logoutlog();
				 		Auth::logout(false);
				 		$request->session()->flush();
				 		return Redirect::back()->withErrors(array('Role not assigned!'));
				 	}
				 	$roleIdArr =$AppUserRole->getRoleIdArrByCompanyIdUserId($companyIdArr[0],$userData["user_id"]);
				 	$subMenuId =$permission->getPermissionSubMenuId($companyIdArr[0],$roleIdArr[0]);
				 	if ($subMenuId!=null) {
				 		$subMenuId =explode(',', $subMenuId);
				 	}else{
				 		$this->logoutlog();
				 		Auth::logout(false);
				 		$request->session()->flush();
				 		return Redirect::back()->withErrors(array('No menu assigned'));
				 	}

				 	$arrUserROleId =parentTree($roleIdArr[0]);
				 	$arrUserId= $AppUserRole->getUserIdByArrUserRoleId($arrUserROleId); 
				 	$arrUserId[]=$userData["user_id"];
				 	$menuId =$subMenu->getMenuBySubMenuId($subMenuId);
				 	$sesData["user_type"]=$userTypeData[0]->user_type;
				 	$sesData["company_id"]=$companyIdArr;
				 	$sesData["set_company_id"]=$companyIdArr[0];
				 	$sesData["role_id"]=$roleIdArr;
				 	$sesData["set_role_id"]=$roleIdArr[0];
				 	$sesData["sub_menu_id"]=$subMenuId;
				 	$sesData["menu_id"]=$menuId;
				 	$sesData["child_user_id"]=$arrUserId;
				 } 
				initiate($sesData); 
				$up_u=array();
				$up_u['mfa_attempt'] = 0;
				$AppUsers->updateuserdetail($up_u,$userData["user_id"]);
				return redirect('/dashboard');
			}
		}catch(\Exception $e){
			Log::error('LoginController-loginSubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}

	}	
	//Forgot Password
	public function forgotpassword(Request $request){		
		$rules=[
		    'email' => 'required|email',         
		    ];
		$this->validate($request,$rules);
	    try{
	        $user= new AppUsers();
	        $users= $user->getdetailbyemail($request->email); 
	        if ($users){ 
	            $MailHelper = new MailHelper();
	            $MailHelper->forgetmail($request->email);
	            $msg="Check your email for a verification link"; 
	            return Redirect::Route('login')->with(['message'=>$msg]);
	        }else{
	            $error="Email Id Invalid";
	            return Redirect::Route('login')->withErrors(array($error));
	        }
	    }catch(\Exception $e){
	        Log::error('LoginController-forgotpassword: '.$e->getMessage());        // making log in file
	        return view('error.home');
		}

	}
	//New Password
	public function passwordreset(Request $request,$token){ 
			try{

				if(!is_string($token) || $token=='' || strlen($token)!=64)
				{
					return redirect('/login');
				}

			$AppUser=AppUsers::where('token',$token)->count();
			if ($AppUser>0){ 
				$data=array();
				$data['token']=$token;
				return view('Login::newpassword',$data);		 
			}else{
				return redirect('/login');
				// return Redirect('/');
			}
			
		}catch(\Exception $e){
			Log::error('LoginController-passwordreset: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}

	}
		//Store New Password
	public function storepassword(Request $request){ 
		try{
			$rules=[
			'token'=> 'required|size:64|string',
			'otp'=> 'required|min:6',	
			'password'=> 'required|min:6|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
			'passwordconfirmation'=> 'required|min:6|same:password|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
			 ];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
				$user=new AppUsers();
				$userDetails=$user->getdetailbyToken($request->token);
				
				$user_id=$userDetails->user_id;
				$otp=$userDetails->otp;
				if ($request->otp != $otp) {
					 $response=['status'=>0,'msg'=>'Oops..!!! OTP is incorrect, try again.'];
					 return response()->json($response);
				}else{
					$AppUserAuth = new AppUserAuth();
					$AppUsers = new AppUsers();
					$userarray=array();
					$userarray['password']=Hash::make($request->password);
					if($AppUserAuth->passwordcount($user_id)==0)
					{
						$userarray["user_id"]=$user_id;
						$userarray["status"]=1;
						$AppUserAuth->insAppAuth($userarray);
					}
					else
					{
						$AppUserAuth->updateuserpassword($userarray,$user_id);
					}

					$userarray=array();
					$userarray["token"]=null;
					$userarray["status"]=1;
					$userarray["otp"]=null;
					$AppUsers->updateuserdetail($userarray,$user_id);
					
					$response=['status'=>1,'msg'=>'Password Update Successfully'];
					return response()->json($response);
				}
				
		}catch(\Exception $e){
			Log::error('LoginController-storepassword: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}

	}

	public function logout(Request $request){
		try{
			$this->logoutlog();
			Auth::logout(false);
			$request->session()->flush();
			return redirect('/');
		}catch(\Exception $e){
			Log::error('LoginController-logout: '.$e->getMessage()); 				// making log in file
			return view('error.home');
		}
	}

	// store user information Login time time
	public function loginlog(Request $request){
		try{

		     $agent = new Agent(); 
		     $browser = $agent->browser();
		     $platform = $agent->platform();
		     $device = $agent->device();
		     $LoginLog = new LoginLog(); 
		  	  $ins=array(); 
		  	  $ip =$request->ip();
              $ipinfoAPI="http://ip-api.com/json/$ip";
              $json =file_get_contents($ipinfoAPI);
              $data= (array) json_decode($json); 
             if ( isset ( $data['city']) ) {
              $city= $data['city'];
               $ins["city"]=$city;
             }
             if ( isset( $data['country']) ) {
              $country= $data['country']; 
               $ins["country"]=$country;
             }

              $ins["json"]=json_encode($data); 
		  	  $ins["user_id"]=getUserId();
		  	  $ins["user_ses_id"]=Session::getId();
		  	  $ins["login_time"]=date('Y-m-d H:i:s');
		  	  $ins["ip"]=$ip; 
		  	  $ins["device_name"]=$device; 
		  	  if ($agent->isDesktop()) {
		  	    $ins["device_type"]='Desktop'; 
		  	  }elseif ($agent->isPhone()) {
		  	    $ins["device_type"]='Phone'; 
		  	  }elseif ($agent->isRobot()) {
		  	    $ins["device_type"]='Robot'; 
		  	  } else {
		  	    $ins["device_type"]='Other'; 
		  	  } 
		  	 
		  	  $ins["browser_name"]=$browser; 
		  	  $ins["browser_version"]=$agent->version($browser);; 
		  	  $ins["platform_name"]=$platform; 
		  	  $ins["platform_version"]=$agent->version($platform); 
		  	  $ins["status"]=1; 
		  	  $LoginLog->insLoginLog($ins); 
 
		}catch(\Exception $e){
			Log::error('LoginController-loginlog: '.$e->getMessage()); 				// making log in file
			return view('error.home');
		}
	}
	public function logoutlog(){
		try{ 
			$LogoutLog= new LoginLog();
			$LogoutLog = LoginLog::where('user_id',getUserId())->where('user_ses_id',Session::getId())->update(['logout_time'=>date('Y-m-d H:i:s')]);  
		}catch(\Exception $e){
			Log::error('LoginController-logoutlog: '.$e->getMessage()); 				// making log in file
			return view('error.home');
		}
	}

	 
}