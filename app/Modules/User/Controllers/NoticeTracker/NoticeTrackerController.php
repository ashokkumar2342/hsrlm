<?php 
namespace App\Modules\Litigation\Controllers\NoticeTracker;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Permission;
use App\Modules\Litigation\Models\NoticeTracker;
use App\Modules\Litigation\Models\DefaultSubMenu;
use App\Modules\Litigation\Models\NoticeTrackerDocument;
use Auth;
use Response;
use Validator;
use Illuminate\Http\Request;
use App\Helpers\generalHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
class NoticeTrackerController extends Controller
{ 
	public function index(Request $request){
		try{
			return view('Litigation::NoticeTracker.view');
		}catch(\Exception $e){
			Log::error('NoticeTrackerController-index: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function viewtable(Request $request){
		try{
			$data=$arr=array();
			$arr['id'] = getSetCompanyId();
			$NoticeTracker = new NoticeTracker();
			$data['noticeResult'] = $NoticeTracker->getTrackerList($arr,4);
			return view('Litigation::NoticeTracker.viewtable',$data);
		}catch(\Exception $e){
			Log::error('NoticeTrackerController-viewtable: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function download(Request $request){  
		try{   
			$id = Crypt::decrypt($request->id);
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

			$arr['id'] = $id;
			$NoticeTrackerDocument = new NoticeTrackerDocument();
			$document = $NoticeTrackerDocument->getResult($arr,3); 

			$storagePath = storage_path('domainstorage/'.$document->document_url);
			$mimeType = mime_content_type($storagePath);
			if( ! \File::exists($storagePath)){
				return view('error.home');
			}
			$headers = array(
				'Content-Type' => $mimeType,
				'Content-Disposition' => 'inline; filename="'.$document->document_url.'"'
			);
			return Response::make(file_get_contents($storagePath), 200, $headers); 

			$response['status'] = 1; 
			return response()->json($response); 
		}catch(\Exception $e){
           Log::error('NoticeTrackerController-download: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

   	public function popup(Request $request){  
		try{   
			$id = Crypt::decrypt($request->id);
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

			$mainMenu = getMenuId()[4];
			$data['tracker_id'] = $arr['id'] = $id;

			$Permission = new Permission();
			$NoticeTracker = new NoticeTracker();
			$DefaultSubMenu = new DefaultSubMenu();
			$data['trackerResult'] = $NoticeTracker->getTrackerList($arr,3);
			$subMenuId = $DefaultSubMenu->getSubMenuByMenuIdAndSubMenuName($mainMenu)->id;
			$data['userList'] = $Permission->getUserLsit(getSetCompanyId(),$subMenuId);
			return view('Litigation::NoticeTracker.popup',$data);
		}catch(\Exception $e){
           Log::error('NoticeTrackerController-popup: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

   	public function accept(Request $request){  
		try{   
			$tracker_id = Crypt::decrypt($request->tracker_id);
			$rules=[
				$tracker_id => 'numeric',
				'remark' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			
			$updArr = array();
			$NoticeTracker = new NoticeTracker();

			$updArr['status'] = 2;
			$updArr['user_remark'] = $request->remark;
			$updArr['action_by'] = getUserId();
			$NoticeTracker->updateArr($updArr,$tracker_id);

			$response=array();
			$response["status"]=1;
			$response["msg"]='Accepted';
	        return response()->json($response);// response as json
		}catch(\Exception $e){
           Log::error('NoticeTrackerController-accept: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

   	public function reject(Request $request){  
		try{   
			$tracker_id = Crypt::decrypt($request->tracker_id);
			$rules=[
				$tracker_id => 'numeric',
				'remark' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			
			$updArr = array();
			$NoticeTracker = new NoticeTracker();

			$updArr['status'] = 3;
			$updArr['user_remark'] = $request->remark;
			$updArr['action_by'] = getUserId();
			$NoticeTracker->updateArr($updArr,$tracker_id);

			$response=array();
			$response["status"]=1;
			$response["msg"]='Rejected';
	        return response()->json($response);// response as json
		}catch(\Exception $e){
           Log::error('NoticeTrackerController-reject: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

   	public function transfer(Request $request){  
		try{   
			$tracker_id = Crypt::decrypt($request->tracker_id);
			$rules=[
				$tracker_id => 'numeric',
				'user_list' => 'required',
				'remark' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			
			$updArr = array();
			$NoticeTracker = new NoticeTracker();

			$updArr['transfer_by'] = getUserId();
			$updArr['user_remark'] = $request->remark;
			$updArr['transfer_to'] = Crypt::decrypt($request->user_list);

			$NoticeTracker->updateArr($updArr,$tracker_id);

			$response=array();
			$response["status"]=1;
			$response["msg"]='Transfered';
	        return response()->json($response);// response as json
		}catch(\Exception $e){
           Log::error('NoticeTrackerController-transfer: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}
	
}

