<?php 
namespace App\Modules\Litigation\Controllers\Document;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Confidentiality;
use App\Modules\Litigation\Models\Defaults\CaseStatus;
use App\Modules\Litigation\Models\Defaults\CaseType;
use App\Modules\Litigation\Models\Defaults\LegalCategory;
use App\Modules\Litigation\Models\Document;
use App\Modules\Litigation\Models\DocumentType;
use App\Modules\Litigation\Models\MasterTree;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use App\Modules\Login\Models\AppUsers;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Response;
use Validator;
class DocumentController extends Controller
{
	public function show($ref_id,$legal_id)
	{
		try {
			$ref_id = Crypt::decrypt($ref_id); 
			$legal_id = Crypt::decrypt($legal_id); 
			if($legal_id == 1){
				$Cases = new Cases();
				$result = $Cases->getCaseById($ref_id); 
			}elseif($legal_id == 2){
				$Notice = new Notice();
				$result = $Notice->getNoticeById($ref_id);
			}elseif($legal_id == 4){
				$arr['id'] = $ref_id;
				$Hearing = new Hearing();
				$result = $Hearing->getResult($arr,'hearingWithOwnerId');
			}else{
				$result = '';
			}

			$masterTree = new MasterTree();
			$masterTrees = $masterTree->getMasterTreeByMasterId($ref_id);

			$documentType = new DocumentType();
			$documentTypes = $documentType->getDocumentTypeByLegalId($legal_id); 

			$confidentiality = new Confidentiality();
			$confidentialitys = $confidentiality->getConfidentiality(); 

			$document = new Document();
			$documents = $document->getDocumentByRefId($ref_id); 

			$data =array();		 
			$data['ref_id'] =$ref_id;		 
			$data['legal_id'] =$legal_id;		 
			$data['masterTrees']=$masterTrees;		 
			$data['result'] =$result;  
			$data['documents'] =$documents;  
			$data['documentTypes'] =$documentTypes;  
			$data['confidentialitys'] =$confidentialitys;  

			return view('Litigation::Document.document_view',$data)->render();

		} catch (Exception $e) {
			Log::error('DocumentController-show: '.$e->getMessage()); 		
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
				return view('Litigation::Document.upload',$data);
			}
		}catch(\Exception $e){
			Log::error('DocumentController-upload: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function uploadsubmit(Request $request){
		try{  
			$id=Crypt::decrypt($request->id);
			$divide = explode('-', $id);
			$reference_id = $divide[0];
			$legal_type_id = $divide[1];
			$matter_id =null;
			if ($legal_type_id==1) {
				$case = new Cases();
				$cases = $case->getCaseById($reference_id);   
				$matter_id=$cases->matter_id;
			}else if ($legal_type_id==2) {
				$notice = new Notice();
				$notices = $notice->getNoticeById($reference_id);   
				$matter_id=$notices->matter_id;
			}else if ($legal_type_id==3) {


			}else if ($legal_type_id==4) {


			}else if ($legal_type_id==5) {


			}else if ($legal_type_id==6) {

			}else if ($legal_type_id==7) {

			}
			
			$rules=[
				$reference_id => 'Numeric',
				'matter_id' => 'Numeric',
			];

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$company_id=getSetCompanyId();
			$file = $request->file('myfile');
			$path ='litigation_document/'.$company_id.'/'.$matter_id.'/'.$legal_type_id.'/'.$reference_id;
			$file->store($path);
			$document_original = $request->myfile->getClientOriginalName();
			$withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $document_original);
			$fileName = $file->hashName();
			$Document= new Document();
			$ins=array(); 
			$ins['matter_id']=$matter_id;
			$ins['reference_id']=$reference_id;
			$ins['legal_type_id']=$legal_type_id;
			$ins['document_name']=$withoutExt;
			$ins['document_url']=$path.'/'.$fileName;	
			$ins['document_original']=$document_original;
			$ins['document_size']=$request->file('myfile')->getClientSize(); 
			$ins['created_by']=getUserId(); 
		    $ins['company_id']=$company_id; 
			$ins['status']=1;
			$Document->insert($ins);
			$response=array();
			$response["status"]=1;
			$response["msg"]='Done';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('DocumentController-uploadsubmit: '.$e->getMessage()); 		// making log in file
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
			$Document= new Document();
			$document=$Document->getDocumentById($id); 
			// $Document->deletedocument($id);
			$response["status"]=1;
			$response["msg"]='Delete';
			return response()->json($response);
		}catch(\Exception $e){
			Log::error('DocumentController-documentdelete: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function download(Request $request,$id){  
		try{   
			$id= Crypt::decrypt($id);
			$document = new Document();
			$document = $document-> getDocumentById($id); 

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
           Log::error('DocumentController-download: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   }

   public function documentUpdate(Request $request){
   	try{ 
   		$id=Crypt::decrypt($request->document_id);
   		$rules=[
   			'document_type' => 'required|notIn:null',
   			'document_name' => 'required',
   		];
   		$validator = Validator::make($request->all(),$rules);
   		if ($validator->fails()) {
   			$errors = $validator->errors()->all();
   			$response=array();
   			$response["status"]=0;
   			$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$data=$ins=array();
			$Document= new Document();
			if($request->document_type != 'null'){
				$document_type = Crypt::decrypt($request->document_type);
			}else{
				$document_type = $request->document_type;
			}
			$ins['document_type_id']=$document_type;
			$ins['document_name']=$request->document_name;
			$ins['description']=$request->description;
			$ins['company_id']=getSetCompanyId();
			$Document->updatedocument($ins,$id);
			$response["status"]=1;
			$response["msg"]='Update Successful';
			return response()->json($response);
		}catch(\Exception $e){
			Log::error('DocumentController-documentUpdate: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function refreshlist(Request $request)
	{
		try {
			$ref_id = Crypt::decrypt($request->id); 
			$legal_id = Crypt::decrypt($request->legal_id); 
			if($legal_id == 1){
				$Cases = new Cases();
				$result = $Cases->getCaseById($ref_id); 
			}elseif($legal_id == 2){
				$Notice = new Notice();
				$result = $Notice->getNoticeById($ref_id);
			}elseif($legal_id == 4){
				$arr['id'] = $ref_id;
				$Hearing = new Hearing();
				$result = $Hearing->getResult($arr,'hearingWithOwnerId');
			}else{
				$result = '';
			}

			$documentType = new DocumentType();
			$documentTypes = $documentType->getDocumentTypeByLegalId($legal_id);

			$confidentiality = new Confidentiality();
			$confidentialitys = $confidentiality->getConfidentiality(); 

			$document = new Document();
			$documents = $document->getDocumentByRefId($ref_id); 

			$data =array();	
			$data['ref_id'] =$ref_id;		 
			$data['legal_id'] =$legal_id;				 
			$data['result'] =$result; 	 		 
			$data['documents'] =$documents;  
			$data['documentTypes'] =$documentTypes;  
			$data['confidentialitys'] =$confidentialitys;  

			return view('Litigation::Document.table',$data)->render();

		} catch (Exception $e) {
			Log::error('DocumentController-refreshlist: '.$e->getMessage()); 		
			return view('error.home');	
		}
	}

	public function statusChange(Request $request){
		try{ 
			$id=Crypt::decrypt($request->document_id);
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
			$data=$ins=array();
			$Document= new Document();

			$ins['status']=0;
			$Document->updatedocument($ins,$id);
			$response["status"]=1;
			$response["msg"]='Document Disabled Successful';
			return response()->json($response);
		}catch(\Exception $e){
			Log::error('DocumentController-statusChange: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}
	
}

