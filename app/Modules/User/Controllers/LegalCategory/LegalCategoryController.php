<?php 
namespace App\Modules\Litigation\Controllers\LegalCategory;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\Defaults\LegalCategory;
use App\Modules\Litigation\Models\ActMaster;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
class LegalCategoryController extends Controller
{
	public function __construct(){
		$this->middleware('admin');
	}
	
	public function index(Request $request){
		try{
			$data=array();
			$LegalCategory = new LegalCategory();
			$data["catList"]=$LegalCategory->getLegalCategory();
			return view('Litigation::LegalCategory.view',$data);
		}catch(\Exception $e){
			Log::error('LegalCategoryController-index: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}


	//Legal Category Function Start Here
	public function addCat(Request $request){
		try{
			return view('Litigation::LegalCategory.Category.addpopup');
		}catch(\Exception $e){
			Log::error('LegalCategoryController-addCat: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function refreshCat(Request $request){
		try{
			$data=array();
			$LegalCategory = new LegalCategory();
			$data['catList']=$LegalCategory->getLegalCategory();
			return view('Litigation::LegalCategory.Category.table',$data);
		}catch(\Exception $e){
			Log::error('LegalCategoryController-refreshCat: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function submitCategory(Request $request){
		try{
			$rules=[
				'cat_name' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$insArr['status'] = 1;
			$insArr['default_status'] = 1;
			$insArr['name'] = $request->cat_name;
			$LegalCategory = new LegalCategory();
			$LegalCategory->insArr($insArr);
			
			$response["status"]=1;
			$response["msg"]="Category added Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('LegalCategoryController-submitCategory: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}	

	public function editCat(Request $request){
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
			$LegalCategory= new LegalCategory();
			$data['list']=$LegalCategory->getLegalCategoryById($id);
			return view('Litigation::LegalCategory.Category.editpopup',$data);
		}catch(\Exception $e){
			Log::error('LegalCategoryController-editCat: '.$e->getMessage()); 		
			return view('error.home');									
			return false;
		}
	}


	public function updateCategory(Request $request){
		try{
			$id = Crypt::decrypt($request->cat_id);
			$rules=[
				$id => 'numeric',
				'cat_name' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$updArr['name'] = $request->cat_name;
			$LegalCategory = new LegalCategory();
			$LegalCategory->updateCategory($updArr,$id);
			
			$response["status"]=1;
			$response["msg"]="Category update Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('LegalCategoryController-updateCategory: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}	
	//Legal Category Function End Here	

	//Act Function Start Here
	public function actList(Request $request){
		try{
			$data=array();
			$ActMaster = new ActMaster();
			$data["actList"]=$ActMaster->getActs();
			return view('Litigation::LegalCategory.Act.view',$data);
		}catch(\Exception $e){
			Log::error('LegalCategoryController-actList: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function addAct(Request $request){
		try{
			$LegalCategory = new LegalCategory();
			$data["catList"]=$LegalCategory->getLegalCategory();
			return view('Litigation::LegalCategory.Act.addpopup',$data);
		}catch(\Exception $e){
			Log::error('LegalCategoryController-addCat: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function refreshAct(Request $request){
		try{
			$data=array();
			$ActMaster = new ActMaster();
			$data["actList"]=$ActMaster->getActs();
			return view('Litigation::LegalCategory.Act.table',$data);
		}catch(\Exception $e){
			Log::error('LegalCategoryController-refreshCat: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function submitAct(Request $request){
		try{
			$rules=[
				'legal_cat' => 'required',
				'act_name' => 'required',
				'short_name' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$insArr['status'] = 1;
			$insArr['default_status'] = 1;
			$insArr['name'] = $request->act_name;
			$insArr['short_name'] = $request->short_name;
			$insArr['legal_category'] = implode(',',decrypt_array($request->legal_cat));
			$ActMaster = new ActMaster();
			$ActMaster->insArr($insArr);
			
			$response["status"]=1;
			$response["msg"]="Act added Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('LegalCategoryController-submitAct: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}

	public function editAct(Request $request){
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
			$ActMaster = new ActMaster();
			$LegalCategory= new LegalCategory();
			$data['list']=$ActMaster->getActById($id);
			$data['catList']=$LegalCategory->getLegalCategory();

			return view('Litigation::LegalCategory.Act.editpopup',$data);
		}catch(\Exception $e){
			Log::error('LegalCategoryController-editAct: '.$e->getMessage()); 		
			return view('error.home');									
			return false;
		}
	}	

	public function updateAct(Request $request){
		try{
			$id = Crypt::decrypt($request->act_id);
			$rules=[
				$id => 'numeric',
				'legal_cat' => 'required',
				'act_name' => 'required',
				'short_name' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$updArr['name'] = $request->act_name;
			$updArr['short_name'] = $request->short_name;
			$updArr['legal_category'] = implode(',',decrypt_array($request->legal_cat));
			$ActMaster = new ActMaster();
			$ActMaster->updateAct($updArr,$id);
			
			$response["status"]=1;
			$response["msg"]="Act update Successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('LegalCategoryController-updateAct: '.$e->getMessage()); 		
			return view('error.home');									
		}
	}	

}

