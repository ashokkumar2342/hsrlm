<?php 
namespace App\Modules\Litigation\Controllers\Report;
use App\Helpers\MailHelper;
use App\Helpers\generalHelper;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\ActMaster;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\CompanyUser;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\Orders;
use App\Modules\Litigation\Models\Matters;
use App\Modules\Litigation\Models\UserRole;
use App\Modules\Login\Models\AppUserRole;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;
use usersSessionHelper;
class ReportController extends Controller
{ 
	public function index(Request $request){
		try{   
			$data=array();
			$UserRole = new UserRole();
			$data['roleList']=$UserRole->getDetail();
			return view('Litigation::Report.form',$data);
		}catch(\Exception $e){
			Log::error('ReportController-index: '.$e->getMessage()); 		
			return view('error.home');
		}
	} 
	
}

