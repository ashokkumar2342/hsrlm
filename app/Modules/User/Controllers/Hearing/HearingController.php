<?php 
namespace App\Modules\Litigation\Controllers\Hearing;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Login\Models\AppUserRole;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\OutcomeType;
use App\Modules\Litigation\Models\ExpenseType;
use App\Modules\Litigation\Models\NextHearing;
use App\Modules\Litigation\Models\ExpenseStatus;
use App\Modules\Litigation\Models\HearingStatus;
use App\Modules\Litigation\Models\SettlementType;
use App\Modules\Litigation\Models\HearingJudgement;
use App\Modules\Litigation\Models\SettlementStatus;
use App\Modules\Litigation\Models\HearingSettlement;
use Auth;
use Validator;
use usersSessionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
class HearingController extends Controller
{ 
	public function index(Request $request){
		try{
			$data = array(); 
			$data['year'] = date('Y');
			return view('Litigation::Hearing.hearing_list',$data);
		}catch(\Exception $e){
			Log::error('HearingController-index: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function getpiechart(Request $request){
		try{
			$data=$arr=array();
			$Hearing = new Hearing();
			$year =  Crypt::decrypt($request->year);
			$arr['from'] = $year.'-01-01';
			$arr['to'] = $year.'-12-31';
			$data['super_critical'] = $Hearing->getResult($arr,'super_critical',1);
			$data['critical'] = $Hearing->getResult($arr,'critical',1);
			$data['important'] = $Hearing->getResult($arr,'important',1);
			$data['routine'] = $Hearing->getResult($arr,'routine',1);
			$data['normal'] = $Hearing->getResult($arr,'normal',1);
			return view('Litigation::Graph.Hearing.piechart',$data);
		}catch(\Exception $e){
			Log::error('HearingController-getpiechart: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function getbarchart(Request $request){
		try{
			$data=$arr=array();
			$Hearing = new Hearing();
			$year =  Crypt::decrypt($request->year);
			$month_select=array("Jan"=>"01","Feb"=>"02","Mar"=>"03","Apr"=>"04","May"=>"05","Jun"=>"06","Jul"=>"07","Aug"=>"08","Sep"=>"09","Oct"=>"10","Nov"=>"11","Dec"=>"12");
			$pending = $upcoming = array();
			foreach ($month_select as $va => $key) {
				$arr['from'] = $year.'-'.$key.'-01';
                $arr['to'] = $year.'-'.$key.'-31';
                $pending[] = $Hearing->getResult($arr,'pending',1);
                $upcoming[] = $Hearing->getResult($arr,'upcoming',1);
			}
			$data['pending'] = implode(',',$pending);
			$data['upcoming'] = implode(',',$upcoming);
			return view('Litigation::Graph.Hearing.barchart',$data);
		}catch(\Exception $e){
			Log::error('HearingController-getbarchart: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function listpopupbyyearpie(Request $request){
		try { 
			$data = $arr = array();
			$Hearing = new Hearing();
			$type = $request->type;
			if($type == 'SUPER CRITICAL'){
				$type = 'super_critical';
			}else{
				$type = strtolower($type);
			}
			$year = Crypt::decrypt($request->year);
			$arr['from'] = $year.'-01-01';
			$arr['to'] = $year.'-12-31';
			$data['hearingList'] = $Hearing->getResult($arr,$type,2);
			return view('Litigation::Hearing.list_popup',$data)->render();
		} catch (Exception $e) {
			Log::error('HearingController-listpopupbyyearpie: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function listpopupbymonthyear(Request $request){
		try { 
			$data = $arr = array();
			$Hearing = new Hearing();
			$type = strtolower($request->type);
			$year = Crypt::decrypt($request->year);
			if(strlen($request->month) < 5){
				$month = $request->month;
			}else{
				$month = Crypt::decrypt($request->month);
			}
			$arr['from'] = $year.'-'.$month.'-01';
			$arr['to'] = $year.'-'.$month.'-31';
			$data['hearingList'] = $Hearing->getResult($arr,$type,2);
			return view('Litigation::Hearing.list_popup',$data)->render();
		} catch (Exception $e) {
			Log::error('HearingController-listpopupbymonthyear: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function show(){	
		try{ 
			$data = $arr = array();
			$Hearing = new Hearing();
			$arr['childId'] = getChildUserId();
			$data['hearingList'] = $Hearing->getResult($arr,'withMatterName');
			return view('Litigation::Hearing.hearing_table',$data)->render();
		}catch(\Exception $e){
			Log::error('CasesController-show: '.$e->getMessage()); 		
			return view('error.home');
		}

	}
	public function listPopup($type){	
		try{ 
			$data = $arr = array();
			$Hearing = new Hearing();
			$arr['childId'] = getChildUserId();
			$data['hearingList'] = $Hearing->getResult($arr,$type);
			return view('Litigation::Hearing.list_popup',$data)->render();
		}catch(\Exception $e){
			Log::error('CasesController-show: '.$e->getMessage()); 		
			return view('error.home');
		}

	}
	public function create(Request $request){
		try{
			$country = new Country();
			$company = new Company(); 
			$act = new ActMaster();
			$acts = $act->getActs();
			$appUserRole = new AppUserRole(); 
			$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
			$companies = $company->getCompanyByArrId($appUserRoleCompanyId);
			$countries = $country->getCountry();
			$data =array();
			$data['countries'] =$countries;
			$data['companies'] =$companies; 
			$data['acts'] =$acts; 
			return view('Litigation::Hearing.hearing_form',$data);
		}catch(\Exception $e){
			Log::error('HearingController-create: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//hearing show popup
	public function popupForm(Request $request)
	{  
		try{
			$matter_id = Crypt::decrypt($request->matter_id);
			$legal_type_id =Crypt::decrypt($request->legal_type_id);
			$rules=[
				$matter_id => 'numeric',
				$legal_type_id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$Cases = new Cases(); 
			$Matters = new Matters(); 
			$Country = new Country();
			$Company = new Company(); 
			$AppUsers = new AppUsers(); 
			$ActMaster = new ActMaster();
			$AppUserRole = new AppUserRole(); 
			$ExpenseType = new ExpenseType(); 
			$ExpenseStatus = new ExpenseStatus();
			$HearingStatus = new HearingStatus();

			$appUserRoleCompanyId = $AppUserRole->getCompanyIdArrayByUserId(getUserId());

			$data =array();
			$data['legal_type_id'] = $legal_type_id; 
			$data['master_id'] = $request->master_id; 

			$data['acts'] = $ActMaster->getActs(); 
			$data['countries'] = $Country->getCountry();
			$data['matter'] = $Matters->getMatterById($matter_id); 
			$data['expenseType'] = $ExpenseType->getExpenseType(); 
			$data['expenseStatus'] = $ExpenseStatus->getExpenseStatus();
			$data['hearingStatus'] = $HearingStatus->getHearingStatus();
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 
			$data['companies'] = $Company->getCompanyByArrId($appUserRoleCompanyId);
		    $data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());
			$data['caseDetail'] = $Cases->getCaseById(Crypt::decrypt($request->master_id));

			if ($legal_type_id==1) {
				$data['case_id'] = $request->master_id;  
			}
			if ($legal_type_id==2) {
				$data['notice_id'] = $request->master_id;  
			}

			return view('Litigation::Hearing.hearing_popup',$data)->render();
		}catch (Exception $e) {
			Log::error('HearingController-popupForm: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//hearing edit
	public function view(Request $request){   
		try {
			$hearing_id = Crypt::decrypt($request->hearing_id); 
			$rules=[
				$hearing_id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$Cases = new Cases(); 
			$Matters = new Matters(); 
			$Hearing = new Hearing();
			$Country = new Country();
			$Company = new Company(); 
			$AppUsers = new AppUsers(); 
			$ActMaster = new ActMaster();
			$AppUserRole = new AppUserRole(); 
			$ExpenseType = new ExpenseType(); 
			$ExpenseStatus = new ExpenseStatus();
			$HearingStatus = new HearingStatus();

			$result = $Hearing->getHearingById($hearing_id);

			$appUserRoleCompanyId = $AppUserRole->getCompanyIdArrayByUserId(getUserId());

			$data =array();
			$data['legal_type_id'] = 4; 
			$data['hearing_id'] = $hearing_id; 
			$data['master_id'] = $result->case_id; 

			$data['hearingResult'] = $result; 
			$data['acts'] = $ActMaster->getActs(); 
			$data['countries'] = $Country->getCountry();
			$data['expenseType'] = $ExpenseType->getExpenseType(); 
			$data['caseDetail'] = $Cases->getCaseById($result->case_id);
			$data['expenseStatus'] = $ExpenseStatus->getExpenseStatus();
			$data['hearingStatus'] = $HearingStatus->getHearingStatus();
			$data['matter'] = $Matters->getMatterById($result->matter_id); 
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 
			$data['companies'] = $Company->getCompanyByArrId($appUserRoleCompanyId);
		    $data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());

			return view('Litigation::Hearing.hearing_edit_form',$data)->render();
		}catch (Exception $e) {
			Log::error('HearingController-view: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
		//hearing store
	public function store(Request $request){
		try{  
			$master_id =Crypt::decrypt($request->master_id); 
			$matter_id =Crypt::decrypt($request->matter_id); 
			$legal_type_id =Crypt::decrypt($request->legal_type_id);
			$rules=[
				$master_id => 'numeric',
				$matter_id => 'numeric',
				$legal_type_id => 'numeric',
				'criticality'=>'required', 
				'hearing_date'=>'required|date|date_format:Y-m-d|after_or_equal:'.date('Y-m-d'),
				'team_responsible'=>'required',
				'expected_agenda'=>'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}  

			$insArr = array();

			$Hearing = new Hearing(); 
			$generalHelper =new generalHelper();
 
			$insArr['status'] = 1;	
			$insArr['case_id'] = $master_id;
			$insArr['matter_id'] = $matter_id;
			$insArr['created_by'] = getUserId();  
			$insArr['hearing_id'] = generateId();
			$insArr['company_id'] = getSetCompanyId();
			$insArr['title'] = $request->expected_agenda;
			$insArr['criticality'] = $request->criticality;
			$insArr['description'] = $request->description;
			$insArr['court_detail'] = $request->court_detail;
			$insArr['coram_detail'] = $request->coram_detail;
			$insArr['hearing_date'] = $request->hearing_date;
			$insArr['notify_id'] = implode(',', decrypt_array($request->team_responsible));

			$Hearing = $Hearing->insArr($insArr);
			$generalHelper->storeMasterTree($matter_id,$insArr['hearing_id'],$master_id,4,getSetCompanyId(),getUserId());

			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;
		}catch(\Exception $e){
			Log::error('HearingController-store: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function chnagegainexpousrebox(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$hearing_id=Crypt::decrypt($request->hearing_id);
			$rules=[
				$id => 'numeric',
				$hearing_id => 'numeric',
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
			$arr['hearing_id'] = $hearing_id;

			$Company = new Company();
			$HearingJudgement = new HearingJudgement();
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 
			$data['result'] = $HearingJudgement->getHearingJudgement($arr,'getAmountByHearingId');

			return view('Litigation::Hearing.chnagegainexpousrebox',$data)->render();
		}catch(\Exception $e){
			Log::error('HearingController-chnagegainexpousrebox: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}

	public function edithearing(Request $request){   
		try {
			$hearing_id = Crypt::decrypt($request->hearing_id); 
			$rules=[
				$hearing_id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
 
			$Hearing = new Hearing();
			$Company = new Company(); 
			$AppUsers = new AppUsers(); 
			$AppUserRole = new AppUserRole(); 

			$arr['id'] = $hearing_id;

			$result = $Hearing->getResult($arr,'hearingWithOwnerId');

			$appUserRoleCompanyId = $AppUserRole->getCompanyIdArrayByUserId(getUserId());

			$data =array();
			$data['legal_type_id'] = 4; 
			$data['hearing_id'] = $hearing_id; 
			$data['master_id'] = $result->case_id; 

			$data['hearingResult'] = $result; 
			$data['companies'] = $Company->getCompanyByArrId($appUserRoleCompanyId);
		    $data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());

			return view('Litigation::Hearing.hearing_edithearing',$data)->render();
		}catch (Exception $e) {
			Log::error('HearingController-edithearing: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function updatehearing(Request $request){   
		try {
			$hearing_id = Crypt::decrypt($request->hearing_id); 
			$rules=[
				$hearing_id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$Cases = new Cases(); 
			$Matters = new Matters(); 
			$Hearing = new Hearing();
			$Country = new Country();
			$Company = new Company(); 
			$AppUsers = new AppUsers(); 
			$ActMaster = new ActMaster();
			$AppUserRole = new AppUserRole(); 
			$ExpenseType = new ExpenseType(); 
			$ExpenseStatus = new ExpenseStatus();
			$HearingStatus = new HearingStatus();

			$arr['id'] = $hearing_id;

			$result = $Hearing->getResult($arr,'hearingWithOwnerId');

			$appUserRoleCompanyId = $AppUserRole->getCompanyIdArrayByUserId(getUserId());

			$data =array();
			$data['legal_type_id'] = 4; 
			$data['hearing_id'] = $hearing_id; 
			$data['master_id'] = $result->case_id; 

			$data['hearingResult'] = $result; 
			$data['acts'] = $ActMaster->getActs(); 
			$data['countries'] = $Country->getCountry();
			$data['expenseType'] = $ExpenseType->getExpenseType(); 
			$data['caseDetail'] = $Cases->getCaseById($result->case_id);
			$data['expenseStatus'] = $ExpenseStatus->getExpenseStatus();
			$data['hearingStatus'] = $HearingStatus->getHearingStatus();
			$data['matter'] = $Matters->getMatterById($result->matter_id); 
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 
			$data['companies'] = $Company->getCompanyByArrId($appUserRoleCompanyId);
		    $data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());

			return view('Litigation::Hearing.hearing_updatehearing',$data)->render();
		}catch (Exception $e) {
			Log::error('HearingController-updatehearing: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function editdetails(Request $request){
		try{  
			$hearing_id =Crypt::decrypt($request->hearing_id); 
			$rules=[
				$hearing_id => 'numeric',
				'hearing_date'=>'required|date|date_format:Y-m-d|after_or_equal:'.date('Y-m-d'),
				'expected_agenda'=>'required',
				'team_responsible'=>'required',
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
			$Hearing = new Hearing();
			$arr['id'] = $hearing_id;
			$updArr['title'] = $request->expected_agenda;
			$updArr['description'] = $request->description;
			$updArr['court_detail'] = $request->court_detail;
			$updArr['coram_detail'] = $request->coram_detail;
			$updArr['hearing_date'] = $request->hearing_date;
			$updHArr['update_remark'] = $request->update_remark;
			$updArr['notify_id'] = implode(',', decrypt_array($request->team_responsible));
			$Hearing->updArr($updArr,$arr,$hearing_id);
			$response=array();
			$response["status"]=1;
			$response["msg"]="Edit Successfully";
			return $response;
		}catch(\Exception $e){
			Log::error('HearingController-editdetails: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function statusdata(Request $request){
		try{
			$id=Crypt::decrypt($request->status_id);
			$hearing_id=Crypt::decrypt($request->hearing_id);
			$rules=[
				$id => 'numeric',
				$hearing_id => 'numeric',
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
			$data['status_id'] = $id;
			$arr['hearing_id'] = $arr['ref_id'] = $data['hearing_id']  = $hearing_id;

			if($id == 1){
				$AppUsers = new AppUsers();  
				$OutcomeType = new OutcomeType();
				$HearingJudgement = new HearingJudgement();
				$data['outcomeType'] = $OutcomeType->getOutcomeType();
				$data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());
				$data['judgementResult'] = $HearingJudgement->getHearingJudgement($arr,'getByHearingId');
			}

			if($id == 2){
				$NextHearing = new NextHearing();
				$data['nextHearingResult'] = $NextHearing->getNextHearing($arr,'getByRefId');
			}

			if($id == 3){
				$SettlementType = new SettlementType();
				$HearingSettlement = new HearingSettlement();
				$data['settlementType'] = $SettlementType->getSettlementType();
				$data['settlementResult'] = $HearingSettlement->getHearingSettlement($arr,'getByHearingId');
			}

			$Company = new Company();
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 

			return view('Litigation::Hearing.hearing_status_data',$data)->render();
		}catch(\Exception $e){
			Log::error('HearingController-statusdata: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}

	public function updatedetails(Request $request){
		try{  
			$Hearing = new Hearing();
			$cases = new Cases();
			$NextHearing = new NextHearing();
			$HearingJudgement = new HearingJudgement();
			$HearingSettlement = new HearingSettlement();

			$hearing_id =Crypt::decrypt($request->hearing_id);
			//get next hearing data
			$arr['ref_id'] = $hearing_id;
			$nextHearingResult = $NextHearing->getNextHearing($arr,'getByRefId');
			//get next hearing data

			$rules=[
				$hearing_id => 'numeric',
				'hearing_status'=>'required', 
				'order_release_date'=>'nullable|date|date_format:Y-m-d',
				'update_remark'=>'required',
			];

			$hearing_status = $request->hearing_status != NULL?Crypt::decrypt($request->hearing_status):'';
			if($hearing_status == 1){
				$rules=[
					'outcome'=>'required',
					'appeal_last_date' => 'nullable|date|date_format:Y-m-d'
				];
				$outcome = $request->outcome != NULL?Crypt::decrypt($request->outcome):'';
				if($outcome == 1){
					$rules['gain_value'] = 'required|numeric';
				}else{
					$rules['exposure_value'] = 'required|numeric';
				}
			}elseif($hearing_status == 3){
				$rules=[
					'settlement_type' => 'required',
					'claim_amount'=>'required|numeric',
					'settled_amount'=>'required|numeric',
				];
			}else{
				if(empty($nextHearingResult)){
					$rules=[
						'criticality' => 'required',
						'next_hearing_date'=>'required|date|date_format:Y-m-d|after_or_equal:'.date('Y-m-d'),
						'expected_agenda'=>'required',
					];
				}
			}

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}  
			$updHArr = $updJArr = $updNHArr = $updSArr = array();

			$arr['id'] = $arr['hearing_id'] = $hearing_id;

			$updHArr['order_no'] = $request->order_no;
			$updHArr['observation'] = $request->observation;
			$updHArr['hearing_status_id'] = $hearing_status;
			$updHArr['order_summary'] = $request->order_summary;
			$updHArr['order_release_date'] = $request->order_release_date;
			$updHArr['update_remark'] = $request->update_remark;
			$id=$Hearing->updArr($updHArr,$arr,'byHearingId');

			if($hearing_status == 1){
				$updJArr['hearing_id'] = $hearing_id;
				$updJArr['comments'] = $request->comments; 
				$updJArr['implication'] = $request->implication; 
				$updJArr['outcome'] = Crypt::decrypt($request->outcome); 

				if($updJArr['outcome'] == 1){
					$updJArr['amount'] = $request->gain_value; 
				}else{
					$updJArr['amount'] = $request->exposure_value; 
				}

				$updJArr['appeal_last_date'] = $request->appeal_last_date; 
				$updJArr['appeal_to_be_filed'] = $request->appeal_to_be_filed; 
				$updJArr['reappeal_expected'] = $request->reappeal_expected; 
				$updJArr['satisfied_with_outcome'] = $request->satisfied_with_outcome; 
				$updJArr['appeal_team'] = implode(',',decrypt_array($request->appeal_team)); 
				$HearingJudgement->createOrUpdate($updJArr,$arr,'byHearingId');
				$hearingResult = $Hearing->getHearingById($hearing_id);
				$cases->updateArr($hearingResult->case_id,['case_status_id'=>12]);
			}elseif($hearing_status == 3){
				$updSArr['hearing_id'] = $hearing_id;
				$updSArr['comments'] = $request->comments;
				$updSArr['claim_amount'] = $request->claim_amount;
				$updSArr['settled_amount'] = $request->settled_amount;
				$updSArr['settlement_type'] = Crypt::decrypt($request->settlement_type);
				$HearingSettlement->createOrUpdate($updSArr,$arr,'byHearingId');
			}else{
				if(empty($nextHearingResult)){
					$insArr = array();
					$generalHelper = new generalHelper();
					$hearingResult = $Hearing->getHearingById($hearing_id);

					$insArr['status'] = 1;
					$insArr['created_by'] = getUserId();
					$insArr['hearing_id'] = generateId();
					$insArr['title'] = $request->expected_agenda;
					$insArr['case_id'] = $hearingResult->case_id;
					$insArr['criticality'] = $request->criticality;
					$insArr['description'] = $request->description;
					$insArr['matter_id'] = $hearingResult->matter_id;
					$insArr['notify_id'] = $hearingResult->notify_id;
					$insArr['company_id'] = $hearingResult->company_id;
					$insArr['hearing_date'] = $request->next_hearing_date;
					$insArr['court_detail'] = $hearingResult->court_detail;
					$insArr['coram_detail'] = $hearingResult->coram_detail;
					$Hearing->insArr($insArr);

					$generalHelper->storeMasterTree($hearingResult->matter_id,$insArr['hearing_id'],$hearingResult->case_id,4,getSetCompanyId(),getUserId());

					$updNHArr['ref_id'] = $hearing_id;
					$updNHArr['title'] = $request->expected_agenda;
					$updNHArr['hearing_id'] = $insArr['hearing_id'];
					$updNHArr['criticality'] = $request->criticality;
					$updNHArr['description'] = $request->description;
					$updNHArr['hearing_date'] = $request->next_hearing_date;
					$NextHearing->createOrUpdate($updNHArr,$arr,'byRefId');
				}
			}
			$MailHelper = new MailHelper();
			if(!empty($id) && in_array($hearing_status, [1,3])){
				$MailHelper->hearingStatusChange($hearing_id);
			}
			$response=array();
			$response["status"]=1;
			$response["msg"]="Update Successfully";
			return $response;
		}catch(\Exception $e){
			Log::error('HearingController-updatedetails: '.$e->getMessage()); 		
			return view('error.home');
		}
	}


}

