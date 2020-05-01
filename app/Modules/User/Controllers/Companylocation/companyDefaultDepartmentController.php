<?php 
namespace App\Modules\Litigation\Controllers\Companylocation;
use App\Http\Controllers\Controller;				// controller lib

use App\Modules\Litigation\Models\DefaultDepartment; 			// model of company loation table
use App\Modules\Litigation\Models\CompanyDepartment; 	 			// model of company loation 
use Illuminate\Http\Request;						// to handle the request data
use Auth;
use Validator;
use Illuminate\Support\Facades\Log;					// log for exception handling
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Crypt;	


class CompanyDefaultDepartmentController extends Controller
{

	 public function __construct(){
		 $this->middleware('admin');
	}


	public function compdepartmentadd(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}
			$data=array();
			$DefaultDepartment=new DefaultDepartment();
			$data["CompanyId"]=$id;
			$data["departmentlist"]=$DefaultDepartment->getDepartment();
			return view('Litigation::Companylocation.compdepartmentview',$data);
		}catch(\Exception $e){
			Log::error('CompanyDefaultDepartmentController-compdepartmentadd: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function compdepartmentsubmit(Request $request){
		try{
			$company_id=Crypt::decrypt($request->company_id);
			$rules=[
				$company_id => 'numeric',
				'dept_name' => 'required|string|unique:company_department,name,NULL,id,company_id,' . $company_id,
				'short_name' => 'required|string',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}
			$response=array();
			$CompanyDepartment=new CompanyDepartment();
			$insarray=array();
			$insarray['company_id']=$company_id;
			$insarray['name']=$request->dept_name;
			$insarray['short_name']=$request->short_name;
			$insarray['status']=1;
			$CompanyDepartment->addCompanyDepartment($insarray);
			
			$response["status"]=1;
			$response["msg"]="Company Department submit Successfully";
			return response()->json($response);// response as json
		}catch(\Exception $e){
			Log::error('CompanyDefaultDepartmentController-compdepartmentsubmit: '.$e->getMessage());
			return view('error.home');									// showing the err page
		}
	}

	public function compdepartmentlistshow(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}
			$data=array();
			$CompanyDepartment=new CompanyDepartment();
			$data["departmentlist"]=$CompanyDepartment->getDepartmentlist($id);
			return view('Litigation::Companylocation.compdepartmentlistshow',$data);
		}catch(\Exception $e){
			Log::error('CompanyDefaultDepartmentController-compdepartmentlistshow: '.$e->getMessage());
			return view('error.home');
		}
	}

}

