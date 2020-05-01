<?php 
namespace App\Modules\Litigation\Controllers\Orders;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\Orders;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
use usersSessionHelper;
class OrdersController extends Controller
{ 
	public function index(Request $request){
		try{   
			$data=array();
			$UserRole = new UserRole();
			$data['roleList']=$UserRole->getDetail();
			return view('Litigation::Orders.orders_list',$data);
		}catch(\Exception $e){
			Log::error('OrdersController-index: '.$e->getMessage()); 		
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
			return view('Litigation::Orders.orders_form',$data);
		}catch(\Exception $e){
			Log::error('OrdersController-create: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//Orders show popup
	public function popupForm($matter_id,$master_id,$legal_type_id){  
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
			
			return view('Litigation::Orders.orders_popup',$data)->render();
		
		} catch (Exception $e) {
			Log::error('OrdersController-popupForm: '.$e->getMessage()); 		
			return view('error.home');			
		}
	}
	//Orders store
	public function store(Request $request){
		try{  
			$rules=[
				'master_id'=>'nullable',
				'matter_id'=>'nullable',
				'notice_no'=>'nullable',
				'orders_date'=>'nullable',
				'orders_type_id'=>'nullable',
				'exposure_gain'=>'nullable',
				'amount'=>'nullable',
				'satisfied_with_outcome'=>'nullable',
				'orders_summary'=>'nullable',
				'final_implication'=>'nullable',
				'comment'=>'nullable',
				'last_date_of_filing_appeal'=>'nullable',
				'will_file_appeal'=>'nullable',
				'set_reminder_date'=>'nullable',
				'set_reminder_frequency'=>'nullable',
				'responsibility'=>'nullable',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}   
			$order =new Orders();
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
			$data['orders_id']=generateId(); 
			$data['orders_no']=$request->orders_no; 
			$data['title']=$request->orders_no; 		
			$data['orders_date']=$request->orders_date;
			$data['orders_type_id']=$request->orders_type;
			$data['exposure_gain']=$request->exposure_gain;
			$data['amount']=$request->amount;
			$data['satisfied_with_outcome']=$request->satisfied_with_outcome;
			$data['orders_summary']=$request->orders_summary;
			$data['final_implication']=$request->final_implication;
			$data['comment']=$request->comment;
			$data['last_date_of_filing_appeal']=$request->last_date_of_filing_appeal;
			$data['will_file_appeal']=$request->will_file_appeal;
			$data['set_reminder_date']=$request->set_reminder_date;
			$data['set_reminder_frequency_id']=$request->set_reminder_frequency;
			$data['notify_id']=$request->responsibility; 
		 	$data['created_by']=getUserId(); 
			$data['company_id']=getSetCompanyId();
			$data['status']=0;			
			$orders =$order->insArr($data);
			$gereralHelper =new generalHelper();
			$gereralHelper->storeMasterTree($request->matter,$data['orders_id'],$master_id,6,getSetCompanyId(),getUserId());
			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;
		 
		}catch(\Exception $e){
			Log::error('OrdersController-store: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	
}

