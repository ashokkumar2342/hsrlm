<?php 
namespace App\Modules\Litigation\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\AppUsers;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\ExpenseType;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\LawFirm;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\NextAction;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\Opponents;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use App\Reports\MyReport;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Redirect;
use Session;
use URL;
use Validator;
use generalHelper;					// log for exception handling
use usersSessionHelper;					// log for exception handling
class UserDashboardController extends Controller
{

	public function __construct(){
		 $this->middleware('user');
	}

	public function index(Request $request){
		try{  
			$data = $arr = array();
			$arr['childId'] = getChildUserId();

			$matter = new Matters();
			$matters = $matter->getMatterByCompanyId(getSetCompanyId());

			$Cases = new Cases(); 
			$cases = $Cases->getResult($arr,'openCases');
			$byCases = $Cases->getResult($arr,'byCases');
			$againstCases = $Cases->getResult($arr,'againstCases');
			
			$Notice = new Notice();
			$notices = $Notice->getResult($arr,'openNotice'); 
			$receivedNotices = $Notice->getResult($arr,'receivedNotice'); 
			$sentNotices = $Notice->getResult($arr,'sentNotice'); 
			$LawFirm = new LawFirm();
			$lawFirms = $LawFirm->getFirm(getSetCompanyId()); 
			
			$Hearing = new Hearing();
			$arr['childId'] = getChildUserId();
			$hearings = $Hearing->getResult($arr,'hearing_status'); 
			$hearingUpcoming = $Hearing->getResult($arr,'hearing_upcoming'); 
			$hearingPending = $Hearing->getResult($arr,'hearing_pending'); 

			$NextAction = new NextAction(); 
			$data['nextActions'] = $NextAction->getResult($arr,'nextaction_open');
			$data['nextPending'] = $NextAction->getResult($arr,'nextaction_pending');
			$data['nextUpcoming'] = $NextAction->getResult($arr,'nextaction_upcoming');

			$val = $this->datetobydatefrom(4);
			$data['from'] = $arr['from'] = $val['from'];
			$data['to'] = $arr['to'] = $val['to'];

			$data['super_critical'] = $Cases->getResult($arr,'super_critical',1);
			$data['critical'] = $Cases->getResult($arr,'critical',1);
			$data['important'] = $Cases->getResult($arr,'important',1);
			$data['routine'] = $Cases->getResult($arr,'routine',1);
			$data['normal'] = $Cases->getResult($arr,'normal',1);

			$ExpenseType = new ExpenseType();
			$data['expenseTypes'] = $ExpenseType->getExpenseType(); 			 
			$data['cases']=$cases;
			$data['byCases']=$byCases;
			$data['againstCases']=$againstCases;
			$data['matters']=$matters;
			$data['hearings']=$hearings;
			$data['hearingUpcoming']=$hearingUpcoming;
			$data['hearingPending']=$hearingPending;
			$data['notices']=$notices;
			$data['sentNotices']=$sentNotices;
			$data['receivedNotices']=$receivedNotices;
		    $data['calender']=$hearings;
		    $data['lawFirms']=$lawFirms;
			return view('Litigation::Dashboard.User.dashboard',$data);
		}catch(\Exception $e){
			Log::error('UserDashboardController-index: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	public function roleSearch(Request $request){
		try{
			$companyId = Crypt::decrypt($request->id);
			$role = new UserRole(); 
			$appUserRole = new AppUserRole(); 
			$rolesIdArr = $appUserRole->getRoleIdArrByCompanyIdUserId($companyId,getUserId()); 
			$companyRoles = $role->getRoleByIdArray($rolesIdArr); 
			$data=array();	
			$data['companyRoles']=$companyRoles;
			return view('Litigation::Dashboard.User.user_role_select_box',$data);
			 

		}catch(\Exception $e){
			Log::error('UserDashboardController-roleSearch: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	public function setCompany(Request $request,$company_id){
		try{ 
			$companyId = Crypt::decrypt($company_id);
			$request->session()->put('userData.set_company_id', $companyId);				 
			return redirect()->back();
		}catch(\Exception $e){
			Log::error('UserDashboardController-setCompany: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//search result list
	public function resultList(Request $request,$id,$type){
		try{ 
			$id = Crypt::decrypt($id);
			$type = Crypt::decrypt($type);
			$case = new Cases();
			$notice = new Notice();
			$opponent = new Opponents();
			$hearing = new Hearing();
			$data=array(); 
			$arr=array();
			$data['type']=$type;
			if ($type=='opponent') {
				$arrCaseId = $opponent->getCaseIdByOpponentName($id,1); 
				$arrNoticeId = $opponent->getCaseIdByOpponentName($id,2); 
				$arr['case_id']=$arrCaseId;
				$arr['notice_id']=$arrNoticeId;
				$caseList = $case->getResult($arr,'caseArrId'); 
				$noticeList = $notice->getResult($arr,'noticeArrId'); 
				$hearingList = $hearing->getResult($arr,'hearingWithArrCaseId'); 
			 	$data['caseList']=$caseList;
			 	$data['noticeList']=$noticeList;
			 	$data['hearingList']=$hearingList;
			}elseif ($type=='case' || $type=='notice') { 
			 	$arr['case_id']=[$id];
			 	$arr['notice_id']=[$id];
			 	$caseList = $case->getResult($arr,'caseArrId'); 
			 	$noticeList = $notice->getResult($arr,'noticeArrId'); 
			 	$hearingList = $hearing->getResult($arr,'hearingWithArrCaseId');
			  	$data['caseList']=$caseList;
			  	$data['noticeList']=$noticeList;
			  	$data['hearingList']=$hearingList;
			}elseif ($type=='byForm') {
				$caseList = $case->getSearchResult($request->title); 
				$noticeList = $notice->getSearchResult($request->title); 	
				$hearingList = $hearing->getSearchResult($request->title); 	
				$data['noticeList']=$noticeList;
			  	$data['caseList']=$caseList;
			  	$data['hearingList']=$hearingList;

			} 
			
			return view('Litigation::Dashboard.User.search_result_list',$data);
		}catch(\Exception $e){
			Log::error('UserDashboardController-setCompany: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	public function globalSearch(Request $reqeust)
	{   
		try{  

			if ($reqeust->id != '') {
			$case = new Cases();
			$notice = new Notice();
			$opponent = new Opponents();
			$caseList = $case->getSearchResult($reqeust->id); 
			$noticeList = $notice->getSearchResult($reqeust->id); 	  
			$opponents = $opponent->getSearchResult($reqeust->id); 	  
			$data=array();
			$data['caseList']=$caseList;
			$data['noticeList']=$noticeList; 
			$data['opponents']=$opponents; 
			return view('Litigation::Dashboard.User.search_result',$data);
			}
			
		 
			 
		}catch(\Exception $e){
			Log::error('CasesController-globalSearch: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//calender show 
	public function calender(Request $request){
		try{
			$month=Crypt::decrypt($request->month);
			$year=Crypt::decrypt($request->year);
			$view_mode=$request->view_mode;
			$rules=[
			$month => 'Numeric|min:1|max:12',
			$year => 'Numeric|min:2000|max:2050',
			$view_mode => 'Numeric|min:1|max:2',
			];

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				return view('error.home');
			}

			$detail=array();
			$company=getSetCompanyId();
			$hearing = new Hearing();
			
			$detail['month']=$month;
			$detail['year']=$year;
			$detail['view_mode']=$view_mode;

			if($view_mode=='2')
			{
				$view_page='Litigation::Dashboard.User.tableview';
				$detail['tableview']['calender']= $hearing->getHearingByCompanyId(getSetCompanyId()); 
			}
			else
			{
				$view_page='Litigation::Dashboard.User.calender';
				$detail['calender']= $hearing->getHearingByCompanyId(getSetCompanyId());
			}
			 
			return response()->view($view_page,$detail);
		}catch(\Exception $e){
			Log::error('UserDashboardController-calender: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function casePopup(Request $request){
		try{
			$to = Crypt::decrypt($request->to);
			$risk = Crypt::decrypt($request->risk);
			$type = Crypt::decrypt($request->type);
			$from = Crypt::decrypt($request->from);
			$likelihood = Crypt::decrypt($request->likelihood);
			$rules=[
				$risk => 'numeric', 
				$type => 'numeric', 
				$likelihood => 'numeric', 
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
			$generalHelper = new generalHelper();
			$data['caseList'] = $generalHelper->getLiklihoodData($from,$to,$likelihood,$risk,$type);
			return view('Litigation::Cases.list_popup',$data)->render();
		}catch(\Exception $e){
			Log::error('UserDashboardController-calender: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function datetobydatefrom($id){
		try{

			$array=array('1'=>'0','2'=>'-7','3'=>'-30','4'=>'-90');
			$val=array();
			if(array_key_exists($id, $array))
			{
				$key=$array[$id];
				$currentdate=date('Y-m-d');
				$fromdate=date('Y-m-d', strtotime($currentdate. " $key days"));

			}			
				
			if($array[$id]<0 && isset($fromdate))
			{
				$val['from']=$fromdate;
				$val['to']=date('Y-m-d');
			}	
			else
			{
				$val['from']=date('Y-m-d');
				$val['to']=$fromdate;
			}

			return $val;
		}catch(\Exception $e){
			Log::error('MatrixReportController-datetobydatefrom: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}
	
}

