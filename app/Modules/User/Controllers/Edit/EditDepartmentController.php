<?php 
namespace App\Modules\Litigation\Controllers\Edit;
use App\Http\Controllers\Controller;				// controller lib
use Illuminate\Http\Request;						// to handle the request data
use App\Modules\Litigation\Models\CompanyDepartment;
use App\Modules\Litigation\Models\DefaultDepartment;
use App\Modules\Litigation\Models\AdminLog;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\generalHelper;

class EditDepartmentController extends Controller
{
	public function __construct(){
		$this->middleware('admin');
	}
	
	public function popup(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'Numeric',
			];

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}

			$CompanyDepartment=new CompanyDepartment();
			$DefaultDepartment=new DefaultDepartment();
			$data=array();

			$data['detail']=$CompanyDepartment->getAlldetailofCompanyDepartment($id);
			$data["departmentlist"]=$DefaultDepartment->getDepartment();

			return view('Litigation::Edit.Department.popup',$data);
		}catch(\Exception $e){
			Log::error('EditDepartmentController-popup: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function submit(Request $request){
		try{
			$id=Crypt::decrypt($request->row_id);
			$rules=[
				$id => 'Numeric',
				'dept_index' => 'required|string',
			];

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$updArr=array();
			$updArr["dept_index_name"]=$request->dept_index;

			
			$AdminLog=new AdminLog();
			$CompanyDepartment=new CompanyDepartment();
			$search=$CompanyDepartment->getAlldetailofCompanyDepartment($id);
			$CompanyDepartment->updateRecord($updArr,$id);


			$LogArr=Array();
			$LogArr['user_id']=getUserId();
			$LogArr['company_id']=$search['company_id'];
			$LogArr['control']='Company Department';
			$LogArr['keyword']='Update';
			$LogArr['unique_id']=$id;
			$LogArr['message']="Company Department Update";
			$LogArr['data_from']=$search;
			$LogArr['data_to']=$updArr;
			$AdminLog->addlog($LogArr);

			$response=array();
			$response["status"]=1;
			$response["msg"]="Company Department Update Successfully";
			return response()->json($response);// response as json
		}catch(\Exception $e){
			Log::error('EditDepartmentController-submit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function deactivate(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				return view('error.home');
			}

			$data=array();
			$data['id']=$id;
			$data['click']='ve_adl';
			return view('Litigation::Edit.Department.DeactivatePopup',$data);
		}catch(\Exception $e){
			Log::error('EditDepartmentController-deactivate: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function deactivateSubmit(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
				'deactivation_date' => 'required|date|date_format:Y-m-d',
			];
			
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			}

			$CompanyDepartment=new CompanyDepartment();

			$generalHelper = new generalHelper();
			$AdminLog=new AdminLog();

			$record=$CompanyDepartment->getAlldetailofCompanyDepartment($id);

			$company_id=$record->company_id;
			$location_id=$record->location_id;
			$department_id=$id;

			$thismarch=date('Y-03-31');
			$loopend_date=date('Y-m-d', strtotime($thismarch." + 12 month"));

			$generalHelper->DeactivateComplianceWithDetail($company_id,$location_id,$department_id,'','','','','',$request->deactivation_date,$loopend_date);

			$updArr=array();
			$updArr["deactivate_date"]=$request->deactivation_date;
			$CompanyDepartment->updateRecord($updArr,$id);

			
			$LogArr=Array();
			$LogArr['user_id']=getUserId();
			$LogArr['company_id']=$record->company_id;
			$LogArr['control']='Department Deactivate';
			$LogArr['keyword']='Delete';
			$LogArr['unique_id']=$id;
			$LogArr['message']="Deactivate Department by ID";
			$LogArr['data_from']=$record;
			$LogArr['data_to']=array($request->deactivation_date);
			$AdminLog->addlog($LogArr);

			$response=array();
			$response["status"]=1;
			$response["msg"]="Department Deactivate";
				return response()->json($response);// response as json

		}catch(\Exception $e){
			Log::error('EditDepartmentController-deactivateSubmit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}
	
}

