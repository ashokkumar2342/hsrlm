<?php 
namespace App\Modules\Litigation\Controllers\Dashboard;
use App\Http\Controllers\Controller;				// controller lib
use App\Modules\Login\Models\AppUsers;	// model of user table
use App\Modules\Login\Models\AppUserType;	// model of user table
use App\Modules\Login\Models\AppUserRole;	// model of user table
use App\Modules\Litigation\Models\Company;	// model of user table
use App\Modules\Litigation\Models\Document;	// model of user table
use App\Modules\Litigation\Models\ActMaster;	// model of user table
use App\Modules\Litigation\Models\CompanyUser;	// model of user table
use App\Modules\Litigation\Models\CompanyLocation;	// model of user table
use App\Modules\Litigation\Models\CompanyDepartment;	// model of user table
use Illuminate\Http\Request;						// to handle the request data
use Auth;
use Redirect;
use Validator;
use usersSessionHelper;
use Illuminate\Support\Facades\Crypt;
use URL;
use Session;
use App\Reports\MyReport;
use Illuminate\Support\Facades\Log;					// log for exception handling
class AdminDashboardController extends Controller
{

	public function __construct(){
		 $this->middleware('admin');
	}

	public function index(Request $request){
		try{
			$data = $datapass = array();
			$datapass['user_id'] = getUserId();

			$AppUserType = new AppUserType();
			$Company = new Company();
			$CompanyUser = new CompanyUser();
			$CompanyLocation = new CompanyLocation();
			$CompanyDepartment = new CompanyDepartment();

			$datapass['arrayCompanyId']=$CompanyUser->getCompanyIdByUser(getUserId()); 
			$data['totalUser'] = $AppUserType->getResult($datapass,'countByArray');
			$data['totalEntity'] = $CompanyUser->getResult($datapass,'count');
			$data['totalLocation'] = $CompanyLocation->getlocationlistForAdminDashboard($datapass,'count');
			$data['totalDepartment'] = $CompanyDepartment->getdepartmentlistForAdminDashboard($datapass,'count');

			$data['company_list'] = $Company->getResult($datapass,'getList');

			return view('Litigation::Dashboard.Admin.dashboard',$data);
		}catch(\Exception $e){
			Log::error('AdminDashboardController-index page: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function popuptop(Request $request){
		try{
			$data = $datapass = array();
			$type = Crypt::decrypt($request->type);

			$Company = new Company();
			$AppUsers = new AppUsers();
			$CompanyUser = new CompanyUser();
			$CompanyLocation = new CompanyLocation();
			$CompanyDepartment = new CompanyDepartment();

			$data['type'] = $type;
			$datapass['arrayCompanyId'] = $arrayCompanyId = $CompanyUser->getCompanyIdByUser(getUserId()); 

			if($type == 'company_list_top'){
				$data['companyList'] = $Company->getResult($datapass,'getList');
			}elseif($type == 'user_list_top'){
				$data['userList'] =$AppUsers->getAllCompanyUsers($arrayCompanyId); 
			}elseif($type == 'location_list_top'){
				$data['locationList'] = $CompanyLocation->getlocationlistForAdminDashboard($datapass,'list'); 
			}else{
				$data['departmentList'] = $CompanyDepartment->getdepartmentlistForAdminDashboard($datapass,'list'); 
			}

			return view('Litigation::Dashboard.Admin.popup',$data);
		}catch(\Exception $e){
			Log::error('AdminDashboardController-popuptop page: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}



	public function popup(Request $request){
		try{
			$data = $arr = array();
			$company_id = $arr['company_id'] = Crypt::decrypt($request->id);
			$type = $request->type;

			$rules=[
				$company_id => 'Numeric',
				$type=>'string'
			];
			
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				return view('error.home');
			}

            $Document = new Document();
            $ActMaster = new ActMaster();
            $CompanyUser = new CompanyUser();
            $AppUserRole = new AppUserRole();
			$CompanyLocation = new CompanyLocation();
            $CompanyDepartment = new CompanyDepartment();

			switch ($request->type) {
				case "location":
			        $data['type']='location_list_top';
			        $data['locationList']=$CompanyLocation->getlocationlistForAdminDashboard($arr,'listByCompanyId');
			        break;

			    case "department":
		        	$data['type']='department_list_top';
		        	$data['departmentList']=$CompanyDepartment->getdepartmentlistForAdminDashboard($arr,'listByCompanyId');
		        	break; 

		        case "act":
		        	$data['type']='Act';
		        	$data['actList']=$ActMaster->getActlistForAdminDashboard($arr,'listByCompanyId');
		        	break;

		        case "active_user":
		        	$data['type']='user_list_top';
		        	$data['userList']=$AppUserRole->getUserlistForAdminDashboard($arr,'listByCompanyId');
		        	break;

		        case "storagelist":
		        	$data['type']='Storage';
		        	$data['docSize']=$Document->getDocumentForAdminDashboard($arr,'sizeByCompanyId');
		        	break;

		        case "storagesize":
		        	$data['type']='Storage';
		        	$data['docSize']=$Document->getDocumentForAdminDashboard($arr,'sizeByCompanyId');
		        	break;

				default:
			        $type="location";
			}

			return view('Litigation::Dashboard.Admin.popup',$data);
		}catch(\Exception $e){
			Log::error('AdminDashboardController-popup page: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}
	
}

