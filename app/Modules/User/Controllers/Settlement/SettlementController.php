<?php 
namespace App\Modules\Litigation\Controllers\Settlement;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\Settlement;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
use usersSessionHelper;
class SettlementController extends Controller
{ 
	public function index(Request $request){
		try{   
			$data=array();
			$UserRole = new UserRole();
			$data['roleList']=$UserRole->getDetail();
			return view('Litigation::Settlement.settlement_list',$data);
		}catch(\Exception $e){
			Log::error('SettlementController-index: '.$e->getMessage()); 		
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
			return view('Litigation::Settlement.settlement_form',$data);
		}catch(\Exception $e){
			Log::error('SettlementController-create: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}
	//Settlement show popup
	public function popupForm($matter_id,$master_id,$legal_type_id){  
		try {  
			$matter = new Matters(); 
			$legal_type_id =Crypt::decrypt($legal_type_id);
			$matter_id =Crypt::decrypt($matter_id);
			$matter =$matter->getMatterById($matter_id); 
			$data =array(); 
			$data['matter'] =$matter; 
			$data['master_id'] =$master_id; 
			$data['legal_type_id'] =$legal_type_id; 
			if ($legal_type_id==1) {
				$data['case_id'] =$master_id;  
			}
			if ($legal_type_id==2) {
				$data['notice_id'] =$master_id;  
			}
			
			return view('Litigation::Settlement.settlement_popup',$data)->render();

		} catch (Exception $e) {
			Log::error('SettlementController-popupForm: '.$e->getMessage()); 		
			return view('error.home');				
		}
	}
	//Settlement show popup
	public function view($settlement_id){   
		try {
			$settlement_id = Crypt::decrypt($settlement_id); 
			$settlement = new Settlement();
			$masterTree = new MasterTree();
			$masterTrees = $masterTree->getMasterTreeByMasterId($settlement_id);
			$settlements = $settlement->getSettlementById($settlement_id); 
			$matter = new Matters(); 			
			$data =array();
			$data['settlement']=$settlements; 
			$data['masterTrees']=$masterTrees;
			return view('Litigation::Settlement.settlement_edit',$data)->render();

		} catch (Exception $e) {
			Log::error('SettlementController-view: '.$e->getMessage()); 		
			return view('error.home');				
		}
	}
	//Settlement store
	public function store(Request $request){
		try{  
			$rules=[
				'master_id'=>'nullable',
				'matter_id'=>'nullable', 
				'settlement_date'=>'nullable',
				'settlement_type'=>'nullable', 
				'settlement_summary'=>'nullable', 
				'amount'=>'nullable', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}   
			$settlement =new Settlement();
			$legal_type_id =Crypt::decrypt($request->legal_type_id);
			$master_id =Crypt::decrypt($request->master_id); 
			if (is_null($master_id)) {
				$response["status"]=0;
				$response["msg"]="Parent Id  Required";
				return $response;
			}
			if ($legal_type_id!=null) {
				$fieldName = getLegalType($legal_type_id)->field_name; 
			}else{
				$fieldName =''; 
			}			
			$data =array(); 				
			$data['matter_id']=$request->matter;
			if ($fieldName!='') {
				$data[$fieldName]=$request->$fieldName; 
			} 
			$data['settlement_id']=generateId(); 

			$data['title']=$request->settlement; 		
			$data['settlement_date']=$request->settlement_date;
			$data['settlement_type_id']=$request->settlement_type;				 
			$data['amount']=$request->amount;
			$data['settlement_summary']=$request->settlement_summary;				 
			$data['settlement_status']=$request->settlement_status;				 
			$data['created_by']=getUserId(); 
			$data['company_id']=getSetCompanyId();
			$data['status']=0;			
			$settlement =$settlement->insArr($data);
			$gereralHelper =new generalHelper();
			$gereralHelper->storeMasterTree($request->matter,$data['settlement_id'],$master_id,8,getSetCompanyId(),getUserId());
			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;

		}catch(\Exception $e){
			Log::error('SettlementController-store: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}
	//Settlement update
	public function update(Request $request,$settlement_id){
		try{  
			$rules=[
				'master_id'=>'nullable',
				'matter_id'=>'nullable', 
				'settlement_date'=>'nullable',
				'settlement_type'=>'nullable', 
				'settlement_summary'=>'nullable', 
				'amount'=>'nullable', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}   
			$settlement =new Settlement();
			$legal_type_id =Crypt::decrypt($request->legal_type_id);
			$master_id =Crypt::decrypt($request->master_id); 
			if (is_null($master_id)) {
				$response["status"]=0;
				$response["msg"]="Parent Id  Required";
				return $response;
			}
			if ($legal_type_id!=null) {
				$fieldName = getLegalType($legal_type_id)->field_name; 
			}else{
				$fieldName =''; 
			}			
			$data =array(); 				
			$data['matter_id']=$request->matter;
			if ($fieldName!='') {
				$data[$fieldName]=$request->$fieldName; 
			} 
			$data['settlement_id']=generateId(); 

			$data['title']=$request->settlement; 		
			$data['settlement_date']=$request->settlement_date;
			$data['settlement_type_id']=$request->settlement_type;				 
			$data['amount']=$request->amount;
			$data['settlement_summary']=$request->settlement_summary;				 
			$data['settlement_status']=$request->settlement_status;				 
			$data['created_by']=getUserId(); 
			$data['company_id']=getSetCompanyId();
			$data['status']=0;			
			$settlement =$settlement->insArr($data);
			$gereralHelper =new generalHelper();
			$gereralHelper->storeMasterTree($request->matter,$data['settlement_id'],$master_id,8,getSetCompanyId(),getUserId());
			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;
		}catch(\Exception $e){
			Log::error('SettlementController-update: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}		
}

