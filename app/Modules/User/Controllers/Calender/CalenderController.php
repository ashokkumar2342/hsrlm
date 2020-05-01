<?php 
namespace App\Modules\Litigation\Controllers\Calender;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Hearing;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Validator;					// log for exception handling
class CalenderController extends Controller
{
	 
	public function view(Request $request){
		try{
			$rules=[
			'm' => 'required',
			'y' => 'required',
			];

			$m=Crypt::decrypt($request->m);
			$y=Crypt::decrypt($request->y);

			$rules[$m]='numeric|between:1,12';
			$rules[$y]='numeric|between:2015,2050';
			

			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				
				return view('error.home');
			}else{
				

				$data=array();
				
				$data["month"]=$m;
				$data["year"]=$y;
				//dd($data);
				return view('Compliance::Calender.view',$data);
			}
			
			
		}catch(\Exception $e){
			Log::error('CalenderController-view: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}


	public function popup(Request $request,$date){
		try{
			$date=Crypt::decrypt($date);
			$rules=[
			$date => 'date|date_format:Y-m-d',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}else{
				$data = $arr = array();
				$Hearing=new Hearing();  
				$arr['childId'] = getChildUserId();
				$arr['date'] = $date;				 
		        $data["hearingList"]=$Hearing->getResult($arr,'hearing_date'); 
		        $data["date"]=$date; 
				return view('Litigation::Calender.popup',$data);
			}
		}catch(\Exception $e){
			Log::error('CalenderController-popup: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	
	
	
}

