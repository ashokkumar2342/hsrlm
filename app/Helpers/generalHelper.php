<?php
namespace App\Helpers;
use App\Models\Domain;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\LawFirm;
use App\Modules\Litigation\Models\CompanyDepartment;
use App\Modules\Litigation\Models\CompanyLocation;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\Document;
use App\Modules\Litigation\Models\Expenses;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\LawyerMaster;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\NoticeTrackerDocument;
use App\Modules\Litigation\Models\Defaults\Likelihood;
use App\Modules\Litigation\Models\Defaults\CriticalityRisk;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserAuth;
use App\Modules\Login\Models\AppUserRole;
use App\Modules\Login\Models\AppUserType;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Login\Models\LoginLog;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;   // model of CompanyUser table


class generalHelper
{

    function status($status_id,$due_date,$filing_date) {
        try{
            $DefaultStatus=new DefaultStatus();

            if($status_id==7 && $due_date>=$filing_date)
            {
                $status=$DefaultStatus->getStatusbystatusid(7);
                return $status['status_name'];
            }
            elseif($status_id==7 && $due_date<$filing_date)
            {
                $status=$DefaultStatus->getStatusbystatusid(8);
                return $status['status_name'];
            }
            else
            {  
                if($status_id!='')
                {
                    $status=$DefaultStatus->getStatusbystatusid($status_id);
                    return $status['status_name'];
                }
                else
                {
                    $status['status_name']='';
                    return $status['status_name'];
                }
            }
        }catch(\Exception $e){
            Log::error('generalHelper-status: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function getnamebyuserid($user_id) {
        try{
            $AppUsers=new AppUsers();
            $user=$AppUsers->getdetailbyuserid($user_id);
            return $user['name'];
        }catch(\Exception $e){
            Log::error('generalHelper-getnamebyuserid: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function generateId(){
        try{
            return rand('1000','5000').time().rand('5001','9999');
        }catch(\Exception $e){
            Log::error('generalHelper-generateId: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }  

    function fileUploadSizeOfCompliance($audit_id,$type) {
        try{
            $ClientAudit=new ClientAudit();
            if($type=='mime'){
                $val=$ClientAudit->getCompanyFileMime($audit_id);
                return $val['file'];
            }else{
                $val=$ClientAudit->getCompanyFileType($audit_id);
                return $val['file'];
            } 
        }catch(\Exception $e){
            Log::error('generalHelper-fileUploadSizeOfCompliance: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function filetypeOfCompany($company_id,$type) {
        try{
            $Company=new Company();
            if($type=='mime'){
                $val=$Company->getCompanyFileMime($company_id);
                return $val['file'];
            }else{
                $val=$Company->getCompanyFileType($company_id);
                return $val['file'];
            } 
        }catch(\Exception $e){
            Log::error('generalHelper-filetypeOfCompany: '.$e->getMessage());        // making log in file
            return view('error.home');
        }  
    }

    function fileUploadSizeOfCompany($company_id) {
        try{
            $Company=new Company();
            $val=$Company->getCompanyById($company_id);
            return $val['upload_size'];
        }catch(\Exception $e){
            Log::error('generalHelper-fileUploadSizeOfCompany: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function filetypeOfCompliance($id) {
        try{
            $ClientCompliances=new ClientCompliances();
            $val=$ClientCompliances->getUploadSize($id);
            return $val->upload_size;
        }catch(\Exception $e){
            Log::error('generalHelper-filetypeOfCompliance: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function NotificationTypeNamebyId($id){
        try{
            $NotificationType=new NotificationType();
            $list = $NotificationType->getNotificationTypeById($id);
            return $list->name;
        }catch(\Exception $e){
            Log::error('generalHelper-NotificationTypeNamebyId: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function countUserResult($id){
        try{
            $AppUserAuth=new AppUserAuth();
            return $AppUserAuth->passwordcount($id);
        }catch(\Exception $e){
            Log::error('generalHelper-countUserResult: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function getdetailofuseractivity($user_id,$type){
        try{
            if($type=='log'){
                $LoginLog=new LoginLog();
                return $LoginLog->Activity($user_id);
            }
            else{
                $AppUserAuth=new AppUserAuth();
                return $AppUserAuth->lastpassword($user_id);
            }
        }catch(\Exception $e){
            Log::error('generalHelper-getdetailofuseractivity: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }


    function getCompanyIdById($id){
        try{
            $ClientCompliances=new ClientCompliances();
            return $ClientCompliances->getCompanyIdById($id);
        }catch(\Exception $e){
            Log::error('generalHelper-getCompanyIdById: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function  getRoleBycompanyId($companyidArray,$userId){
        try{
            $role= new AppUserRole();
            return $roles= $role->getRoleByCompanyIdArrayUserId($companyidArray,$userId);
        }catch(\Exception $e){
            Log::error('generalHelper-getRoleBycompanyId: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function  getcompanyIdByUserId($user_id){
        try{
            $role= new AppUserRole();
            return $companyId= $role->getCompanyIdArrayByUserId($user_id);
        }catch(\Exception $e){
            Log::error('generalHelper-getcompanyIdByUserId: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    } 

    function  storeMasterTree($matter_id,$master_id,$master_parent_id,$legal_type_id,$company_id,$user_id){
        try{
            $masterTree = new MasterTree(); 
            $data =array();
            $data['matter_id'] =$matter_id;
            $data['master_id'] =$master_id;
            $data['master_parent_id'] =$master_parent_id;
            $data['legal_type_id'] =$legal_type_id;
            $data['company_id'] =$company_id;
            $data['created_by'] =$user_id;
            $masterTrees = $masterTree->insArr($data);  
        }catch(\Exception $e){
            Log::error('generalHelper-storeMasterTree: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function CheckCompanyDefaultDepartmentIdInCompanyDepartment($company,$location,$defaultcompanydepartment,$res){
        try{
            $CompanyDepartment = new CompanyDepartment();
            return $CompanyDepartment->CheckCompanyDefaultDepartmentIdInCompanyDepartment($company,$location,$defaultcompanydepartment,$res);
        }catch(\Exception $e){
            Log::error('generalHelper-CheckCompanyDefaultDepartmentIdInCompanyDepartment: '.$e->getMessage());
            return view('error.home');
        }
    }

    function getUserDataById($id){
        try{
            $AppUsers = new AppUsers();
            $data = $AppUsers->getUserDataById($id);
            return $data;
        }catch(\Exception $e){
            Log::error('generalHelper-getUserDataById: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function getReletionFieldData($key,$id){
        try{ 
            if (!is_null($id)) {
                switch ($key) {
                    case 'matter_id':
                    $data= $this->getModelDataByModelId($id,'Matters','matter_id');
                    return $data->name;
                    break; 
                    case 'matter_type_id':
                    $data= $this->getDefaultModelDataById($id,'MatterType');
                    return $data->name;
                    break; 
                    case 'matter_group_id':
                    $data= $this->getDefaultModelDataById($id,'MatterGroup');
                    return $data->name;
                    break; 
                    case 'document_type_id':
                    $data= $this->getModelDataById($id,'DocumentType');
                    return $data->name;
                    break;                
                    case 'notice_type_id':
                    $data= $this->getDefaultModelDataById($id,'NoticeType');
                    return $data->name;
                    break;
                    case 'location_id':                
                    $data= $this->getModelDataById($id,'CompanyLocation'); 
                    return $data->location_name;
                    break;
                    case 'default_department_id':                
                    $data= $this->getModelDataById($id,'DefaultDepartment'); 
                    return $data->department_name;
                    break;
                    case 'notice_status_id':                
                    $data= $this->getDefaultModelDataById($id,'NoticeStatus'); 
                    return $data->name;
                    break;
                    case 'criticality_risk_id':                
                    $data= $this->getDefaultModelDataById($id,'CriticalityRisk'); 
                    return $data->name;
                    break;
                    case 'legal_category_id':                
                    $data= $this->getDefaultModelDataById($id,'LegalCategory'); 
                    return $data->name;
                    break;
                    case 'notice_category_id':                
                    $data= $this->getDefaultModelDataById($id,'NoticeCategory'); 
                    return $data->name;
                    break;
                    case 'act_id':
                       $actArrid= explode(',', $id);
                       $act='';
                       foreach ($actArrid as $key => $id) {
                          $data= $this->getModelDataById($id,'ActMaster'); 
                          if(empty($data)){
                            $act='';
                          }else{
                            $act.=$data->short_name.', ';
                          }
                       } 
                    return $act;
                    break;
                    case 'legal_team_id':   
                       $userArrid= explode(',', $id); 
                       $legal_team='';
                       foreach ($userArrid as $key => $id) { 
                          $data= $this->getLoginModelDataById($id,'AppUsers'); 
                          $legal_team.=$data->name.', ';
                       } 
                    return $legal_team;     
                    break;
                     case 'lawyers':   
                       $Arrid= explode(',', $id); 
                       $lawyers='';
                       foreach ($Arrid as $key => $id) { 
                          $data= $this->getModelDataById($id,'LawyerMaster'); 
                          $lawyers.=$data->name.', ';
                       } 
                    return $lawyers;     
                    break;
                    case 'owner_id':                
                    $data= $this->getLoginModelDataById($id,'AppUsers'); 
                    return $data->name;
                    break;
                    case 'notify_id':                
                    $notifyUserArrid= explode(',', $id);
                       $notify='';
                       foreach ($notifyUserArrid as $key => $id) {
                          $data= $this->getLoginModelDataById($id,'AppUsers'); 
                          $notify.=$data->name.', ';
                       } 
                    return $notify;   
                    break;
                    case 'case_status_id':                
                    $data= $this->getDefaultModelDataById($id,'CaseStatus'); 
                    return $data->name;
                    break;
                    case 'case_type_id':    
                    if ($id==0) { 
                     return 'Other';
                    }        
                    $data= $this->getDefaultModelDataById($id,'CaseType');
                      
                    return $data->name;
                    break;
                    case 'court_category_id':                
                    $data= $this->getDefaultModelDataById($id,'CourtCategory'); 
                    return $data->name;
                    case 'court_id':                
                    $data= $this->getDefaultModelDataById($id,'Court'); 
                    return $data->name;
                    break;
                    case 'supreme_court_id':                
                    $data= $this->getDefaultModelDataById($id,'SupremeCourt'); 
                    return $data->name;
                    break;
                    case 'bench_id':                
                    $data= $this->getDefaultModelDataById($id,'Bench'); 
                    return $data->name;
                    break;
                    case 'bench_side_id':              
                    $data= $this->getDefaultModelDataById($id,'BenchSide'); 
                    return $data->name; 
                    break;
                    case 'bench_side_stamp_id':                
                    $data= $this->getDefaultModelDataById($id,'BenchSideStamp'); 
                    return $data->name;
                    break;
                    case 'state_id':                
                    $data= $this->getModelDataById($id,'CountryState'); 
                    return $data->state_name;
                    break;
                    case 'state_district_id':                
                    $data= $this->getModelDataById($id,'StateDistrict'); 
                    return $data->name;
                    break;
                    case 'court_establishment_id':                
                    $data= $this->getDefaultModelDataById($id,'CourtEstablishment'); 
                    return $data->name;
                    break;
                    case 'commissions_id':                
                    $data= $this->getDefaultModelDataById($id,'Commissions'); 
                    return $data->name;
                    break;
                    case 'commissions_state_id':                
                    $data= $this->getDefaultModelDataById($id,'CommissionsState'); 
                    return $data->name;
                    break;
                    case 'commissions_state_district_id':                
                    $data= $this->getDefaultModelDataById($id,'CommissionsStateDistrict'); 
                    return $data->name;
                    case 'tribunals_authorities_id':                
                    $data= $this->getDefaultModelDataById($id,'TribunalsAuthorities'); 
                    return $data->name;
                    case 'tribunals_authorities_state_id':                
                    $data= $this->getDefaultModelDataById($id,'TribunalsAuthoritiesState'); 
                    return $data->name;
                    case 'court_establishment_id':                
                    $data= $this->getDefaultModelDataById($id,'TribunalsAuthoritiesStateSection'); 
                    return $data->name;
                    break;
                    case 'revenue_court_id':                
                    $data= $this->getDefaultModelDataById($id,'RevenueCourt'); 
                    return $data->name;
                    break;
                    case 'revenue_district_court_id':                
                    $data= $this->getDefaultModelDataById($id,'RevenueDistrictCourt'); 
                    return $data->name;
                    break;
                    case 'commissionerate_id':                
                    $data= $this->getDefaultModelDataById($id,'Commissionerate'); 
                    return $data->name;
                    break;
                    case 'commissionerate_authority_id':                
                    $data= $this->getDefaultModelDataById($id,'CommissionerateAuthority'); 
                    return $data->name;
                    break;
                    case 'potential_id':                
                    $data= $this->getDefaultModelDataById($id,'Potential'); 
                    return $data->name;
                    break;
                    case 'kmp_involved_id': 
                       $kmpArrid= explode(',', $id);
                       $kmp_involved='';
                       foreach ($kmpArrid as $key => $id) {
                          $data= $this->getDefaultModelDataById($id,'KmpInvolved'); 
                          $kmp_involved.=$data->name.', ';
                       } 
                    return $kmp_involved;   
                    break;
                    case 'likelihood_id':                
                    $data= $this->getDefaultModelDataById($id,'Likelihood'); 
                    return $data->name;
                    break; 
                    case 'opponents_type_id':                
                    $data= $this->getDefaultModelDataById($id,'OpponentsType'); 
                    return $data->name;
                    break;
                    case 'lawyer_type_id':                
                    $data= $this->getDefaultModelDataById($id,'LawyerType'); 
                    return $data->name;
                    break;
                    case 'hearing_status_id':                
                    $data= $this->getModelDataById($id,'HearingStatus'); 
                    return $data->name;
                    break;
                    case 'settlement_type':                
                    $data= $this->getModelDataById($id,'SettlementType'); 
                    return $data->name;
                    break;
                    case 'appearing_model_id':                
                    $data= $this->getDefaultModelDataById($id,'AppearingModel'); 
                    return $data->name;
                    break; 
                    case 'outcome':                
                    $data= $this->getModelDataById($id,'OutcomeType'); 
                    return $data->name;
                    break;
                    case 'settlement_type':                
                    $data= $this->getModelDataById($id,'SettlementType'); 
                    return $data->name;
                    break;
                    case 'next_action_category_id':                
                    $data= $this->getModelDataById($id,'NextActionCategory'); 
                    return $data->name;
                    break;
                    default:
                        # code...
                    break;
                }   
            }
        }catch(\Exception $e){
            Log::error('generalHelper-getReletionFieldData: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function getDefaultModelDataById($id,$modelName){
        try{
            $model =  'App\Modules\Litigation\Models\Defaults\\'.$modelName;  
            return $model::find($id); 
        }catch(\Exception $e){
            Log::error('generalHelper-getDefaultModelDataById: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function getModelDataById($id,$modelName){
        try{
            $model =  'App\Modules\Litigation\Models\\'.$modelName;  
            return $model::find($id); 
        }catch(\Exception $e){
            Log::error('generalHelper-getModelDataById: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function getModelDataByModelId($id,$modelName,$model_id){
        try{
            $model =  'App\Modules\Litigation\Models\\'.$modelName;  
            return $model::where($model_id,$id)->first(); 
        }catch(\Exception $e){
            Log::error('generalHelper-getModelDataById: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function getLoginModelDataById($id,$modelName){
        try{
            $model =  'App\Modules\Login\Models\\'.$modelName;  
            return $model::where('user_id',$id)->first(); 
        }catch(\Exception $e){
            Log::error('generalHelper-getLoginModelDataById: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    }

    function LoginLogByUserIdFromAndToDate($userid,$from,$to){
        try{
            $LoginLog = new LoginLog();
            return $LoginLog->LoginLogByUserIdFromAndToDate($userid,$from,$to);  
        }catch(\Exception $e){
            Log::error('generalHelper-LoginLogByUserIdFromAndToDate: '.$e->getMessage());        // making log in file
            return view('error.home');              // throw the err
        }
    }


    function getcoverageforadmin($company_id,$data){
        try{
            $arr['company_id'] = $company_id = Crypt::decrypt($company_id);
            $CompanyLocation = new CompanyLocation();
            $CompanyDepartment = new CompanyDepartment();
            $ActMaster = new ActMaster();
            $CompanyUser = new CompanyUser();
            $AppUsers = new AppUsers();
            $Document = new Document();
            $AppUserRole = new AppUserRole();

            if($data=='location'){
               return $CompanyLocation->getlocationlistForAdminDashboard($arr,'countByCompanyId'); 
            }

            if($data=='department'){
               return $CompanyDepartment->getdepartmentlistForAdminDashboard($arr,'countByCompanyId'); 
            }

            if($data=='act'){
               return $ActMaster->getActlistForAdminDashboard($arr,'countByCompanyId'); 
            }

            if($data=='active_user'){
               return $AppUserRole->getUserlistForAdminDashboard($arr,'countByCompanyId'); 
            }

            if($data=='compliance_document_list'){
               return $Document->getDocumentForAdminDashboard($arr,'countByCompanyId'); 
            }

            if($data=='compliance_document_size'){
               return $Document->getDocumentForAdminDashboard($arr,'sumSize'); 
            }

            if($data=='locationByCompanyId'){
                return $CompanyLocation->listcompanylocation($company_id); 
            }
        }catch(\Exception $e){
            Log::error('generalHelper-getcoverageforadmin: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
        
    }

    function getcoverageformatter($matter_id,$legal_type_id){
        try{
            $arr = array();
            $arr['childId'] = getChildUserId();
            $arr['legal_type_id'] = $legal_type_id;
            $arr['matter_id'] = $matter_id = Crypt::decrypt($matter_id);

            $Cases = new Cases();
            $Notice = new Notice();
            $Hearing = new Hearing();

            if($legal_type_id==1){
               return $Cases->getcaselistForUserDashboard($arr,1); 
            }

            if($legal_type_id==2){
               return $Notice->getnoticelistForUserDashboard($arr,1); 
            }

            if($legal_type_id==4){
               return $Hearing->gethearinglistForUserDashboard($arr,1); 
            }
        }catch(\Exception $e){
            Log::error('generalHelper-getcoverageforadmin: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
        
    }

    function getLegalTitle($legal_type_id,$ref_id){
        try{
            $Cases = new Cases();
            $Notice = new Notice();
            if($legal_type_id == 1){
                return $Cases->getCaseById($ref_id)->title;
            }elseif($legal_type_id == 2){
                return $Notice->getNoticeById($ref_id)->title;
            }
        }catch(\Exception $e){
            Log::error('generalHelper-getLegalTitle: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function convertsizeofbytes($size)
    {
        try{
            $count=0;
            $array=array('Byte','KB','MB','GB','TB');
            do{
                $size=$size/1000;
                $count++;
            }while($size>1000 && $count<=3);
            return round($size,2).' '.$array[$count];
        }catch(\Exception $e){
            Log::error('generalHelper-convertsizeofbytes: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function getHearingDate($id){
        try{
            $Hearing = new Hearing();
            $hearing_id = Crypt::decrypt($id);
            return $Hearing->getHearingById($hearing_id);
        }catch(\Exception $e){
            Log::error('generalHelper-getHearingDate: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function getTrackerDocument($id){
        try{
            $arr['id'] = $id;
            $NoticeTrackerDocument = new NoticeTrackerDocument();
            return $NoticeTrackerDocument->getResult($arr,2);
        }catch(\Exception $e){
            Log::error('generalHelper-getTrackerDocument: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function getCountryCurrency($id){
        try{
            $arr['id'] = $id;
            $Country = new Country();
            return $Country->getResult($arr,4)->currency_code;
        }catch(\Exception $e){
            Log::error('generalHelper-getCountryCurrency: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function getLawyers($id){
        try{
            $LawyerMaster = new LawyerMaster();
            return $LawyerMaster->getLawyersByFirmId($id);
        }catch(\Exception $e){
            Log::error('generalHelper-getLawyers: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function LoginLogByUserIdFromAndToDateAndTime($userid,$from,$to){
        try{
            $from = $from.' 00:00:00';
            $to = $to.' 23:59:59';
            $LoginLog = new LoginLog();
            return $LoginLog->LoginLogByUserIdFromAndToDate($userid,$from,$to);  
        }catch(\Exception $e){
            Log::error('generalHelper-LoginLogByUserIdFromAndToDate: '.$e->getMessage());        // making log in file
            throw $e;               // throw the err
        }
    }
    function getCasesByYear($year){
        try{
            $arr = array();
            
            $Cases = new Cases(); 
            $month_select=array("Jan"=>"01","Feb"=>"02","Mar"=>"03","Apr"=>"04","May"=>"05","Jun"=>"06","Jul"=>"07","Aug"=>"08","Sep"=>"09","Oct"=>"10","Nov"=>"11","Dec"=>"12");
            $month=array();
            foreach ($month_select as $key => $value) {
                $arr['firstdate'] = $year.'-'.$value.'-01';
                $arr['lastdate'] = $year.'-'.$value.'-31';
                $month[]= $Cases->getResult($arr,'byYearOrMonth');
            } 
            return implode(',',$month);
        }catch(\Exception $e){
            Log::error('generalHelper-getCaseByYear: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }
    //get expence by Expence Type id
    function getExpenceByExpenceTypeId($legal_type_id,$expence_type_id,$from,$to){
        try{
            $Expenses = new Expenses();
            return $Expenses->getExpenceByExpenceTypeId($legal_type_id,$expence_type_id,$from,$to);
        }catch(\Exception $e){
            Log::error('generalHelper-getLawyers: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    //get expence by Expence Type id
    function getExpenceTotal($ref_id,$legal_type_id){
        try{
            $Expenses = new Expenses();
             
            if (!empty($legal_type_id) && !empty($ref_id)) {
                 
                return $Expenses->getExpenses($ref_id,$legal_type_id)->sum('expense_amount'); 
            }else{ 
                return 0; 
            }
            
            
          
                
        }catch(\Exception $e){
            Log::error('generalHelper-getExpenceTotal: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function getActNameById($id){
        try{
            $arr = explode(',', $id);
            $ActMaster = new ActMaster();
            return $ActMaster->getActByIdArr($arr)->short_name;
        }catch(\Exception $e){
            Log::error('generalHelper-getLawyers: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    public function getMultipleDataByIdArr($arr_id,$modelName){
        try {
             $arr = explode(',', $arr_id);
            $model =  'App\Modules\Litigation\Models\Defaults\\'.$modelName;
            return $model::whereIn('id',$arr)
            ->selectRaw('GROUP_CONCAT(name) as name')
            ->first()->name;
        } catch (QueryException $e) {
            return $e; 
        }
    }

     public function getLiklihoodData($from,$to,$likli,$risk,$result){
        try{
            $Cases = new Cases();
            $CriticalityRisk = new CriticalityRisk();
            $Likelihood = new Likelihood();
            $risk = $CriticalityRisk->getByValue($risk)->id;
            $likli = $Likelihood->getByValue($likli)->id;
            return $Cases->getLiklihoodData($from,$to,$likli,$risk,$result);   
        } catch (QueryException $e) {
            return $e; 
        }
    }
    //gerHearing
    function getHearing($ref_id,$text){
        try{
           
            $Hearing = new Hearing();
            $hearings= $Hearing->getHearingLastByCaseId($ref_id);
            if ($text=='last_hearing_date') {
                if ($hearings !=null) {
                 return $hearings->hearing_date; 
                }else{
                    return '-';
                }
            }
        }catch(\Exception $e){
            Log::error('generalHelper-getHearing: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function getTotalHearings($ref_id){
        try{
            $Hearing = new Hearing();
            return $Hearing->getTotalHearings($ref_id); 
        }catch(\Exception $e){
            Log::error('generalHelper-getTotalHearings: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function getLawFirm($id){
        try{
            if(!empty($id)){
                $LawFirm = new LawFirm();
                return $LawFirm->getListById($id)->name; 
            }else{
                return '';
            }
        }catch(\Exception $e){
            Log::error('generalHelper-getLawFirm: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }

    function getPosition($id,$type){
        try{
            $Cases = new Cases();
            $result = $Cases->getPosition($id);
            if($type == 'our_position'){
                return $result->our_position;
            }elseif($type == 'max_budget'){
                return $result->max_budget;
            }elseif($type == 'applicable_from'){
                return $result->applicable_from;
            }elseif($type == 'applicable_to'){
                return $result->applicable_to;
            }elseif($type == 'rationale'){
                return $result->rationale;
            }
        }catch(\Exception $e){
            Log::error('generalHelper-getLawFirm: '.$e->getMessage());        // making log in file
            return view('error.home');               // throw the err
        }
    }


}