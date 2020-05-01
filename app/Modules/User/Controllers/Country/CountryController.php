<?php

namespace App\Modules\Litigation\Controllers\Country;

use App\Http\Controllers\Controller;
use App\Modules\Litigation\Models\City;
use App\Modules\Litigation\Models\Country;
use App\Modules\Litigation\Models\CountryState;
use App\Modules\Login\Models\AppUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    public function search(Request $request){  
        try{  
            $state=new CountryState(); 
            $states =$state->getState($request->country); 
            return view('Litigation::SelectOption.state',compact('states'))->render();
        }catch(\Exception $e){
            Log::error('CountryController-search: '.$e->getMessage());         // making log in file
            return view('error.home');  
        }
    }
    // city search
    public function citySearch(Request $request){  
        try{  
            $city =new City();
            return $cities =$city->getCity($request->country,$request->state); 
        }catch(\Exception $e){
            Log::error('CountryController-citySearch: '.$e->getMessage());         // making log in file
            return view('error.home');  
        }
    } 
}
