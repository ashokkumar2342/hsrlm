<?php 
namespace App\Modules\Litigation\Controllers\LoginReport;
use App\Http\Controllers\Controller;		 
use App\Modules\Login\Models\LoginLog; 
use App\Modules\Login\Models\AppUserType; 		
use App\Modules\Litigation\Models\Company;									
use Validator;	
use generalHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;	
use Illuminate\Support\Facades\Crypt;

class LoginReportController extends Controller
{
	
	public function __construct(){
		 $this->middleware('admin');
	}
	
	public function index(Request $request){
		try{
			$data=array();
			$Company=new Company();
			$data["companyList"]=$Company->listCompany();
			return view('Litigation::LoginReport.view',$data);
		}catch(\Exception $e){
			Log::error('LoginReportController-index: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function report(Request $request){
		try{
			$id=Crypt::decrypt($request->id);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}else{
				$data=array();
				$AppUserType=new AppUserType();
				$data["company_id"]=$id;
				$data["report"]=$AppUserType->getLoginReport($id);
				return view('Litigation::LoginReport.data',$data);
			}
		}catch(\Exception $e){
			Log::error('LoginReportController-report: '.$e->getMessage()); 		// making log in file
			return view('error.home');
		}
	}

	public function popup(Request $request){
		try{
			$user_id=Crypt::decrypt($request->id);
			$days=Crypt::decrypt($request->days);
			$rules=[
				$days => 'Numeric',
				$user_id => 'Numeric',
			];
			
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				return view('error.home');
			}

			$LoginLog = new LoginLog();
			$generalHelper=new generalHelper();
			$data=array();
			
			$title="";
			if($days <= 30){
				$labelArr = array();
				if($days == 7){
					$title = "7 Days Report";
					$dateArr = $this->getLastNDays(7, 'Y-m-d');
				}elseif($days == 30){
					$title = "30 Days Report";
					$dateArr = $this->getLastNDays(30, 'Y-m-d');
				}
				for($i=0;$i<count($dateArr);$i++){
					$result[] = $generalHelper->LoginLogByUserIdFromAndToDateAndTime($user_id,$dateArr[$i],$dateArr[$i]);
					$labelArr[] = date('d/m/Y',strtotime($dateArr[$i]));
				}
			}

			if($days >= 60){
				$labelArr = array();
				if($days == 60){
					$title = "60 Days Report";
					$dateArr = $this->getLastNDays(60, 'Y-m-d');
				}elseif($days == 90){
					$title = "90 Days Report";
					$dateArr = $this->getLastNDays(90, 'Y-m-d');
				}elseif($days == 180){
					$title = "180 Days Report";
					$dateArr = $this->getLastNDays(180, 'Y-m-d');
				}
    			$last = end($dateArr);
				$count = (int)(count($dateArr)/7)+1;
				for($i=1;$i<=$count;$i++){
					if($i == 1){
						$startdate = date('Y-m-d');
						$strtotime = strtotime($startdate);
						$enddate = date('Y-m-d', strtotime('-6 days', $strtotime));
						$labelArr[] = date('d/m/Y',strtotime($startdate)).'-'.date('d/m/Y',strtotime($enddate));
					}elseif($i == $count){
						$startdate = date('Y-m-d', strtotime($enddate .' -1 day'));
						$enddate = $last;
						$labelArr[] = date('d/m/Y',strtotime($startdate)).'-'.date('d/m/Y',strtotime($enddate));
					}else{
						$startdate = date('Y-m-d', strtotime($enddate .' -1 day'));
						$strtotime = strtotime($startdate);
						$enddate = date('Y-m-d', strtotime('-6 days', $strtotime));
						$labelArr[] = date('d/m/Y',strtotime($startdate)).'-'.date('d/m/Y',strtotime($enddate));
					}
					$result[] =  $generalHelper->LoginLogByUserIdFromAndToDateAndTime($user_id,$enddate,$startdate);
				}
			}

			$lastdate = end($dateArr).' 00:00:00';
			$firstdate = date('Y-m-d').' 23:59:59';
			$data['ActivitySummery'] = $LoginLog->ActivitySummeryByDateRange($user_id,$lastdate,$firstdate);

			$view = 'Litigation::LoginReport.popup';
			$data['title'] = $title;
			$data['result'] = implode(',',$result);
			$data['dateArr'] = '"'.implode('","', $labelArr).'"';
			return view($view,$data);
		}catch(\Exception $e){
			Log::error('LoginReportController-popup: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		} 
	}

	public function getLastNDays($days, $format = 'm-d'){
	    $m = date("m"); $de= date("d"); $y= date("Y");
	    $dateArray = array();
	    for($i=0; $i<=$days-1; $i++){
	        $dateArray[] = date($format, mktime(0,0,0,$m,($de-$i),$y)); 
	    }
	    return $dateArray;
	}

	
}

