<?php 
namespace App\Modules\Litigation\Controllers\Dashboard;
use App\Http\Controllers\Controller;                // controller lib
use App\Modules\Login\Models\AppUsers;         // model of user table
use Illuminate\Http\Request;                        // to handle the request data
use Illuminate\Support\Facades\Crypt;                       // to handle the request data
use Auth;
use Redirect;
use Validator;
use usersSessionHelper;
use URL;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;                 // log for exception handling
class DashboardController extends Controller
{
    public function index(Request $request){
        try{
            if(getUserType() == 1){
                return redirect()->route('superadmin');
            }
            if(getUserType() == 2){
                return redirect()->route('admin');
            }
            if(getUserType() == 3){
                return redirect()->route('user');
            }
            if(getUserType() == 4){
                return redirect()->route('support');
            }

        }catch(\Exception $e){
            Log::error('DashboardController-index: '.$e->getMessage());        // making log in file
            return view('error.home');
        }
    } 
}

