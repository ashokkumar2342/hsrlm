<?php 
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\DefaultMenu;
use App\Modules\Litigation\Models\DefaultSubMenu;
use App\Modules\Litigation\Models\Defaults\LegalType;
use App\Modules\Litigation\Models\HearingStatus;
use App\Modules\Litigation\Models\Permission;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserAuth;
use App\Modules\Login\Models\AppUserRole;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Login\Models\DomainConfig;
use App\Modules\NotificationCenter\Models\NotificationCenter;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

function initiate($data){
    try{
        Session::put('userData',$data);
    }catch(\Exception $e){
        Log::error('usersSessionHelper-initiate: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getUserData(){
    try{
        return Session::get('userData');
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getUserData: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}


function UserRole(){
	try{
		return $user_type =Auth::user()->user_type; 
	}catch(\Exception $e){
        Log::error('usersSessionHelper-UserRole: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getUserName(){
    try{
        return Auth::user()->name;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getUserName: '.$e->getMessage());      // making log in file
        return $e;
    }
}
function getUserDetails(){
	try{
		return $user_type =Auth::user(); 
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getUserDetails: '.$e->getMessage());        // making log in file
        return view('error.home');
    }

}

function getUserId(){ 
	try{
		return Session::get('userData.user_id');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getUserId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }

}

function getChildUserId(){ 
    try{
        return Session::get('userData.child_user_id');
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getChildUserId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }

}

function parentTree($role_id){
    try {
     $UserRole = new UserRole();  
     $Role = $UserRole->getRoleById($role_id); 
     $tree=''; 

     if(count($Role->childs)) {
         $tree .=','.childCall($Role);
     } 

     return $data= array_filter(explode(',', $tree));

 } catch (Exception $e) {
       Log::error('usersSessionHelper-parentTree: '.$e->getMessage());        // making log in file
       return view('error.home');                                  // showing the err page
       return false;  
   }

}

   
function childCall($Role){
    $childRoleId  ='';
    foreach ($Role->childs as $arr) {
        if(count($arr->childs)){
            $childRoleId .=$arr->id;                  
            $childRoleId .= ','.childCall($arr);
        }else{
            $childRoleId .=','.$arr->id;
        }                
   }
   return $childRoleId;    
}


function generateId(){
	try{
		return rand('1000','5000').time().rand('5001','9999');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-generateId: '.$e->getMessage());        // making log in file
        return view('error.home');                                  // showing the err page
        return false;
    }
}
//secure device	
function getSecureDevice(){ 
    try{
        return Session::get('userData.secure_device')==1;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getSecureDevice: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//domain config
function domainConfig(){ 
	try{
		$Domain=new DomainConfig();
		return $Domain->domain();
	}catch(\Exception $e){
        Log::error('usersSessionHelper-domainConfig: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}  
//domain config




//menu start

function getMenu(){
	try{
		$menu=array();
		if(checkSuperAdmin()){
			$menu=getSuperAdminMenu();
		}elseif(checkAdmin()){
			$menu=getAdminMenu();
		}elseif(checkUser()){
			$menu=getUserMenu();
		}elseif(checkSupport()){
            $menu=getSupportMenu();
        }
        return $menu;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getMenu: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getActiveMenuClass($menu){
	try{
		if(Request::is($menu) || Request::segment(1)."/".Request::segment(2)==$menu)
			return 'active';
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getActiveMenuClass: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getSubMenuActive($menu){
	try{
		if(Request::is($menu))
			return 'active-page';
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getSubMenuActive: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getCollapseIn($menu){
	try{
		if(Request::is($menu) || Request::segment(1)."/".Request::segment(2)==$menu)
			return 'in';
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getCollapseIn: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

//Super Admin Menu start
function checkSuperAdmin(){
	try{
		return Session::get('userData.user_type')==1;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-checkSuperAdmin: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getSuperAdminMenu(){
	try{
		$menu=array();
		$menu[]=array('url'=>'dashboard','title'=>'Company','class'=>'zmdi zmdi-edit mr-20');
// $menu[]=array('url'=>'audit/companycreation','title'=>'Company','class'=>'zmdi zmdi-edit mr-20');
		return $menu;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getSuperAdminMenu: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//Super Admin Menu end

//admin menu start
function checkAdmin(){
	try{
		return Session::get('userData.user_type')==2;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-checkAdmin: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//admin menu start
function checkUser(){
	try{
		return Session::get('userData.user_type')==3;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-checkUser: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//admin menu start
function checkSupport(){
    try{
        return Session::get('userData.user_type')==4;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-checkSupport: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//admin menu start
function checkPermission(){
	try{
		$currentUrl =request()->path();
		$defaultSubMenu = new DefaultSubMenu();
		$defaultSubMenusUrl=$defaultSubMenu->getSubMenuUrl();	 
		$userHasSubMenusUrl=$defaultSubMenu->getSubMenuUrlById(getSubMenuId());	 
		if (in_array($currentUrl,$userHasSubMenusUrl)) {
			return true;
		}else{
			if (in_array($currentUrl,$defaultSubMenusUrl)) {
				return false;
			}else{
				return true;
			}
		}
	}catch(\Exception $e){
        Log::error('usersSessionHelper-checkPermission: '.$e->getMessage());        // making log in file
        return view('error.home');
    }

}

function getAdminMenu(){
	try{
		$menu=array();
		$menu[]=array('url'=>'dashboard','title'=>'Dashboard','class'=>'zmdi zmdi-home mr-20');
        // $menu[]=array('url'=>'adminmaster','title'=>'Masters','class'=>'zmdi zmdi-flag mr-20','subMenu'=>[array('url'=>'companylocation','title'=>'Location/Department Master'),array('url'=>'legalcategory','title'=>'Legal Category/Act Master'),array('url'=>'mattermaster','title'=>'Matter Master')]);
		$menu[]=array('url'=>'adminmaster','title'=>'Masters','class'=>'zmdi zmdi-flag mr-20');
        $menu[]=array('url'=>'usermaster','title'=>'User Masters','class'=>'zmdi zmdi-account mr-20');
        $menu[]=array('url'=>'loginreport','title'=>'Login Report','class'=>'zmdi zmdi-assignment-o mr-20');
        // $TicketMenu=array('url'=>'usersupport','title'=>'User Support','class'=>'zmdi zmdi-ticket-star mr-20');
        // if(TicketDomainLive()=='true'){
        // if(checkTicketAccess()=='true')
        // {
        //     $TicketMenu["subMenu"][]=array('url'=>'ticket-admin/active','title'=>'Ticket Center');
        // }
        // }
        

        // $menu[]=$TicketMenu;
        return $menu;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getAdminMenu: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//admin menu end
//user menu
function getUserMenu(){
	try{
		$defaultMenu = new DefaultMenu();
		$defaultSubMenu = new DefaultSubMenu();
		$defaultMenus=$defaultMenu->getMenuById(getMenuId());  
		$menu=array();	 
		$menu[]=array('url'=>''.$defaultMenu->url,'title'=>'Dashboard','class'=>'zmdi zmdi-home mr-20');
		foreach ($defaultMenus as  $defaultMenu) {  
			$defaultSubMenus=$defaultSubMenu->getSubMenuByMenuId($defaultMenu->id);
			$subMenu =array();
			// foreach ($defaultSubMenus as $key => $defaultSubMenu) {
			// 	if (in_array($defaultSubMenu->id, getSubMenuId())) {
			// 		$subMenu[]=			
			// 		array('url'=>''.$defaultSubMenu->url,'title'=>$defaultSubMenu->name,'class'=>'zmdi zmdi-users mr-20');
			// 	}

			// }
			$menu[]=array('url'=>$defaultSubMenus[0]->url,'title'=>$defaultMenu->name,'class'=>''.$defaultMenu->icon		 
				 
			);	 
		}         
        $menu[]=array('url'=>'usersupport','title'=>'User Support','class'=>'zmdi zmdi-ticket-star mr-20','subMenu'=>[array('url'=>'/ticket/view','title'=>'Ticket center')]);
        return $menu;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getUserMenu: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//menu end

//support menu
function getSupportMenu(){
    try{
        $menu=array();
        $menu[]=array('url'=>'dashboard','title'=>'Notice Tracker','class'=>'zmdi zmdi-edit mr-20');
        return $menu;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getAdminMenu: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//end menu

function getCompanyId(){
	try{
		return Session::get('userData.company_id');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getCompanyId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getSetCompanyId(){
	try{
		return Session::get('userData.set_company_id');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getSetCompanyId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
function getSetRoleId(){
	try{
		return Session::get('userData.set_role_id');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getSetRoleId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
function getRoleId(){
	try{
		return Session::get('userData.role_id');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getRoleId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
function getSubMenuId(){
	try{
		return Session::get('userData.sub_menu_id');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getSubMenuId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
function getMenuId(){
	try{
		return Session::get('userData.menu_id');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getMenuId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getCompanyIdName(){ 
	try{
		$company = new Company();
		$appUserRole = new AppUserRole(); 
		$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
		$data = $company->getCompanyByArrId($appUserRoleCompanyId);
		return $data;	
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getCompanyIdName: '.$e->getMessage());        // making log in file
        return view('error.home');
    } 
}
function getRoleIdName(){ 
	try{
		$companyId = getSetCompanyId();
		$role = new UserRole(); 
		$appUserRole = new AppUserRole(); 
		$rolesIdArr = $appUserRole->getRoleIdArrByCompanyIdUserId($companyId,getUserId()); 
		$companyRoles = $role->getRoleByIdArray($rolesIdArr); 
		return $companyRoles;	
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getRoleIdName: '.$e->getMessage());        // making log in file
        return view('error.home');
    } 
}

function getUserType(){
	try{
//return Auth::user()->userTypes->user_type;
		return Session::get('userData.user_type');
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getUserType: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getGroupId(){
	try{
		$company = new Company();
		$data = $company->groupId(getCompanyId());
		return $data->group_id;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getGroupId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function decrypt_array($arr){
	try{
		$deArr = array();
		for($i=0;$i<count($arr);$i++){
			if($arr[$i] == 'null'){
				$deArr[] = '';
			}else{
				$deArr[] = Crypt::decrypt($arr[$i]);
			}
		}
		return $deArr;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-decrypt_array: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function encrypt_array($arr){
	try{
		$deArr = array();
		for($i=0;$i<count($arr);$i++){
			if($arr[$i] == ''){
				$deArr[] = '';
			}else{
				$deArr[] = Crypt::encrypt($arr[$i]);
			}
		}
		return $deArr;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-encrypt_array: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getPermissionSubMenuId($company_id,$role_id){
	try{
		$permission =new Permission();
		return $permission->getPermissionSubMenuId($company_id,$role_id);
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getPermissionSubMenuId: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function  getCompanyNameById($id){
	try{
		$company= new Company();
		$companys=  $company->getDetail($id);
		return  $companys->name;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getCompanyNameById: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function  getRoleNameById($id){
	try{
		$role= new UserRole();
		$roles=  $role->getRoleById($id);
		return  $roles->name;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getRoleNameById: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

//jurisdiction select box show data
function jurisdiction(){ 
	try{
		return $jurisdiction =['National'=>'National','State'=>'State','Municipal'=>'Municipal']; 
	}catch(\Exception $e){
        Log::error('usersSessionHelper-jurisdiction: '.$e->getMessage());        // making log in file
        return view('error.home');
    }


}

function getSelectBox($modelName,$selectedId=null){
	try{
		$model =  'App\Modules\Litigation\Models\Defaults\\'.$modelName;  
		$options =  $model::where('status',1)->orderBy('id')->get();

		$data ='';
        if($modelName == 'Court'){
            foreach ($options as $key => $option) {
                $selected =$option->id==$selectedId?'selected':'';
                $id = Crypt::encrypt($option->id);
                $data.='<option value="'.$id.'" '.$selected.'>'.$option->name.'</option>';
            }
        }else{
            foreach ($options as $key => $option) {
                $selected =$option->id==$selectedId?'selected':'';
                $data.='<option value="'.$option->id.'" '.$selected.'>'.$option->name.'</option>';
            }    
        }
		 
		if (UserRole()==2) { 
			$data.='<option value="more" class="btn btn-success">More</option>';   
		}
		return $data;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getSelectBox: '.$e->getMessage());        // making log in file
        return view('error.home');
    } 
}
function getSelectByDefault($modelName,$selectedId=null){
	try{
		$model =  'App\Modules\Litigation\Models\Defaults\\'.$modelName;  
		$options =  $model::where('status',1)->get();
		$data ='';
		foreach ($options as $key => $option) {
			$selected =$option->id==$selectedId?'selected':'';
			$data.='<option value="'.$option->id.'" '.$selected.'>'.$option->name.'</option>';
		}  
		return $data; 
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getSelectByDefault: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
function getMultipleSelectBox($modelName,$selectedIdArr=null){
	try{
		$model =  'App\Modules\Litigation\Models\Defaults\\'.$modelName;  
		$options =  $model::where('status',1)->get();
		$data ='';
		$selectedIdArrs = explode(',', $selectedIdArr);
        if($modelName == 'Court'){
            foreach ($options as $key => $option) {
                $selected =in_array($option->id,$selectedIdArrs)?'selected':'';
                $id = Crypt::encrypt($option->id);
                $data.='<option value="'.$id.'" '.$selected.'>'.$option->name.'</option>';
            } 
        }else{
            foreach ($options as $key => $option) {
                $selected =in_array($option->id,$selectedIdArrs)?'selected':'';
                $data.='<option value="'.$option->id.'" '.$selected.'>'.$option->name.'</option>';
            }    
        }
		if (UserRole()==2) { 
			$data.='<option value="more" class="btn btn-success">More</option>';   
		}
		return $data; 
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getMultipleSelectBox: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getSelectOption($modelName,$fielId,$fielName,$selectedId=null){
	try{
		$model =  'App\Modules\Litigation\Models\\'.$modelName;  
		$options =  $model::where('status',1)->get();
		$data ='';
		foreach ($options as $key => $option) {
			$selected =$option[$fielId]==$selectedId?'selected':'';
			$data.='<option value='.$option[$fielId].' '.$selected.'>'.$option[$fielName].'</option>';
		} 

		return $data;
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getSelectOption: '.$e->getMessage());        // making log in file
        return view('error.home');
    }

}

function getModelId($modelName){
    try{
        $model =  'App\Modules\Litigation\Models\Defaults\\'.$modelName;  
        $options =  $model::where('status',1)->orderBy('id')->get();
        return $options->pluck('id')->toarray();
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getSelectBox: '.$e->getMessage());        // making log in file
        return view('error.home');
    } 
}

function getLegalType($id){
	try{
		$legal = new LegalType();
		return $legals =$legal->getLegalType($id);
	}catch(\Exception $e){
        Log::error('usersSessionHelper-getLegalType: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}
//get data by tree master id 
function getDataById($legal_type_id,$id){
	try{
		if ($legal_type_id==1) {
			$modelName = 'Cases';
			$field = 'case_id';
            $status = 1;
        }else if ($legal_type_id==2) {
           $modelName = 'Notice';
           $field = 'notice_id';
           $status = 1;
       }else if ($legal_type_id==3) {
           $modelName = 'Judgement';
           $field = 'judgement_id';
           $status = 1;
       }else if ($legal_type_id==4) {
           $modelName = 'Hearing';
           $field = 'hearing_id';
           $status = 1;
       }

       $model =  'App\Modules\Litigation\Models\\'.$modelName;  
		// $data =  $model::where($field,$id)->where('company_id',getSetCompanyId())->where('created_by',getUserId())->where('status',1)->with('getStatus')->first();
       $data =  $model::where($field,$id)->where('company_id',getSetCompanyId())->where('status',$status)->first();
        // $data =  $model::where($field,$id)->where('company_id',getSetCompanyId())->where('status',1)->with('getStatus')->first();
       return $data;
   }catch(\Exception $e){
        Log::error('usersSessionHelper-getDataById: '.$e->getMessage());        // making log in file
        return view('error.home');
    }

}

function checkPassword180DaysOld(){
	try{
		$AppUserAuth = new AppUserAuth();
		$result = $AppUserAuth->lastpassword(getUserId());
		if($result->diff > 180){
			return false;
		}else{
			return true;
		}
	}catch(\Exception $e){
        Log::error('usersSessionHelper-checkPassword180DaysOld: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function profilepic($id){
    $user_id=$id;
    $AppUser=AppUsers::where('user_id',$user_id)->select('image')->first();          
    return $AppUser->image;
}

function timezoneShortName(){
    try{
        $city = env('APP_TIMEZONE');
        $timezone = new DateTimeZone($city);
        $date_time = new DateTime('now', $timezone);
        return $date_time->format('T');
    }catch(\Exception $e){
        Log::error('usersSessionHelper-timezoneShortName: '.$e->getMessage());        // making log in file
        return view('error.home');               // throw the err
    }
}

function ConvertedTime($timestamp){
    try{
        $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, 'UTC'); 
        $ddd=$date->setTimezone(env('APP_TIMEZONE'));
        return date('d M,Y h:i A',strtotime($ddd));
    }catch(\Exception $e){
        Log::error('usersSessionHelper-ConvertedTime: '.$e->getMessage());        // making log in file
        return view('error.home');               // throw the err
    }
}

function ConvertedDate($timestamp){
    try{
        $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, 'UTC'); 
        $ddd=$date->setTimezone(env('APP_TIMEZONE'));
        return date('d M,Y',strtotime($ddd));
    }catch(\Exception $e){
        Log::error('usersSessionHelper-ConvertedTime: '.$e->getMessage());        // making log in file
        return view('error.home');               // throw the err
    }
}


function checkTicketAccess(){ 
    try{ 
        $user= getUserData();
        $email_id =  $user['email'];            
        $domain_url =  Url('/');            
        $client = new Client();
        $res = $client->request('POST', env('DOMAIN_URL').'/api/ticket-admin/check-ticket-access', [
            'form_params' => [
                'email' => $email_id ,                   
                'domain' => $domain_url,
            ], 
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.env('API_TOKEN'),
            ],
        ]);  
        $data= $res->getBody()->getContents(); 
        $datas =  (array) json_decode($data); 
        return $datas['data'];  

    }catch(\Exception $e){
        Log::error('usersSessionHelper-checkTicketAccess: '.$e->getMessage());        // making log in file
        return view('error.home');
    } 
}

function TicketDomainLive(){ 
    try{ 
        $file = env('DOMAIN_URL');
        $file_headers = @get_headers($file);
        if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            $exists = false;
        }
        else {
            $exists = true;
        } 
        return $exists;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-TicketDomainLive: '.$e->getMessage());        // making log in file
        return view('error.home');
    } 
}

function getHearingStatus($id){
    try{ 
        $HearingStatus = new HearingStatus();
        return $HearingStatus->getHearingStatusById($id)->name;
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getHearingStatus: '.$e->getMessage());        // making log in file
        return view('error.home');
    } 
}

function getSubMenu($id){
    try{
        $DefaultSubMenu = new DefaultSubMenu();
        return $DefaultSubMenu->getAllSubMenuByMenuId($id)->pluck('id')->toarray();
    }catch(\Exception $e){
        Log::error('usersSessionHelper-getSubMenu: '.$e->getMessage());        // making log in file
        return view('error.home');
    }
}

function getDateBy(){
    try{
        return array(1=> 'Last 7 Days',2=> 'Last 30 Days', 3=>'Last 90 Days', 4=>'Current Calendar', 5=>'Current Quarter', 6=>'Last Quarter', 7=>'Custom');
    }catch(\Exception $e){
        Log::error('advanceSearchHelper-getDateBy view page: '.$e->getMessage());       // making log in file
        return $e;
    }
}

function remove_element($array,$value) {
  return array_diff($array, (is_array($value) ? $value : array($value)));
}

// notification save send function
function notificationCenter($to_user_id,$from_user_id,$role_id,$reference_id,$message,$legal_type_id){
    try {
        $notifications = new NotificationCenter();
         $insArray = array();   
            
             $insArray['user_id'] = $to_user_id;    
             $insArray['from_user_id'] = $from_user_id;
             $insArray['role_id'] = $role_id;
             $insArray['reference_id'] = $reference_id;
             $insArray['message'] = $message;
             $insArray['legal_type_id'] = $legal_type_id; 
             $insArray['status'] = 1;   
             $insArray['read_status'] = 1;  
             $notifications->insNotificationCenter($insArray); 
         
        
    } catch (Exception $e) {
        Log::error('Gereral-Helper-notificationCenter: '.$e->getMessage()); // making log in file
        return $e;  
    }
    
     
} 

function countNotificationCenter(){  
    try {
        $NotificationCenter = new NotificationCenter(); 
        $id =getUserId();
        return $notifications = $NotificationCenter->countNotificationCenter($id); 
    } catch (Exception $e) {
        Log::error('Gereral-Helper-countNotificationCenter: '.$e->getMessage()); // making log in file
        return $e;  
    }
    
}