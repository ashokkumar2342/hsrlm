<?php 
namespace App\Modules\Litigation\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Cases;
use App\Modules\Litigation\Models\Notice;
use App\Modules\Litigation\Models\Company;
use App\Modules\Litigation\Models\ActMaster;
use Illuminate\Http\Request;						// to handle the request data
use Illuminate\Support\Facades\Crypt;						// to handle the request data
use Response;
use Validator;							
use Illuminate\Support\Facades\Log;					// log for exception handling
class FileHandleController extends Controller
{
	public function getProfilePic(Request $request,$id){
		try{
			$rules = [
				"$id"=>'required|numeric'
			];
			$validator = Validator::make($request->all(),$rules);

			$storagePath = storage_path('domainstorage/'.profilepic($id));
			$mimeType = mime_content_type($storagePath);
			if( ! \File::exists($storagePath)){
				return view('error.home');
			}
			$headers = array(
				'Content-Type' => $mimeType,
				'Content-Disposition' => 'inline; '
			);
			
			if(profilepic($id)=='')
			{
				return Response::make(file_get_contents('dist/img/mock1.jpg'), 200, $headers);
			}
			else
			{	return Response::make(file_get_contents($storagePath), 200, $headers);

			}

			
		}catch(\Exception $e){
			Log::error('FileHandleController-getProfilePic: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

	public function getAct(Request $request){
		try{ 
			$data = array();  
			$id = Crypt::decrypt($request->id); 
			$val = Crypt::decrypt($request->val); 
			$type = Crypt::decrypt($request->type); 
			$rules=[
				$id=>'numeric', 
				$val=>'numeric', 
				$type=>'string', 
			];
			$validator = Validator::make($request->all(),$rules);
			if ($validator->fails()) {
				$errors = $validator->errors()->all();
				$response=array();
				$response["status"]=0;
				$response["msg"]=$errors[0];
				return response()->json($response);// response as json
			} 

			$data['id'] = $id;
			$data['val'] = $val;

			if($val != 1){
				if($type == 'notice'){
					$Notice = new Notice();
					$result = $Notice->getNoticeById($val);
					$data['actIdArr'] = explode(',',$result->act_id);	
				}else{
					$Cases = new Cases();
					$result = $Cases->getCaseById($val);
					$data['actIdArr'] = explode(',',$result->act_id);
				}
			}

			$ActMaster = new ActMaster();
			$data['actList'] = $ActMaster->getActByCatId($id,getSetCompanyId());
			$data['type'] = $type;
		 	return view('Litigation::ActMaster.actList',$data)->render();
		}catch(\Exception $e){
			Log::error('FileHandleController-getAct: '.$e->getMessage()); 		
			return view('error.home');
		}
	}

	public function getCompanyLogo(Request $request){
		try{
			$Company=new Company();
			if(UserRole()==1 || UserRole()==2)
			{
				$id=getGroupId();
			}
			else
			{
				$id=getSetCompanyId();
				// $comp_id=getCompanyId();
				// if(count($comp_id)==1)
				// {
				// 	$id=$comp_id[0];
				// }
				// else
				// {
				// 	$grp_detail=$Company->getDistinctSuperAdmin($comp_id)->toArray();
				// 	$id=$grp_detail[0]['group_id'];
					
				// }
			}
			
			$com_detail=$Company->getCompanyById($id);

			$logo_url=$com_detail['logo_url'];
			$storagePath = storage_path('domainstorage/'.$logo_url);

			$mimeType = mime_content_type($storagePath);
			if( ! \File::exists($storagePath)){
				return view('error.home');
			}
			$headers = array(
				'Content-Type' => $mimeType,
				'Content-Disposition' => 'inline; '
			);
			
			if($logo_url=='')
			{
				return Response::make(file_get_contents('img/defaultlogo.png'), 200, $headers);
			}
			else
			{	
				return Response::make(file_get_contents($storagePath), 200, $headers);
			}

			
		}catch(\Exception $e){
			Log::error('FileHandleController-getCompanyLogo: '.$e->getMessage()); 		// making log in file
			return view('error.home');									// showing the err page
			return false;
		}
	}

}