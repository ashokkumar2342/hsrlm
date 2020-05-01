<?php 
namespace App\Modules\Litigation\Controllers\SecureDevice;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Modules\Login\Models\AppUserAuth;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Login\Models\LoginLog;
use Auth;
use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Hash;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use Validator;				// log for exception handling
class SecureDeviceController extends Controller
{
	public function index(Request $request){
		try{
			$LoginLog=new LoginLog();
			$AppUsers=new AppUsers(); 
			$count=count($LoginLog->ActivitySummery(getUserId())); 
			$login_array=$this->loginlog($request);
			$device_count=$LoginLog->DeviceSummery($login_array); 
			$u_detail=$AppUsers->getdetailbyuserid(getUserId()); 
			$max_attempt=3;
			if($u_detail->device_level==2)
			{
				if($u_detail->mfa_attempt=='' || $u_detail->mfa_attempt<$max_attempt){
					$MailHelper= new MailHelper();
					$MailHelper->securedevicealways(getUserId());
				}
				return view('Litigation::SecureDevice.index',array('data'=>$u_detail));
			}
			elseif($u_detail->device_level==3)
			{
				$request->session()->put('userData.secure_device',1);
				$this->loginlogsubmit($request);
				return redirect('/dashboard');
			}
			elseif($count==0)
			{
				$request->session()->put('userData.secure_device',1);
				$this->loginlogsubmit($request);
				$up_u=array();
				$AppUsers=new AppUsers();
				if($u_detail->device_cookie!='')
				{
					$up_u['device_cookie'] = $u_detail->device_cookie;
				}
				else
				{
					$up_u['device_cookie'] = md5(time());
				}
				
				Cookie::forever('device_cookie', $up_u['device_cookie'],time() + 10080);
				$AppUsers->updateuserdetail($up_u,getUserId());
				return redirect('/dashboard');
			}
			elseif($device_count>0 && $request->cookie('device_cookie')==$u_detail->device_cookie && $u_detail->device_cookie!='')
			{
				$request->session()->put('userData.secure_device',1);
				$this->loginlogsubmit($request);
				return redirect('/dashboard');
			}
			else
			{ 
				if($u_detail->mfa_attempt=='' || $u_detail->mfa_attempt<$max_attempt){
					$MailHelper= new MailHelper();
					$MailHelper->securedevice(getUserId(),$login_array);
				}
				return view('Litigation::SecureDevice.index',array('data'=>$u_detail));
			}	
		}catch(\Exception $e){
			Log::error('SecureDeviceController-index: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function validatedevice(Request $request){ 
		try{
			$rules=[
				'otp'=> 'required|min:6',	
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$user=AppUsers::where('user_id',getUserId())->first();
			$otp=$user->device_token;
			if ($request->otp != $otp) {
				$response=['status'=>0,'msg'=>'Otp is incorrect'];
				return response()->json($response);
			}else{
				$up_u=array();
				$AppUsers=new AppUsers();
				$up_u['device_token'] = '';
				$up_u['mfa_attempt'] = 0;
				if($user->device_cookie!='')
				{
					$up_u['device_cookie'] = $user->device_cookie;
				}
				else
				{
					$up_u['device_cookie'] = md5(time());
				}
				$AppUsers->updateuserdetail($up_u,getUserId());
				$request->session()->put('userData.secure_device',1);
				$this->loginlogsubmit($request);
				$response=['status'=>1,'msg'=>'Device Set Successfully'];
				return response()->json($response)->withCookie(Cookie::forever('device_cookie', $up_u['device_cookie'],time() + 10080));
			}

		}catch(\Exception $e){
			Log::error('SecureDeviceController-validatedevice: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function loginlog(Request $request){
		try{
			$agent = new Agent(); 
			$browser = $agent->browser();
			$platform = $agent->platform();
			$device = $agent->device();
			$ins=array(); 
			$ip =$this->getIp();
			$ipinfoAPI="https://pro.ip-api.com/json/$ip?key=IKe2Ubk6uK5jIhN";
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
			$ins["user_id"]=getUserId();
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
			$ins["platform_name"]=$platform; 
			$ins["platform_version"]=$agent->version($platform); 
			$ins["status"]=1; 
			return $ins; 
		}catch(\Exception $e){
			Log::error('SecureDeviceController-loginlog: '.$e->getMessage()); 				// making log in file
			return view('error.home');
		}
	}

	public function getIp(){
		try{
			$ip = $_SERVER['REMOTE_ADDR'];
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
				foreach ($matches[0] AS $xip) {
					if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
						$ip = $xip;
						break;
					}
				}
			} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
				$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
			} elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
				$ip = $_SERVER['HTTP_X_REAL_IP'];
			}
			return $ip;
		}catch(\Exception $e){
			Log::error('SecureDeviceController-getIp: '.$e->getMessage()); 				// making log in file
			return view('error.home');
		}
	}

	public function loginlogsubmit(Request $request){
		try{
			$agent = new Agent(); 
			$browser = $agent->browser();
			$platform = $agent->platform();
			$device = $agent->device();
			$LoginLog = new LoginLog(); 
			$ins=array(); 
			$ip =$this->getIp();
			$ipinfoAPI="https://pro.ip-api.com/json/$ip?key=IKe2Ubk6uK5jIhN";
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
			$ins["user_ses_id"]=getUserId();
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
			Log::error('SecureDeviceController-loginlogsubmit Error: '.$e->getMessage()); 				// making log in file
			return view('error.home');
		}
	}

	public function newPasswordReset(Request $request){  
		try{
			return view('Login::newpasswordreset'); 
		}catch(\Exception $e){
	        Log::error('SecureDeviceController-newPasswordReset: '.$e->getMessage());        // making log in file
	        return view('error.home');
	    }
	}


	public function storeNewPassword(Request $request){ 
		try{
			$rules=[
				'oldpassword'=> 'required',
				'password'=> 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
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
			$user_id=getUserId();			 	
			$AppUser =AppUserAuth::where('user_id',$user_id)->first();	
			if(password_verify($request->oldpassword,$AppUser->password)){
				if ($request->oldpassword == $request->password) {
					$response=['status'=>0,'msg'=>"Old and new passwords can't be same"];
					return response()->json($response);
				}else{
					$userarray=array();
					$userarray["password"]=Hash::make($request->password); 
					$AppUser->updateuserpassword($userarray,$user_id);
					$response=['status'=>1,'msg'=>'Password changed successfully'];

					$MailHelper= new MailHelper();
					$MailHelper->resetnotification($user_id);
					
					 return response()->json($response);// response as json
					}

				}else{				 
					$response=['status'=>0,'msg'=>'Old password is not correct'];
				return response()->json($response);// response as json
			}		 
			
		}catch(\Exception $e){
			Log::error('SecureDeviceController-storeNewPassword: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	} 
}