<?php 
namespace App\Modules\Litigation\Controllers\MatterMaster;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Defaults\MatterType;
use App\Modules\Litigation\Models\Defaults\MatterGroup;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
class MatterMasterController extends Controller
{
	public function __construct(){
		$this->middleware('admin');
	}
	
	// public function index(Request $request){
	// 	try{
	// 		$data=array();
	// 		$MatterGroup = new MatterGroup();
	// 		$data["groupList"] = $MatterGroup->getMatterGroup();
	// 		return view('Litigation::MatterMaster.view',$data);
	// 	}catch(\Exception $e){
	// 		Log::error('MatterMasterController-index: '.$e->getMessage()); 		
	// 		return view('error.home');									
	// 	}
	// }

	public function groupList(Request $request){
		try{
			$data=array();
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data['CompanyId'] = $id;
			$MatterGroup = new MatterGroup();
			$data["groupList"] = $MatterGroup->getMatterGroupByCompanyId($id);
			return view('Litigation::MatterMaster.Group.view',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-groupList: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	//Group Function Start Here
	public function addGroup(Request $request){
		try{
			$data=array();
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data['CompanyId'] = $id;
			return view('Litigation::MatterMaster.Group.addgroup',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-addGroup: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function refViewGroupList(Request $request){
		try{
			$data=array();
			$id=Crypt::decrypt($request->company_id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data['CompanyId'] = $id;
			$MatterGroup = new MatterGroup();
			$data["groupList"]=$MatterGroup->getMatterGroupByCompanyId($id);
			return view('Litigation::MatterMaster.Group.refview',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-refViewGroupList: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function submitGroup(Request $request){
		try{
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				$company_id => 'numeric',
				'group_name' => 'required|unique:default_matter_group,name,NULL,id,company_id,' . $company_id,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$MatterGroup = new MatterGroup();
			$insArr['status'] = 1;
			$insArr['default_status'] = 1;
			$insArr['company_id'] = $company_id;
			$insArr['name'] = $request->group_name;
			$MatterGroup->insArr($insArr);

			$response["status"]=1;
			$response["msg"]="Group added Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('MatterMasterController-submitGroup: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function editGroup(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data=array();
			$MatterGroup = new MatterGroup();
			$data['list']=$MatterGroup->getMatterGroupById($id);
			return view('Litigation::MatterMaster.Group.editgroup',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-editGroup: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function updateGroup(Request $request){
		try{
			$id=Crypt::decrypt($request->group_id);
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				$id => 'numeric',
				$company_id => 'numeric',
				'group_name' => 'required|unique:default_matter_group,name,'.$id.',id,company_id,' . $company_id,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$response=$updArr=array();
			$MatterGroup = new MatterGroup();
			$updArr['name'] = $request->group_name;
			$MatterGroup->updateGroup($updArr,$id);
			$response["status"]=1;
			$response["msg"]="Group updated Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('MatterMasterController-updateGroup: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}
	//Group Function End Here



	//Type Function Start Here
	public function typeList(Request $request){
		try{
			$data=array();
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data['CompanyId'] = $id;
			$MatterType = new MatterType();
			$data["typeList"] = $MatterType->getMatterTypeByCompanyId($id);
			return view('Litigation::MatterMaster.Type.view',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-typeList: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function addType(Request $request){
		try{
			$data=array();
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data['CompanyId'] = $id;
			return view('Litigation::MatterMaster.Type.addtype',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-addType: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function refViewTypeList(Request $request){
		try{
			$data=array();
			$id=Crypt::decrypt($request->company_id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data['CompanyId'] = $id;
			$MatterType = new MatterType();
			$data["typeList"] = $MatterType->getMatterTypeByCompanyId($id);
			return view('Litigation::MatterMaster.Type.refview',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-refViewTypeList: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function submitType(Request $request){
		try{
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				$company_id => 'numeric',
				'type_name' => 'required|unique:default_matter_type,name,NULL,id,company_id,' . $company_id,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$MatterType = new MatterType();
			$insArr['status'] = 1;
			$insArr['default_status'] = 1;
			$insArr['company_id'] = $company_id;
			$insArr['name'] = $request->type_name;
			$MatterType->insArr($insArr);

			$response["status"]=1;
			$response["msg"]="Type added Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('MatterMasterController-submitType: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function editType(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data=array();
			$MatterType = new MatterType();
			$data['list']=$MatterType->getMatterTypeById($id);
			return view('Litigation::MatterMaster.Type.edittype',$data);
		}catch(\Exception $e){
			Log::error('MatterMasterController-editType: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function updateType(Request $request){
		try{
			$id=Crypt::decrypt($request->type_id);
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				$id => 'numeric',
				$company_id => 'numeric',
				'type_name' => 'required|unique:default_matter_type,name,'.$id.',id,company_id,'.$company_id,
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$response=$updArr=array();
			$MatterType = new MatterType();
			$updArr['name'] = $request->type_name;
			$MatterType->updateType($updArr,$id);
			$response["status"]=1;
			$response["msg"]="Type updated Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('MatterMasterController-updateType: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}
	//Type Function End Here

}

