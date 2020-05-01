<?php 
namespace App\Modules\Litigation\Controllers\Cases;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\CountryState;
use App\Modules\Litigation\Models\Defaults\AppearingModel;
use App\Modules\Litigation\Models\Defaults\Bench;
use App\Modules\Litigation\Models\Defaults\BenchSide;
use App\Modules\Litigation\Models\Defaults\BenchSideStamp;
use App\Modules\Litigation\Models\Defaults\CaseStatus;
use App\Modules\Litigation\Models\Defaults\CaseType;
use App\Modules\Litigation\Models\Defaults\Commissionerate;
use App\Modules\Litigation\Models\Defaults\CommissionerateAuthority;
use App\Modules\Litigation\Models\Defaults\Commissions;
use App\Modules\Litigation\Models\Defaults\CommissionsState;
use App\Modules\Litigation\Models\Defaults\CommissionsStateDistrict;
use App\Modules\Litigation\Models\Defaults\Court;
use App\Modules\Litigation\Models\Defaults\CourtCategory;
use App\Modules\Litigation\Models\Defaults\CourtEstablishment;
use App\Modules\Litigation\Models\Defaults\HighCourt;
use App\Modules\Litigation\Models\Defaults\KmpInvolved;
use App\Modules\Litigation\Models\Defaults\LegalCategory;
use App\Modules\Litigation\Models\Defaults\RevenueCourt;
use App\Modules\Litigation\Models\Defaults\RevenueDistrict;
use App\Modules\Litigation\Models\Defaults\RevenueDistrictCourt;
use App\Modules\Litigation\Models\Defaults\SupremeCourt;
use App\Modules\Litigation\Models\Defaults\TribunalsAuthorities;
use App\Modules\Litigation\Models\Defaults\TribunalsAuthoritiesState;
use App\Modules\Litigation\Models\Defaults\TribunalsAuthoritiesStateSection;
use App\Modules\Litigation\Models\Expenses;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\LawFirm;
use App\Modules\Litigation\Models\Lawyer;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\Opponents;
use App\Modules\Litigation\Models\StateDistrict;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use App\Modules\Login\Models\AppUsers;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
use usersSessionHelper;
class CasesController extends Controller
{ 
	public function index(Request $request){
		try{
			$data = array(); 
			$data['year'] = date('Y');
			return view('Litigation::Cases.cases_list',$data);
		}catch(\Exception $e){
			Log::error('CasesController-index: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	//bar chart
	public function getbarchart(Request $request){
		try{
			$data=$arr=array();
			$Cases = new Cases();
			$year =  Crypt::decrypt($request->year);
			$month_select=array("Jan"=>"01","Feb"=>"02","Mar"=>"03","Apr"=>"04","May"=>"05","Jun"=>"06","Jul"=>"07","Aug"=>"08","Sep"=>"09","Oct"=>"10","Nov"=>"11","Dec"=>"12");
			$totalsent = $totalreceived = array();
			foreach ($month_select as $va => $key) {
				$arr['from'] = $year.'-'.$key.'-01';
                $arr['to'] = $year.'-'.$key.'-31';
                $by[] = $Cases->getResult($arr,'conutBy');
                $against[] = $Cases->getResult($arr,'conutAgainst');
			}
			$data['by'] = implode(',',$by);
			$data['against'] = implode(',',$against);
			return view('Litigation::Graph.Cases.barchart',$data);
		}catch(\Exception $e){
			Log::error('NoticeController-getbarchart: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}
	//bar chart

	//case show
	public function show(){	
		try{ 
			$data = $arr = array();
			$Cases = new Cases();
			$arr['childId'] = getChildUserId();
			$data['caseList'] = $Cases->getResult($arr,'getFullCase');
			return view('Litigation::Cases.cases_table',$data)->render();
		}catch(\Exception $e){
			Log::error('CasesController-show: '.$e->getMessage()); 		
			return view('error.home');
		}

	}//case list popup
	public function listPopup($type){	
		try{ 
			$data = $arr = array();
			$Cases = new Cases();
			$arr['childId'] = getChildUserId();
			$data['caseList'] = $Cases->getResult($arr,$type);
			return view('Litigation::Cases.list_popup',$data)->render();
		}catch(\Exception $e){
			Log::error('CasesController-show: '.$e->getMessage()); 		
			return view('error.home');
		}

	}
	//case show

	public function listpopupbymonthyear(Request $request){
		try { 
			$data = $arr = array();
			$Cases = new Cases();
			$type = $request->type;
			$year = Crypt::decrypt($request->year);
			if(strlen($request->month) < 5){
				$month = $request->month;
			}else{
				$month = Crypt::decrypt($request->month);
			}
			$arr['from'] = $year.'-'.$month.'-01';
			$arr['to'] = $year.'-'.$month.'-31';
			$data['caseList'] = $Cases->getResult($arr,$type);
			return view('Litigation::Cases.list_popup',$data)->render();
		} catch (Exception $e) {
			Log::error('CasesController-listpopupbymonthyear: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function getpopupbydaterange(Request $request){
		try { 
			$data = $arr = array();
			$Cases = new Cases();
			$type = $request->type;
			if($type == 'SUPER CRITICAL'){
				$type = 'super_critical';
			}else{
				$type = strtolower($type);
			}
			$arr['from'] = Crypt::decrypt($request->from);
			$arr['to'] = Crypt::decrypt($request->to);
			$data['caseList'] = $Cases->getResult($arr,$type,2);
			return view('Litigation::Cases.list_popup',$data)->render();
		} catch (Exception $e) {
			Log::error('CasesController-getpopupbydaterange: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function showByMatterId(Request $request){   
		try{ 
			$matter_id = Crypt::decrypt($request->matter_id);
			$rules=[
				$matter_id => 'numeric',
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
			$data['cases'] = $Cases->getCaseByMatterId($matter_id,getSetCompanyId());
			return view('Litigation::Cases.cases_table',$data)->render();
		}catch(\Exception $e){
			Log::error('CasesController-showByMatterId: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
		//case show popup
	public function popupForm($matter_id,$master_id,$legal_type_id=null)
	{  
		try {
			$data =array();
			$matter_id =Crypt::decrypt($matter_id);
			if ($legal_type_id!=null) {				 
				$legal_type_id =Crypt::decrypt($legal_type_id);
			}
			$Country = new Country();
			$Company = new Company(); 
			$court = new Court(); 
			$LawFirm = new LawFirm(); 
			$legalCategory = new LegalCategory(); 
			$legalCategorys = $legalCategory->getLegalCategory(); 
			$companyDetail = $Company->getDetail(getSetCompanyId()); 
			$courts = $court->getCourt(); 
			$appearingModel = new AppearingModel(); 
			$appearingModels = $appearingModel->getAppearingModel(); 
			$act = new ActMaster();
			$matter = new Matters(); 
			$matter =$matter->getMatterById($matter_id);	
			$appUser = new AppUsers(); 
			$appUsers =$appUser->getAllUserByCompanyId(getSetCompanyId());
			$acts = $act->getActs();
			$appUserRole = new AppUserRole(); 
			$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
			$data['companies'] = $Company->getCompanyByArrId($appUserRoleCompanyId);
			$data['countries'] = $Country->getCountry();
			$data['firmList'] = $LawFirm->getFirm(getSetCompanyId());
			$data['acts'] =$acts;  
			$data['matter']=$matter;
			$data['appUsers']=$appUsers;
			$data['master_id'] =$master_id; 
			$data['legal_type_id'] =$legal_type_id;  
			$data['companyDetail'] =$companyDetail;  
			$data['legalCategorys'] =$legalCategorys;  
			$data['appearingModels'] =$appearingModels;  
			$data['courts'] =$courts;  
			return view('Litigation::Cases.cases_popup',$data)->render();
		}catch (Exception $e) {
			Log::error('CasesController-popupForm: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function view($case_id){  
		try {
			$case_id = Crypt::decrypt($case_id); 
			$case = new Cases();
			$cases = $case->getCaseById($case_id); 
			$country = new Country();
			$court = new Court(); 
			$company = new Company(); 
			$Hearing = new Hearing();
			$LawFirm = new LawFirm(); 
			$Expenses = new Expenses();
			$legalCategory = new LegalCategory(); 
			$legalCategorys = $legalCategory->getLegalCategory(); 
			$companyDetail = $company->getDetail(getSetCompanyId()); 
			$courts = $court->getCourt(); 
			$appearingModel = new AppearingModel(); 
			$appearingModels = $appearingModel->getAppearingModel(); 
			$appUser = new AppUsers(); 
			$appUsers =$appUser->getAllUserByCompanyId(getSetCompanyId());
			$act = new ActMaster(); 
			$acts = $act->getActs();
			$appUserRole = new AppUserRole(); 
			$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
			$companies = $company->getCompanyByArrId($appUserRoleCompanyId);
			$countries = $country->getCountry();
			$masterTree = new MasterTree();
			$masterTrees = $masterTree->getMasterTreeByMasterId($case_id);
			$parent_id = '';
			if ($masterTrees->master_parent_id==0) {
				$parent_id = $cases->matter->id;
			}else{
				$parent_id = $masterTree->getParentDataIdById($masterTrees->master_parent_id)->id;
			}	      
			$lawyer = new Lawyer();
			$lawyers = $lawyer->getLawyerByCaseId($case_id);
			$opponent = new Opponents();	
			$opponents = $opponent->getOpponentByCaseId($case_id);
			$kmp = new KmpInvolved();	
			$kmpInvolveds = $kmp->getAllKmpByCompanyId(getSetCompanyId());

			$data =array();
			$data['firmList'] = $LawFirm->getFirm(getSetCompanyId());
			$data['case_id'] =$case_id;
			$data['countries'] =$countries; 
			$data['masterTrees']=$masterTrees; 
			$data['parent_id']=$parent_id;
			$data['acts'] =$acts;  
			$data['cases'] =$cases;  
			$data['appUsers'] =$appUsers;  
			$data['courts'] =$courts;  
			$data['legalCategorys'] =$legalCategorys;  
			$data['appearingModels'] =$appearingModels;  	 
			$data['companyDetail'] =$companyDetail;  	 
			$data['lawyers'] =$lawyers;  	 
			$data['opponents'] =$opponents;  	 
			$data['kmpInvolveds'] =$kmpInvolveds;  	 
				//total expense
			$arr['id'] = $case_id;
			$getHearing = $Hearing->getResult($arr,'hearingWithCaseId')->pluck('hearing_id')->toarray();
			array_push($getHearing, $case_id);
			$data['totalExpense'] = array_sum($Expenses->getExpensesByRefIds($getHearing)->pluck('expense_amount')->toarray());
			    //total expense
			return view('Litigation::Cases.cases_view',$data)->render();
		} catch (Exception $e) {
			Log::error('CasesController-view: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function edit($case_id)
	{  
		try {
			$case_id = Crypt::decrypt($case_id); 
			$case = new Cases();
			$cases = $case->getCaseById($case_id); 
			$country = new Country();
			$court = new Court(); 
			$company = new Company(); 
			$Hearing = new Hearing();
			$LawFirm = new LawFirm(); 
			$Expenses = new Expenses();
			$legalCategory = new LegalCategory(); 
			$legalCategorys = $legalCategory->getLegalCategory(); 
			$companyDetail = $company->getDetail(getSetCompanyId()); 
			$courts = $court->getCourt(); 
			$appearingModel = new AppearingModel(); 
			$appearingModels = $appearingModel->getAppearingModel(); 
			$appUser = new AppUsers(); 
			$appUsers =$appUser->getAllUserByCompanyId(getSetCompanyId());
			$act = new ActMaster(); 
			$acts = $act->getActs();
			$appUserRole = new AppUserRole(); 
			$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
			$companies = $company->getCompanyByArrId($appUserRoleCompanyId);
			$countries = $country->getCountry();
			$masterTree = new MasterTree();
			$masterTrees = $masterTree->getMasterTreeByMasterId($case_id);
			$parent_id = '';
			if ($masterTrees->master_parent_id==0) {
				$parent_id = $cases->matter->id;
			}else{
				$parent_id = $masterTree->getParentDataIdById($masterTrees->master_parent_id)->id;
			}
			$lawyer = new Lawyer();
			$lawyers = $lawyer->getLawyerByCaseId($case_id);
			$opponent = new Opponents();	
			$opponents = $opponent->getOpponentByCaseId($case_id);
			$kmp = new KmpInvolved();	
			$kmpInvolveds = $kmp->getAllKmpByCompanyId(getSetCompanyId());
			$data =array();
			$data['firmList'] = $LawFirm->getFirm(getSetCompanyId());
			$data['case_id'] =$case_id; 
			$data['countries'] =$countries; 
			$data['masterTrees']=$masterTrees;
			$data['parent_id']=$parent_id;
			$data['acts'] =$acts;  
			$data['cases'] =$cases;  
			$data['appUsers'] =$appUsers;  
			$data['courts'] =$courts;  
			$data['legalCategorys'] =$legalCategorys;  
			$data['appearingModels'] =$appearingModels;  	 
			$data['companyDetail'] =$companyDetail;  	 
			$data['lawyers'] =$lawyers;  	 
			$data['opponents'] =$opponents;  	 
			$data['kmpInvolveds'] =$kmpInvolveds;  	 
				//total expense
			$arr['id'] = $case_id;
			$getHearing = $Hearing->getResult($arr,'hearingWithCaseId')->pluck('hearing_id')->toarray();
			array_push($getHearing, $case_id);
			$data['totalExpense'] = array_sum($Expenses->getExpensesByRefIds($getHearing)->pluck('expense_amount')->toarray());
			    //total expense
			return view('Litigation::Cases.cases_edit',$data)->render();
		} catch (Exception $e) {
			Log::error('CasesController-edit: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function create(){
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
			return view('Litigation::Cases.cases_form',$data);
		}catch(\Exception $e){
			Log::error('CasesController-create: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function store(Request $request){
		try{  
			$rules=[
				'matter'=>'required',
				'title'=>'required',
				'case_summary'=>'nullable',
				'legal_category'=>'required',
				'act'=>'required',
				'opponents_type.*' => 'required',
				'opponents_name.*' => 'required',
				'lawyer_type' => 'required',
				'legal_team' => 'required',
				'owner' => 'required',
				'lawyers' => 'required_if:lawyer_type,==,2',
				'case_status_id'=>'nullable',
				'company'=>'nullable',
				'country'=>'nullable',
				'jurisdiction'=>'nullable',
				'state'=>'nullable',
				'city'=>'nullable',
				'court_category'=>'nullable',
				'court'=>'nullable',
				'date_of_filing'=>'required|date|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
				'date_of_receipt_by_company'=>'nullable',
				'period_from'=>'nullable',
				'period_to'=>'nullable',
				'criticality_risk'=>'nullable',
				'criticality_risk_comments'=>'nullable',
				'potential'=>'nullable',
				'kmp_involved'=>'nullable',
				'exposure_demand_claim'=>'nullable|numeric',
				'exposure_interest'=>'nullable|numeric',
				'exposure_penalty'=>'nullable|numeric',
				'exposure_other'=>'nullable|numeric',
				'exposure_total'=>'nullable|numeric',
				'gain_interest'=>'nullable|numeric',
				'gain_demand_claim'=>'nullable|numeric',
				'gain_penalty'=>'nullable|numeric',
				'gain_other'=>'nullable|numeric',
				'gain_total'=>'nullable|numeric',
				'contingency_amount'=>'nullable|numeric',
				'likelihood'=>'nullable',
				'contingency_remark'=>'nullable',
				'assessed_on'=>'nullable|date|date_format:Y-m-d',
				'provision_in_books'=>'nullable|numeric',
				'case_handled'=>'nullable',
				'authorized_signatory'=>'nullable',
				'personnel_involved_comments'=>'nullable',
				'opponents_mobile.*' => 'nullable|numeric|digits_between:10,12',
			];
			if($request->court == 1){
				if($request->supreme_court == 1){
					$rules['case_type'] = "required";
					$rules['case_no'] = "required|unique:cases";

				}else{
					$rules['case_type'] = "nullable";
					$rules['diary_no'] = "required|unique:cases";
				}
			}else if($request->court == 2){
				if($request->court_category == 1){
					$rules['cnr_no'] = "required|unique:cases";
				}else{
					$rules['case_no'] = "required|unique:cases";
					$rules['case_type'] = "required";
					if ($request->has('bench')) {
						$rules['high_court'] = "required"; 
					} 
					if ($request->has('bench')) {
						$rules['bench'] = "required";	 
				   	}
					
				}
			}else if($request->court == 3){
				if ($request->has('state')) {
					$rules['state'] = "required";
				}
				if ($request->has('district')) {
					$rules['district'] = "required";
				}
				if ($request->has('court_establishment')) {
					$rules['court_establishment'] = "required";
				}
				
				$rules['case_no'] = "required|unique:cases";
				$rules['case_type'] = "required";
			}else if($request->court == 10){
				if ($request->has('commissions')) {
					$rules['commissions'] = "required";
				}
				if ($request->has('commissions_state')) {
					$rules['commissions_state'] = "required";
				}
				if ($request->has('commissions_district')) {
					$rules['commissions_district'] = "required";
				}
				 
				$rules['case_no'] = "required|unique:cases";
				$rules['case_type'] = "required";
			}else if($request->court == 4){
				if ($request->has('tribunals_authorities')) {
					$rules['tribunals_authorities'] = "required";
				} 
				$rules['case_no'] = "required|unique:cases";
				$rules['case_type'] = "required";
			}else if($request->court == 11){
				if ($request->has('revenue_court')) {
					$rules['revenue_court'] = "required";
				} 
				
				$rules['case_no'] = "required|unique:cases";
				$rules['case_type'] = "required";
			}else if($request->court == 14){
				if ($request->has('commissionerate')) {
					$rules['commissionerate'] = "required";
				}
				
				$rules['case_no'] = "required|unique:cases";
				$rules['case_type'] = "required";
			}else if($request->court == 9){
				$rules['case_no'] = "nullable";
			}
			if ($request->case_type !=null) { 
				if (Crypt::decrypt($request->case_type)==0) {
					  $rules['other_case_type'] = "required";
				} 
			}else{
				$rules['other_case_type'] = "nullable";
			}
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
					return response()->json($response);// response as json
			}   
			$case =new Cases(); 
			$matter_id =Crypt::decrypt($request->matter);
			$master_id =Crypt::decrypt($request->master_id);
			$legal_type_id =Crypt::decrypt($request->legal_type_id);

			if (is_null($master_id)) {
				$master_id =0; 
			}
			if ($legal_type_id!=null) {
				$fieldName = getLegalType($legal_type_id)->field_name; 
			}else{
				$fieldName =''; 
			}  
			$data =array();
			$case_id =generateId(); 
			$data['case_id']=$case_id;
			$data['matter_id']=$matter_id;
			$data['court_id']=$request->court;
			$data['supreme_court_id']=$request->supreme_court;
			$data['high_court_id']=$request->high_court;
			if ($request->has('bench')) {
				$data['bench_id']=$request->bench;
			} 
			$data['bench_side_id']=$request->bench_side;
			$data['bench_side_stamp_id']=$request->bench_stamp;
			if ($request->has('state')) {
				$data['state_id']=$request->state;
			} 
			if ($request->has('district')) {
				$data['state_district_id']=$request->district;
			}			
			$data['commissions_id']=$request->commissions;
			$data['court_establishment_id']=$request->court_establishment;
			$data['commissions_state_id']=$request->commissions_state;
			$data['commissions_state_district_id']=$request->commissions_district; 
			if ($request->has('commissions_bench')) {
				$data['bench_id']=$request->commissions_bench;
			}
			$data['tribunals_authorities_id']=$request->tribunals_authorities;
			$data['tribunals_authorities_state_id']=$request->tribunals_authorities_state;
			$data['tribunals_authorities_state_section_id']=$request->tribunals_authorities_state_section;
			$data['revenue_court_id']=$request->revenue_court;
			if ($request->has('revenue_district')) {
				$data['state_district_id']=$request->revenue_district;
			} 
			$data['revenue_district_court_id']=$request->revenue_district_court;
			$data['commissionerate_id']=$request->commissionerate;
			if ($request->has('commissionerate_state')) {
				$data['state_id']=$request->commissionerate_state;
			}
			$data['commissionerate_authority_id']=$request->commissionerate_authority;
			
			$data['case_no']=$request->case_no;
			$data['cnr_no']=$request->cnr_no;
			$data['diary_no']=$request->diary_no;
			$data['year']=$request->year;
			
			if (isset($request->other_case_type)) {
			 	$data['other_case_type']=$request->other_case_type; 
			 	$data['case_type_id']=0;
			 }else{
			 	$data['case_type_id']=$request->case_type !=null?Crypt::decrypt($request->case_type):null;
			 } 

			if ($data['court_id']==2 || $data['court_id']==14) {
				$data['appearing_model_id']=1; 
			}else{
				$data['appearing_model_id']=isset($request->appearing_model)?Crypt::decrypt($request->appearing_model):'';
			}
					
			$data['appearing_model_as']=isset($request->appearing_model_as)?Crypt::decrypt($request->appearing_model_as):'';
			$data['appearing_field']=$request->appearing_field;			
			if ($fieldName!='') {
				$data[$fieldName]=$request->$fieldName; 
			}
			$data['title']=$request->title;
			$data['case_summary']=$request->case_summary;
			$data['case_remarks']=$request->case_remarks; 
			if (!empty($request->act)) {	
				$data['act_id']=implode(',', decrypt_array($request->act));
			}
			$data['section']= $request->section;
			$data['case_status_id']=$request->case_status;
			$data['legal_category_id']=Crypt::decrypt($request->legal_category);
			$data['case_sub_type_id']=$request->case_sub_type;
			$data['law_type_id']=$request->law_type;
			$data['proceeding_id']=$request->proceeding;

			$data['company_id']=getSetCompanyId(); 
			$data['date_of_filing']=$request->date_of_filing;
			
			$data['criticality_risk_id']=$request->criticality_risk;
			$data['criticality_risk_comments']=$request->criticality_risk_comments;
			$data['potential_id']=$request->potential;
			if (!empty($request->$request->kmp_involved)) {	
				$data['kmp_involved_id']=implode(',', $request->kmp_involved);
			}			
			$data['exposure_demand_claim']=$request->exposure_demand_claim;
			$data['exposure_interest']=$request->exposure_interest;
			$data['exposure_penalty']=$request->exposure_penalty;
			$data['exposure_other']=$request->exposure_other;
			$data['exposure_total']=$request->exposure_total;
			$data['gain_interest']=$request->gain_interest;
			$data['gain_demand_claim']=$request->gain_demand_claim;
			$data['gain_penalty']=$request->gain_penalty;
			$data['gain_other']=$request->gain_other;
			$data['gain_total']=$request->gain_total;
			$data['contingency_amount']=$request->contingency_amount;
			$data['likelihood_id']=$request->likelihood;
			$data['contingency_remark']=$request->contingency_remark;
			$data['assessed_on']=$request->assessed_on;
			$data['provision_in_books']=$request->provision_in_books;
			if (!empty($request->legal_team)) {	
				$data['legal_team_id']=implode(',', $request->legal_team);
			}
			$data['owner_id']=$request->owner;
			$data['authorized_signatory_id']=$request->authorized_signatory;
			
			$data['lawyer_type_id']=$request->lawyer_type;
			if($request->lawyer_type == 2){
				$data['lawyers'] = implode(',', decrypt_array($request->lawyers));
			}
			$data['created_by']=!empty($request->owner)?$request->owner:getUserId(); 
			$data['status']=1;

			$cases =$case->addCase($data); 
			// $this->laywerStore($data,$request);
			$this->opponentsStore($data,$request);
			$gereralHelper =new generalHelper();
			$gereralHelper->storeMasterTree($matter_id,$data['case_id'],$master_id,1,getSetCompanyId(),getUserId());
			$MailHelper = new MailHelper();
			if(!empty($cases)){
				$MailHelper->caseAdd($data);
			}
			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;
		}catch(\Exception $e){
			Log::error('CasesController-store: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function update(Request $request,$case_id){
		try{    
			$case_id =Crypt::decrypt($case_id);
			$rules=[
				'title'=>'required',
				'case_summary'=>'nullable',
				'legal_category'=>'required',
				'act'=>'required',
				'opponents_type.*' => 'required',
				'opponents_name.*' => 'required',
				'lawyer_type' => 'required',
				'legal_team' => 'required',
				'owner' => 'required',
				'lawyers' => 'required_if:lawyer_type,==,2',
				'case_status_id'=>'nullable',
				
				'company'=>'nullable',
				'country'=>'nullable',
				'jurisdiction'=>'nullable',
				'state'=>'nullable',
				'city'=>'nullable',
				'court_category'=>'nullable',
				'court'=>'nullable',
				'date_of_filing'=>'required|date|date_format:Y-m-d|before_or_equal:'.date('Y-m-d'),
				'date_of_receipt_by_company'=>'nullable',
				'period_from'=>'nullable',
				'period_to'=>'nullable',
				'criticality_risk'=>'nullable',
				'criticality_risk_comments'=>'nullable',
				'potential'=>'nullable',
				'kmp_involved'=>'nullable',
				'exposure_demand_claim'=>'nullable|numeric',
				'exposure_interest'=>'nullable|numeric',
				'exposure_penalty'=>'nullable|numeric',
				'exposure_other'=>'nullable|numeric',
				'exposure_total'=>'nullable|numeric',
				'gain_interest'=>'nullable|numeric',
				'gain_demand_claim'=>'nullable|numeric',
				'gain_penalty'=>'nullable|numeric',
				'gain_other'=>'nullable|numeric',
				'gain_total'=>'nullable|numeric',
				'contingency_amount'=>'nullable|numeric',
				'likelihood'=>'nullable',
				'contingency_remark'=>'nullable',
				'assessed_on'=>'nullable|date|date_format:Y-m-d',
				'provision_in_books'=>'nullable|numeric',
				'case_handled'=>'nullable',
				'authorized_signatory'=>'nullable',				
				'personnel_involved_comments'=>'nullable',
				'update_remark'=>'required', 
				'opponents_mobile.*' => 'nullable|numeric|digits_between:10,12',
			];
			if($request->court == 1){
				if($request->supreme_court == 1){
					$rules['case_type'] = "required";
					$rules['case_no'] = 'required|unique:cases,case_no,'.$case_id.',case_id';
				}else{
					$rules['case_type'] = "nullable";
					$rules['diary_no'] = 'required|unique:cases,diary_no,'.$case_id.',case_id';
				}
			}else if($request->court == 2){
				if($request->court_category == 1){
					$rules['cnr_no'] = 'required|unique:cases,cnr_no,'.$case_id.',case_id';
				}else{
					$rules['case_no'] = 'required|unique:cases,case_no,'.$case_id.',case_id';
					$rules['case_type'] = "required";			
					if ($request->has('bench')) {
						$rules['high_court'] = "required"; 
					} 
					if ($request->has('bench')) {
						$rules['bench'] = "required";	 
				   	}
				}
			}else if($request->court == 3){
				
				 if ($request->has('state')) {
				 	$rules['state'] = "required";	
				 }
				if ($request->has('district')) {
					$rules['district'] = "required";	
				}
				if ($request->has('court_establishment')) {
					$rules['court_establishment'] = "required";
				}
				
				$rules['case_no'] = 'required|unique:cases,case_no,'.$case_id.',case_id';
				$rules['case_type'] = "required";
			}else if($request->court == 10){
				$rules['commissions'] = "required";
				
				if ($request->has('commissions_state')) {
					$rules['commissions_state'] = "required";	
				}
				if ($request->has('commissions_district')) {
					$rules['commissions_district'] = "required";	
				}
				
				$rules['case_no'] = 'required|unique:cases,case_no,'.$case_id.',case_id';
				$rules['case_type'] = "required";
			}else if($request->court == 4){
				if ($request->has('tribunals_authorities')) {
					$rules['tribunals_authorities'] = "required";
				}
				
				$rules['case_no'] = 'required|unique:cases,case_no,'.$case_id.',case_id';
				$rules['case_type'] = "required";
			}else if($request->court == 11){
				if ($request->has('revenue_court')) {
					$rules['revenue_court'] = "required";
				}
				if ($request->has('')) {
					$rules['case_no'] = 'required|unique:cases,case_no,'.$case_id.',case_id';
				}				
				$rules['case_type'] = "required";
			}else if($request->court == 14){
				if ($request->has('commissionerate')) {
					$rules['commissionerate'] = "required";
				}
				if ($request->has('case_no')) {
					$rules['case_no'] = 'required|unique:cases,case_no,'.$case_id.',case_id';
				}
				$rules['case_type'] = "required";
				
				
			}else if($request->court == 9){
				$rules['case_no'] = "nullable";
			}
			if ($request->case_type !=null) { 
				if (Crypt::decrypt($request->case_type)==0) {
					  $rules['other_case_type'] = "required";
				} 
			}else{
				$rules['other_case_type'] = "nullable";
			}
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}   
			$case =new Cases(); 
			
			// $lawyer_id =decrypt_array(array_filter($request->lawyer_id));
			$opponent_id =decrypt_array(array_filter($request->opponent_id));
			$matter_id =Crypt::decrypt($request->matter);
			$caseDetails=$case->getCaseById($case_id); 
			$data =array(); 
			$data['case_id']=$case_id;
			$data['matter_id']=$matter_id;
			$data['court_id']=$request->court;
			$data['supreme_court_id']=$request->supreme_court;
			$data['high_court_id']=$request->high_court;
			if ($request->has('bench')) {
				$data['bench_id']=$request->bench;
			} 
			$data['bench_side_id']=$request->bench_side;
			$data['bench_side_stamp_id']=$request->bench_stamp;
			if ($request->has('state')) {
				$data['state_id']=$request->state;
			} 
			if ($request->has('district')) {
				$data['state_district_id']=$request->district;
			}			
			$data['commissions_id']=$request->commissions;
			$data['court_establishment_id']=$request->court_establishment;
			$data['commissions_state_id']=$request->commissions_state;
			$data['commissions_state_district_id']=$request->commissions_district; 
			if ($request->has('commissions_bench')) {
				$data['bench_id']=$request->commissions_bench;
			}
			$data['tribunals_authorities_id']=$request->tribunals_authorities;
			$data['tribunals_authorities_state_id']=$request->tribunals_authorities_state;
			$data['tribunals_authorities_state_section_id']=$request->tribunals_authorities_state_section;
			$data['revenue_court_id']=$request->revenue_court;
			if ($request->has('revenue_district')) {
				$data['state_district_id']=$request->revenue_district;
			} 
			$data['revenue_district_court_id']=$request->revenue_district_court;
			$data['commissionerate_id']=$request->commissionerate;
			if ($request->has('commissionerate_state')) {
				$data['state_id']=$request->commissionerate_state;
			} 
			$data['commissionerate_authority_id']=$request->commissionerate_authority;
			$data['case_no']=$request->case_no;
			$data['cnr_no']=$request->cnr_no;
			$data['diary_no']=$request->diary_no;
			$data['year']=$request->year;
			if (isset($request->other_case_type)) {
			 	$data['other_case_type']=$request->other_case_type; 
			 	$data['case_type_id']=0;
			 }else{
			 	$data['case_type_id']=$request->case_type !=null?Crypt::decrypt($request->case_type):null;
			 }
			$data['appearing_model_id']=isset($request->appearing_model)?Crypt::decrypt($request->appearing_model):'';		
			$data['appearing_model_as']=isset($request->appearing_model)?Crypt::decrypt($request->appearing_model_as):'';
			$data['appearing_field']=$request->appearing_field;
			// $data['court_hall']=$request->court_hall;
			// $data['floor']=$request->floor;

			$data['title']=$request->title;
			$data['case_summary']=$request->case_summary;
			$data['case_remarks']=$request->case_remarks;
			if (!empty($request->act)) {	
				$data['act_id']=implode(',', decrypt_array($request->act));
			}
			$data['section']= $request->section;
			$data['case_status_id']=$request->case_status;
			$data['legal_category_id']=Crypt::decrypt($request->legal_category);
			$data['case_sub_type_id']=$request->case_sub_type;
			$data['law_type_id']=$request->law_type;
			$data['proceeding_id']=$request->proceeding; 
			$data['date_of_filing']=$request->date_of_filing;
			
			$data['criticality_risk_id']=$request->criticality_risk;
			$data['criticality_risk_comments']=$request->criticality_risk_comments;
			$data['potential_id']=$request->potential;		
			if (!empty($request->kmp_involved)) {	
				$data['kmp_involved_id']=implode(',', $request->kmp_involved);
			}
			$data['exposure_demand_claim']=$request->exposure_demand_claim;
			$data['exposure_interest']=$request->exposure_interest;
			$data['exposure_penalty']=$request->exposure_penalty;
			$data['exposure_other']=$request->exposure_other;
			$data['exposure_total']=$request->exposure_total;
			$data['gain_demand_claim']=$request->gain_demand_claim;
			$data['gain_interest']=$request->gain_interest;
			$data['gain_penalty']=$request->gain_penalty;
			$data['gain_other']=$request->gain_other;
			$data['gain_total']=$request->gain_total;
			$data['contingency_amount']=$request->contingency_amount;
			$data['likelihood_id']=$request->likelihood;
			$data['contingency_remark']=$request->contingency_remark;
			$data['assessed_on']=$request->assessed_on;
			$data['provision_in_books']=$request->provision_in_books;
			if (!empty($request->legal_team)) {	
				$data['legal_team_id']=implode(',', $request->legal_team);
			}
			$data['owner_id']=$request->owner;
			$data['authorized_signatory_id']=$request->authorized_signatory;

			$data['lawyer_type_id']=$request->lawyer_type;
			if($request->lawyer_type == 2){
				$data['lawyers'] = implode(',', decrypt_array($request->lawyers));
			}else{
				$data['lawyers'] = '';
			}
			$data['updated_by']=getUserId(); 	
			$data['update_remark']=$request->update_remark;	
			$data['created_by']=!empty($request->owner)?$request->owner:getUserId(); 	   
			$cases =$case->updateArr($case_id,$data); 
			// $this->laywerUpdate($lawyer_id,$data,$request);
			$this->opponentsUpdate($opponent_id,$data,$request);
			$MailHelper = new MailHelper();
			if(!empty($cases) && $data['case_status_id'] != $caseDetails->case_status_id){
				$MailHelper->caseStatusChange($caseDetails);
			}
			$response=array();
			$response["status"]=1;
			$response["msg"]="Update Successfully";
			return $response;
		}catch(\Exception $e){
			Log::error('CasesController-update: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	//laywerStore
	// public function laywerStore($dataArr,$request){
	// 	try { 
	// 		foreach ($request->lawyer_name as $key=>$value) {
	// 			$lawer = new Lawyer();	
	// 			$data =array();
	// 			$data['reference_id']=$dataArr['case_id']; 
	// 			$data['legal_type_id']=1;  
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
	// 		Log::error('CaseController-laywerStore: '.$e->getMessage()); 		
	// 		return view('error.home'); 
	// 	}
	// }

	//laywerStore
	public function opponentsStore($dataArr,$request){
		try { 
			foreach ($request->opponents_name as $key=>$value) {
				$opponent = new Opponents();	
				$data =array();
				$data['reference_id']=$dataArr['case_id'];  
				$data['legal_type_id']=1;    
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
		}catch (Exception $e) {
			Log::error('CaseController-opponentsStore: '.$e->getMessage()); 		
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
	// 			$case_id =$dataArr['case_id'];	 			
	// 			$data =array();
	// 			if($id == ''){
	// 				$data['status'] = 1; 
	// 				$data['created_by'] = getUserId(); 
	// 				$data['legal_type_id'] = 1;  	
	// 			} 
	// 			$data['reference_id']= $case_id;  
	// 			$data['matter_id']=$dataArr['matter_id']; 
	// 			$data['company_id']=getSetCompanyId(); 
	// 			$data['updated_by']=$dataArr['updated_by']; 
	// 			$data['name']=$request->lawyer_name[$key];
	// 			$data['mobile_no']=$request->lawyer_mobile[$key];
	// 			$data['address']=$request->lawyer_address[$key];
	// 			$data['email']=$request->lawyer_email[$key]; 
	// 			$lawers =$lawer->updateOrCreateByCaseId($id,$case_id,$data); 
	// 		}
	// 	} catch (Exception $e) {
	// 		Log::error('CaseController-laywerUpdate: '.$e->getMessage()); 		
	// 		return view('error.home'); 
	// 	}
	// }
	//laywer delete
	public function lawyerDelete($id){
		try { 
			$id = Crypt::decrypt($id); 
			$lawer = new Lawyer();
			$updArr['status'] = 0;	
			$lawer->deleteById($updArr,$id); 
			$response=array();
			$response['status'] = 1; 
			$response['msg'] = 'Lawyer Details Delete Successful';
			return $response;
		} catch (Exception $e) {
			Log::error('CaseController-lawyerDelete: '.$e->getMessage()); 		
			return view('error.home'); 
		}
	}
	//opponent delete
	public function opponentDelete($id){
		try { 
			$id = Crypt::decrypt($id); 
			$opponent = new Opponents();
			$updArr['status'] = 0;	
			$opponent->deleteById($updArr,$id); 	
			$response=array();
			$response['status'] = 1; 
			$response['msg'] = 'Opponents Details Delete Successful';
			return $response;
		} catch (Exception $e) {
			Log::error('CaseController-opponentDelete: '.$e->getMessage()); 		
			return view('error.home'); 
		}
	}

	//laywerStore
	public function opponentsUpdate($opponent_id,$dataArr,$request){
		try {  
			foreach ($request->opponents_name as $key=>$value) {
				$id = '';
				$opponent = new Opponents();	
				if ($key<=count($opponent_id)-1) { 
					$id =$opponent_id[$key];   
				}
				$case_id =$dataArr['case_id'];	 			
				$data =array();
				if($id == ''){
					$data['status']= 1; 
					$data['created_by']= getUserId(); 
					$data['legal_type_id'] = 1;  	
				} 
				$data['reference_id']= $case_id; 
				$data['matter_id']=$dataArr['matter_id'];  
				$data['company_id']=getSetCompanyId();  
				$data['updated_by']=$dataArr['updated_by']; 
				$data['name']=$request->opponents_name[$key];
				$data['opponents_type_id']=$request->opponents_type[$key];
				$data['mobile_no']=$request->opponents_mobile[$key];
				$data['address']=$request->opponents_address[$key];
				$data['email']=$request->opponents_email[$key];
				$opponents =$opponent->updateOrCreateByCaseId($id,$case_id,$data);
			}
		} catch (Exception $e) {
			Log::error('CaseController-opponentsUpdate: '.$e->getMessage()); 		
			return view('error.home'); 
		}
	}

	//select optin create
	public function selectOptionCreate($modelName){
		try {
			return view('Litigation::Cases.select_option_create',compact('modelName'));
		} catch (Exception $e) {
			Log::error('CaseController-selectOptionCreate: '.$e->getMessage()); 		
			return view('error.home'); 
		}
	}
	//select optin store
	public function selectOptionStore(Request $request,$modelName){
		try {
			$rules=[
				'option_name'=>'required', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$modelName= Crypt::decrypt($modelName); 
			$model =  'App\Modules\Litigation\Models\Defaults\\'.$modelName; 
			$option=new $model; 
			$option->name=$request->option_name;
			$option->company_id=getSetCompanyId();
			$option->save();
			$options =  $model::all(); 
			$selectedOptionsId =  $option->id; 
			$response=array();
			$response['status'] = 1; 
			$response['msg'] = 'Add Option Successful';		      
			$response['data'] = view('Litigation::Cases.select_option_view',compact('options','selectedOptionsId'))->render();
		     //return $response;
			return response()->json($response);
		} catch (Exception $e) {
			Log::error('CaseController-selectOptionStore: '.$e->getMessage()); 		
			return view('error.home'); 
		}
	}

public function courtCategory(Request $request)
	{
		try{   
			$court_id=$request->id;
			$court  = new Court(); 
			$courts = $court->getCourtById($court_id);
			$data =array(); 
			if ($court_id==1) {
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				$SupremeCourt = new SupremeCourt();
				$SupremeCourts = $SupremeCourt->getSupremeCourt();
				$data['SupremeCourts'] =$SupremeCourts;
				return view('Litigation::SelectOption.supreme_court',$data);  
			} 
			elseif($court_id==2) {
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				}
				$HighCourt = new HighCourt();
				$HighCourts = $HighCourt->getHighCourt();
				$data['HighCourts'] =$HighCourts;
				return view('Litigation::SelectOption.high_court',$data); 
			}elseif($court_id==3) {
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				$CountryState = new CountryState();
				$States = $CountryState->getState(12);
				$data['States'] =$States;
				return view('Litigation::SelectOption.state',$data); 
			} 
			elseif($court_id==10) {
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				$Commissions = new Commissions();
				$Commissions = $Commissions->getCommissions(); 
				$data['Commissions'] =$Commissions;
				return view('Litigation::SelectOption.commissions',$data); 
			}elseif($court_id==4) {
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				$TribunalsAuthoritie = new TribunalsAuthorities();
				$TribunalsAuthorities = $TribunalsAuthoritie->getTribunalsAuthorities(); 
				$data['TribunalsAuthorities'] =$TribunalsAuthorities;
				return view('Litigation::SelectOption.tribunals_authorities',$data); 
			}
			elseif($court_id==11) {
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				$RevenueCourt = new RevenueCourt();
				$States = $RevenueCourt->getState($court_id); 
				$data['States'] =$States;
				return view('Litigation::SelectOption.revenue',$data); 
			}elseif($court_id==14) {
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				$Commissionerate = new Commissionerate();
				$Commissionerates = $Commissionerate->getCommissionerate(); 
				$data['Commissionerates'] =$Commissionerates;
				return view('Litigation::SelectOption.commissionerate',$data); 
			}elseif($court_id==9) {
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				$data['col'] =6;
				$data['is_show'] =1;
				return view('Litigation::SelectOption.other_case_type',$data); 
				
			}
		}catch(\Exception $e){
		Log::error('CasesController-courtCategory: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//high court wise bench
	public function courtBench(Request $request)
	{
		try{  
			$high_court_id=$request->id;
			$Bench  = new Bench(); 
			$Benchs = $Bench->getBenchByHighCourtId($high_court_id);
			$data =array();  
			$data['Benchs'] =$Benchs;
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			if (count($Benchs)==0) { 
				$data['is_show'] =0;
				$data['col'] =12;
			    return view('Litigation::SelectOption.other_case_type',$data); 
			}
			return view('Litigation::SelectOption.bench',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-courtBench: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//high court wise bench Side
	public function courtBenchSide(Request $request)
	{
		try{  
			$bench_id=$request->id;
			$BenchSide  = new BenchSide();  
			$BenchSides = $BenchSide->getBenchSideByBenchId($bench_id); 
			$data =array(); 
			if (count($BenchSides)==0) {  
				$CaseType  = new CaseType();
				$Bench  = new Bench();
				$benchData=$Bench->getBenchById($bench_id);
				if (isset($benchData)) {
					 $caseTypes=$CaseType->getCaseType($benchData,'bench');
					 $data['caseTypes'] =$caseTypes;
				}			
				
			}
			$data['BenchSides'] =$BenchSides;
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			}  
			return view('Litigation::SelectOption.bench_side',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-courtBenchSide: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}//high court wise bench Side
	public function courtBenchSideStamp(Request $request)
	{ 
		try{  
			$side_id=$request->id;
			$BenchSideStamp  = new BenchSideStamp(); 
			$BenchSideStamps = $BenchSideStamp->getBenchSideStampBySideId($side_id);
			$data =array(); 
			if (count($BenchSideStamps)==0) {  
				$CaseType  = new CaseType();
				$BenchSide  = new BenchSide();
				$BenchSideData=$BenchSide->getBenchSideById($side_id);
				if (isset($BenchSideData)) {
					 $caseTypes=$CaseType->getCaseType($BenchSideData,'side');
					 $data['caseTypes'] =$caseTypes;
				}			
				
			}
			$data['BenchSideStamps'] =$BenchSideStamps;
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			return view('Litigation::SelectOption.bench_side_stamp',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-courtBenchSideStamp: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//Distict court  
	public function district(Request $request)
	{
		try{  
			$state_id=$request->id;
			$District  = new StateDistrict(); 
			$Districts = $District->getDistrictByStateId($state_id);
			$data =array(); 
			$data['Districts'] =$Districts;
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			}  
			if (count($Districts)==0) { 
				$data['is_show'] =1;
				$data['col'] =12;
			    return view('Litigation::SelectOption.other_case_type',$data); 
			}
			return view('Litigation::SelectOption.district',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-district: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//Court Establishment
	public function courtEstablishment(Request $request)
	{
		try{   
			$district_id=$request->id;
			$CourtEstablishment  = new CourtEstablishment(); 
			$CourtEstablishments = $CourtEstablishment->getCourtEstablishmentByDistrictId($district_id);
			$data =array(); 
			$data['CourtEstablishments'] =$CourtEstablishments;
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			 
			return view('Litigation::SelectOption.court_establishment',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-courtEstablishment: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
    //Commissions Type
	public function commissionsType(Request $request)
	{
		try{   
			$commissions_type_id=$request->id;
			$data =array(); 
			if ($commissions_type_id==27) {
				$data['commissions_type_id'] =$commissions_type_id;
				$State = new CommissionsState();
				$States = $State->getCommissionsState($commissions_type_id); 
				$data['States'] =$States;
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				return view('Litigation::SelectOption.commissions_state',$data); 
			}else if($commissions_type_id==25) {
				$high_court_id=6;
				$Bench  = new CommissionsState(); 
				$Benchs = $Bench->getCommissionsState($commissions_type_id);
				$data =array(); 
				$data['Benchs'] =$Benchs;
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				return view('Litigation::SelectOption.commissions_bench',$data);  	 
			}else if($commissions_type_id==26) { 
				$State = new CommissionsState(); 
				$States = $State->getCommissionsState($commissions_type_id); 
				$data['commissions_type_id'] =$commissions_type_id;
				$data['States'] =$States;
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				return view('Litigation::SelectOption.commissions_state',$data); 
			}else{
				return 'no commissions type';
			}
			
		}catch(\Exception $e){
			Log::error('CasesController-commissionsType: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}

	 //commissionsStateDistrict
	public function commissionsStateDistrict(Request $request)
	{
		try{   
			$commissions_state_id=$request->id;
			$CommissionsStateDistrict  = new CommissionsStateDistrict(); 
			$CommissionsStateDistricts = $CommissionsStateDistrict->getCommissionsStateDistrictByCommissionsStateId($commissions_state_id);
			$data =array(); 
			$data['CommissionsStateDistricts'] =$CommissionsStateDistricts;
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			return view('Litigation::SelectOption.commissions_district',$data); 			 
		}catch(\Exception $e){
			Log::error('CasesController-commissionsStateDistrict: '.$e->getMessage()); 		
			return view('error.home');
		} 
	} 
	//commissionerateType
	public function commissionerateType(Request $request)
	{
		try{   
			$commissionerate_type_id=$request->id;
			
				$CommissionerateAuthority = new CommissionerateAuthority();
				$CommissionerateAuthoritys = $CommissionerateAuthority->getCommissionerateAuthority($commissionerate_type_id); 
				$data['CommissionerateAuthoritys'] =$CommissionerateAuthoritys;  
				$data['commissionerate_type_id'] =$commissionerate_type_id;  
				if ($request->has('case_id')) { 
					$case_id = Crypt::decrypt($request->case_id); 
					$case = new Cases();
					$cases = $case->getCaseById($case_id); 
					$data['cases'] =$cases;
				} 
				return view('Litigation::SelectOption.commissionerate_authority',$data); 
			 

		}catch(\Exception $e){
			Log::error('CasesController-commissionerateType: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//tribunalsAuthoritieState
	public function tribunalsAuthoritieState(Request $request)
	{
		try{    
			$tribunals_authorities_id =$request->id;
			$TribunalsAuthoritiesState = new TribunalsAuthoritiesState();
			$TribunalsAuthoritiesStates = $TribunalsAuthoritiesState->getTribunalsAuthoritiesStateByTribunalsAuthoritiesId($tribunals_authorities_id); 
			$data['TribunalsAuthoritiesStates'] =$TribunalsAuthoritiesStates; 
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			return view('Litigation::SelectOption.tribunals_authorities_states',$data); 
		}catch(\Exception $e){
			Log::error('CasesController-tribunalsAuthoritieState: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//tribunalsAuthoritieState
	public function tribunalsAuthoritieStateSection(Request $request)
	{
		try{    
			$tribunals_authorities_state_id =$request->id;
			$TribunalsAuthoritiesStateSection = new TribunalsAuthoritiesStateSection();
			$TribunalsAuthoritiesStateSections = $TribunalsAuthoritiesStateSection->getTribunalsAuthoritiesStateByTribunalsAuthoritiesStateId($tribunals_authorities_state_id);
			if (count($TribunalsAuthoritiesStateSections)==0) {  
				$CaseType  = new CaseType();
				$TribunalsAuthoritiesState  = new TribunalsAuthoritiesState();
				$TribunalsAuthoritiesStateData=$TribunalsAuthoritiesState->getTribunalsAuthoritiesStateById($tribunals_authorities_state_id);
				if (isset($TribunalsAuthoritiesStateData)) {
					 $caseTypes=$CaseType->getCaseType($TribunalsAuthoritiesStateData,'bench');
					 $data['caseTypes'] =$caseTypes;
				}			
				
			} 
			$data['TribunalsAuthoritiesStateSections'] =$TribunalsAuthoritiesStateSections; 
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			return view('Litigation::SelectOption.tribunals_authorities_states_section',$data); 
		}catch(\Exception $e){
			Log::error('CasesController-tribunalsAuthoritieStateSection: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
   //revenueDistrict
	public function revenueDistrict(Request $request)
	{
		try{  
			$state_id=$request->id; 
			 
			$District  = new RevenueDistrict(); 
			$Districts = $District->getDistrictByStateId($state_id);
			$data =array(); 
			$data['Districts'] =$Districts;
			$data['state_id'] =$state_id;
		 
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			return view('Litigation::SelectOption.revenue_district',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-revenueDistrict: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
   //revenueDistrictCourt
	public function revenueDistrictCourt(Request $request)
	{
		try{  
			// $state_id=$request->state_id;
			$district_id=$request->id;
			$RevenueDistrictCourt  = new RevenueDistrictCourt(); 
			$RevenueDistrictCourts = $RevenueDistrictCourt->getRevenueDistrictCourtByDistrictId($district_id);
			$data =array(); 
			$data['RevenueDistrictCourts'] =$RevenueDistrictCourts;
			if ($request->has('case_id')) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			return view('Litigation::SelectOption.revenue_district_court',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-revenueDistrictCourt: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//appearingModelAs
	public function appearingModelAs(Request $request)
	{
		try{    
			
			$id= Crypt::decrypt($request->id);
			$AppearingModel = new AppearingModel();
		    $appearingModelAs =$AppearingModel->getAppearingModelById($id);
		    $data = array();
		    $case = new Cases();
		    if ($request->has('case_id')) {
				 $case_id= Crypt::decrypt($request->case_id);
		         $cases = $case->getCaseById($case_id); 
		          $data['appearing_model_as'] =$cases->appearing_model_as;
		          $data['appearing_field'] =$cases->appearing_field;
			}
		    
		   
		    $appearingModelAss= explode('|',$appearingModelAs->value);
			
			
			$data['appearingModelAs'] =$appearingModelAss; 
			return view('Litigation::SelectOption.appearing_model_as',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-appearingModelAs: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//appearingField
	public function appearingField(Request $request)
	{ 
		try{    
			$appearingFiled= $request->id != null?Crypt::decrypt($request->id):''; 
			 $appearingFiledValue= $request->appearing_field != null?Crypt::decrypt($request->appearing_field):''; 
			$data = array();
			$data['appearingFiled'] =$appearingFiled; 
			$data['appearingFiledValue'] =$appearingFiledValue; 
			if ($appearingFiled !=null) {
				 return view('Litigation::SelectOption.appearing_field',$data);
			}
			  
		}catch(\Exception $e){
			Log::error('CasesController-appearingField: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//caseType
	public function caseType(Request $request)
	{   
		try{   
			$CaseType =new CaseType();			
			$data = array();
			if ($request->has('case_id') && $request->case_id !=null) { 
				$case_id = Crypt::decrypt($request->case_id); 
				$case = new Cases();
				$cases = $case->getCaseById($case_id); 
				$data['cases'] =$cases;
			} 
			if ($request->has('court_id')) {
				$data['caseTypes'] =$CaseType->getCaseType($court['id']=$request->court_id,'court');	  
			}elseif ($request->has('bench') && $request->bench !=null) {
				$Bench  = new Bench();
				$benchData=$Bench->getBenchById($request->bench);
				$data['caseTypes'] =$CaseType->getCaseType($benchData,'bench');	 
			}elseif ($request->has('stamp_id') && $request->stamp_id !=null) { 
				$BenchSideStamp  = new BenchSideStamp();
				$stampData=$BenchSideStamp->getBenchSideStampById($request->stamp_id);
				$data['caseTypes'] =$CaseType->getCaseType($stampData,'stamp');	 
			}elseif ($request->has('court_establishment') && $request->court_establishment!=null) {  
					 $CourtEstablishment  = new CourtEstablishment();
					 $CourtEstablishmentData=$CourtEstablishment->getCourtEstablishmentById($request->court_establishment);
					 $data['caseTypes'] =$CaseType->getCaseType($CourtEstablishmentData,'side'); 
			}elseif ($request->has('tribunals_authorities_state_section') && $request->tribunals_authorities_state_section!=null) {  
					 $TribunalsAuthoritiesStateSection  = new TribunalsAuthoritiesStateSection();
					 $TribunalsAuthoritiesStateSectiontData=$TribunalsAuthoritiesStateSection->getTribunalsAuthoritiesStateSectionById($request->tribunals_authorities_state_section);
					 $data['caseTypes'] =$CaseType->getCaseType($TribunalsAuthoritiesStateSectiontData,'side'); 
			}elseif ($request->has('commissionerate_authority') && $request->commissionerate_authority!=null) {  
					 $CommissionerateAuthority  = new CommissionerateAuthority();
					 $CommissionerateAuthorityData=$CommissionerateAuthority->getCommissionerateAuthorityById($request->commissionerate_authority);
					 $data['caseTypes'] =$CaseType->getCaseType($CommissionerateAuthorityData,'bench'); 
			}elseif ($request->has('revenue_district_court') && $request->revenue_district_court!=null) {  
					 $RevenueDistrictCourt  = new RevenueDistrictCourt();
					 $RevenueDistrictCourtData=$RevenueDistrictCourt->getRevenueDistrictCourtById($request->revenue_district_court);
					 $data['caseTypes'] =$CaseType->getCaseType($RevenueDistrictCourtData,'side'); 
			}
					 
			return view('Litigation::SelectOption.case_type_option',$data);  
		}catch(\Exception $e){
			Log::error('CasesController-caseType: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
	//other case Type
	public function otherCaseType(Request $request)
	{   
		try{   
			$id =  $request->id !=null?Crypt::decrypt($request->id):null;
			$case_id =  $request->case_id !=null?Crypt::decrypt($request->case_id):'';
			$Cases =new Cases(); 
			$data =array();
			if (!empty($case_id)) {
			  $data['cases'] =$Cases->getCaseById($case_id); 
			}
			 
			if (is_null($id)) {
				  return '';
			}elseif($id==0){
				return view('Litigation::SelectOption.other_case_type',$data);
			} 
			
			 
		}catch(\Exception $e){
			Log::error('CasesController-caseType: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}
		//barchart
	public function barchart(Request $reqeust,$y)
	{   
		try{  
			$y= Crypt::decrypt($y);  
			$data=array(); 
			$data["year"]=$y; 
			return view('Litigation::Cases.case_barchart',$data);
		 
			 
		}catch(\Exception $e){
			Log::error('CasesController-caseType: '.$e->getMessage()); 		
			return view('error.home');
		} 
	}	//globalSearch
	


	
}

