<?php 
namespace App\Modules\Litigation\Controllers\Edit;
use App\Http\Controllers\Controller;				// controller lib
use Illuminate\Http\Request;						// to handle the request data

use App\Modules\Litigation\Models\CompanyDepartment;
use App\Modules\Litigation\Models\CompanyDefaultDepartment;
use App\Modules\Litigation\Models\DefaultDepartment;
use App\Modules\Litigation\Models\AdminLog;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\generalHelper;

class EditDefaultDepartmentController extends Controller
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

			$data=array();

			$data['detail']=$CompanyDepartment->getDetailById($id);

			return view('Litigation::Edit.CompanyDefaultDepartment.popup',$data);
		}catch(\Exception $e){
			Log::error('EditDefaultDepartmentController-popup: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function submit(Request $request){
		try{
			$id=Crypt::decrypt($request->row_id);
			$CompanyDepartment=new CompanyDepartment();
			$search=$CompanyDepartment->getDetailById($id);
			$rules=[
				$id => 'Numeric',
				'dept_name' => 'required|string|unique:company_department,name,'.$id.',id,company_id,'.$search->company_id,
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

			$updArr=array();
			$updArr["name"]=$request->dept_name;
			$updArr["short_name"]=$request->short_name;
			$CompanyDepartment->updateRecord($updArr,$id);

			$AdminLog = new AdminLog();
			$LogArr = array();
			$LogArr['user_id']=getUserId();
			$LogArr['company_id']=$search->company_id;
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
			Log::error('EditDefaultDepartmentController-submit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}
	
}

