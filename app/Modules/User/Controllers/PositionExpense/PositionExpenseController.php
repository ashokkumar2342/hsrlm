<?php 
namespace App\Modules\Litigation\Controllers\PositionExpense;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\Hearing;
use App\Modules\Litigation\Models\Expenses;
use App\Modules\Litigation\Models\CasePosition;
use Auth;
use Response;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
class PositionExpenseController extends Controller
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

			$data = $arr = array();

			$arr['id'] = $ref_id;
			$data['ref_id'] = $ref_id;
			$data['legal_type_id'] = $legal_type_id;

			$Company = new Company();
			$Hearing = new Hearing();
			$Expenses = new Expenses();
			$CasePosition = new CasePosition();

			$data['positionResult'] = $CasePosition->getPostion($ref_id);
			$data['companyDetail'] = $Company->getDetail(getSetCompanyId());
			$getHearing = $Hearing->getResult($arr,'hearingWithCaseId')->pluck('hearing_id')->toarray();
			array_push($getHearing, $ref_id);
			$data['expenseResult'] = $Expenses->getExpenses($getHearing,'',1); 

			//total expense code
			$data['totalExpense'] = array_sum($Expenses->getExpensesByRefIds($getHearing)->pluck('expense_amount')->toarray());
			//total expense code

			//get last position
			$lastPosition = $CasePosition->getLastRaw($ref_id);
			if(!empty($lastPosition)){
				$maxBudget = $lastPosition->max_budget;
			}else{
				$maxBudget = 0;
			}

			$data['maxBudget'] = $maxBudget;
			$data['difference'] = $maxBudget - $data['totalExpense'];
			//get last position

			return view('Litigation::PositionExpense.view',$data)->render();
		}catch (Exception $e) {
			Log::error('PositionExpenseController-index: '.$e->getMessage()); 		
			return view('error.home');		
		}
	}
   	
}

