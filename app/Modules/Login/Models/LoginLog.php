<?php

namespace App\Modules\Login\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
	
	protected $table = 'login_log';
    public $timestamps = false;			// asssigning default timestamp to false 
    
    public function insLoginLog($insArr){
    	try {
    		return $this->insertGetId($insArr);
    	} catch (QueryException $e) {
    		return $e; 
    	}
    } 
    public function Activity($userid){
    	try {
    		$query=$this->where('user_id',$userid)
    		->orderBy('timestamp', 'desc')
    		->skip(1)->take(1)->first();
    		if($query==null)
    		{
    			$query=$this->where('user_id',$userid)
    			->orderBy('timestamp', 'desc')
    			->first();
    		}
    		return $query;
    	} catch (QueryException $e) {
    		return $e; 
    	}
    }

    public function ActivitySummery($userid){
    	try {
    		$query=$this->where('user_id',$userid)
    		->orderBy('timestamp', 'desc')
    		->get(); 
    		return $query;
    	} catch (QueryException $e) {
    		return $e; 
    	}
    }

    public function DeviceSummery($array){
    	try {
    		$query=$this->where('user_id',$array['user_id'])
    		->where('ip',$array['ip'])
    		->where('device_name',$array['device_name'])
    		->where('device_type',$array['device_type'])
    		->where('browser_name',$array['browser_name'])
    		->where('platform_name',$array['platform_name'])
    		->where('platform_version',$array['platform_version']);

    		if(isset($array['city']))
    		{
    			$query->where('city',$array['city']);
    		}

    		if(isset($array['country']))
    		{
    			$query->where('country',$array['country']);	
    		}

    		return $query->count();
    	} catch (QueryException $e) {
    		return $e; 
    	}
    }

    public function LoginLogByUserIdFromAndToDate($userid,$from,$to){
        try {
            $query=$this->where('user_id',$userid)
            ->whereBetween('timestamp',[$from,$to])
            ->count();
            return $query;
        } catch (QueryException $e) {
            return $e; 
        }
    }

    public function ActivitySummeryByDateRange($userid,$from,$to){
        try {
            $query=$this->where('user_id',$userid)
            ->whereBetween('timestamp',[$from,$to])
            ->orderBy('timestamp', 'desc')
            ->get();
            return $query;
        } catch (QueryException $e) {
            return $e; 
        }
    }
}
