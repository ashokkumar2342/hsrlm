<?php 
namespace App\Modules\Litigation\Controllers\CompanyConfig;
use App\Http\Controllers\Controller;				// controller lib
use App\Modules\Litigation\Models\Company;		// model of location table
use App\Modules\Litigation\Models\AdminLog; 			// model of location table
use Illuminate\Http\Request;						// to handle the request data
use Auth;
use Validator;
use Illuminate\Support\Facades\Log;					// log for exception handling
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Helpers\generalHelper;
use Illuminate\Support\Facades\Crypt;	
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



class CompanyconfigController extends Controller
{

	 public function __construct(){
		 $this->middleware('admin');
	}
	
    public function sizefilesubmit(Request $request){ 
    	try{
		 	$allowed_size_data=array('2048','3072','4096','5120','6144','7168','8192','9216','10240','11264','12288','13312','14336','15360','16384','17408','18432','19456','20480','21504','22528','23552','24576','25600'); 
		 	$company_id=Crypt::decrypt($request->company_id);
    		$rules=[
    			$company_id => 'numeric',
    			'allowed_size' => 'required|numeric|in:' . implode(',', $allowed_size_data), 
                'allowed_file' => 'required|array', 
    		];
    		$validator = Validator::make($request->all(),$rules);
    		if ($validator->fails()) {
    			$errors = $validator->errors()->all();
    			$response=array();
    			$response["status"]=0;
    			$response["msg"]=$errors[0];
    			return response()->json($response);// response as json
    		}else{
    			$data=array();
    			$Company=new Company();
    			$search=$Company->getCompanyById($company_id);
				$AdminLog=new AdminLog();

    			$data["upload_size"]=$request->allowed_size;
                $data["upload_file"]=implode(',', $request->allowed_file);
    			$Company->updateCompany($data,$company_id);
				$response=array();
    			$response["status"]=1;
    			$response["msg"]="Allowed Size Limit & File Type Set.";

    			return response()->json($response);// response as json
    		}
    		
    	}catch(\Exception $e){
    		Log::error('CompanyconfigController-sizefilesubmit page: '.$e->getMessage()); 		// making log in file
    		return view('error.home');
    	}
    }
	
}

