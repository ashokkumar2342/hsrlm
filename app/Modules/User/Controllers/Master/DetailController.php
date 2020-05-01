<?php 

namespace App\Modules\Litigation\Controllers\Master;
use App\Http\Controllers\Controller;				// controller lib
use App\Modules\Login\Models\AppUsers; 			// model of Master table
use Illuminate\Http\Request;						// to handle the request data
use Auth;
use Validator;
use Illuminate\Support\Facades\Log;					// log for exception handling
use Illuminate\Support\Facades\Crypt;
class DetailController extends Controller
{
	public function getuser(Request $request){
		try{
			$id=Crypt::decrypt($request->userId);
			$rules=[
				$id => 'numeric',
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				return view('error.home');
			}else{
				$data=array();
				$AppUsers=new AppUsers();
				$data['key']=$AppUsers->getdetailbyuserid($id);
				return view('Litigation::Master.Detail.User',$data);
			}
		}catch(\Exception $e){
			Log::error('DetailController-getuser: '.$e->getMessage()); 		// making log in file
			return view('error.home');	
		}
	}
	
}

