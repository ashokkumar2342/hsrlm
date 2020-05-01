<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// prevent-back-history middleware 
Route::group(['middleware' => 'prevent-back-history'],function(){
	Route::get('/','HomeController@index');
	Route::get('/',[ 'as' => 'login', 'uses' => 'HomeController@index']);
	Route::get('logout',[ 'as' => 'logout', 'uses' => 'HomeController@logout']);
});
//Log file delete and show
Route::get('log/show/{name}', function($name) {    
        $file= storage_path(). "\logs/".$name;
        $headers = array(
                  'Content-Type: application/log',
                );
        return Response::download($file, 'filename.log', $headers);
});
 Route::get('log/delete/{name}', function($name) {    
        $file= storage_path(). "\logs/".$name;
        unlink($file);
        return 'delete success';
});

//end log file delete and show 
  
 
 
 

