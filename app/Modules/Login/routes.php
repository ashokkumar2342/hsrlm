<?php 

 Route::group(['prefix' => '/', 'middleware' => ['web','prevent-back-history'],'namespace' => 'App\Modules\Login\Controllers'], function() { 
	Route::get('/', 'LoginController@index')->name('login');
	Route::get('login', 'LoginController@index')->name('user.login'); 
	Route::post('password/email', 'LoginController@forgotpassword')->name('user.password.email');
	Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('user.password.request');
	Route::get('logout', 'LoginController@logout')->name('user.logout.get');
	Route::post('login-submit', 'LoginController@loginSubmit')->name('user.login'); 
	Route::get('passwordreset/{token}','LoginController@passwordreset');
	Route::post('storepassword','LoginController@storepassword')->name('user.storepassword');  
 });
 
