<?php 
namespace App\Modules\Litigation\Controllers\NextAction;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Login\Models\AppUsers;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\NextAction;
use App\Modules\Litigation\Models\NextActionStatus;
use App\Modules\Litigation\Models\NextActionCategory;
use App\Modules\Litigation\Models\NextActionDocument;
use Auth;
use Response;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
class NextActionController extends Controller
{ 
	public function index(Request $request){
		try{ 
			$id = Crypt::decrypt($request->id); 
			$type = Crypt::decrypt($request->type); 
			$rules=[
				$id=>'numeric', 
				$type=>'numeric', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 
			$data['id'] = $id;
			$data['type'] = $type;
			$NextAction = new NextAction();
			$data['NextAction'] = $NextAction->getFullResult($id);
		 	return view('Litigation::NextAction.view',$data)->render();
		}catch(\Exception $e){
			Log::error('NextActionController-index: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function nextActionAddForm(Request $request){
		try{ 
			$data = array();  
			$id = Crypt::decrypt($request->id); 
			$type = Crypt::decrypt($request->type); 
			$rules=[
				$id=>'numeric', 
				$type=>'numeric', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 

			$data['id'] = $id;
			$data['type'] = $type;

			$AppUsers = new AppUsers(); 
			if($type == 1){
				$Cases = new Cases();
				$data['Ref'] = $Cases->getCaseById($id);
				$userArr = explode(',',$data['Ref']->legal_team_id);
		    	$data['userList'] =$AppUsers->getFullUsersDetailById($userArr,'byUserArr');
			}elseif($type == 2){
				$Notice = new Notice();
				$data['Ref'] = $Notice->getNoticeById($id);
				$userArr = explode(',',$data['Ref']->legal_team_id);
				$data['userList'] =$AppUsers->getFullUsersDetailById($userArr,'byUserArr');
			}elseif($type == 4){
				$arr['id'] = $id;
				$Hearing = new Hearing();
				$data['Ref'] = $Hearing->getResult($arr,'hearingWithOwnerId');
				$userArr = explode(',',$data['Ref']->notify_id);
				$data['userList'] =$AppUsers->getFullUsersDetailById($userArr,'byUserArr');
			}
			
			$NextActionStatus = new NextActionStatus();
			$data['NextActionStatus'] = $NextActionStatus->getStatus();

			$NextActionCategory = new NextActionCategory();
			$data['NextActionCategory'] = $NextActionCategory->getCategory();

		 	return view('Litigation::NextAction.next_action_form',$data)->render();
		}catch(\Exception $e){
			Log::error('NextActionController-nextActionAddForm: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function nextActionEditForm(Request $request){
		try{  
			$data = array();  
			$id = Crypt::decrypt($request->id); 
			$rules=[
				$id=>'numeric', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 

			$NextAction = new NextAction();
			$data['NextAction'] = $NextAction->getDataById($id);

			$AppUsers = new AppUsers(); 
			if($data['NextAction']->legal_type_id == 1){
				$Cases = new Cases();
				$data['Ref'] = $Cases->getCaseById($data['NextAction']->reference_id);
				$userArr = explode(',',$data['Ref']->legal_team_id);
		    	$data['userList'] =$AppUsers->getFullUsersDetailById($userArr,'byUserArr');
			}elseif($data['NextAction']->legal_type_id == 2){
				$Notice = new Notice();
				$data['Ref'] = $Notice->getNoticeById($data['NextAction']->reference_id);
				$userArr = explode(',',$data['Ref']->legal_team_id);
				$data['userList'] =$AppUsers->getFullUsersDetailById($userArr,'byUserArr');
			}elseif($data['NextAction']->legal_type_id == 4){
				$arr['id'] = $data['NextAction']->reference_id;
				$Hearing = new Hearing();
				$data['Ref'] = $Hearing->getResult($arr,'hearingWithOwnerId');
				$userArr = explode(',',$data['Ref']->notify_id);
				$data['userList'] =$AppUsers->getFullUsersDetailById($userArr,'byUserArr');
			}

			$NextActionStatus = new NextActionStatus();
			$data['NextActionStatus'] = $NextActionStatus->getStatus();

			$NextActionCategory = new NextActionCategory();
			$data['NextActionCategory'] = $NextActionCategory->getCategory();

			$Company = new Company();
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 

			$nextArr['id'] = $id;
			$NextActionDocument = new NextActionDocument();
			$data['docList'] = $NextActionDocument->getResult($nextArr,1);

		 	return view('Litigation::NextAction.next_action_edit_form',$data)->render();
		}catch(\Exception $e){
			Log::error('NextActionController-nextActionEditForm: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function nextActionSubmit(Request $request){
		try{ 
			$response = $insArr = array();
			$ref_id = Crypt::decrypt($request->ref_id); 
			$matter_id = Crypt::decrypt($request->matter_id); 
			$legal_type = Crypt::decrypt($request->legal_type); 
			$rules=[
				$ref_id => 'numeric',
				$matter_id => 'numeric',
				$legal_type => 'numeric',
				'category' => 'required',
				'target_date' => 'required|date|date_format:Y-m-d',
				'person_responsible' => 'required',
				'additional_remark' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 
			
			$insArr['legal_type_id'] = $legal_type;
			$insArr['reference_id'] = $ref_id;
			$insArr['matter_id'] = $matter_id;
			$insArr['created_by'] = getUserId();
			$insArr['updated_by'] = getUserId();
			$insArr['company_id'] = getSetCompanyId();
			$insArr['action_status'] = 1;
			$insArr['additional_remark'] = $request->additional_remark;
			$insArr['category_id'] = Crypt::decrypt($request->category);
			$insArr['target_date'] = date('Y-m-d',strtotime($request->target_date));
			$insArr['person_responsible'] = Crypt::decrypt($request->person_responsible);

			$NextAction = new NextAction();
			$nextId = $NextAction->insArr($insArr);
			$MailHelper = new MailHelper();
			if(!empty($nextId)){
				$MailHelper->nextAction($insArr);
			}
			$response["status"]=1;
			$response["msg"]="Action Added Successfully";
			return $response;
		 
		}catch(\Exception $e){
			Log::error('NextActionController-nextActionSubmit: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function nextActionUpdate(Request $request){
		try{ 
			$response = $updateArr = array();
			$next_id = Crypt::decrypt($request->next_id); 

			$NextAction = new NextAction();
			$data = $NextAction->getDataById($next_id);

			if(getUserId() == $data->created_by){
				if($data->action_status != 3){
					if(getUserId() != $data->person_responsible){
						$rules=[
							$next_id => 'numeric',
							'category' => 'required',
							'target_date' => 'required|date|date_format:Y-m-d',
							'person_responsible' => 'required',
							'additional_remark' => 'required',
						];
					}else{
						$rules=[
							$next_id => 'numeric',
							'category' => 'required',
							'target_date' => 'required|date|date_format:Y-m-d',
							'person_responsible' => 'required',
							'additional_remark' => 'required',
							'status' => 'required',
							'completion_date'=>"nullable|required_if:status,==,3|date|date_format:Y-m-d",
						];
					}	
				}
			}else{
				$rules=[
					$next_id => 'numeric',
					'status' => 'required',
					'completion_date'=>"required_if:status,==,3|date|date_format:Y-m-d",
				];
			}

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 

			if(getUserId() == $data->created_by){
				if($data->action_status != 3){
					if(getUserId() != $data->person_responsible){
						$updateArr['updated_by'] = getUserId();
						$updateArr['additional_remark'] = $request->additional_remark;
						$updateArr['category_id'] = Crypt::decrypt($request->category);
						$updateArr['target_date'] = date('Y-m-d',strtotime($request->target_date));
						$updateArr['person_responsible'] = Crypt::decrypt($request->person_responsible);
					}else{
						$updateArr['updated_by'] = getUserId();
						$updateArr['additional_remark'] = $request->additional_remark;
						$updateArr['category_id'] = Crypt::decrypt($request->category);
						$updateArr['target_date'] = date('Y-m-d',strtotime($request->target_date));
						$updateArr['person_responsible'] = Crypt::decrypt($request->person_responsible);
						$updateArr['action_status'] = $request->status;
						$updateArr['completion_remark'] = $request->completion_remark;
						$updateArr['completion_date'] = isset($request->completion_date)?date('Y-m-d',strtotime($request->completion_date)):'';	
					}
				}
			}else{
				$updateArr['updated_by'] = getUserId();
				$updateArr['action_status'] = $request->status;
				$updateArr['completion_remark'] = $request->completion_remark;
				$updateArr['completion_date'] = isset($request->completion_date)?date('Y-m-d',strtotime($request->completion_date)):'';	
			}

			$nextId = $NextAction->updatenextAction($updateArr,$next_id);
			$MailHelper = new MailHelper();
			if(!empty($nextId)){
				if ($request->status == 3) {
					$MailHelper->nextActionTaskCompleted($updateArr,$data);	 
				}
				
			}
			$response["status"]=1;
			$response["msg"]="Action Updated Successfully";
			return $response;
		 
		}catch(\Exception $e){
			Log::error('NextActionController-nextActionUpdate: '.$e->getMessage()); 		
			return view('error.home');
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
				return view('Litigation::NextAction.upload',$data);
			}
		}catch(\Exception $e){
			Log::error('NextActionController-upload: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function uploadsubmit(Request $request){
		try{  
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'Numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$file = $request->file('myfile');
			$path ='litigation_document/'.getSetCompanyId().'/NextAction/'.$id;

			$file->store($path);
			$document_original = $request->myfile->getClientOriginalName();
			$withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $document_original);
			$fileName = $file->hashName();

			$NextActionDocument = new NextActionDocument();

			$insArr=array(); 
		    $insArr['ref_id']=$id; 
			$insArr['document_name']=$withoutExt;
			$insArr['document_url']=$path.'/'.$fileName;	
			$insArr['document_original']=$document_original;
			$insArr['document_size']=$request->file('myfile')->getClientSize();  
			$NextActionDocument->insert($insArr);
			$response=array();
			$response["status"]=1;
			$response["msg"]='Done';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('NextActionController-uploadsubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function documentdelete(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'Numeric',
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
			$NextActionDocument= new NextActionDocument();
			$NextActionDocument->deletedocument($id);
			$response["status"]=1;
			$response["msg"]='Document Deleted!';
			return response()->json($response);
		}catch(\Exception $e){
			Log::error('NextActionController-documentdelete: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function getDocList(Request $request){  
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

			$data = $arr = array();
			$arr['id'] = $id;
			$NextActionDocument = new NextActionDocument();
			$data['docList'] = $NextActionDocument->getResult($arr,1);

			$NextAction = new NextAction();
			$data['NextAction'] = $NextAction->getDataById($id);

			return view('Litigation::NextAction.doctable',$data);
		}catch(\Exception $e){
           Log::error('NextActionController-getDocList: '.$e->getMessage());         // making log in file
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
			$NextActionDocument = new NextActionDocument();
			$document = $NextActionDocument->getResult($arr,2); 

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
           Log::error('NextActionController-download: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

   	public function nextactiondoclist(Request $request){  
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

			$data = $arr = array();
			$arr['id'] = $id;
			$NextActionDocument = new NextActionDocument();
			$data['docList'] = $NextActionDocument->getResult($arr,1);

			return view('Litigation::NextAction.nextactiondoclist',$data);
		}catch(\Exception $e){
           Log::error('NextActionController-nextactiondoclist: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

   	public function nextactionreopen(Request $request){  
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

			$data = array();
			$data['nextaction_id'] = $id;

			return view('Litigation::NextAction.nextactionreopen',$data);
		}catch(\Exception $e){
           Log::error('NextActionController-nextactionreopen: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}


   	public function reopennextaction(Request $request){
		try{ 
			$id = Crypt::decrypt($request->nextaction_id); 
			$rules=[
				$id => 'numeric',
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

			$NextAction = new NextAction();
			$updateArr['action_status'] = 2;
			$updateArr['additional_remark'] = $request->remark;
			$nextId = $NextAction->updatenextAction($updateArr,$id);

			$response["status"]=1;
			$response["msg"]="Reopened Successfully";
			return $response;
		}catch(\Exception $e){
			Log::error('NextActionController-nextActionUpdate: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	//next action show
	public function listPopup($type)
	{
		try { 
			$data = $arr = array();
			$NextAction = new NextAction();
			$arr['childId'] = getChildUserId();
			$data['NextAction'] = $NextAction->getResult($arr,$type);
			return view('Litigation::NextAction.list_popup',$data)->render();
		} catch (Exception $e) {
			Log::error('notice show page: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}
	
}

