<?php 
namespace App\Modules\Litigation\Controllers\Judgement;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\Judgement;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
use usersSessionHelper;
class JudgementController extends Controller
{ 
	public function index(Request $request){
		try{   
			$data=array();
			$UserRole = new UserRole();
			$data['roleList']=$UserRole->getDetail();
			return view('Litigation::Judgement.judgement_list',$data);
		}catch(\Exception $e){
			Log::error('JudgementController-index: '.$e->getMessage()); 		
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
			return view('Litigation::Judgement.judgement_form',$data);
		}catch(\Exception $e){
			Log::error('JudgementController-create: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//Judgement show popup
	public function popupForm($matter_id,$master_id,$legal_type_id)
	{  
		try {
			$country = new Country();
			$company = new Company(); 
			$act = new ActMaster();
			$acts = $act->getActs();
			$appUserRole = new AppUserRole(); 
			$appUserRoleCompanyId = $appUserRole->getCompanyIdArrayByUserId(getUserId());
			$matter = new Matters(); 
			$legal_type_id =Crypt::decrypt($legal_type_id);
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
			if ($legal_type_id==2) {
			 $data['notice_id'] =$master_id;  
			}
			
			return view('Litigation::Judgement.judgement_popup',$data)->render();
		
		} catch (Exception $e) {
			Log::error('JudgementController-popupForm: '.$e->getMessage()); 		
			return view('error.home');			
		}
	}
	//Judgement view
	public function view($judgement_id)
	{   
		try {
			$judgement_id = Crypt::decrypt($judgement_id); 
			$judgement = new Judgement();
			$masterTree = new MasterTree();
			$masterTrees = $masterTree->getMasterTreeByMasterId($judgement_id);
			$judgements = $judgement->getJudgementById($judgement_id); 
			$matter = new Matters(); 			
	     	$data =array();
			$data['judgement']=$judgements; 
			$data['masterTrees']=$masterTrees;
		return view('Litigation::Judgement.judgement_edit',$data)->render();
		
		} catch (Exception $e) {
			Log::error('JudgementController-view: '.$e->getMessage()); 		
			return view('error.home');			
		}
	}
	//Judgement store
	public function store(Request $request){
		try{  
			$rules=[
				'master_id'=>'nullable',
				'matter_id'=>'nullable',					 
				'judgement_date'=>'nullable|date',
				'judgement'=>'required|string',
				'judgement_summary'=>'nullable|string',
				 
			]; 
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}   
			$judgement =new Judgement();
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
			$data['judgement_id']=generateId(); 				 
			$data['title']=$request->judgement; 		
			$data['judgement_date']=$request->judgement_date;				 
			$data['judgement_summary']=$request->judgement_summary;				
		 	$data['created_by']=getUserId(); 
			$data['company_id']=getSetCompanyId();
			$data['status']=0;			
			$Judgement =$judgement->insArr($data);
			$gereralHelper =new generalHelper(); 
			$gereralHelper->storeMasterTree($request->matter,$data['judgement_id'],$master_id,3,getSetCompanyId(),getUserId());
			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;
			 
		}catch(\Exception $e){
			Log::error('JudgementController-store: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	
}

