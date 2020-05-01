<?php

namespace App\Modules\Litigation\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ActivityController extends Controller
{    
  
    // show  activity
    public function show(Request $request){  
        try{  
            $id =Crypt::decrypt($request->id);
            $table_id =Crypt::decrypt($request->table_id);
            $activity=new Activity(); 
            $activitys =$activity->getActivityByRefId($id); 
            $data =array();
            $data['table_id'] = $table_id;
            $data['activitys']=$activitys;
            return view('Litigation::Activity.activity_table',$data)->render();
        }catch(\Exception $e){
            Log::error('ActivityController-show: '.$e->getMessage());         // making log in file
            return view('error.home');  
        }
    }
     
}
