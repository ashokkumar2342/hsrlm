<?php

namespace App\Modules\Login\Models;

use Illuminate\Database\Eloquent\Model;

class AppUserAuth extends Model
{
    protected $table = 'app_user_auth';
    protected $fillable = [
	    'user_id','password','status'
	];
    public $timestamps = false;			// asssigning default timestamp to false 

    public function insAppAuth($insArr){
		try {
			return $this->insertGetId($insArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}
   
	public function updateuserpassword($updArr,$user_id){
		try {
			return $this->where('user_id',$user_id)
			->update($updArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}	
	public function createOrUpdateUserPassword($updArr,$user_id){
		try {
			return $this->updateOrCreate(['user_id'=>$user_id],$updArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function lastpassword($user_id){
		try {
			return $this->where('user_id',$user_id)
			->selectRaw('`updated_at`,DATEDIFF(CURRENT_TIMESTAMP(),`updated_at`) as diff')->first();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	
	public function passwordcount($user_id){
		try {
			return $this->where('user_id',$user_id)
			->count();
		} catch (QueryException $e) {
			return $e; 
		}
	}
}
