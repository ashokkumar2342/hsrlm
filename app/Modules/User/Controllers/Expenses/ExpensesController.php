<?php 
namespace App\Modules\Litigation\Controllers\Expenses;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\Expenses;
use App\Modules\Litigation\Models\ExpenseType;
use App\Modules\Litigation\Models\ExpenseStatus;
use App\Modules\Litigation\Models\ExpenseDocument;
use Auth;
use Response;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
class ExpensesController extends Controller
{
	public function index(Request $request){
		try{
			$ref_id = Crypt::decrypt($request->ref_id);
			$legal_type_id = Crypt::decrypt($request->legal_type_id);
			$rules=[
				$ref_id=>'numeric', 
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

			$data = array();
			$data['ref_id'] = $ref_id;
			$data['legal_type_id'] = $legal_type_id;

			$Expenses = new Expenses();
			if($legal_type_id == 4){
				$data['expenseResult'] = $Expenses->getExpenses($ref_id,$legal_type_id); 
			}elseif($legal_type_id == 2){
				$data['expenseResult'] = $Expenses->getExpenses($ref_id,$legal_type_id); 
			}
			return view('Litigation::Expenses.view',$data)->render();
		}catch (Exception $e) {
			Log::error('ExpensesController-index: '.$e->getMessage()); 		
			return view('error.home');		
		}
	}

	public function addpopup(Request $request){
		try{
			$ref_id = Crypt::decrypt($request->ref_id);
			$legal_type_id = Crypt::decrypt($request->legal_type_id);
			$rules=[
				$ref_id=>'numeric', 
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

			$data = array();
			$data['ref_id'] = $ref_id;
			$data['temp_id'] = generateId();
			$data['legal_type_id'] = $legal_type_id;

			$Company = new Company();
			$Hearing = new Hearing();
			$ExpenseType = new ExpenseType();
			$ExpenseStatus = new ExpenseStatus();
			if($legal_type_id == 4){
				$data['hearingResult'] = $Hearing->getHearingById($ref_id); 
			}
			$data['expenseType'] = $ExpenseType->getExpenseType(); 
			$data['expenseStatus'] = $ExpenseStatus->getExpenseStatus();
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 
			return view('Litigation::Expenses.addpopup',$data)->render();
		}catch (Exception $e) {
			Log::error('ExpensesController-addpopup: '.$e->getMessage()); 		
			return view('error.home');		
		}
	}

	public function editpopup(Request $request){
		try{
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

			$data = array();
			$data['id'] = $arr['id'] = $id;
			$data['type'] = 2;

			$Company = new Company();
			$Hearing = new Hearing();
			$Expenses = new Expenses();
			$ExpenseType = new ExpenseType();
			$ExpenseStatus = new ExpenseStatus();
			$ExpenseDocument = new ExpenseDocument();
			$data['expenseType'] = $ExpenseType->getExpenseType(); 
			$data['docList'] = $ExpenseDocument->getResult($arr,2);
			$data['expenseResult'] = $Expenses->getExpensesById($id);
			$data['legal_type_id'] = $data['expenseResult']->legal_type_id;
			if($data['expenseResult']->legal_type_id == 4){
				$data['hearingResult'] = $Hearing->getHearingById($data['expenseResult']->ref_id); 
			}else{
				$data['ref_id'] = $data['expenseResult']->ref_id;
			} 
			$data['expenseStatus'] = $ExpenseStatus->getExpenseStatus();
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId()); 
			if(count(array_filter((array)$data['docList'])) == 0){
				$data['temp_id'] = generateId();
			}else{
				$data['temp_id'] = array_unique($data['docList']->pluck('temp_id')->toarray())[0];
			}
			return view('Litigation::Expenses.editpopup',$data)->render();
		}catch (Exception $e) {
			Log::error('ExpensesController-editpopup: '.$e->getMessage()); 		
			return view('error.home');		
		}
	}

	public function addexpense(Request $request){
		try{  
			$ref_id=Crypt::decrypt($request->ref_id);
			$temp_id=Crypt::decrypt($request->temp_id);
			$legal_type_id=Crypt::decrypt($request->legal_type_id);
			$rules=[
				$ref_id => 'numeric',
				$temp_id => 'numeric',
				$legal_type_id => 'numeric',
				'expense_type' => 'required',
				'expense_date' => 'required|date|date_format:Y-m-d',
				'expense_amount' => 'required|numeric',
				'expense_status' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$Expenses = new Expenses();
			$ExpenseDocument = new ExpenseDocument();

			$insArr=array(); 
		    $insArr['ref_id'] = $ref_id;
		    $insArr['legal_type_id'] = $legal_type_id;
		    if($legal_type_id == 4){
		    	$insArr['expense_header'] = 'Hearing';
		    }elseif($legal_type_id == 1){
		    	$insArr['expense_header'] = 'Case';
		    }elseif($legal_type_id == 2){
		    	$insArr['expense_header'] = 'Notice';
		    }
		    $insArr['remark'] = $request->remark;
		    $insArr['expense_date'] = $request->expense_date;
		    $insArr['expense_amount'] = $request->expense_amount;
		    $insArr['expense_type'] = Crypt::decrypt($request->expense_type);
		    $insArr['expense_status'] = Crypt::decrypt($request->expense_status);
			$hId = $Expenses->insert($insArr);

			$updArr['ref_id'] = $hId;
			$ExpenseDocument->updateArrByTempId($updArr,$temp_id);

			$response=array();
			$response["status"]=1;
			$response["msg"]='Expense Added';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('ExpensesController-addexpense: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function editexpense(Request $request){
		try{  
			$id=Crypt::decrypt($request->expense_id);
			$temp_id=Crypt::decrypt($request->temp_id);
			$rules=[
				$id => 'numeric',
				$temp_id => 'numeric',
				'expense_type' => 'required',
				'expense_date' => 'required|date|date_format:Y-m-d',
				'expense_amount' => 'required|numeric',
				'expense_status' => 'required',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$Expenses = new Expenses();
			$ExpenseDocument = new ExpenseDocument();

			$updArr=array(); 
		    $updArr['remark'] = $request->remark;
		    $updArr['expense_date'] = $request->expense_date;
		    $updArr['expense_amount'] = $request->expense_amount;
		    $updArr['expense_type'] = Crypt::decrypt($request->expense_type);
		    $updArr['expense_status'] = Crypt::decrypt($request->expense_status);
			$Expenses->updArr($updArr,$id);

			$updDArr['ref_id'] = $id;
			$ExpenseDocument->updateArrByTempId($updDArr,$temp_id);

			$response=array();
			$response["status"]=1;
			$response["msg"]='Expense Updated';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('ExpensesController-editexpense: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function upload(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$type=Crypt::decrypt($request->type);
			$rules=[
				$id => 'Numeric',
				$type => 'Numeric',
			];

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}else{
				$data=array();
				$data['id']=$id;
				$data['type']=$type;
				return view('Litigation::Expenses.upload',$data);
			}
		}catch(\Exception $e){
			Log::error('ExpensesController-upload: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function uploadsubmit(Request $request){
		try{  
			$id=Crypt::decrypt($request->id);
			$divide = explode('-', $id);
			$temp_id = $divide[0];
			$case_id = $divide[1];
			$rules=[
				$temp_id => 'Numeric',
				$case_id => 'Numeric',
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
			$path ='litigation_document/'.getSetCompanyId().'/expenses/'.$case_id;

			$file->store($path);
			$document_original = $request->myfile->getClientOriginalName();
			$withoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $document_original);
			$fileName = $file->hashName();

			$ExpenseDocument = new ExpenseDocument();

			$insArr=array(); 
		    $insArr['temp_id']=$temp_id; 
			$insArr['name']=$withoutExt;
			$insArr['doc_url']=$path.'/'.$fileName;	
			$insArr['doc_original']=$document_original;
			$insArr['doc_size']=$request->file('myfile')->getClientSize();  
			$ExpenseDocument->insert($insArr);
			$response=array();
			$response["status"]=1;
			$response["msg"]='Done';
	        return response()->json($response);// response as json
	    }catch(\Exception $e){
			Log::error('ExpensesController-uploadsubmit: '.$e->getMessage()); 		// making log in file
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
			$ExpenseDocument= new ExpenseDocument();
			$ExpenseDocument->deletedocument($id);
			$response["status"]=1;
			$response["msg"]='Document Deleted!';
			return response()->json($response);
		}catch(\Exception $e){
			Log::error('ExpensesController-documentdelete: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
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
			$ExpenseDocument = new ExpenseDocument();
			$data['docList'] = $ExpenseDocument->getResult($arr,1);

			return view('Litigation::Expenses.doctable',$data);
		}catch(\Exception $e){
           Log::error('ExpensesController-getDocList: '.$e->getMessage());         // making log in file
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
			$ExpenseDocument = new ExpenseDocument();
			$document = $ExpenseDocument->getResult($arr,3); 

			$storagePath = storage_path('domainstorage/'.$document->doc_url);
			$mimeType = mime_content_type($storagePath);
			if( ! \File::exists($storagePath)){
				return view('error.home');
			}
			$headers = array(
				'Content-Type' => $mimeType,
				'Content-Disposition' => 'inline; filename="'.$document->doc_url.'"'
			);
			return Response::make(file_get_contents($storagePath), 200, $headers); 

			$response['status'] = 1; 
			return response()->json($response); 
		}catch(\Exception $e){
           Log::error('ExpensesController-download: '.$e->getMessage());         // making log in file
           return view('error.home');  
       }
   	}

   	
}

