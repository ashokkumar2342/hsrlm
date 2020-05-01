<?php

namespace App\Modules\Litigation\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Modules\Login\Models\AppUserAuth;
use App\Modules\Login\Models\AppUserType;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Login\Models\LoginLog;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Response;
class ProfileController extends Controller
{
    public function index(Request $request){	
    	try{ 
    		$users = Auth::user(); 
            $user_type = Session::get('userData.user_type');
            $LoginLog = new LoginLog();
            $AppUserAuth = new AppUserAuth();
            $AppUserType = new AppUserType();
            $user_type=$AppUserType->getUserTypenamebyid($user_type);
            $activity = $LoginLog->activity(getUserId()); 
            $ActivitySummery = $LoginLog->ActivitySummery(getUserId()); 
            $lastResetPassword = $AppUserAuth->lastpassword(getUserId());
    		return view('Litigation::Profile.profile',compact('users','activity','ActivitySummery','lastResetPassword','user_type'));	
    	}catch(\Exception $e){
    		Log::error('ProfileController-index: '.$e->getMessage()); 		// making log in file
    		return view('error.home');
    	}
    }

    public function updatesubmit(Request $request){		
    	try{
    		$rules=[
    		'name' => 'required',
            'mfa' => 'required',
    		'mobile' => 'required|numeric|digits:10',
    		];

    		$validator = Validator::make($request->all(),$rules);
    		if ($validator->fails()) {
    			$errors = $validator->errors()->all();
    			$response=array();
    			$response["status"]=0;
    			$response["msg"]=$errors[0];
    			return response()->json($response);// response as json
    		} 
    			$user = new AppUsers(); 
                $updArr = array();
                $updArr['name'] =$request->name;
                $updArr['mobile'] =$request->mobile;
                $updArr['device_level'] =$request->mfa; 
                $user->updateuserdetail($updArr,getUserId()); 
    			$response=array();
    			$response["status"]=1;
    			$response["msg"]="Profile update Successful";
    			return response()->json($response);// response as json
    		
    	}catch(\Exception $e){
    		Log::error('ProfileController-updatesubmit: '.$e->getMessage()); 		// making log in file
    		return view('error.home');
            return false;
    	}
    }

    public function changepassword(Request $request){        
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
            $user_id=$request->session()->get('userData.user_id');              
            $AppUser =AppUserAuth::where('user_id',$user_id)->first();  
            if(password_verify($request->oldpassword,$AppUser->password)){
                if ($request->oldpassword == $request->password) {
                     $response=['status'=>0,'msg'=>'Old Password And New Password Cannot Be Same'];
                     return response()->json($response);
                }else{
                    $userarray=array();
                    $userarray["password"]=Hash::make($request->password); 
                    $AppUser->updateuserpassword($userarray,$user_id);
                    $response=['status'=>1,'msg'=>'Password Change Successfully'];
                     return response()->json($response);// response as json
                }
                
            }else{               
                $response=['status'=>0,'msg'=>'Old Password Is Not Correct'];
                return response()->json($response);// response as json
            }        
            
        }catch(\Exception $e){
            Log::error('ProfileController-changepassword: '.$e->getMessage());       // making log in file
            return view('error.home');
        }

    }

    public function userpic(Request $request){  
        try{
            $rules=[
            'image' => 'required|mimes:jpeg,jpg,png|max:100',            
            ];
            $validator = Validator::make($request->all(),$rules);
            if ($validator->fails()) {
                $errors = $validator->errors()->all();
                $response=array();
                $response["status"]=0;
                $response["msg"]=$errors[0];
                return response()->json($response);// response as json
            }   
             $file = $request->file('image');
             $file->store('profile_picture');
             $fileName = $file->hashName();
             $user_id=getUserId();
             $AppUsers=new AppUsers();
             $userarray=array();
             $userarray["image"]='profile_picture/'.$fileName;          
             $AppUsers->updateuserdetail($userarray,$user_id);
             $response=array();
             $response["status"]=1;
             $response["msg"]=route('userpic.show');
             return response()->json($response);// response as json
        }catch(\Exception $e){
            Log::error('ProfileController-userpic : '.$e->getMessage());      // making log in file
            return view('error.home');
        } 
    }

      

    public function getProfilePic(Request $request){

    	try{
    		$user = Auth::user(); 
    		$storagePath = storage_path('domainstorage/'.$user->image);              
    		$mimeType = mime_content_type($storagePath); 
    		if( ! \File::exists($storagePath)){

    			return view('error.home');
    		}
    		$headers = array(
    			'Content-Type' => $mimeType,
    			'Content-Disposition' => 'inline; '
    		);
    		  
    		if($user->image==null)
            {
                ob_end_clean(); // discards the contents of the topmost output buffer
                return Response::make(file_get_contents('img/profilepic.png'), 200, $headers);
            }
    		else
    		{	
                return Response::make(file_get_contents($storagePath), 200, $headers);

    		}
    	}catch(\Exception $e){
    		Log::error('ProfileController-getProfilePic: '.$e->getMessage()); 		// making log in file
    		return view('error.home');
    	}
    }

    public function activityLog(Response $request){
        try{
            $LoginLog = new LoginLog();
            $activity = $LoginLog->activity(getUserId()); 
            $ActivitySummery = $LoginLog->ActivitySummery(getUserId()); 
            return view('Litigation::Profile.activity_log',compact('activity','ActivitySummery'))->render();
        }catch(\Exception $e){
            Log::error('ProfileController-activityLog: '.$e->getMessage());         // making log in file
            return view('error.home');
        }
    }

    public function getProfilePicById(Request $request,$id){
            try{
                $rules = [
                "$id"=>'required|numeric'];
                $validator = Validator::make($request->all(),$rules);

                $storagePath = storage_path('domainstorage/'.profilepic($id));
                $mimeType = mime_content_type($storagePath);
                if( ! \File::exists($storagePath)){
                    return view('error.home');
                }
                $headers = array(
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; '
                );
                
                if(profilepic($id)=='')
                {
                    return Response::make(file_get_contents('dist/img/mock1.jpg'), 200, $headers);
                }
                else
                {   return Response::make(file_get_contents($storagePath), 200, $headers);

                }

                
            }catch(\Exception $e){
                Log::error('FileHandleController-getProfilePic: '.$e->getMessage());        // making log in file
                return view('error.home');                                  // showing the err page
                return false;
            }
        }

}
