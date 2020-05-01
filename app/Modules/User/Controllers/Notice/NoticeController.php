<?php 
namespace App\Modules\Litigation\Controllers\Notice;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyDepartment;
use App\Modules\Litigation\Models\CompanyLocation;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\DefaultNoticeCategory;
use App\Modules\Litigation\Models\Defaults\CaseStatus;
use App\Modules\Litigation\Models\Defaults\CaseType;
use App\Modules\Litigation\Models\Defaults\LegalCategory;
use App\Modules\Litigation\Models\Defaults\NoticeStatus;
use App\Modules\Litigation\Models\Lawyer;
use App\Modules\Litigation\Models\LawFirm;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\Opponents;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use App\Modules\Login\Models\AppUsers;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
class NoticeController extends Controller
{ 
	public function index(Request $request){
		try{
			$data=$arr=array();
			$Notice = new Notice();
			$UserRole = new UserRole();
			$year =  date('Y');
			$data['year'] = $year;
			$data['roleList']=$UserRole->getDetail();
			return view('Litigation::Notice.notice_list',$data);
		}catch(\Exception $e){
			Log::error('NoticeController-index: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function getpiechart(Request $request){
		try{
			$data=$arr=array();
			$Notice = new Notice();
			$arr['from'] = Crypt::decrypt($request->year).'-'.Crypt::decrypt($request->month).'-01';
			$arr['to'] = Crypt::decrypt($request->year).'-'.Crypt::decrypt($request->month).'-31';
			$data['totalsent'] = $Notice->getResult($arr,'conutSentNotice');
			$data['totalreceived'] = $Notice->getResult($arr,'conutReceviedNotice');
			return view('Litigation::Graph.Notice.piechart',$data);
		}catch(\Exception $e){
			Log::error('NoticeController-getpiechart: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function getbarchart(Request $request){
		try{
			$data=$arr=array();
			$Notice = new Notice();
			$year =  Crypt::decrypt($request->year);
			$month_select=array("Jan"=>"01","Feb"=>"02","Mar"=>"03","Apr"=>"04","May"=>"05","Jun"=>"06","Jul"=>"07","Aug"=>"08","Sep"=>"09","Oct"=>"10","Nov"=>"11","Dec"=>"12");
			$totalsent = $totalreceived = array();
			foreach ($month_select as $va => $key) {
				$arr['from'] = $year.'-'.$key.'-01';
                $arr['to'] = $year.'-'.$key.'-31';
                $totalsent[] = $Notice->getResult($arr,'conutSentNotice');
                $totalreceived[] = $Notice->getResult($arr,'conutReceviedNotice');
			}
			$data['totalsent'] = implode(',',$totalsent);
			$data['totalreceived'] = implode(',',$totalreceived);
			return view('Litigation::Graph.Notice.barchart',$data);
		}catch(\Exception $e){
			Log::error('NoticeController-getbarchart: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}
//notice show
	public function show()
	{
		try {

			$data = $arr = array();
			$Notice = new Notice();
			$arr['childId'] = getChildUserId();
			$data['noticeList'] = $Notice->getResult($arr,'getFullCase');
			return view('Litigation::Notice.notice_table',$data)->render();
		} catch (Exception $e) {
			Log::error('notice show page: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}//notice show
	public function listPopup($type)
	{
		try { 
			$data = $arr = array();
			$Notice = new Notice();
			$arr['childId'] = getChildUserId();
			$data['noticeList'] = $Notice->getResult($arr,$type);
			return view('Litigation::Notice.list_popup',$data)->render();
		} catch (Exception $e) {
			Log::error('notice show page: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function listpopupbymonthyear(Request $request){
		try { 
			$data = $arr = array();
			$Notice = new Notice();
			$type = $request->type;
			$year = Crypt::decrypt($request->year);
			if(strlen($request->month) < 5){
				$month = $request->month;
			}else{
				$month = Crypt::decrypt($request->month);
			}
			$arr['from'] = $year.'-'.$month.'-01';
			$arr['to'] = $year.'-'.$month.'-31';
			$data['noticeList'] = $Notice->getResult($arr,$type);
			return view('Litigation::Notice.list_popup',$data)->render();
		} catch (Exception $e) {
			Log::error('notice show page: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

//notice show by matter id
	public function showByMatterId($matter_id)
	{   try {
		$matter_id = Crypt::decrypt($matter_id);
		$notice = new Notice();
		$notices = $notice->getNoticeByMatterId($matter_id,getSetCompanyId(),getUserId());
		$data['notices']=$notices;
		return view('Litigation::Notice.notice_table',$data)->render();

	} catch (Exception $e) {
		Log::error('notice show page: '.$e->getMessage()); 		
		return view('error.home');	
	}
}
//show form 
public function create(){
	try{
		$country = new Country();
		$company = new Company(); 
		$act = new ActMaster(); 
		$LawFirm = new LawFirm();  
		$appUserRole = new AppUserRole(); 
		$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
		 
		$companies = $company->getCompanyByArrId($appUserRoleCompanyId);
		$countries = $country->getCountry();
		$legalCategory = new LegalCategory(); 
		$legalCategorys = $legalCategory->getLegalCategory(); 
		$companyDetail = $company->getDetail(getSetCompanyId()); 
		$appUser = new AppUsers(); 
		$appUsers =$appUser->getAllUserByCompanyId(getSetCompanyId());
		$companyLocation = new CompanyLocation(); 
		$companyLocations =$companyLocation->getCompanyLocation(getSetCompanyId());
		$CompanyDepartment = new CompanyDepartment(); 
		$deptList = $CompanyDepartment->getDepartmentlist(getSetCompanyId());
		$DefaultNoticeCategory = new DefaultNoticeCategory();	
		$data =array(); 
		$data['firmList'] = $LawFirm->getFirm(getSetCompanyId());
		$data['countries'] =$countries;
		$data['companies'] =$companies;  
	 	$data['legalCategorys'] =$legalCategorys;
		$data['companyDetail'] =$companyDetail;  
		$data['appUsers']=$appUsers;
		$data['companyLocations']=$companyLocations;
		$data['deptList']=$deptList;
		$data['categoryList'] = $DefaultNoticeCategory->getCategory();
		return view('Litigation::Notice.notice_popup',$data);
	}catch(\Exception $e){
		Log::error('company add page: '.$e->getMessage()); 		
		return view('error.home');									
		return false;
	}
}
//notice show popup
public function popupForm($matter_id,$master_id,$legal_type_id=null)
{  
	try {  
	$country = new Country();
	$company = new Company(); 
	$act = new ActMaster();
		//$acts = $act->getActs();
	if ($legal_type_id!=null) {				 
		$legal_type_id =Crypt::decrypt($legal_type_id);
	}
	$appUserRole = new AppUserRole(); 
	$LawFirm = new LawFirm(); 
	$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
	$matter = new Matters(); 
	$matter_id =Crypt::decrypt($matter_id);
	$matter =$matter->getMatterById($matter_id);
	$companies = $company->getCompanyByArrId($appUserRoleCompanyId);
	$countries = $country->getCountry();
	$legalCategory = new LegalCategory(); 
	$legalCategorys = $legalCategory->getLegalCategory(); 
	$companyDetail = $company->getDetail(getSetCompanyId()); 
	$appUser = new AppUsers(); 
	$appUsers =$appUser->getAllUserByCompanyId(getSetCompanyId());
	$companyLocation = new CompanyLocation(); 
	$companyLocations =$companyLocation->getCompanyLocation(getSetCompanyId());
	$CompanyDepartment = new CompanyDepartment(); 
	$deptList = $CompanyDepartment->getDepartmentlist(getSetCompanyId());
	$DefaultNoticeCategory = new DefaultNoticeCategory();	
	$data =array(); 
	$data['firmList'] = $LawFirm->getFirm(getSetCompanyId());
	$data['countries'] =$countries;
	$data['companies'] =$companies; 
		//$data['acts'] =$acts; 
	$data['matter'] =$matter; 
	$data['master_id'] =$master_id; 
	$data['legal_type_id'] =$legal_type_id;  
	$data['legalCategorys'] =$legalCategorys;  
	$data['companyDetail'] =$companyDetail;  
	$data['appUsers']=$appUsers;
	$data['companyLocations']=$companyLocations;
	$data['deptList']=$deptList;
	$data['categoryList'] = $DefaultNoticeCategory->getCategory();
	if ($legal_type_id==1) {
		$data['case_id'] =$master_id;  
	}


	return view('Litigation::Notice.notice_popup',$data)->render();
	
} catch (Exception $e) {

}
}
public function view($notice_id)
{  
	try {  

	$notice_id = Crypt::decrypt($notice_id);  
	$notice = new Notice();
	$notices = $notice->getNoticeById($notice_id); 
	$country = new Country();
	$company = new Company(); 
	$LawFirm = new LawFirm();
	$act = new ActMaster(); 
	$acts = $act->getActs();
	$appUserRole = new AppUserRole(); 
	$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
	$companies = $company->getCompanyByArrId($appUserRoleCompanyId);
	$countries = $country->getCountry();
	$legalCategory = new LegalCategory(); 
	$legalCategorys = $legalCategory->getLegalCategory(); 
	$companyDetail = $company->getDetail(getSetCompanyId()); 
	$appUser = new AppUsers(); 
	$appUsers =$appUser->getAllUserByCompanyId(getSetCompanyId());
	$companyLocation = new CompanyLocation(); 
	$companyLocations =$companyLocation->getCompanyLocation(getSetCompanyId());
	$CompanyDepartment = new CompanyDepartment(); 
	$deptList = $CompanyDepartment->getDepartmentlist(getSetCompanyId());
	$masterTree = new MasterTree(); 
	$parent_id = ''; 
	if ($notices->matter_id!=null) {
		 $masterTrees = $masterTree->getMasterTreeByMasterId($notice_id);
		 if ($masterTrees->master_parent_id==0) {
		 	$parent_id = $notices->matter->id;
		 }else{
		 	$parent_id = $masterTree->getParentDataIdById($masterTrees->master_parent_id)->id;
		 }
	}
	$lawyer = new Lawyer();
	$lawyers = $lawyer->getLawyerByCaseId($notice_id);
	$opponent = new Opponents();	
	$opponents = $opponent->getOpponentByCaseId($notice_id);
	$DefaultNoticeCategory = new DefaultNoticeCategory();	
	$ns = new NoticeStatus();
	
	$data =array(); 
	$data['firmList'] = $LawFirm->getFirm(getSetCompanyId());
	$data['notice_id'] = $notice_id;
	$data['countries'] =$countries; 
	if ($notices->matter_id!=null) {
	$data['masterTrees']=$masterTrees;
	}
	$data['parent_id']=$parent_id;
	$data['acts'] =$acts;  
	$data['notice'] =$notices;  		 
	$data['legalCategorys'] =$legalCategorys;  
	$data['companyDetail'] =$companyDetail;  
	$data['appUsers']=$appUsers;
	$data['companyLocations']=$companyLocations;
	$data['deptList']=$deptList;
	$data['lawyers'] =$lawyers;  	 
	$data['opponents'] =$opponents;  
	$data['status'] = $ns->getNoticeStatusByNoticeTypeId($notices->notice_type_id);
	$data['categoryList'] = $DefaultNoticeCategory->getCategory();

	return view('Litigation::Notice.notice_view',$data)->render();
	
} catch (Exception $e) {
	Log::error('case view: '.$e->getMessage()); 		
	return view('error.home');	
}
}
public function edit($notice_id)
{  
	try { 

	$notice_id = Crypt::decrypt($notice_id);  
	$notice = new Notice();
	$notices = $notice->getNoticeById($notice_id); 
	$country = new Country();
	$company = new Company(); 
	$LawFirm = new LawFirm();
	$act = new ActMaster(); 
	$acts = $act->getActs();
	$appUserRole = new AppUserRole(); 
	$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
	$companies = $company->getCompanyByArrId($appUserRoleCompanyId);
	$countries = $country->getCountry(); 
	$legalCategory = new LegalCategory(); 
	$legalCategorys = $legalCategory->getLegalCategory(); 
	$companyDetail = $company->getDetail(getSetCompanyId()); 
	$appUser = new AppUsers(); 
	$appUsers =$appUser->getAllUserByCompanyId(getSetCompanyId());
	$companyLocation = new CompanyLocation(); 
	$companyLocations =$companyLocation->getCompanyLocation(getSetCompanyId());
	$CompanyDepartment = new CompanyDepartment(); 
	$deptList = $CompanyDepartment->getDepartmentlist(getSetCompanyId());
	$masterTree = new MasterTree(); 
	$parent_id = '';
	if ($notices->matter_id!=null) {
		 $masterTrees = $masterTree->getMasterTreeByMasterId($notice_id);
		 if ($masterTrees->master_parent_id==0) {
		 	$parent_id = $notices->matter->id;
		 }else{
		 	$parent_id = $masterTree->getParentDataIdById($masterTrees->master_parent_id)->id;
		 }
	}	
	$lawyer = new Lawyer();
	$lawyers = $lawyer->getLawyerByCaseId($notice_id);
	$opponent = new Opponents();	
	$opponents = $opponent->getOpponentByCaseId($notice_id);
	$DefaultNoticeCategory = new DefaultNoticeCategory();	
	$ns = new NoticeStatus();
	
	$data =array(); 
	$data['firmList'] = $LawFirm->getFirm(getSetCompanyId());
	$data['notice_id'] = $notice_id;
	$data['countries'] =$countries; 
	if ($notices->matter_id!=null) {
	$data['masterTrees']=$masterTrees;
    }
	$data['parent_id']=$parent_id;
	$data['acts'] =$acts;  
	$data['notice'] =$notices;  		 
	$data['legalCategorys'] =$legalCategorys;  
	$data['companyDetail'] =$companyDetail;  
	$data['appUsers']=$appUsers;
	$data['companyLocations']=$companyLocations;
	$data['deptList']=$deptList;
	$data['lawyers'] =$lawyers;  	 
	$data['opponents'] =$opponents;  
	$data['status'] = $ns->getNoticeStatusByNoticeTypeId($notices->notice_type_id);
	$data['categoryList'] = $DefaultNoticeCategory->getCategory();
	return view('Litigation::Notice.notice_edit',$data)->render();
	
} catch (Exception $e) {
	Log::error('case view: '.$e->getMessage()); 		
	return view('error.home');	
}
}
public function store(Request $request){
	try{ 
		$rules=[
			'notice_type'=>'required',
			'notice_id'=>'nullable',
			'title'=>'required',
			'notice_status'=>'required',
			'legal_category' => 'required',
			'act' => 'required',
			'opponents_type.*' => 'required',
			'opponents_name.*' => 'required',
			'lawyer_type' => 'required',
			'legal_team' => 'required',
			'owner' => 'required',
			'lawyers' => 'required_if:lawyer_type,==,2',
			'opponents_mobile.*' => 'nullable|numeric|digits_between:10,12',
			'received_at'=>"required_if:notice_type,==,2",
			'department'=>"nullable",
			'notice_date'=>'required|date|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
			'sent_date'=>'nullable|required_if:notice_type,==,1|date|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
			'received_date'=>'nullable|required_if:notice_type,==,2|date|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
			'relief_claimed'=>'nullable',
			'additional_remarks'=>'nullable',
			'criticality_risk'=>'nullable',	
		];
		$validator = Validator::make($request->all(),$rules);
		if ($validator->fails()) {
			$errors = $validator->errors()->all();
			$response=array();
			$response["status"]=0;
			$response["msg"]=$errors[0];
			return response()->json($response);// response as json
		} 
		$location_id=null;  
		if ($request->has('received_at')) {
			$location_id = Crypt::decrypt($request->received_at); 
		}
		$department_id=null;
		if ($request->has('department')) {
			$department_id = Crypt::decrypt($request->department);
		}
		
		
		$notice =new Notice();
		if ($request->matter!=null) {
			 $matter_id =Crypt::decrypt($request->matter);	
			 $master_id =Crypt::decrypt($request->master_id);
		}else{
			$matter_id =null;	
			$master_id =null;
		}
		
		$legal_type_id =Crypt::decrypt($request->legal_type_id);
		$notice_status =Crypt::decrypt($request->notice_status);
		if (is_null($master_id)) {
			$master_id =0; 
		}
		if ($legal_type_id!=null) {
			$fieldName = getLegalType($legal_type_id)->field_name; 
		}else{
			$fieldName =''; 
		}			
		$data =array();
		$data['notice_id']=generateId(); 
		$data['matter_id']=$matter_id;
		if ($fieldName!='') {
			$data[$fieldName]=$request->$fieldName; 
		} 			
		$data['notice_type_id']=$request->notice_type;			
		$data['title']=$request->title;
		$data['notice_status_id']=$notice_status;
		$data['location_id']=$location_id;
		$data['default_department_id']=$department_id;
		$data['notice_date']=$request->notice_date;
		if($request->notice_type == 1){
			$data['send_received_date']=$request->sent_date;
		}else{
			$data['send_received_date']=$request->received_date;
		}
		$data['relief_claimed']=$request->relief_claimed;
		$data['additional_remarks']=$request->additional_remarks;
		$data['criticality_risk_id']=$request->criticality_risk;
		$data['legal_category_id']=Crypt::decrypt($request->legal_category); 
		$data['notice_category_id']=!empty($request->notice_category)?Crypt::decrypt($request->notice_category):null; 
		if (!empty($request->act)) {	
			$data['act_id']=implode(',', decrypt_array($request->act));
		} 		
		if (!empty($request->legal_team)) {	
			$data['legal_team_id']=implode(',', $request->legal_team);
		}
		$data['owner_id']=$request->owner;
		
		$data['lawyer_type_id']=$request->lawyer_type;
		if($request->lawyer_type == 2){
			$data['lawyers'] = implode(',', decrypt_array($request->lawyers));
		}
		$data['company_id']=getSetCompanyId();
		$data['created_by']=!empty($request->owner)?$request->owner:getUserId(); 
		$data['status']=1;	 
		$id =$notice->insArr($data);		
		if ($request->matter!=null) {
		$gereralHelper =new generalHelper();
		$gereralHelper->storeMasterTree($matter_id,$data['notice_id'],$master_id,2,getSetCompanyId(),getUserId());
	   }
		// $this->laywerStore($data,$request);
		$this->opponentsStore($data,$request);
		$MailHelper = new MailHelper();
		if(!empty($id)){
			$MailHelper->noticeadd($data);
		}
		$response=array();
		$response["status"]=1;
		$response["msg"]="Save Successfully";
		return $response;

	}catch(\Exception $e){
		Log::error('NoticeController-store: '.$e->getMessage()); 		
		return view('error.home');									
		return false;
	}
}

public function update(Request $request,$notice_id){
	try{  
		$rules=[
			'notice_id'=>'nullable',
			'title'=>'required',
			'notice_status'=>'required',
			'legal_category'=>'required',
			'act'=>'required',
			'opponents_type.*' => 'required',
			'opponents_name.*' => 'required',
			'lawyer_type' => 'required',
			'legal_team' => 'required',
			'owner' => 'required',
			'lawyers' => 'required_if:lawyer_type,==,2',
			'opponents_mobile.*' => 'nullable|numeric|digits_between:10,12',
			'received_at'=>"required_if:notice_type,==,2",
			'department'=>"nullable",
			'notice_date'=>'required|date|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
			'sent_date'=>'nullable|required_if:notice_type,==,1|date|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
			'received_date'=>'nullable|required_if:notice_type,==,2|date|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
			'relief_claimed'=>'nullable',
			'additional_remarks'=>'nullable',
			'criticality_risk'=>'nullable',
			'update_remark'=>'required', 

		];
		$validator = Validator::make($request->all(),$rules);
		if ($validator->fails()) {
			$errors = $validator->errors()->all();
			$response=array();
			$response["status"]=0;
			$response["msg"]=$errors[0];
			return response()->json($response);// response as json
		} 
		$location_id=null;  
		if ($request->has('received_at')) {
			$location_id = Crypt::decrypt($request->received_at); 
		}
		$department_id=null;
		if ($request->has('department')) {
			$department_id = Crypt::decrypt($request->department);
		}
		
		
		$notice =new Notice(); 
		$notice_id = Crypt::decrypt($notice_id);
		$notices=$notice->getNoticeById($notice_id);  
		// $lawyer_id =decrypt_array(array_filter($request->lawyer_id));
		$opponent_id =decrypt_array(array_filter($request->opponent_id));	
		if ($request->matter!=null) {
			 $matter_id =Crypt::decrypt($request->matter);	 
		}else{
			$matter_id =null;	 
		}

		$notice_status =Crypt::decrypt($request->notice_status); 

		$data =array();
				
		$data['notice_id']=$notice_id;			
		$data['matter_id']=$matter_id;			
		$data['title']=$request->title;
		$data['notice_status_id']=$notice_status;
		$data['location_id']=$location_id;
		$data['default_department_id']=$department_id;
		$data['notice_date']=$request->notice_date;
		if($request->notice_type == 1){
			$data['send_received_date']=$request->sent_date;
		}else{
			$data['send_received_date']=$request->received_date;
		}
		$data['relief_claimed']=$request->relief_claimed;
		$data['additional_remarks']=$request->additional_remarks;
		$data['criticality_risk_id']=$request->criticality_risk;
		$data['legal_category_id']=Crypt::decrypt($request->legal_category); 
		$data['notice_category_id']=!empty($request->notice_category)?Crypt::decrypt($request->notice_category):null; 
		if (!empty($request->act)) {	
			$data['act_id']=implode(',', decrypt_array($request->act));
		} 		
		if (!empty($request->legal_team)) {	
			$data['legal_team_id']=implode(',', $request->legal_team);
		}
		$data['owner_id']=$request->owner;
		 
		$data['lawyer_type_id']=$request->lawyer_type;
		if($request->lawyer_type == 2){
			$data['lawyers'] = implode(',', decrypt_array($request->lawyers));
		}
		$data['company_id']=getSetCompanyId();
		$data['updated_by']=getUserId(); 
		$data['update_remark']=$request->update_remark; 
		$data['created_by']=!empty($request->owner)?$request->owner:getUserId();
		$id =$notice->updateArr($notice_id,$data); 

		// $this->laywerUpdate($lawyer_id,$data,$request);
		$this->opponentsUpdate($opponent_id,$data,$request);
		$MailHelper = new MailHelper();
		if(!empty($id) && $notice_status != $notices->notice_status_id){
			$MailHelper->noticeStatusChange($notices);
		}
		$response=array();
		$response["status"]=1;
		$response["msg"]="Update Successfully";
		return $response;

	}catch(\Exception $e){
		Log::error('case add page: '.$e->getMessage()); 		
		return view('error.home');									
		return false;
	}
}

//laywerStore
// public function laywerStore($dataArr,$request){
// 	try { 
// 		foreach ($request->lawyer_name as $key=>$value) {
// 			$lawer = new Lawyer();	
// 			$data =array();
// 			$data['reference_id']=$dataArr['notice_id']; 
// 			$data['legal_type_id']=2;  
// 			$data['matter_id']=$dataArr['matter_id']; 			 
// 			$data['company_id']=$dataArr['company_id']; 
// 			$data['created_by']=$dataArr['created_by']; 
// 			$data['name']=$request->lawyer_name[$key];
// 			$data['mobile_no']=$request->lawyer_mobile[$key];
// 			$data['address']=$request->lawyer_address[$key];
// 			$data['email']=$request->lawyer_email[$key]; 
// 			$data['status']=1; 
// 			$lawers =$lawer->insArr($data); 
// 		}

// 	} catch (Exception $e) {
// 		Log::error('CaseControllerlaywerStore: '.$e->getMessage()); 		
// 		return view('error.home'); 
// 	}
// }

//laywerStore
public function opponentsStore($dataArr,$request){
	try { 
		foreach ($request->opponents_name as $key=>$value) {
			$opponent = new Opponents();	
			$data =array();
			$data['reference_id']=$dataArr['notice_id'];  
			$data['legal_type_id']=2;    
			$data['matter_id']=$dataArr['matter_id'];  
			$data['company_id']=$dataArr['company_id']; 
			$data['created_by']=$dataArr['created_by']; 
			$data['name']=$request->opponents_name[$key];
			$data['opponents_type_id']=$request->opponents_type[$key];
			$data['mobile_no']=$request->opponents_mobile[$key];
			$data['address']=$request->opponents_address[$key];
			$data['email']=$request->opponents_email[$key]; 
			$data['status']=1; 
			$opponents =$opponent->insArr($data); 

		}

	} catch (Exception $e) {
		Log::error('CaseControlleropponentsStore: '.$e->getMessage()); 		
		return view('error.home'); 
	}
}

//laywer Update
// public function laywerUpdate($lawyer_id,$dataArr,$request){
// 	try { 
// 		foreach ($request->lawyer_name as $key=>$value) {
// 			$id = '';
// 			$lawer = new Lawyer();				 
// 			if ($key<=count($lawyer_id)-1) { 
// 				$id =$lawyer_id[$key];   
// 			}
// 			$notice_id =$dataArr['notice_id'];	 			
// 			$data =array(); 
// 			if($id == ''){
// 				$data['status']= 1; 
// 				$data['created_by']= getUserId(); 
// 				$data['legal_type_id'] = 2;  	
// 			}
// 			$data['reference_id']= $notice_id;  
// 			$data['matter_id']=$dataArr['matter_id'];  
// 			$data['company_id']=getSetCompanyId(); 
// 			$data['updated_by']=$dataArr['updated_by']; 
// 			$data['name']=$request->lawyer_name[$key];
// 			$data['mobile_no']=$request->lawyer_mobile[$key];
// 			$data['address']=$request->lawyer_address[$key];
// 			$data['email']=$request->lawyer_email[$key];
// 			$lawers =$lawer->updateOrCreateByCaseId($id,$notice_id,$data); 
// 		}

// 	} catch (Exception $e) {
// 		Log::error('case laywerStore func: '.$e->getMessage()); 		
// 		return view('error.home'); 
// 	}
// }

//laywerStore
public function opponentsUpdate($opponent_id,$dataArr,$request){
	try {  
		// dd(count($opponent_id)); 
		foreach ($request->opponents_name as $key=>$value) {
			$id = '';
			$opponent = new Opponents();	
			if ($key<count($opponent_id)) { 
				$id =$opponent_id[$key];   
			}

			// dd($request->opponents_name[$key]);
			$notice_id =$dataArr['notice_id'];	 			
			$data =array(); 
			if($id == ''){
				$data['status'] = 1; 
				$data['created_by']= getUserId(); 
				$data['legal_type_id'] = 2;  	
			}
			$data['reference_id']= $notice_id; 
			$data['matter_id']=$dataArr['matter_id'];  
			$data['company_id']=getSetCompanyId();  
			$data['updated_by']=$dataArr['updated_by']; 
			$data['name']=$request->opponents_name[$key];
			$data['opponents_type_id']=$request->opponents_type[$key];
			$data['mobile_no']=$request->opponents_mobile[$key];
			$data['address']=$request->opponents_address[$key];
			$data['email']=$request->opponents_email[$key];
			// Log::info($data);
			$opponents =$opponent->updateOrCreateByCaseId($id,$notice_id,$data);
		}

	} catch (Exception $e) {
		Log::error('case laywerStore func: '.$e->getMessage()); 		
		return view('error.home'); 
	}
}

public function noticeTypeChnage(Request $request){
	try{   
		$id = $request->id;
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
		$ns = new NoticeStatus();
		$data['type'] = 0;
		$data['id'] = $id;
		$data['status'] = $ns->getNoticeStatusByNoticeTypeId($id);
		return view('Litigation::SelectOption.notice_status',$data)->render();
	}catch(\Exception $e){
		Log::error('case add page: '.$e->getMessage()); 		
		return view('error.home');									
		return false;
	}
}

public function statuschange(Request $request){
	try{   
		$id = $request->id;
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
		$ns = new NoticeStatus();
		$data['type'] = 1;
		$data['id'] = $id;
		$data['status'] = $ns->getNoticeStatusByNoticeTypeId($id);
		return view('Litigation::SelectOption.notice_status',$data)->render();
	}catch(\Exception $e){
		Log::error('case add page: '.$e->getMessage()); 		
		return view('error.home');									
		return false;
	}
}

public function statuschangemultiple(Request $request){
	try{ 
		if($request->type_id != 'null'){ 
			$id = explode(',',$request->type_id);
			$ns = new NoticeStatus();
			$data['type'] = 1;
			$data['id'] = $id;
			$data['status'] = $ns->getNoticeStatusByNoticeTypeId($id);
			return view('Litigation::SelectOption.notice_status',$data)->render();	
		} else{
			$data['type'] = 1;
			$data['id'] = array();
			return view('Litigation::SelectOption.notice_status',$data)->render();
		}
	}catch(\Exception $e){
		Log::error('case add page: '.$e->getMessage()); 		
		return view('error.home');									
		return false;
	}
}

public function noticeLinkMatter(Request $request){
	try{    
		$id = Crypt::decrypt($request->notice_id);
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

		 $notice = new Notice();
		 $notices = $notice->getNoticeById($id);		 
		 $matter = new Matters();
		 $matters = $matter->getMatterByCompanyId(getSetCompanyId());
		 $data =array();
		 $data['notices']=$notices;
		 $data['matters']=$matters;
		return view('Litigation::Notice.notice_link_matter',$data)->render();
	}catch(\Exception $e){
		Log::error('case add page: '.$e->getMessage()); 		
		return view('error.home');									
		return false;
	}
}
public function noticeLinkMatterStore(Request $request){
	try{    
		$notice_id = Crypt::decrypt($request->notice_id);
		$rules=[
			'matter'=>'required', 
			'notice_id'=>'required', 
			'update_remark'=>'required', 
		];
		$validator = Validator::make($request->all(),$rules);
		if ($validator->fails()) {
			$errors = $validator->errors()->all();
			$response=array();
			$response["status"]=0;
			$response["msg"]=$errors[0];
			return response()->json($response);// response as json
		}

		$notice = new Notice();
		$data =array();
		$data['matter_id'] = Crypt::decrypt($request->matter);
		$data['update_remark']=$request->update_remark;
		$notices =$notice->updateArr($notice_id,$data); 
		$gereralHelper =new generalHelper();
		$gereralHelper->storeMasterTree($data['matter_id'],$notice_id,0,2,getSetCompanyId(),getUserId());
		$response=array();
		$response["status"]=1;
		$response["msg"]="Notice Linked With Matter Successfully";
		return $response;
	}catch(\Exception $e){
		Log::error('NoticeController-noticeLinkMatterStore: '.$e->getMessage()); 		
		return view('error.home');									
		return false;
	}
}


}

