<?php 
namespace App\Modules\Litigation\Controllers\MatrixReport;
use App\Http\Controllers\Controller;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\LawyerMaster;
use App\Modules\Litigation\Models\CompanyLocation;
use App\Modules\Litigation\Models\CompanyDepartment;
use App\Modules\Litigation\Models\AdvanceFilterField;
use App\Modules\Litigation\Models\AdvanceFilterMaster;
use App\Modules\Litigation\Models\Defaults\NoticeType;
use App\Modules\Litigation\Models\Defaults\CaseStatus;	
use App\Modules\Litigation\Models\Defaults\LawyerType;
use App\Modules\Litigation\Models\Defaults\NoticeStatus;	
use App\Modules\Litigation\Models\Defaults\OpponentsType;	
use App\Modules\Litigation\Models\Defaults\LegalCategory;	
use App\Modules\Litigation\Models\Defaults\CriticalityRisk;	
use App\Modules\Litigation\Models\AdvanceFilterMasterCase;
use Auth;
use Session;
use Validator;		
use mailHelper;	
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;	
use Illuminate\Support\Facades\Crypt;
class MatrixReportController extends Controller
{
	protected $user;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	
	
	public function index(Request $request){
		try{
			$data=array();
			$data["showingFilter"]=0;
			$data["filterData"]=array();
			$data["dateby"] = getDateBy();
			$data["fselectedData"]=array();
			$AdvanceFilterField = new AdvanceFilterField();

			//notice field data
			$AppUsers = new AppUsers();
			$LawyerMaster = new LawyerMaster();
			$LegalCategory = new LegalCategory();
			$CompanyLocation = new CompanyLocation();
			$CompanyDepartment = new CompanyDepartment();

			$data['noticeField'] = $AdvanceFilterField->getField([2]);
			$data['categoryList'] = $LegalCategory->getLegalCategory();
			$data['lawyerList'] = $LawyerMaster->getLawyers(getSetCompanyId());
			$data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());
			$data['deptList'] = $CompanyDepartment->getDepartmentlist(getSetCompanyId());
			$data['locationList'] = $CompanyLocation->getCompanyLocation(getSetCompanyId());
			//notice field data

			$data["savedFilters"] = $this->getSavedFilters(2);
			$data["searchCaseFormURL"]=url('matrixreport/searchcase');
			$data["searchNoticeFormURL"]=url('matrixreport/searchnotice');
			$data["advanceFilterUrl"]=url('matrixreport/advancefilternotice');

			return view('Litigation::MatrixReport.view',$data);
		}catch(\Exception $e){
			Log::error('MatrixReportController-index: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}

	public function getcaseform(Request $request){
		try{
			$data=array();
			$data["showingFilter"]=0;
			$data["filterData"]=array();
			$data["dateby"] = getDateBy();
			$data["fselectedData"]=array();
			$AdvanceFilterField = new AdvanceFilterField();

			//case field data
			$AppUsers = new AppUsers();
			$CaseStatus = new CaseStatus();
			$LawyerMaster = new LawyerMaster();
			$LegalCategory = new LegalCategory();

			$data['caseField'] = $AdvanceFilterField->getField([1,4]);
			$data['categoryList'] = $LegalCategory->getLegalCategory();
			$data['lawyerList'] = $LawyerMaster->getLawyers(getSetCompanyId());
			$data['statusList'] = $CaseStatus->getCaseStatus(getSetCompanyId());
			$data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());
			//notice field data

			$data["savedFilters"] = $this->getSavedFiltersCase();
			$data["searchCaseFormURL"]=url('matrixreport/searchcase');
			$data["advanceFilterUrl"]=url('matrixreport/advancefiltercase');

			return view('Litigation::MatrixReport.caseform',$data);
		}catch(\Exception $e){
			Log::error('MatrixReportController-getcaseform: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}

	function getSavedFilters($id){
		try{
			$AdvanceFilterMaster=new AdvanceFilterMaster();
			return $AdvanceFilterMaster->getSavedFilters(getUserId());
		}catch(\Exception $e){
			Log::error('MatrixReportController-getSavedFilters: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	function getSavedFiltersCase(){
		try{
			$AdvanceFilterMasterCase = new AdvanceFilterMasterCase();
			return $AdvanceFilterMasterCase->getSavedFilters(getUserId());
		}catch(\Exception $e){
			Log::error('MatrixReportController-getSavedFilters: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}


	//notice
	public function searchNoticeResult(Request $request){
		try{

			$rules=[
				"notice_type" => "nullable",
				"location" => "nullable",
				"notice_status" => "nullable",
				"department" => "nullable",
				"legal_category" => "nullable",
				"criticality" => "nullable",
				"lawyer" => "nullable",
				"owner" => "nullable",
				"filter_by_date"=>"required|numeric",
				"filter_date_range"=>"required_if:filter_by_date,==,7",
				"search_form"=>"required|string",
				"advanceselect"=>"array|required_if:search_form,==,advanced",
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);
				// response as json
			}

			// $ClientCompliances = new ClientCompliances();
			$AdvanceFilterField = new AdvanceFilterField();
			$data=array(); 
			$where=$request->all();
			$cryptarray=array('location','notice_status','department','legal_category','act','lawyer','owner');
			foreach($where as $key => $value)
			{
				if(in_array($key, $cryptarray))
				{
					$cc=0;
					if(is_array($where[$key]))
					{
						foreach($where[$key] as $k)
						{
							if($k!=null)
							{
								$where[$key][$cc]=Crypt::decrypt($k);
							}
							$cc++;
						}
					}
					else
					{
						$where[$key]=Crypt::decrypt($value);
					}	
				}	
			}
			$user_id=getUserId();
			$user_type=$request->session()->get('userData.user_type');

			if($request->search_form=='advanced')
			{
				$array=$this->getSearchSelectArr($request->all());
				$data['heading']=$AdvanceFilterField->getnameFieldbyselect($array,array(2));
				$data['select']=$AdvanceFilterField->getcolumnnameFieldbyselect($array,array(2));
				$daterange=$request->filter_date_range;
			}
			if($request->filter_by_date==7)
			{
					$val=$this->daterange($daterange);
					$dateto=$val['to'];
					$datefrom=$val['from'];
			}
			elseif($request->filter_by_date==5){
				$val = $this->get_dates_of_quarter();
				$dateto=$val['end'];
				$datefrom=$val['start'];
			}
			elseif($request->filter_by_date==6){
				$val = $this->get_dates_of_quarter('previous');
				$dateto=$val['end'];
				$datefrom=$val['start'];
			}
			elseif($request->filter_by_date==4){
				$dateto = date('Y-m-d');
				$datefrom = date('Y').'-01-01';
			}
			else
			{
				$val=$this->datetobydatefrom($request->filter_by_date);
				$dateto=$val['to'];
				$datefrom=$val['from'];
			}
			$select=$this->multitoassoc($data["select"]);
			$data['select']=$select;
			$select=$this->implode($select);
//dd($where);

			$where['notice_type'] = $request->notice_type;
			$where['criticality'] = $request->criticality;
			$Notice = new Notice();
			$result = $Notice->matrixreportsearch($user_id,$user_type,$datefrom,$dateto,$where,$select);
			$data['result']=$result;

			$response=array();
			$response["status"]=1;
			$response['data'] = view('Litigation::MatrixReport.basicview',$data)->render();
			return response()->json($response);
			
			
		}catch(\Exception $e){
			Log::error('MatrixReportController-searchNoticeResult: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}

	public function searchpivotnotice(Request $request){
		try{
			$rules=[
				"notice_type" => "nullable",
				"location" => "nullable",
				"notice_status" => "nullable",
				"department" => "nullable",
				"legal_category" => "nullable",
				"criticality" => "nullable",
				"lawyer" => "nullable",
				"owner" => "nullable",
				"filter_by_date"=>"required|numeric",
				"filter_date_range"=>"required_if:filter_by_date,==,7",
				"search_form"=>"required|string",
				"advanceselect"=>"array|required_if:search_form,==,advanced",
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);
				// response as json
			}

			// $ClientCompliances = new ClientCompliances();
			$AdvanceFilterField = new AdvanceFilterField();
			$data=array(); 
			$where=$request->all();
			$cryptarray=array('location','notice_status','department','legal_category','act');
			foreach($where as $key => $value)
			{
				if(in_array($key, $cryptarray))
				{
					$cc=0;
					if(is_array($where[$key]))
					{
						foreach($where[$key] as $k)
						{
							if($k!=null)
							{
								$where[$key][$cc]=Crypt::decrypt($k);
							}
							$cc++;
						}
					}
					else
					{
						$where[$key]=Crypt::decrypt($value);
					}	
				}	
			}
			$user_id=getUserId();
			$user_type=$request->session()->get('userData.user_type');

			if($request->search_form=='advanced')
			{
				$array=$this->getSearchSelectArr($request->all());
				$data['heading']=$AdvanceFilterField->getnameFieldbyselect($array,array(2));
				$data['select']=$AdvanceFilterField->getcolumnnameFieldbyselect($array,array(2));
				$daterange=$request->filter_date_range;
			}
			if($request->filter_by_date==7)
			{
					$val=$this->daterange($daterange);
					$dateto=$val['to'];
					$datefrom=$val['from'];
			}
			elseif($request->filter_by_date==5){
				$val = $this->get_dates_of_quarter();
				$dateto=$val['end'];
				$datefrom=$val['start'];
			}
			elseif($request->filter_by_date==6){
				$val = $this->get_dates_of_quarter('previous');
				$dateto=$val['end'];
				$datefrom=$val['start'];
			}
			elseif($request->filter_by_date==4){
				$dateto = date('Y-m-d');
				$datefrom = date('Y').'-01-01';
			}
			else
			{
				$val=$this->datetobydatefrom($request->filter_by_date);
				$dateto=$val['to'];
				$datefrom=$val['from'];
			}
			$select=$this->multitoassoc($data["select"]);
			$data['select']=$select;
			$select=$this->implode($select);
//dd($where);

			$where['notice_type'] = $request->notice_type;
			$where['criticality'] = $request->criticality;
			$Notice = new Notice();
			$result = $Notice->matrixreportsearch($user_id,$user_type,$datefrom,$dateto,$where,$select);
			$data['result']=$result;

			$response=array();
			$response["status"]=1;
			$response['data'] = view('Litigation::MatrixReport.pivot',$data)->render();
			return response()->json($response);
			//return view('Compliance::MatrixReport.pivot',$data);
			
			
		}catch(\Exception $e){
			Log::error('MatrixReportController-searchpivotnotice: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}

	public function loadAdvanceFilterNotice(Request $request){
		try{
			$rules=[
				'id' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$response=array();
				$response["status"]=0;
				$response["msg"]="Error";
				return response()->json($response);
			}
			$id=Crypt::decrypt($request->id);
			$data=array();
			$data["dateby"] = getDateBy();
			

			$AppUsers = new AppUsers();
			$LawyerMaster = new LawyerMaster();
			$LegalCategory = new LegalCategory();
			$CompanyLocation = new CompanyLocation();
			$CompanyDepartment = new CompanyDepartment();
			$AdvanceFilterField = new AdvanceFilterField();
			$AdvanceFilterMaster = new AdvanceFilterMaster();

			
			$data['noticeField'] = $AdvanceFilterField->getField([2]);
			$data['categoryList'] = $LegalCategory->getLegalCategory();
			if($id != 0){
				$data['filterData'] = 1;
				$data['masterField'] = $AdvanceFilterMaster->getFilterData($id);
			}else{
				$data['filterData'] = array();
			}
			$data['lawyerList'] = $LawyerMaster->getLawyers(getSetCompanyId());
			$data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());
			$data['deptList'] = $CompanyDepartment->getDepartmentlist(getSetCompanyId());
			$data['locationList'] = $CompanyLocation->getCompanyLocation(getSetCompanyId());
			
			if($id != 0){
				return view('Litigation::MatrixReport.Notice.noticeChangeForm',$data);
			}else{
				$data["fselectedData"]=array();
				return view('Litigation::MatrixReport.Notice.form',$data);
			}
		}catch(\Exception $e){
			Log::error('MatrixReportController-loadAdvanceFilterNotice: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}

	//notice


	//case
	public function searchCaseResult(Request $request){
		try{

			$rules=[
				"case_position"=>"nullable",
				"court_type"=>"nullable",
				"case_status"=>"nullable",
				"legal_category"=>"nullable",
				"criticality"=>"nullable",
				"kmp_involved"=>"nullable",
				"case_year"=>"nullable",
				"owner"=>"nullable",
				"potential"=>"nullable",
				"filter_by_date"=>"required|numeric",
				"filter_date_range"=>"required_if:filter_by_date,==,7",
				"search_form"=>"required|string",
				"advanceselect"=>"array|required_if:search_form,==,advanced",
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);
				// response as json
			}

			// $ClientCompliances = new ClientCompliances();
			$AdvanceFilterField = new AdvanceFilterField();
			$data=array(); 
			$where=$request->all();
			foreach($where as $key => $value)
			{	
				if(in_array($key,array('potential','criticality'))){
					$where[$key] = $value;	
				}elseif(in_array($key,array('kmp_involved'))){
					$where[$key]=Crypt::decrypt($value);
				}elseif(in_array($key,array('case_position','court_type','case_status','legal_category','case_year','owner'))){
					$carray=array();
					foreach($value as $vv){
						$carray[]=Crypt::decrypt($vv);
					}
					$where[$key]=$carray;
				}
			}

			$user_id = getUserId();
			$user_type =$request->session()->get('userData.user_type');

			if($request->search_form=='advanced')
			{
				$array=$this->getSearchSelectArr($request->all());
				$data['heading']=$AdvanceFilterField->getnameFieldbyselect($array,[1,4]);
				$data['select']=$AdvanceFilterField->getcolumnnameFieldbyselect($array,[1,4]);
				$daterange=$request->filter_date_range;
			}
			if($request->filter_by_date==7)
			{
					$val=$this->daterange($daterange);
					$dateto=$val['to'];
					$datefrom=$val['from'];
			}
			elseif($request->filter_by_date==5){
				$val = $this->get_dates_of_quarter();
				$dateto=$val['end'];
				$datefrom=$val['start'];
			}
			elseif($request->filter_by_date==6){
				$val = $this->get_dates_of_quarter('previous');
				$dateto=$val['end'];
				$datefrom=$val['start'];
			}
			elseif($request->filter_by_date==4){
				$dateto = date('Y-m-d');
				$datefrom = date('Y').'-01-01';
			}
			else
			{
				$val=$this->datetobydatefrom($request->filter_by_date);
				$dateto=$val['to'];
				$datefrom=$val['from'];
			}
			$select=$this->multitoassoc($data["select"]);
			$select=$this->implode($select);
//dd($where);
			$kmp = getModelId('KmpInvolved');
			$Cases = new Cases();
			$result = $Cases->matrixreportsearch($user_id,$user_type,$datefrom,$dateto,$where,$select,$kmp);
			$data['result']=$result;
			if(count(array_filter((array)$result))>0){
				$caseidarr = array_values(array_unique($result->pluck('ref_id')->toarray()));
			}else{
				$caseidarr = array();
			}
			foreach($data["select"] as $i)
			{	
				$val[] = $i['column_name'];	
			}
			if(array_search('cases.lawyers',$val)){
				if(!empty($caseidarr)){
					$list = array();
					$arr = $caseidarr;
					for($l=0;$l<count($arr);$l++){
						$lawyerresult = $Cases->getLawyerList($arr[$l]);
						$case_id = $arr[$l];
						if(count(array_filter((array)$lawyerresult))>0){
							$lname = explode(',',$lawyerresult->name);
							$email = explode(',',$lawyerresult->email);
							$designation = explode(',',$lawyerresult->designation);
							$experience = explode(',',$lawyerresult->experience);
							$law_firm_id = explode(',',$lawyerresult->law_firm_id);
							$list[$case_id]['name'] = $lname;
							$list[$case_id]['email'] = $email;
							$list[$case_id]['experience'] = $experience;
							$list[$case_id]['designation'] = $designation;
							$list[$case_id]['law_firm_id'] = $law_firm_id;
						}
					}
					$data['lawyerList'] = $list;
					$data['maxcount'] = max($Cases->getLawyerCount(array_unique($caseidarr))->pluck('count')->toarray());
				}
			}
			if(array_search('cases.opponents',$val)){
				if(!empty($caseidarr)){
					$list = array();
					for($l=0;$l<count($caseidarr);$l++){
						$case_id = $caseidarr[$l];
						$opponentresult = $Cases->getOpponentList($case_id);
						if(count(array_filter((array)$opponentresult))>0){
							$name = explode(',',$opponentresult->name);
							$email = explode(',',$opponentresult->email);
							$address = explode(',',$opponentresult->address);
							$mobile_no = explode(',',$opponentresult->mobile_no);
							$list[$case_id]['name'] = $name;
							$list[$case_id]['email'] = $email;
							$list[$case_id]['address'] = $address;
							$list[$case_id]['mobile_no'] = $mobile_no;
						}
					}
					$data['opponentList'] = $list;
					$data['max_o_count'] = max($Cases->getOpponentCount($caseidarr)->pluck('count')->toarray());
				}
			}
			$data['select']=$val;
			$response=array();
			$response["status"]=1;
			$response['data'] = view('Litigation::MatrixReport.basicview',$data)->render();
			return response()->json($response);
			
			
		}catch(\Exception $e){
			Log::error('MatrixReportController-searchCaseResult: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}

	public function searchpivotcase(Request $request){
		try{
			$rules=[
				"case_position"=>"nullable",
				"court_type"=>"nullable",
				"case_status"=>"nullable",
				"legal_category"=>"nullable",
				"criticality"=>"nullable",
				"kmp_involved"=>"nullable",
				"case_year"=>"nullable",
				"owner"=>"nullable",
				"potential"=>"nullable",
				"filter_by_date"=>"required|numeric",
				"filter_date_range"=>"required_if:filter_by_date,==,7",
				"search_form"=>"required|string",
				"advanceselect"=>"array|required_if:search_form,==,advanced",
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);
				// response as json
			}

			// $ClientCompliances = new ClientCompliances();
			$AdvanceFilterField = new AdvanceFilterField();
			$data=array(); 
			$where=$request->all();
			foreach($where as $key => $value)
			{	
				if(in_array($key,array('potential','criticality'))){
					$where[$key] = $value;	
				}elseif(in_array($key,array('kmp_involved'))){
					$where[$key]=Crypt::decrypt($value);
				}elseif(in_array($key,array('case_position','court_type','case_status','legal_category','case_year','owner'))){
					$carray=array();
					foreach($value as $vv){
						$carray[]=Crypt::decrypt($vv);
					}
					$where[$key]=$carray;
				}
			}

			$user_id = getUserId();
			$user_type =$request->session()->get('userData.user_type');

			if($request->search_form=='advanced')
			{
				$array=$this->getSearchSelectArr($request->all());
				$data['heading']=$AdvanceFilterField->getnameFieldbyselect($array,[1,4]);
				$data['select']=$AdvanceFilterField->getcolumnnameFieldbyselect($array,[1,4]);
				$daterange=$request->filter_date_range;
			}
			if($request->filter_by_date==7)
			{
					$val=$this->daterange($daterange);
					$dateto=$val['to'];
					$datefrom=$val['from'];
			}
			elseif($request->filter_by_date==5){
				$val = $this->get_dates_of_quarter();
				$dateto=$val['end'];
				$datefrom=$val['start'];
			}
			elseif($request->filter_by_date==6){
				$val = $this->get_dates_of_quarter('previous');
				$dateto=$val['end'];
				$datefrom=$val['start'];
			}
			elseif($request->filter_by_date==4){
				$dateto = date('Y-m-d');
				$datefrom = date('Y').'-01-01';
			}
			else
			{
				$val=$this->datetobydatefrom($request->filter_by_date);
				$dateto=$val['to'];
				$datefrom=$val['from'];
			}
			$select=$this->multitoassoc($data["select"]);
			$select=$this->implode($select);
//dd($where);
			$kmp = getModelId('KmpInvolved');
			$Cases = new Cases();
			$result = $Cases->matrixreportsearch($user_id,$user_type,$datefrom,$dateto,$where,$select,$kmp);
			$data['result'] = $result;
			if(count(array_filter((array)$result))>0){
				$caseidarr = $result->pluck('ref_id')->toarray();
			}else{
				$caseidarr = array();
			}
			foreach($data["select"] as $i)
			{	
				$val[] = $i['column_name'];	
			}
			if(array_search('cases.lawyers',$val)){
				if(!empty($caseidarr)){
					$arr = array_values(array_unique($caseidarr));
					for($l=0;$l<count($arr);$l++){
						$lawyerresult = $Cases->getLawyerList($arr[$l]);
						$case_id = $arr[$l];
						if(count(array_filter((array)$lawyerresult))>0){
							$lname = explode(',',$lawyerresult->name);
							$email = explode(',',$lawyerresult->email);
							$designation = explode(',',$lawyerresult->designation);
							$experience = explode(',',$lawyerresult->experience);
							$law_firm_id = explode(',',$lawyerresult->law_firm_id);
							$list[$case_id]['name'] = $lname;
							$list[$case_id]['email'] = $email;
							$list[$case_id]['experience'] = $experience;
							$list[$case_id]['designation'] = $designation;
							$list[$case_id]['law_firm_id'] = $law_firm_id;
						}
					}
					$data['lawyerList'] = $list;
					$data['maxcount'] = max($Cases->getLawyerCount(array_unique($caseidarr))->pluck('count')->toarray());
				}
			}
			if(array_search('cases.opponents',$val)){
				if(!empty($caseidarr)){
					$list = array();
					for($l=0;$l<count($caseidarr);$l++){
						$case_id = $caseidarr[$l];
						$opponentresult = $Cases->getOpponentList($case_id);
						if(count(array_filter((array)$opponentresult))>0){
							$name = explode(',',$opponentresult->name);
							$email = explode(',',$opponentresult->email);
							$address = explode(',',$opponentresult->address);
							$mobile_no = explode(',',$opponentresult->mobile_no);
							$list[$case_id]['name'] = $name;
							$list[$case_id]['email'] = $email;
							$list[$case_id]['address'] = $address;
							$list[$case_id]['mobile_no'] = $mobile_no;
						}
					}
					$data['opponentList'] = $list;
					$data['max_o_count'] = max($Cases->getOpponentCount($caseidarr)->pluck('count')->toarray());
				}
			}
			$data['select']=$val;

			$response=array();
			$response["status"]=1;
			$response['data'] = view('Litigation::MatrixReport.pivot',$data)->render();
			return response()->json($response);
			
			
		}catch(\Exception $e){
			Log::error('MatrixReportController-searchpivotcase: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}

	public function loadAdvanceFilterCase(Request $request){
		try{
			$rules=[
				'id' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$response=array();
				$response["status"]=0;
				$response["msg"]="Error";
				return response()->json($response);
			}
			$id=Crypt::decrypt($request->id);
			$data=array();
			$data["dateby"] = getDateBy();

			//case field data
			$AppUsers = new AppUsers();
			$CaseStatus = new CaseStatus();
			$LawyerMaster = new LawyerMaster();
			$LegalCategory = new LegalCategory();
			$AdvanceFilterField = new AdvanceFilterField();

			$data['caseField'] = $AdvanceFilterField->getField([1,4]);
			$data['categoryList'] = $LegalCategory->getLegalCategory();
			$data['lawyerList'] = $LawyerMaster->getLawyers(getSetCompanyId());
			$data['statusList'] = $CaseStatus->getCaseStatus(getSetCompanyId());
			$data['userList'] = $AppUsers->getAllUserByCompanyId(getSetCompanyId());
			//notice field data
			
			$AdvanceFilterMasterCase = new AdvanceFilterMasterCase();
			if($id != 0){
				$data['filterData'] = 1;
				$data['masterField'] = $AdvanceFilterMasterCase->getFilterData($id);
			}else{
				$data['filterData'] = array();
			}
			
			if($id != 0){
				return view('Litigation::MatrixReport.Case.changeForm',$data);
			}else{
				$data["fselectedData"]=array();
				return view('Litigation::MatrixReport.Case.form',$data);
			}
		}catch(\Exception $e){
			Log::error('MatrixReportController-loadAdvanceFilterCase: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}
	//case


	public function getNoticeStatus(Request $request){
		try{
			if($request->type_id != 'null'){
				$id=Crypt::decrypt($request->master_id);
				$rules=[
					$id => 'numeric',
				];
				$validator = Validator::make($request->all(),$rules);
				if ($validator->fails()) {
					$response=array();
					$response["status"]=0;
					$response["msg"]="Error";
					return response()->json($response);
				}
				$data=array();
				
				$NoticeStatus = new NoticeStatus();
				$AdvanceFilterMaster = new AdvanceFilterMaster();
				$result = explode(',',$request->type_id);
				$data['result'] = $AdvanceFilterMaster->getFilterData($id);
				$data['statusList'] = $NoticeStatus->getNoticeStatusByNoticeTypeId($result);
				return view('Litigation::MatrixReport.getnoticestatus',$data);
			}else{
				return view('Litigation::MatrixReport.getnoticestatus');
			}
		}catch(\Exception $e){
			Log::error('MatrixReportController-getNoticeStatus: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}

	public function getAct(Request $request){
		try{
			if($request->act_id != 'null'){
				$id=Crypt::decrypt($request->act_id);
				$rules=[
					$id => 'numeric',
				];
				$validator = Validator::make($request->all(),$rules);
				if ($validator->fails()) {
					$response=array();
					$response["status"]=0;
					$response["msg"]="Error";
					return response()->json($response);
				}
				$master_id=Crypt::decrypt($request->master_id);
				$data=array();
				
				$ActMaster = new ActMaster();
				$AdvanceFilterMaster = new AdvanceFilterMaster();

				$data['result'] = $AdvanceFilterMaster->getFilterData($master_id);
				$data['actList'] = $ActMaster->getActByCatId($id,getSetCompanyId());

				return view('Litigation::MatrixReport.getAct',$data);
			}else{
				return view('Litigation::MatrixReport.getAct');
			}
		}catch(\Exception $e){
			Log::error('MatrixReportController-getAct: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}

	function changenoticetemplate(Request $request){
		try{
			$data["showingFilter"]=0;
			$data["savedFilters"] = $this->getSavedFilters(2);
			$data["advanceFilterUrl"]=url('matrixreport/advancefilternotice');
			return view('Litigation::MatrixReport.Notice.template',$data);
		}catch(\Exception $e){
			Log::error('advanceSearchHelper-changenoticetemplate: '.$e->getMessage());
			return $e;
		}
	}

	function changecasetemplate(Request $request){
		try{
			$data["showingFilter"]=0;
			$data["savedFilters"] = $this->getSavedFiltersCase();
			$data["advanceFilterUrl"]=url('matrixreport/advancefiltercase');
			return view('Litigation::MatrixReport.Case.template',$data);
		}catch(\Exception $e){
			Log::error('advanceSearchHelper-changecasetemplate: '.$e->getMessage()); 
			return $e;
		}
	}

	function getSearchSelectArr($data){
		try{
			$formData=array();
			if(isset($data["advanceselect"]) && $data["search_form"]=="advanced"){
				if(isset($data["advanceselect"]))
					$formData=$data["advanceselect"];
			}
			return $formData;
		}catch(\Exception $e){
			Log::error('advanceSearchHelper-getSearchSelectArr view: '.$e->getMessage()); 		// making log in file
			return $e;
		}
	}

	function getSearchArr($data){
		try{
			$formData=array();
			if(isset($data["advancefilter"]) && $data["search_form"]=="advanced"){
				$formData=$data["advancefilter"];
			}else if(isset($data["basicfilter"]) && $data["search_form"]=="basic"){
				$formData=$data["basicfilter"];
			}
			dd($formData);
			return $formData;
		}catch(\Exception $e){
			Log::error('advanceSearchHelper-getSearchArr view: '.$e->getMessage()); 		// making log in file
			return $e;
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

	public function daterange($daterange){
		try{
			$dates=array();
			$a=explode(' - ', $daterange);
			$a[0]=date("Y-m-d",strtotime($a[0]));
			$a[1]=date("Y-m-d",strtotime($a[1]));
			$dates['to']=$a[1];
			$dates['from']=$a[0];
			return $dates;
		}catch(\Exception $e){
			Log::error('MatrixReportController-daterange: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}


	public function multitoassoc($array){
		try{
			$val=array();
			foreach($array as $i)
			{	
				if($i['column_name'] != "cases.lawyers" && $i['column_name'] != "cases.total_hearing" && $i['column_name'] != "cases.opponents" && $i['column_name'] != "cases.position" && $i['column_name'] != "cases.max_budget" && $i['column_name'] != "cases.applicable_from" && $i['column_name'] != "cases.applicable_to" && $i['column_name'] != "cases.rationale"){
					$val[] = $i['column_name'];	
				}
			}	
			return $val;
		}catch(\Exception $e){
			Log::error('MatrixReportController-multitoassoc: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}

	public function implode($array){
		try{
			$string='';
			foreach($array as $i)
			{
				$st=str_replace(".","_",$i);
				$string.=$i." as ".$st.",";
			}	
			$string=substr($string, 0, -1);
			return $string;
		}catch(\Exception $e){
			Log::error('MatrixReportController-implode: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err
		}
	}

	public function get_dates_of_quarter($quarter = 'current', $year = null, $format = null){
	    if ( !is_int($year) ) {        
	       $year = (new DateTime)->format('Y');
	    }
	    $current_quarter = ceil((new DateTime)->format('n') / 3);
	    switch (  strtolower($quarter) ) {
	    case 'this':
	    case 'current':
	       $quarter = ceil((new DateTime)->format('n') / 3);
	       break;

	    case 'previous':
	       $year = (new DateTime)->format('Y');
	       if ($current_quarter == 1) {
	          $quarter = 4;
	          $year--;
	        } else {
	          $quarter =  $current_quarter - 1;
	        }
	        break;

	    case 'first':
	        $quarter = 1;
	        break;

	    case 'last':
	        $quarter = 4;
	        break;

	    default:
	        $quarter = (!is_int($quarter) || $quarter < 1 || $quarter > 4) ? $current_quarter : $quarter;
	        break;
	    }
	    if ( $quarter === 'this' ) {
	        $quarter = ceil((new DateTime)->format('n') / 3);
	    }
	    $start = date('Y-m-d',strtotime($year.'-'.(3*$quarter-2).'-01'));
	    $end = date('Y-m-d',strtotime($year.'-'.(3*$quarter).'-'.($quarter == 1 || $quarter == 4 ? 31 : 30)));

	    return array(
	        'start' => $start,
	        'end' => $end,
	    );
	}
	
}

