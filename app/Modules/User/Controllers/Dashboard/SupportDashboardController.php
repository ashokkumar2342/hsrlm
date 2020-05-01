<?php 
namespace App\Modules\Litigation\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Login\Models\AppUserType;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\ReceiptMode;
use App\Modules\Litigation\Models\NoticeTracker;
use App\Modules\Litigation\Models\CompanyLocation;
use App\Modules\Litigation\Models\SupportUserCompany;
use App\Modules\Litigation\Models\DefaultNoticeCategory;
use App\Modules\Litigation\Models\NoticeTrackerDocument;
use URL;
use Auth;
use Session;
use Redirect;
use Response;
use Validator;
use MailHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;				// log for exception handling
class SupportDashboardController extends Controller
{

	public function __construct(){
		$this->middleware('support');
	}

	public function index(Request $request){
		try{ 
			$arr['user_id'] = getUserId();

			$Company = new Company();
			$ReceiptMode = new ReceiptMode();
			$SupportUserCompany = new SupportUserCompany();
			$DefaultNoticeCategory = new DefaultNoticeCategory();

			$list = $SupportUserCompany->getResult($arr,3)->company_id;
			$data['temp_id'] = $arr['temp_id'] = generateId();

			$data['modeList'] = $ReceiptMode->getReceiptMode();
			$data['categoryList'] = $DefaultNoticeCategory->getCategory();
			$data['companyList'] = $Company->getCompanyByArrId(explode(',',$list));

			return view('Litigation::Dashboard.Support.dashboard',$data);
		}catch(\Exception $e){
			Log::error('SupportDashboardController-index: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function edit(Request $request){
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

			$data['type'] = 2;
			$arr['user_id'] = getUserId();
			$data['id'] = $arr['id'] = $id;

			$Company = new Company();
			$ReceiptMode = new ReceiptMode();
			$NoticeTracker = new NoticeTracker();
			$SupportUserCompany = new SupportUserCompany();
			$DefaultNoticeCategory = new DefaultNoticeCategory();
			$NoticeTrackerDocument = new NoticeTrackerDocument();

			$data['modeList'] = $ReceiptMode->getReceiptMode();
			$list = $SupportUserCompany->getResult($arr,3)->company_id;
			$data['companyList'] = $Company->getCompanyByArrId(explode(',',$list));

			$data['result'] = $NoticeTracker->getTrackerList($arr,3);
			$data['categoryList'] = $DefaultNoticeCategory->getCategory();

			$data['docList'] = $NoticeTrackerDocument->getResult($arr,2);

			return view('Litigation::Dashboard.Support.popup',$data);
		}catch(\Exception $e){
			Log::error('SupportDashboardController-edit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function createNoticeTracker(Request $request){
		try{
			$temp_id = Crypt::decrypt($request->temp_id);
			$rules=[
				'company_name' => 'required',
				'receive_date' => 'required|date|date_format:Y-m-d',
				'mode_of_receipt' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$mailArr = array();
			$company_id = Crypt::decrypt($request->company_name);

			$Company = new Company();
			$mailArr['users'] = $Company->getAllUserByCompanyId($company_id);
			$mailArr['entity_name'] = $Company->getDetail($company_id)->name;

			$arr['id'] = $temp_id;
			$user_id = $arr['user_id'] = getUserId();

			$AppUserType = new AppUserType(); 
			$location_id = $AppUserType->getResult($arr,'getUserType')->location;

			$CompanyLocation = new CompanyLocation();
			$mailArr['location_name'] = $CompanyLocation->getAlldetailofCompanyLocation($location_id)->location_name;

			$AppUsers = new AppUsers();
			$mailArr['support_name'] = $AppUsers->getdetailbyuserid(getUserId())->name;
			$mailArr['date'] = date('d m,Y');

			$NoticeTracker = new NoticeTracker(); 
			$NoticeTrackerDocument = new NoticeTrackerDocument(); 

			$doclist = $NoticeTrackerDocument->getResult($arr,1);

			if(count(array_filter((array)$doclist)) == 0){
				$response=array();
				$response["status"]=0;
				$response["msg"]='Document required!';
				return response()->json($response);
			}

			$insArr = array();

			$insArr['company_id'] = $company_id;
			$insArr['location_id'] = $location_id;
			if(!empty($request->notice_category)){
				$insArr['category'] = Crypt::decrypt($request->notice_category);
			}
			$insArr['receive_date'] = $request->receive_date;
			$insArr['mode_of_receipt'] = Crypt::decrypt($request->mode_of_receipt);
			$insArr['remark']=$request->remark;
			$insArr['created_by']=$user_id;

			$id = $NoticeTracker->insArr($insArr);

			$MailHelper = new MailHelper();
			if(!empty($id)){
				$MailHelper->noticetrackeradd($mailArr);
			}
			$updArr['tracker_id'] = $id;
			$NoticeTrackerDocument->updateArrByTempId($updArr,$temp_id);

			$response = array();
			$response["status"]=1;
			$response["msg"]="Notice tracker added successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('SupportDashboardController-createNoticeTracker: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function getTrackerList(Request $request){
		try{ 
			$NoticeTracker = new NoticeTracker();
			$arr['id'] = getUserId();
			$data['trackerList'] = $NoticeTracker->getTrackerList($arr,2);
			return view('Litigation::Dashboard.Support.trackerlist',$data);
		}catch(\Exception $e){
			Log::error('SupportDashboardController-getTrackerList: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function getUploadButton(Request $request){  
		try{  
			if(!empty($request->id)){
				$type = Crypt::decrypt($request->type);
				$temp_id = Crypt::decrypt($request->temp_id);
				$company_id = Crypt::decrypt($request->company_id);
				$rules=[
					$type => 'numeric',
					$temp_id => 'numeric',
					$company_id => 'numeric',
				];
				$validator = Validator::make($request->all(),$rules);
				if ($validator->fails()) {
					$errors = $validator->errors()->all();
					$response=array();
					$response["status"]=0;
					$response["msg"]=$errors[0];
					return response()->json($response);// response as json
				}

				$data = array();
				$data['type'] = $type;
				$data['temp_id'] = $temp_id;
				$data['company_id'] = $company_id;
				return view('Litigation::Dashboard.Support.docbutton',$data);
			}else{
				return view('Litigation::Dashboard.Support.docbutton');
			}
		}catch(\Exception $e){
           Log::error('SupportDashboardController-getUploadButton: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

   	public function getDocList(Request $request){  
		try{  
			$type = Crypt::decrypt($request->type);
			$temp_id = Crypt::decrypt($request->temp_id);
			$rules=[
				$type => 'numeric',
				$temp_id => 'numeric',
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
			$arr['id'] = $temp_id;
			$data['type'] = $type;
			$NoticeTrackerDocument = new NoticeTrackerDocument();
			if($type == 1){
				$data['docList'] = $NoticeTrackerDocument->getResult($arr,1);
			}else{
				$data['docList'] = $NoticeTrackerDocument->getResult($arr,2);
			}

			return view('Litigation::Dashboard.Support.doctable',$data);
		}catch(\Exception $e){
           Log::error('SupportDashboardController-getDocList: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

	public function download(Request $request,$id){  
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
           Log::error('SupportDashboardController-download: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

	public function updateTracker(Request $request){
		try{
			$id = Crypt::decrypt($request->tracker_id);
			$rules=[
				$id => 'numeric',
				'receive_date' => 'required|date|date_format:Y-m-d',
				'mode_of_receipt' => 'required',
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
			$NoticeTracker = new NoticeTracker();
			$NoticeTrackerDocument = new NoticeTrackerDocument(); 
			$doclist = $NoticeTrackerDocument->getResult($arr,2);

			if(count(array_filter((array)$doclist)) == 0){
				$response=array();
				$response["status"]=0;
				$response["msg"]='Document required!';
				return response()->json($response);
			}

			$updArr = array();
			if(!empty($request->notice_category)){
				$updArr['category'] = Crypt::decrypt($request->notice_category);
			}
			$updArr['mode_of_receipt'] = Crypt::decrypt($request->mode_of_receipt);
			$updArr['receive_date'] = $request->receive_date;
			$updArr['remark']=$request->remark;

			$NoticeTracker->updateArr($updArr,$id);
			
			$response = array();
			$response["status"]=1;
			$response["msg"]="Notice tracker updated successfully";
			return response()->json($response);// response as json	
		}catch(\Exception $e){
			Log::error('SupportDashboardController-updateTracker: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
		}
	}

	public function upload(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'Numeric',
			];

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}else{
				$data=array();
				$data['id']=$id;
				return view('Litigation::Dashboard.Support.upload',$data);
			}
		}catch(\Exception $e){
			Log::error('SupportDashboardController-upload: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function uploadedit(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'Numeric',
			];

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}else{
				$data=array();
				$data['id']=$id;
				$data['type']=2;
				return view('Litigation::Dashboard.Support.upload',$data);
			}
		}catch(\Exception $e){
			Log::error('SupportDashboardController-uploadedit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function uploadsubmit(Request $request){
		try{  
			$id=Crypt::decrypt($request->id);
			$divide = explode('-', $id);
			$temp_id = $divide[0];
			$company_id = $divide[1];
			$rules=[
				$temp_id => 'Numeric',
				$company_id => 'Numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$AppUserType = new AppUserType(); 
			$arr['user_id'] = getUserId();
			$location_id = $AppUserType->getResult($arr,'getUserType')->location;

			$file = $request->file('myfile');
			$path ='litigation_document/'.$company_id.'/notice_tracker/'.$location_id;

			$file->store($path);
			$document_original = $request->myfile->getClientOriginalName();
			$withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $document_original);
			$fileName = $file->hashName();

			$NoticeTrackerDocument= new NoticeTrackerDocument();

			$insArr=array(); 
		    $insArr['temp_id']=$temp_id; 
			$insArr['document_name']=$withoutExt;
			$insArr['document_url']=$path.'/'.$fileName;	
			$insArr['document_original']=$document_original;
			$insArr['document_size']=$request->file('myfile')->getClientSize();  
			$NoticeTrackerDocument->insArr($insArr);
			$response=array();
			$response["status"]=1;
			$response["msg"]='Done';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('SupportDashboardController-uploadsubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function uploadeditsubmit(Request $request){
		try{  
			$id=Crypt::decrypt($request->id);
			$divide = explode('-', $id);
			$temp_id = $divide[0];
			$company_id = $divide[1];
			$rules=[
				$temp_id => 'Numeric',
				$company_id => 'Numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$AppUserType = new AppUserType(); 
			$arr['user_id'] = getUserId();
			$location_id = $AppUserType->getResult($arr,'getUserType')->location;

			$file = $request->file('myfile');
			$path ='litigation_document/'.$company_id.'/notice_tracker/'.$location_id;

			$file->store($path);
			$document_original = $request->myfile->getClientOriginalName();
			$withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $document_original);
			$fileName = $file->hashName();

			$NoticeTrackerDocument= new NoticeTrackerDocument();

			$insArr=array(); 
		    $insArr['tracker_id']=$temp_id; 
			$insArr['document_name']=$withoutExt;
			$insArr['document_url']=$path.'/'.$fileName;	
			$insArr['document_original']=$document_original;
			$insArr['document_size']=$request->file('myfile')->getClientSize();  
			$NoticeTrackerDocument->insArr($insArr);
			$response=array();
			$response["status"]=1;
			$response["msg"]='Done';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('SupportDashboardController-uploadeditsubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}
	
	public function documentdelete(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			if(ctype_digit(join('',(array)$id)) == false){
				$response=array();
				$response["status"]=0;
				$response["msg"]='Id must be a number!';
				return response()->json($response);// response as json
			}
			$data=array();
			$NoticeTrackerDocument= new NoticeTrackerDocument();
			$NoticeTrackerDocument->deletedocument($id);
			$response["status"]=1;
			$response["msg"]='Document Deleted!';
			return response()->json($response);
		}catch(\Exception $e){
			Log::error('SupportDashboardController-documentdelete: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function documentUpdate(Request $request){
   		try{ 
	   		$id=Crypt::decrypt($request->id);
	   		$rules=[
	   			'document_type' => 'required|notIn:null',
	   		];
	   		$validator = Validator::make($request->all(),$rules);
	   		if ($validator->fails()) {
	   			$errors = $validator->errors()->all();
	   			$response=array();
	   			$response["status"]=0;
	   			$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data=$upd=array();
			$NoticeTrackerDocument= new NoticeTrackerDocument();
			if($request->document_type != 'null'){
				$document_type = Crypt::decrypt($request->document_type);
			}else{
				$document_type = $request->document_type;
			}
			$upd['attachment_type']=$document_type;
			$NoticeTrackerDocument->updatedocument($upd,$id);
			$response["status"]=1;
			$response["msg"]='Update Successful';
			return response()->json($response);
		}catch(\Exception $e){
			Log::error('SupportDashboardController-documentUpdate: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}
	
}

