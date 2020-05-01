<?php namespace App\Modules\Litigation\Models;
use Illuminate\Database\Eloquent\Model;
class UserRole extends Model
{
	/**
	 * Added just to demonstrate that models work
	 * @return String
	 */
	 protected $table = 'user_role';
	 public $timestamps = false;
	/*
	* addCompany() is used to add company
	* @param null
	* @return boolean or String Or error
	*/
	public function childs() {
	  try {
	    return $this->hasMany(UserRole::class,'parent_id','id');
	  } catch (QueryException $e) {
	    return $e; 
	  }
	} 
	public function parent() {
	  try {
	     return $this->belongsTo(UserRole::class,'parent_id','id');
	  } catch (QueryException $e) {
	    return $e; 
	  }
	}
	public function parents() {
	  try {
	    return $this->hasMany(UserRole::class,'id','parent_id');
	  } catch (QueryException $e) {
	    return $e; 
	  }
	}
	public function insert($insArr){
		try {
			return $this->insertGetId($insArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function updateRole($updArr,$id){
		try {
			return $this->where('id',$id)
			->update($updArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function getDetail(){
		try {
			return $this->join('company','company.company_id','user_role.company_id')
			->selectRaw('user_role.*,company.name as company_name')
			->get();
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
	public function getRoleByParentId($id){
		try {
			return $this->where('parent_id',$id)->first();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	public function getRoleByIdArray($idArr){
		try {
			return $this->find($idArr);
		} catch (QueryException $e) {
			return $e; 
		}
	}
	public function getRoleByCompanyId($company_id){
		try {
			return $this->where('company_id',$company_id)->where('parent_id',0)->get();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	public function getRoleAllByCompanyId($company_id){
		try {
			return $this->where('company_id',$company_id)->get();
		} catch (QueryException $e) {
			return $e; 
		}
	}
	public function getRoleByCompanyIdArray($companyIdArray){
		try {
			$query=$this->join("company",'company.company_id', '=', 'user_role.company_id');
			
			$query->whereIn("user_role.company_id",$companyIdArray);
			$query->selectRaw('user_role.*,company.name as company_name');
			 $data=$query->get();
			 $grouped = $data->mapToGroups(function ($item, $key) {
			    return [$item['company_name'] => array($item['id']=>$item['name'])];
			});
			return $grouped->toArray();
			
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function checkRoleExistInCompany($arr){
		try {
			return $this->where('company_id',$arr['company_id'])
			->where('name','like',$arr['name'])
			->count();
		} catch (QueryException $e) {
			return $e; 
		}
	}

	public function roleDeleteById($id){
		try {
			return $this->where('id',$id)->delete();
		} catch (QueryException $e) {
			return $e; 
		}
	}

	

	
}