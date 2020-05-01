<?php

namespace App\Modules\Login\Models;

use Illuminate\Database\Eloquent\Model;

class DomainConfig extends Model
{
	protected $table = 'domain_config';
   public $timestamps = false;	// asssigning default timestamp to false 

   public function domain(){
   	try {
   		return $this->orderBy('id', 'asc')
   		->take(1)
   		->first();
   	} catch (QueryException $e) {
   		return $e; 
   	}
   }



}