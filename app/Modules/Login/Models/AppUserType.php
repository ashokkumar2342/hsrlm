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
class AppUserType extends Model
{
	 protected $table = 'app_user_type';					// assigning table name of the model
	 public $timestamps = false;					// asssigning default timestamp to false
	
	/*
	* getUserType() is used to get the usertype of admin or superadmin
	* @param email,passsword
	* @return array of area table values
	*/
	public function getUserType($user_id,$company_id=""){
		try {
			$query=$this->join("company",'company.company_id', '=', 'app_user_type.company_id');
			$query->where("app_user_type.user_id",'=',$user_id);
			if($company_id!="")
				$query->where("app_user_type.company_id",'=',$company_id);
			$query->where("app_user_type.status",'=',1);
			$query->select("app_user_type.user_type","app_user_type.product","company.group_id","company.company_id");
			return $query->get();
		}catch (QueryException $e) {
			return $e; 
		}
	}


 	public function getUserAdminandSuperadminType($user_id,$company_id=""){
 		try {
 			$userarray=array(1,2);
 			$query=$this->join("company",'company.company_id', '=', 'app_user_type.company_id');
 			$query->where("app_user_type.user_id",'=',$user_id);
 			if($company_id!="")
 				$query->where("app_user_type.company_id",'=',$company_id);
 			$query->where("app_user_type.status",'=',1);
 			$query->wherein("app_user_type.user_type",$userarray);
 			$query->select("app_user_type.user_type","app_user_type.product","company.group_id","company.company_id");
 			return $query->get();
 		}catch (QueryException $e) {
 			return $e; 
 		}
 	}


	/*
	* addUserType() is used to add user Type
	* @param null
	* @return boolean or String Or error
	*/
	public function addUserType($insArr){
		try {
			return $this->insertGetId($insArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function checkusertype($company_id,$user_id,$user_type,$product){
		try {
			return $this->where("company_id",$company_id)
			->where("user_id",$user_id)
			->where("user_type",$user_type)
			->where("product",$product)
			->count();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	

	public function getUserTypenamebyid($user_type){
		try {
			$query=$this->join("default_role",'default_role.id', '=', 'app_user_type.user_type');
			$query->where("default_role.id",'=',$user_type);
			$query->select("default_role.name");
			$val=$query->first();
			return $val['name'];
		}catch (QueryException $e) {
			return $e; 
		}
	}

	public function locationOrDepartmentbyCompanyUsertypeUserid($company_id,$user_id,$user_type,$result)
	{
		try {
			$query=$this->wherein("company_id",$company_id)
			->where("user_id",$user_id)
			->where("user_type",$user_type)
			->groupBy('user_type');
			if($result=='loc')
			{
				$query->selectRaw('group_concat(location) as detail');
			}
			elseif($result=='dep')
			{
				$query->selectRaw('group_concat(department) as detail');
			}
			elseif($result=='act')
			{
				$query->selectRaw('group_concat(act) as detail');
			}
			return $query->first();
		} catch (QueryException $e) {
			return $e; 
		}

	}

	public function updateUserType($id,$updArr){
		try {
			return $this->where('user_id',$id)
			->update($updArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function getNullCompnayRaw($id){
		try {
			return $this->where('user_id',$id)
			->whereNull('company_id')
			->count();
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function getResult($arr,$type){
		try {
			if($type == 'countByArray'){
				return $this->join('app_user','app_user.user_id','app_user_type.user_id')
				->whereIn('app_user_type.company_id',$arr['arrayCompanyId'])
				->where("app_user_type.user_type",3)
				->count();	
			}

			if($type == 'getUserType'){
				return $this->where('user_id',$arr['user_id'])->first();
			}
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function getLoginReport($company_id){
		try {
			$query= $this->join('app_user',"app_user_type.user_id","app_user.user_id")
			->join('default_role',"app_user_type.user_type","default_role.id")
			->where('app_user_type.company_id',$company_id)
			->where('app_user_type.user_type','!=',1);
			return $query->selectRaw('group_concat(distinct default_role.name) as role,app_user.name as name,app_user.email as email,app_user.mobile as mobile,app_user.user_id as user_id,app_user.status as status')
			->groupby('app_user.user_id','app_user.name','app_user.email','app_user.mobile','app_user.user_id','app_user.status')->get();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	
	
}