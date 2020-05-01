<?php 
namespace App\Modules\Litigation\Controllers\Edit;
use App\Http\Controllers\Controller;				// controller lib
use Illuminate\Http\Request;						// to handle the request data
use App\Modules\Litigation\Models\CompanyLocation;
use App\Modules\Litigation\Models\CompanyDepartment;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\CountryState;
use App\Modules\Litigation\Models\City;
use App\Modules\Litigation\Models\AdminLog;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\generalHelper;

class EditLocationController extends Controller
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

			$CompanyLocation=new CompanyLocation();
			$Country=new Country();
			$CountryState=new CountryState();
			$City=new City();
			$data=array();

			$data['detail']=$CompanyLocation->getAlldetailofCompanyLocation($id);
			$data["country"]=$Country->getcountryDetail($data['detail']->country);
			$data["state"]=$CountryState->getstateDetail($data['detail']->state);
			$data["city"]=$City->getcityDetail($data['detail']->city);
			
			return view('Litigation::Edit.Location.popup',$data);
		}catch(\Exception $e){
			Log::error('EditLocationController-popup: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function submit(Request $request){
		try{
			$id=Crypt::decrypt($request->row_id);
			$CompanyLocation=new CompanyLocation();
			$search=$CompanyLocation->getAlldetailofCompanyLocation($id);
			$rules=[
				$id => 'Numeric',
				'location_index' => 'required|string|unique:company_location,location_name,'.$id.',id,company_id,'.$search->company_id,
				'zipcode' => 'required|numeric',
				'sgst' => 'nullable|string',
				'address' => 'required|string',
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
			$updArr["location_name"]=$request->location_index;
			$updArr["pincode"]=$request->zipcode;
			$updArr["sgst"]=$request->sgst;
			$updArr["address"]=$request->address;

			$AdminLog=new AdminLog();
			$CompanyLocation->updateRecord($updArr,$id);
			
			$LogArr=Array();
			$LogArr['user_id']=getUserId();
			$LogArr['company_id']=$search->company_id;
			$LogArr['control']='Company Location';
			$LogArr['keyword']='Update';
			$LogArr['unique_id']=$id;
			$LogArr['message']="Company Location Update";
			$LogArr['data_from']=$search;
			$LogArr['data_to']=$updArr;
			$AdminLog->addlog($LogArr);

			$response=array();
			$response["status"]=1;
			$response["msg"]="Company Location Update Successfully";
			return response()->json($response);// response as json
		}catch(\Exception $e){
			Log::error('EditLocationController-submit: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}
	
}

