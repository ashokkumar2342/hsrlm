<?php 
namespace App\Modules\Litigation\Controllers\Position;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\Defaults\CaseStatus;
use App\Modules\Litigation\Models\Defaults\CaseType;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\Position;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
class PositionController extends Controller
{ 
	public function index(Request $request){
		try{
			$data=array();
			$UserRole = new UserRole();
			$data['roleList']=$UserRole->getDetail();
			return view('Litigation::Position.position_list',$data);
		}catch(\Exception $e){
			Log::error('PositionController-index: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}
	//notice show
	public function show(Request $request){
		try {
			$notice = new Notice();
			$notices = $notice->getNoticeByCompanyId(getSetCompanyId());
			$data['notices']=$notices;
			return view('Litigation::Position.position_table',$data)->render();
		} catch (Exception $e) {
			Log::error('PositionController-show: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	//notice show by matter id
	public function showByMatterId($matter_id){   
		try {
			$matter_id = Crypt::decrypt($matter_id);
			$notice = new Notice();
			$notices = $notice->getNoticeByMatterId($matter_id,getSetCompanyId(),getUserId());
			$data['notices']=$notices;
			return view('Litigation::Position.position_table',$data)->render();
		
		} catch (Exception $e) {
			Log::error('PositionController-showByMatterId: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}
	//show form 
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
			return view('Litigation::Position.position_form',$data);
		}catch(\Exception $e){
			Log::error('PositionController-create: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}
	//notice show popup
	public function popupForm($matter_id,$master_id,$legal_type_id=null){  
		try {  
			$country = new Country();
			$company = new Company(); 
			$act = new ActMaster();
			$acts = $act->getActs();
			if ($legal_type_id!=null) {				 
			 $legal_type_id =Crypt::decrypt($legal_type_id);
			}
			$appUserRole = new AppUserRole(); 
			$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
			$matter = new Matters(); 
			$matter_id =Crypt::decrypt($matter_id);
			$matter =$matter->getMatterById($matter_id);
			$companies = $company->getCompanyByArrId($appUserRoleCompanyId);
			$countries = $country->getCountry();
			$data =array();
			$data['countries'] =$countries;
			$data['companies'] =$companies; 
			$data['acts'] =$acts; 
			$data['matter'] =$matter; 
			$data['master_id'] =$master_id; 
			$data['legal_type_id'] =$legal_type_id;  
			if ($legal_type_id==1) {
			 $data['case_id'] =$master_id;  
			}

			
			return view('Litigation::Position.position_popup',$data)->render();
		
		} catch (Exception $e) {
			Log::error('PositionController-popupForm: '.$e->getMessage()); 		
			return view('error.home');				
		}
	}
	//position edit
	public function view($position_id){   
		try {
			$position_id = Crypt::decrypt($position_id); 
			$position = new Position();
			$masterTree = new MasterTree();
			$masterTrees = $masterTree->getMasterTreeByMasterId($position_id);
			$positions = $position->getPositionById($position_id); 
			$matter = new Matters(); 
			$act = new ActMaster();
			$acts = $act->getActs();			
	     	$data =array();
			$data['position']=$positions; 
			$data['masterTrees']=$masterTrees;
			$data['acts'] =$acts; 
			return view('Litigation::Position.position_edit',$data)->render();
		
		} catch (Exception $e) {
			Log::error('PositionController-view: '.$e->getMessage()); 		
			return view('error.home');					
		}
	}
	public function store(Request $request){
		try{ 
			$rules=[
			'master_id'=>'nullable',
			'legal_type_id'=>'nullable',
			'matter'=>'nullable',
			'notice_id'=>'nullable',
			'title'=>'nullable',
			'act'=>'nullable',
			'criticality_risk'=>'nullable',
			'hearing_status'=>'nullable',
			'law_type'=>'nullable',
			'owner'=>'nullable',
			'manager'=>'nullable',
			'our_position'=>'nullable',
			'applicable_from'=>'nullable',
			'applicable_to'=>'nullable',
			'rationale'=>'nullable', 
				 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}   
			$position =new Position();
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
			$data['position_id']=generateId(); 
			$data['matter_id']=$request->matter;
			if ($fieldName!='') {
			 $data[$fieldName]=$request->$fieldName; 
			}			
			
		
			
			$data['title']=$request->title;
			$data['act_id']=$request->act;
			$data['criticality_risk_id']=$request->criticality_risk;
			$data['position_status_id']=$request->position_status;
			$data['law_type_id']=$request->law_type;
			$data['owner_id']=$request->owner;
			$data['manager_id']=$request->manager;
			$data['our_position']=$request->our_position;
			$data['applicable_from']=$request->applicable_from;
			$data['applicable_to']=$request->applicable_to;
			$data['rationale']=$request->rationale;  
			$data['created_by']=getUserId(); 
			$data['company_id']=getSetCompanyId(); 
			$data['status']=0;			
			$positions =$position->insArr($data);
			$gereralHelper =new generalHelper();
			$gereralHelper->storeMasterTree($request->matter,$data['position_id'],$master_id,4,getSetCompanyId(),getUserId());
			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;
		 
		}catch(\Exception $e){
			Log::error('PositionController-store: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	
}

