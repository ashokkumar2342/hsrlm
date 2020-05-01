<?php 
/*
* File Name: AppUsers.php
* Description: AppUsers.php file has user model is used to handle app_user table data
* Created Date: 9 Apr 2019
* Created By: Ashok Kumar <ashok.kumar@lawrbit.com>
* Modified Date & Reason:
*/
namespace App\Modules\Login\Models;					//name space declaration
use Illuminate\Database\Eloquent\Model;				//Eloquent Model
use Illuminate\Support\Facades\DB;
class AppUsers extends Model
{
	 protected $table = 'app_user';					// assigning table name of the model
	 public $timestamps = false;					// asssigning default timestamp to false
	
	/*
	* ValidateUser() is used to validate with password
	* @param email,passsword
	* @return array of area table values
	*/
	public function companies(){
		$this->hasMany();
	}
	public function ValidateUser($email){
		try {
			$query=$this->join('app_user_auth', 'app_user_auth.user_id', '=', 'app_user.user_id');
			$query->where("app_user.email",'=',$email);
			$query->where("app_user_auth.status",'=',1);
			$query->where("app_user.status",'=',1);
			$query->select(array("app_user.id","app_user.name","app_user.mobile","app_user.email","app_user_auth.status","app_user_auth.password","app_user.status as user_status","app_user.user_id"));
			$data=$query->first();
			if(isset($data))
				return $data->toArray();
		}catch (QueryException $e) {
			return $e; 
		}
	}
	/*
	* checkUserExist() is used to validate with email exist
	* @param email
	* @return user id
	*/
	public function checkUserExist($email){
		try {
			$query=$this->leftJoin('app_user_type', function($join){
				$join->on('app_user.user_id', '=', 'app_user_type.user_id');
			});
			$query->where("app_user.email",$email);
			$query->select(array("app_user.user_id","app_user_type.user_type"));
			$data=$query->first();
			if(isset($data))
				return $data->toArray();
		}catch (QueryException $e) {
			return $e; 
		}
	}
	/*
	* getAllUserType() is used to get user type of particular user
	* @param email
	* @return user id
	*/
	public function getAllUserType($user_id){
		try {
			
			$query=$this->join('app_user_type',"app_user.user_id","app_user_type.user_id");
			$query->join("default_user_type",'default_user_type.id', '=', 'app_user_type.user_type');
			$query->select("app_user_type.user_type","default_user_type.name");
			$query->where("app_user.status",1);
			$query->where("app_user_type.status",1);
			$query->where("app_user.user_id",$user_id);
			$query->orderBy("default_user_type.sort_by");
			$data=$query->groupby('app_user_type.user_type','default_user_type.name')
			->distinct()->get();
			
			if(isset($data))
				return $data->toArray();
			return array();
		}catch (QueryException $e) {
			return $e; 
		}
	}
	/*
	* getAllUserCompany() is used to get user all cmpny user
	* @param email
	* @return user id
	*/
	public function getAllUserCompany($user_id,$type){
		try {
			
			$query=$this->join('app_user_type',"app_user.user_id","app_user_type.user_id");
			$query->join("company",'company.company_id', '=', 'app_user_type.company_id');
			$query->select('company.company_id','company.name');
			$query->where("app_user.status",1);
			$query->where("app_user_type.status",1);
			$query->where("app_user.user_id",$user_id);
			$query->where("app_user_type.user_type",$type);
			$query->orderBy("company.name");
			$data=$query->groupby('company.company_id','company.name')
			->distinct()->get();
			
			if(isset($data))
				return $data->toArray();
			return array();
		}catch (QueryException $e) {
			return $e; 
		}
	}
	//get all user by company id
	public function getAllUserByCompanyId($company_id){
		try {
			
			$query=$this->leftJoin('app_user_role',"app_user.user_id","app_user_role.user_id");
			$query->leftJoin("company",'company.company_id', '=', 'app_user_role.company_id');
			$query->leftJoin("app_user_type",'app_user_type.user_id', '=', 'app_user.user_id');
			$query->leftJoin("user_role",'user_role.id', '=','app_user_role.user_role_id');
			$query->select('app_user.user_id','app_user.name');
			$query->where("app_user.status",1);
			$query->where("app_user_role.company_id",$company_id);
			$query->where("app_user_type.user_type",3);
			$query->orderBy("app_user.name");
			$data=$query->groupby('app_user.user_id','app_user.name')
			->distinct()->get();			
			if(isset($data))
				return $data->toArray();
			return array();
		}catch (QueryException $e) {
			return $e; 
		}
	}

	public function getCompanyUsers($companyId){
		try {
			
			$query=$this->join('app_user_type',"app_user.user_id","app_user_type.user_id");
			$query->select('app_user.user_id','app_user.name','app_user_type.user_type');
			$query->where("app_user.status",1);
			$query->where("app_user_type.status",1);
			$query->where("app_user_type.company_id",$companyId);
			$query->orderBy("app_user.name");
			return $data=$query->get();
		}catch (QueryException $e) {
			return $e; 
		}
	}

	public function getAllCompanyUsers($arrayCompanyId,$type=''){
		try {  
			if($type == ''){
				$query=$this->leftJoin('app_user_role',"app_user.user_id","app_user_role.user_id");
				$query->leftJoin("company",'company.company_id', '=','app_user_role.company_id');
				$query->join("app_user_type",'app_user_type.user_id', '=','app_user.user_id');
				$query->leftJoin("user_role",'user_role.id', '=','app_user_role.user_role_id');
				$query->leftJoin("app_user_profile",'app_user_profile.user_id', '=','app_user.user_id');
				$query->leftJoin("company_department",'company_department.id', '=','app_user_profile.dept_id');
				$query->where("app_user_type.user_type",3); 
				$query->whereIn("app_user_role.company_id",$arrayCompanyId);
				$query->selectRaw('GROUP_CONCAT(app_user_role.company_id) as company_id,app_user.name,app_user.email,app_user.mobile,app_user.user_id,company_department.name as dept_name,app_user.status');
				$query->orderBy("app_user.name"); 
				return $data=$query->groupBy('app_user.name','app_user.email','app_user.mobile','app_user.user_id','company_department.name','app_user.status')->get();
			}

			if($type == 4){
				return $this->join('app_user_type',"app_user.user_id","app_user_type.user_id")
				->join("company_location",'company_location.id','app_user_type.location')
				->join('support_user_company','support_user_company.user_id','app_user.user_id')
				->leftJoin("app_user_profile",'app_user_profile.user_id','app_user.user_id')
				->where("app_user_type.user_type",$type)
				->whereRaw('FIND_IN_SET(?,support_user_company.company_id)', [$arrayCompanyId])
				->selectRaw('app_user.name,app_user.email,app_user.mobile,app_user.user_id,app_user.status,company_location.location_name')
				->orderBy("app_user.name")
				->distinct()
				->get();
			}

			if($type == 'byCompanyId'){
				$query=$this->join('app_user_profile','app_user.user_id','app_user_profile.user_id');
				$query->join("app_user_type",'app_user_type.user_id', '=','app_user.user_id');
				$query->leftJoin("company_department",'company_department.id', '=','app_user_profile.dept_id');
				$query->selectRaw('app_user.name,app_user.email,app_user.mobile,app_user.user_id,app_user.status,company_department.name as dept_name');
				$query->where("app_user_profile.created_by",getUserId());
				$query->where("app_user_type.user_type",3);
				$query->orderBy("app_user.name"); 
				return $data=$query->get();
			}
			
		}catch (QueryException $e) {
			return $e; 
		}
	}


	public function getAdmins(){
		try {
			
			return $this->join('app_user_type',"app_user.user_id","app_user_type.user_id")
			->where('app_user_type.user_type',2)
			->select('app_user.user_id','app_user.name','app_user.email')
			->orderBy("app_user.name")
			->distinct('app_user.name')
			->get();
		}catch (QueryException $e) {
			return $e; 
		}
	}
	
	/*
	* addUser() is used to add user
	* @param null
	* @return boolean or String Or error
	*/
	public function addUser($insArr){
		try {
			return $this->insertGetId($insArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function getdetailbyuserid($user_id){
		try {
			return $this->where("user_id",$user_id)
			->first();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	public function getdetailbyToken($token){
		try {
			return $this->where("token",$token)
			->first();
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function updateuserdetail($updArr,$user_id){
		try {
			return $this->where('user_id',$user_id)
			->update($updArr);
		} catch (QueryException $e) {
			return $e; 
		}
	} 

	public function getdetailbyemail($email){
		try {
			return $this->where("email",$email)->where('status',1)
			->first();
		} catch (QueryException $e) {
			return $e; 
		}
	} 

	public function todos(){
		try {
			return $this->hasMany('App\Modules\Litigation\Models\Todo','user_id','user_id');
		} catch (QueryException $e) {
			return $e; 
		}
	}

	
	
	public function getUserDataById($id){
		try {
			return $this->where("user_id",$id)
			->selectRaw('name,user_id,email')
			->first();
		} catch (QueryException $e) {
			return $e; 
		}
	} 

	public function getUserByArrId($arr_id){
		try {
			return $this->whereIn("user_id",$arr_id)
			->selectRaw('name,user_id,email')
			->get();
		} catch (QueryException $e) {
			return $e; 
		}
	} 

	public function getList(){
		try {
			return $this->select('email')
			->get();
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function getFullUsersDetailById($user_id,$type=''){
		try { 
			if($type == ''){
				return $this->leftJoin('app_user_role',"app_user.user_id","app_user_role.user_id")
				->leftJoin("company",'company.company_id', '=','app_user_role.company_id')
				->join("app_user_type",'app_user_type.user_id', '=','app_user.user_id')
				->leftJoin("user_role",'user_role.id', '=','app_user_role.user_role_id')
				->leftJoin("app_user_profile",'app_user_profile.user_id', '=','app_user.user_id')
				->leftJoin("company_department",'company_department.id', '=','app_user_profile.dept_id')
				->where("app_user.user_id",$user_id)
				->selectRaw('GROUP_CONCAT(app_user_role.company_id) as company_id,app_user.name,app_user.email,app_user.mobile,app_user.user_id,company_department.name as dept_name,app_user.status')
				->orderBy("app_user.name") 
				->groupBy('app_user.name','app_user.email','app_user.mobile','app_user.user_id','company_department.name','app_user.status')->first();	
			}

			if($type == 4){
				return $this->join('app_user_type',"app_user.user_id","app_user_type.user_id")
				->join("company_location",'company_location.id','app_user_type.location')
				->leftJoin("app_user_profile",'app_user_profile.user_id','app_user.user_id')
				->where("app_user.user_id",$user_id)
				->selectRaw('app_user.name,app_user.email,app_user.mobile,app_user.user_id,app_user.status,company_location.location_name')
				->orderBy("app_user.name")->first();	
			}

			if($type == 'byUserArr'){
				return $this->whereIn('user_id',$user_id)
				->where('status',1)
				->orderBy('name','Asc')
				->get();
			}
			
		}catch (QueryException $e) {
			return $e; 
		}
	}
	
	
}