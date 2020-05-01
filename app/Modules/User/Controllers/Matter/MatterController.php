<?php 
namespace App\Modules\Litigation\Controllers\Matter;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\Defaults\MatterGroup;
use App\Modules\Litigation\Models\Defaults\MatterType;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
use usersSessionHelper;
class MatterController extends Controller
{ 
	public function index(Request $request){
		try{
			$data=array();
			$UserRole = new UserRole();
			$data['roleList']=$UserRole->getDetail();
			return view('Litigation::Matter.matter_list',$data);
		}catch(\Exception $e){
			Log::error('MatterController-index: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//Matter table show
	public function show()
	{  
		try{
			$matter = new Matters();
			$matters = $matter->getMatterByCompanyId(getSetCompanyId());
			$data['matters']=$matters;
			return view('Litigation::Matter.matter_table',$data)->render();
		}catch(\Exception $e){
			Log::error('MatterController-show: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//show matter form
	public function create(Request $request){
		try{ 
			  
			$matterGroup = new MatterGroup();
			$matterGroups = $matterGroup->getMatterGroupByCompanyId(getSetCompanyId());
			$matterType = new MatterType();
			$matterTypes = $matterType->getMatterTypeByCompanyId(getSetCompanyId());
			$data =array();			 
			$data['matterGroups'] =$matterGroups; 
			$data['matterTypes'] =$matterTypes; 
			 
			return view('Litigation::Matter.matter_form',$data)->render();
		}catch(\Exception $e){
			Log::error('MatterController-create: '.$e->getMessage()); 		
			return view('error.home');
		}
	}	
	//store matter
	public function store(Request $request){
		try {
			$rules=[
				'matter'=>'required', 
				'matter_group'=>'required',
				'matter_type'=>'required', 
				'matter_counter_party_name'=>'required', 
				'contact_persion'=>'required', 
				'matter_description'=>'nullable', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}  
			$matter =new Matters();
			$data =array();
			$data['matter_id']=generateId(); 
			$data['name']=$request->matter;
			$data['matter_type_id']=Crypt::decrypt($request->matter_type);
			$data['matter_group_id']=Crypt::decrypt($request->matter_group);
			$data['matter_counter_party_name']=$request->matter_counter_party_name;
			$data['contact_persion']=$request->contact_persion;
			$data['matter_description']=$request->matter_description; 
			$data['created_by']=getUserId();
			$data['company_id']=getSetCompanyId();
			$data['status']=1;			
			$data['matter_status_id']=0;			
			$matters =$matter->insArr($data);
			$response=array();
			$response["status"]=1;
			$response["msg"]="Save Successfully";
			return $response;

		} catch (Exception $e) {
			Log::error('MatterController-store: '.$e->getMessage()); 		
			return view('error.home'); 
		} 
	}
	//store matter
	public function update(Request $request,$matter_id){
		try {  
			$rules=[
				'matter'=>'required', 
				'matter_group'=>'required',
				'matter_type'=>'required', 
				'update_remark'=>'required', 
				'matter_counter_party_name'=>'required', 
				'contact_persion'=>'required', 
				'matter_description'=>'nullable', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}  
			$matter_id =Crypt::decrypt($matter_id);
			$matter =new Matters();
			$data =array();			
			$data['name']=$request->matter;
			$data['matter_type_id']=Crypt::decrypt($request->matter_type);
			$data['matter_group_id']=Crypt::decrypt($request->matter_group);
			$data['matter_counter_party_name']=$request->matter_counter_party_name;
			$data['contact_persion']=$request->contact_persion;
			$data['matter_description']=$request->matter_description; 			  
			$data['update_remark']=$request->update_remark; 			  
			$data['updated_by']=getUserId(); 
			$matters =$matter->updateArr($matter_id,$data);
			$response=array();
			$response["status"]=1;
			$response["msg"]="Update Successfully";
			return $response;

		} catch (Exception $e) {
			Log::error('MatterController-update: '.$e->getMessage()); 		
			return view('error.home'); 
		} 
	}
	//view matter all releted details
	public function matterView(Request $request){
		try {
			$matter_id =Crypt::decrypt($request->matter_id);
			$rules=[
				$matter_id=>'numeric', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 
			$data =array(); 
			$data['matter_id'] = $matter_id;
			$data['type'] = $request->type;	  
			return view('Litigation::Matter.matter_view',$data);
		} catch (Exception $e) {
			Log::error('MatterController-matterView: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function matterpopup(Request $request){
		try {
			$matter_id =Crypt::decrypt($request->matter_id);
			$rules=[
				$matter_id=>'numeric', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 
			$matter = new Matters();
			$matter =$matter->getMatterById($matter_id); 
			$matterGroup = new MatterGroup();
			$matterGroups = $matterGroup->getMatterGroupByCompanyId(getSetCompanyId());
			$matterType = new MatterType();
			$matterTypes = $matterType->getMatterTypeByCompanyId(getSetCompanyId());
			$data =array(); 
			$data['matter'] =$matter;  
			$data['matterGroups'] =$matterGroups; 
			$data['matterTypes'] =$matterTypes; 		  
			return view('Litigation::Matter.matter_view_details',$data);
		} catch (Exception $e) {
			Log::error('MatterController-matterpopup: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function editmatter(Request $request){
		try {
			$matter_id =Crypt::decrypt($request->matter_id);
			$rules=[
				$matter_id=>'numeric', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 
			$matter = new Matters();
			$matter =$matter->getMatterById($matter_id); 
			$matterGroup = new MatterGroup();
			$matterGroups = $matterGroup->getMatterGroupByCompanyId(getSetCompanyId());
			$matterType = new MatterType();
			$matterTypes = $matterType->getMatterTypeByCompanyId(getSetCompanyId());
			$data = array(); 
			$data['matter'] =$matter;  
			$data['matterGroups'] =$matterGroups; 
			$data['matterTypes'] =$matterTypes; 		  
			return view('Litigation::Matter.matter_edit_form',$data);
		} catch (Exception $e) {
			Log::error('MatterController-editmatter: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	//tree view
	public function treeView(Request $request){
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

			$matter = new Matters(); 
			$matter =$matter->getMatterById($matter_id);
			$matters =$matter->getMatterByCompanyId(getSetCompanyId());
			$case = new Cases();
			$cases = $case->getCaseByMatterId($matter_id,getSetCompanyId()); 
			$data =array(); 
			$data['matter'] =$matter;    
			$data['matters'] =$matters; 
			$data['cases'] =$cases;  
			return view('Litigation::Matter.tree_view',$data);
		}catch(\Exception $e){
			Log::error('MatterController-treeView: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//tree view
	public function treeShowData(Request $request){
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
			$matter = new Matters();  
			$masterTree = new MasterTree();
			$masterTrees = $masterTree->getMasterTreeByMatterId($matter_id,getSetCompanyId());  
			$data =array(); 
			$data['matter_id'] =$matter_id;    
			$data['masterTrees'] =$masterTrees; 

			if (count($masterTrees)==0) {
				return 'No Data Available In Matter';
			}
			return view('Litigation::Matter.tree_view_data',$data);
		}catch(\Exception $e){
			Log::error('MatterController-treeShowData: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//case menus view
	public function menusShow(Request $reqeust,$matter_id){
		try{   
			$matter_id =Crypt::decrypt($matter_id);
			$legal_type_id =$reqeust->legal_type_id!='null'?Crypt::decrypt($reqeust->legal_type_id):'null'; 
			$master_id =$reqeust->master_id!='null'?Crypt::decrypt($reqeust->master_id):'null';;  
			$matter = new Matters(); 
		    $matter =$matter->getMatterById($matter_id); 
			$data =array();  
			$data['master_id'] =$master_id; 
			$data['legal_type_id'] =$legal_type_id; 
			$data['matter'] =$matter; 
			if ($reqeust->legal_type_id!='null') { 		 
				return view('Litigation::Matter.node_by_menu_show',$data);
			}
			return view('Litigation::Matter.matter_by_menu_show',$data);
		}catch(\Exception $e){
			Log::error('MatterController-menusShow: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	//case menus view
	public function matterTreePopupShow(Request $request){
		try{  
			 $legal_type_id =Crypt::decrypt($request->legal_type_id);
			 $title =Crypt::decrypt($request->title);
			$data =array();
			if ($legal_type_id!=null) {
			 $data['legal_type_id'] =$request->legal_type_id; 
			 $data['title'] = $title; 
			}
			if ($legal_type_id==1) {
				$data['case_id'] =$request->master_id;   
			    return view('Litigation::Cases.cases_popup_view',$data)->render(); 
			}else if ($legal_type_id==2) {
				$data['notice_id'] =$request->master_id; 
			    return view('Litigation::Notice.notice_popup_view',$data)->render(); 		 
			}else if ($legal_type_id==3) {
				$data['judgement_id'] =$request->master_id; 
			    return view('Litigation::Judgement.judgement_popup_view',$data)->render(); 	 
			}else if ($legal_type_id==4) {
				$data['hearing_id'] =$request->master_id; 
			    return view('Litigation::Hearing.hearing_popup_view',$data)->render(); 	
			}else if ($legal_type_id==5) {
				 $data['orders_id'] =$request->master_id; 
			    return view('Litigation::Orders.orders_popup_view',$data)->render(); 	
			}else if ($legal_type_id==6) {
				$data['appeal_id'] =$request->master_id; 
			    return view('Litigation::Appeal.appeal_popup_view',$data)->render(); 	 
			}else if ($legal_type_id==7) {
				$data['settlement_id'] =$request->master_id; 
			    return view('Litigation::settlement.settlement_popup_view',$data)->render(); 	 
			}else{
				return 'null';
			}	
		}catch(\Exception $e){
			Log::error('MatterController-matterTreePopupShow: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function noticecasehearinglist(Request $request){
		try {
			$matter_id = Crypt::decrypt($request->matter_id);
			$legal_type_id =Crypt::decrypt($request->legal_type_id);
			$rules=[
				$matter_id=>'numeric', 
				$legal_type_id=>'numeric', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 
			
			$data = $arr = array();
			$Cases = new Cases();
			$Notice = new Notice();
			$Hearing = new Hearing();
			$arr['childId'] = getChildUserId();
			$arr['matter_id'] = $data['matter_id'] = $matter_id;
			$arr['legal_type_id'] = $data['legal_type_id'] = $legal_type_id;
			if($legal_type_id == 1){
				$data['tableResult'] = $Cases->getcaselistForUserDashboard($arr,2);
			}elseif($legal_type_id == 2){
				$data['tableResult'] = $Notice->getnoticelistForUserDashboard($arr,2);
			}elseif($legal_type_id == 4){
				$data['tableResult'] = $Hearing->gethearinglistForUserDashboard($arr,2);
			}
			return view('Litigation::Matter.noticecasehearinglist',$data);
		} catch (Exception $e) {
			Log::error('MatterController-noticecasehearinglist: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	
}

