<?php

namespace App\Http\Controllers;
use App\Modules\Login\Controllers\LoginController;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $LoginController = new LoginController();
		return $LoginController->index();
    } 
	public function logout(Request $request)
    {
       $LoginController = new LoginController();
		return $LoginController->logout($request);
    }
}
