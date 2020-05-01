<?php 
/*
* File Name: AppUserType.php
* Description: AppUserType.php file has user model is used to handle app_user table data
* Created Date: 14 Apr 2017
* Created By: Naresh Shankar S <nareshshankars@gmail.com>
* Modified Date & Reason:
*/
namespace App\Modules\Login\Models;					//name space declaration
use Illuminate\Database\Eloquent\Model;				//Eloquent Model
use Illuminate\Support\Facades\DB;
class AppUserRole extends Model
{
	 protected $table = 'app_user_role';					// assigning table name of the model
	 public $timestamps = false;					// asssigning default timestamp to false
	 protected $fillable = [
	    'user_role_id','user_id','company_id','status'
	];
	/*
	* getUserType() is used to get the usertype of admin or superadmin
	* @param email,passsword
	* @return array of area table values
	*/
	
	/*
	* addUserType() is used to add user Type
	* @param null
	* @return boolean or String Or error
	*/
	public function addUserRole($insArr){
		try {
			return $this->insertGetId($insArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}
	//update
	public function updateAppUserRole($updArr,$user_id){
		try {
			return $this->where('user_id',$user_id)
			->update($updArr);
		} catch (QueryException $e) {
			return $e; 
		}
	} 

	public function updateById($updArr,$id){
		try {
			return $this->where('id',$id)
			->update($updArr);
		} catch (QueryException $e) {
			return $e; 
		}
	} 
	//createOrUpdate
	public function createOrUpdateAppUserRole($updArr,$role_id,$user_id){
		try {
			return $this->updateOrCreate(['user_id'=>$user_id,'user_role_id'=>$role_id],$updArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}
	public function getRoleByCompanyIdArrayUserId($companyId,$userId){
		try {
			return $this->where('user_id',$userId)
			->where('company_id',$companyId)	 
			->first();
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function countRoleByUserId($userId){
		try {
			return $this->where('user_id',$userId)	 
			->count();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	//get user by company id and role id
	public function getUserByCompanyId($company_id){
		try {
			$query=$this->where('company_id',$company_id);		 
			return $query->get();
			
		} catch (QueryException $e) {
			return $e; 
		}
	}
	//get Compamy wise user Id
	public function getCompanyIdArrayByUserId($userId){
		try {
			$query=$this->where('user_id',$userId);		 
			return $query->distinct()->pluck('company_id')->toArray();			 
			
		} catch (QueryException $e) {
			return $e; 
		}
	}	//get Compamy wise user Id
	public function getAppUserRoleByCompanyIdUserId($companyId,$userId){
		try {
			$query=$this->where('user_id',$userId)->whereIn('company_id',$companyId);		 
			return $query->pluck('user_role_id')->toArray();
			 
			
		} catch (QueryException $e) {
			return $e; 
		}
	}
	//get Compamy wise user Id
	public function getRoleIdArrByCompanyIdUserId($companyId,$userId){
		try {
			$query=$this->where('user_id',$userId)->where('company_id',$companyId);		 
			return $query->pluck('user_role_id')->toArray();
			 
			
		} catch (QueryException $e) {
			return $e; 
		}
	}

	
	public function checkCompanyIdExistByUserId($cmp_id,$user_id){
		try {
			return $this->where('user_id',$user_id)->where('company_id',$cmp_id)->count();
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function getUserlistForAdminDashboard($arr,$type){
		try {

			if($type == 'countByCompanyId'){
				return $this->join('app_user','app_user.user_id','app_user_role.user_id')
				->join('app_user_type','app_user_type.user_id','app_user.user_id')
				->where('app_user_role.company_id',$arr['company_id'])
				->where("app_user_type.user_type",3)
				->where('app_user.status',1)
				->distinct('app_user_role.user_id')
				->count('app_user_role.user_id');	
			}

			if($type == 'listByCompanyId'){
				return $this->join('app_user','app_user.user_id','app_user_role.user_id')
				->join('app_user_type','app_user_type.user_id','app_user.user_id')
				->leftJoin("app_user_profile",'app_user_profile.user_id', '=','app_user.user_id')
				->leftJoin("company_department",'company_department.id', '=','app_user_profile.dept_id')
				->selectRaw('app_user.name,app_user.email,app_user.mobile,app_user.user_id,company_department.name as dept_name,app_user.status')
				->where("app_user_role.company_id",$arr['company_id'])
				->where("app_user_type.user_type",3)
				->where('app_user.status',1)
				->distinct()
				->get();
			}
		} catch (QueryException $e) {
			return $e; 
		}
	}
	//get user id by role id
	public function getUserIdByArrUserRoleId($arrUserROleId){
		try {
			return $this->whereIn('user_role_id',$arrUserROleId)->pluck('user_id')->toArray();
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function getRoleById($id){
		try {
			return $this->where('id',$id)->first();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	public function checkRoleExits($role_id){
		try {
			return $this->where('user_role_id',$role_id)->get();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	
}