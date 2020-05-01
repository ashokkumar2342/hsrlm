<?php 
namespace App\Modules\Litigation\Controllers\Graph;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\LawFirm;
use App\Modules\Litigation\Models\ExpenseType;
use Auth;
use Session;
use Redirect;
use Validator;
use generalHelper;		
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;	
use Illuminate\Support\Facades\Crypt;			// log for exception handling
class GraphController extends Controller
{
	public function __construct(){
		 $this->middleware('user');
	}

	public function likelihood(Request $request){
		try{  
			$data = array();
			$daterange = $request->daterange;
			$daterange = explode(' - ', $daterange);
			$data['from'] = date('Y-m-d',strtotime($daterange[0]));
			$data['to'] = date('Y-m-d',strtotime($daterange[1]));
			return view('Litigation::Dashboard.User.likelihood',$data);
		}catch(\Exception $e){
			Log::error('GraphController-likelihood: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function expensegraph(Request $request){
		try{ 
			$data = array(); 
			$daterange = $request->daterange;
			$daterange = explode(' - ', $daterange);
			$data['from'] = date('Y-m-d',strtotime($daterange[0]));
			$data['to'] = date('Y-m-d',strtotime($daterange[1]));
			$ExpenseType = new ExpenseType();
			$data['expenseTypes'] = $ExpenseType->getExpenseType(); 
			return view('Litigation::Dashboard.User.expence_graph',$data);
		}catch(\Exception $e){
			Log::error('GraphController-expensegraph: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function riskpiechart(Request $request){
		try{  
			$Cases = new Cases(); 
			$data = $arr = array(); 

			$daterange = $request->daterange;
			$daterange = explode(' - ', $daterange);
			$data['from'] = $arr['from'] = date('Y-m-d',strtotime($daterange[0]));
			$data['to'] = $arr['to'] = date('Y-m-d',strtotime($daterange[1]));

			$data['super_critical'] = $Cases->getResult($arr,'super_critical',1);
			$data['critical'] = $Cases->getResult($arr,'critical',1);
			$data['important'] = $Cases->getResult($arr,'important',1);
			$data['routine'] = $Cases->getResult($arr,'routine',1);
			$data['normal'] = $Cases->getResult($arr,'normal',1);
			return view('Litigation::Graph.Dashboard.riskpiechart',$data);
		}catch(\Exception $e){
			Log::error('GraphController-riskpiechart: '.$e->getMessage()); 		
			return view('error.home');
		}
	}
	
}

