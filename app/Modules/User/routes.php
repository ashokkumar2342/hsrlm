<?php 
Route::group(['prefix' => '/', 'middleware' => ['web','auth','prevent-back-history','securedevice'], 'namespace' => 'App\Modules\Litigation\Controllers'], function () { 
	//Dashboard
	Route::prefix('dashboard')->group(function () {
		Route::get('/','Dashboard\DashboardController@index');
	}); 

	//superadmin
	Route::get('/superadmin','CompanyCreation\CompanycreationController@index')->name('superadmin');
	//superadmin

	//admin dashboard functions
	Route::get('/admin/dashboard','Dashboard\AdminDashboardController@index')->name('admin');
	//admin dashboard functions

	//profilepic
	Route::get('/profilepic/{id}','Master\FileHandleController@getProfilePic');
	Route::get('/getact/{id}/{val}/{type}','Master\FileHandleController@getAct');  
	Route::get('/companylogo','Master\FileHandleController@getCompanyLogo');
	//profilepic

	Route::prefix('admin')->group(function () {
		Route::get('/popuptop/{type}','Dashboard\AdminDashboardController@popuptop');
		Route::get('/popup/{id}/{type}','Dashboard\AdminDashboardController@popup');
	});

	Route::prefix('graph')->group(function () {
		Route::get('/likelihood','Graph\GraphController@likelihood');
		Route::get('/expensegraph','Graph\GraphController@expensegraph');
		Route::get('/riskpiechart','Graph\GraphController@riskpiechart');
	});

	Route::prefix('support')->group(function () {
		Route::get('/dashboard','Dashboard\SupportDashboardController@index')->name('support');
		Route::post('/createnoticetracker','Dashboard\SupportDashboardController@createNoticeTracker');
		Route::post('/updatetracker','Dashboard\SupportDashboardController@updateTracker')->name('support.update');
		Route::get('/gettrackerlist','Dashboard\SupportDashboardController@getTrackerList');
		Route::get('/getuploadbutton/{company_id}/{temp_id}/{type}','Dashboard\SupportDashboardController@getUploadButton');
		Route::get('/edit/{id}','Dashboard\SupportDashboardController@edit')->name('support.edit');
		Route::get('/download/{id}','Dashboard\SupportDashboardController@download')->name('support.download'); 
		Route::get('/upload/{id}','Dashboard\SupportDashboardController@upload');
		Route::get('/uploadedit/{id}','Dashboard\SupportDashboardController@uploadedit');
		Route::get('/getdoclist/{temp_id}/{type}','Dashboard\SupportDashboardController@getDocList');
		Route::post('/uploadsubmit','Dashboard\SupportDashboardController@uploadsubmit');
		Route::post('/uploadeditsubmit','Dashboard\SupportDashboardController@uploadeditsubmit');
		Route::post('/documentupdate','Dashboard\SupportDashboardController@documentUpdate');
		Route::post('/documentdelete','Dashboard\SupportDashboardController@documentdelete');
	});

 	// profile  user
	Route::group(['prefix' => 'profile'],function(){
		Route::get('/','Profile\ProfileController@index')->name('user.profile');
		Route::post('update','Profile\ProfileController@updatesubmit')->name('profile.update');
		Route::post('changepassword','Profile\ProfileController@changepassword')->name('profile.changepassword');
		Route::post('userpic','Profile\ProfileController@userpic')->name('userpic.upload');
		Route::get('profilepic','Profile\ProfileController@getProfilePic')->name('userpic.show');
		Route::get('profilepicid/{id}','ProfileController@getProfilePicById')->name('userpic.show.byid'); 
		Route::get('activity-log','Profile\ProfileController@activityLog')->name('activity.log'); 
	}); 

	Route::prefix('companycreation')->group(function () {
		Route::get('/companyedit/{id}','CompanyCreation\CompanycreationController@companyedit');
		Route::post('/addsubmit','CompanyCreation\CompanycreationController@addsubmit');
		Route::post('/updatecompany','CompanyCreation\CompanycreationController@updatecompany');
		Route::get('/companylist','CompanyCreation\CompanycreationController@companylist');
		Route::get('/list','CompanyCreation\CompanycreationController@adminlist');
		Route::get('/refreshcontent','CompanyCreation\CompanycreationController@refreshcontent');
		Route::get('/getstate','CompanyCreation\CompanycreationController@getstate');
		Route::post('/addadmin','CompanyCreation\CompanycreationController@addadmin');
		Route::post('/assignadmin','CompanyCreation\CompanycreationController@assignadmin');
		Route::get('/getcurrency/{company_id_array}/{type}','CompanyCreation\CompanycreationController@getcurrency');
	});

	Route::prefix('detail')->group(function () {
	   Route::get('/user/{userId}','Master\DetailController@getuser');
	});

	//matrix report route
	Route::group(['prefix' => 'matrixreport', 'middleware' => ['permission']], function () { 
		Route::get('/','MatrixReport\MatrixReportController@index');
		Route::get('/getcaseform','MatrixReport\MatrixReportController@getcaseform');
		Route::post('/searchcase','MatrixReport\MatrixReportController@searchCaseResult');
		Route::post('/searchpivotcase','MatrixReport\MatrixReportController@searchpivotcase');
		Route::post('/searchnotice','MatrixReport\MatrixReportController@searchNoticeResult');
		Route::get('/getact/{act_id}/{master_id}','MatrixReport\MatrixReportController@getAct');
		Route::post('/searchpivotnotice','MatrixReport\MatrixReportController@searchpivotnotice');
		Route::get('/changecasetemplate','MatrixReport\MatrixReportController@changecasetemplate');
		Route::get('/advancefiltercase','MatrixReport\MatrixReportController@loadAdvanceFilterCase');
		Route::get('/changenoticetemplate','MatrixReport\MatrixReportController@changenoticetemplate');
		Route::get('/advancefilternotice','MatrixReport\MatrixReportController@loadAdvanceFilterNotice');
		Route::get('/getnoticestatus/{master_id}/{type_id}','MatrixReport\MatrixReportController@getNoticeStatus');
	});
	//matrix report route

	Route::prefix('advancefilter')->group(function () {
		Route::post('/savecase','AdvanceFilter\AdvanceFilterController@saveCase');
		Route::post('/savenotice','AdvanceFilter\AdvanceFilterController@saveNotice');
		Route::get('/changefilternotice','AdvanceFilter\AdvanceFilterController@changeFilterNotice');
	});
	//advance filter route

	Route::prefix('noticetracker')->group(function () {
	   Route::get('/view','NoticeTracker\NoticeTrackerController@index');
	   Route::get('/viewtable','NoticeTracker\NoticeTrackerController@viewtable');
	   Route::get('/download/{id}','NoticeTracker\NoticeTrackerController@download'); 
	   Route::get('/popup/{id}','NoticeTracker\NoticeTrackerController@popup'); 
	   Route::post('/accept','NoticeTracker\NoticeTrackerController@accept'); 
	   Route::post('/reject','NoticeTracker\NoticeTrackerController@reject'); 
	   Route::post('/transfer','NoticeTracker\NoticeTrackerController@transfer'); 
	});

	Route::prefix('usermaster')->group(function () {
		Route::get('/','UserMasters\UserMasterController@index'); 

	    //Role Master Route
		Route::get('/rolepopup/{id}','UserMasters\UserMasterController@rolepopup');
		Route::get('/roletablerefresh/{user_id}','UserMasters\UserMasterController@roletablerefresh');
		Route::post('/updateuserrole','UserMasters\UserMasterController@updateuserrole');
		Route::get('/show-tree-role','UserMasters\UserMasterController@showTreeRole')->name('show.tree.role');
		Route::get('/addrole/{company_id}/{parent_id}','UserMasters\UserMasterController@addrole')->name('role.add');
		Route::get('/refviewrolelist','UserMasters\UserMasterController@refviewrolelist');
		Route::post('/submitrole','UserMasters\UserMasterController@submitrole');
		Route::get('/editroledetail/{id}','UserMasters\UserMasterController@editrole')->name('role.edit');
		Route::get('/role-delete/{id}','UserMasters\UserMasterController@roleDelete')->name('role.delete');
		Route::get('/assign-role-form/{company_id}/{id}','UserMasters\UserMasterController@assignRoleForm')->name('role.assign.form');
		Route::post('/updaterole','UserMasters\UserMasterController@updaterole');
	    //Role Master Route

	    //User Master Route
		Route::get('/userlist','UserMasters\UserMasterController@userlist'); 
		Route::get('/selectuserlist','UserMasters\UserMasterController@selectuserlist'); 
		Route::get('/supportuserlist','UserMasters\UserMasterController@supportuserlist'); 
		Route::get('/edituser/{id}','UserMasters\UserMasterController@editUserDetails')->name('user.details.edit');
		Route::get('/adduser','UserMasters\UserMasterController@addUserFormShow')->name('add.user');
		Route::post('/submituser','UserMasters\UserMasterController@storeUserDetails')->name('user.details.store');
		Route::get('/rolesearch/{id}','UserMasters\UserMasterController@roleSearch')->name('role.search');
		Route::get('/refuserviewlist','UserMasters\UserMasterController@refUserlist')->name('user.list.refresh');
		Route::post('/submituserupdate','UserMasters\UserMasterController@updateUserDetails')->name('user.details.update');
		Route::get('/rolesearchUserEdit','UserMasters\UserMasterController@roleSearchEditUser')->name('role.search.user.edit'); 
		Route::post('/blukuploadsubmit','UserMasters\UserMasterController@blukUploadSubmit');
		Route::post('/rolesubmit','UserMasters\UserMasterController@roleSubmit')->name('user.role.submit');
		Route::get('/assignrole','UserMasters\UserMasterController@assignRole');
	    Route::get('/statuschange/{id}/{status}','UserMasters\UserMasterController@statuschange');
	    Route::get('/supportuserstatuschange/{id}/{status}','UserMasters\UserMasterController@supportuserstatuschange');
	    Route::get('/resend/{id}','UserMasters\UserMasterController@resend');
	    Route::get('/getfield/{id}','UserMasters\UserMasterController@getField');
	    //User Master Route

	    //Permission Route
		Route::post('/rolemenu/store','UserMasters\UserMasterController@roleMenuStore')->name('role.menu.store'); 
		Route::get('/rolemenu','UserMasters\UserMasterController@roleMenu'); 
		Route::get('/companylist','UserMasters\UserMasterController@companyList'); 
	    //Permission Route 

	    //department route
		Route::get('/companydept/{id}','UserMasters\UserMasterController@companyDept'); 
		Route::get('/companylocation/{company_id}','UserMasters\UserMasterController@companyLocation'); 
		Route::get('/companydeptedit/{id}/{user_id}','UserMasters\UserMasterController@companyDeptEdit'); 
	    //department route
	});


	Route::prefix('legalcategory')->group(function () {
		Route::get('/','LegalCategory\LegalCategoryController@index'); 

		//Legal Category Route
		Route::get('/refreshcat','LegalCategory\LegalCategoryController@refreshCat'); 
		Route::get('/addcat','LegalCategory\LegalCategoryController@addCat'); 
		Route::get('/editcat/{id}','LegalCategory\LegalCategoryController@editCat'); 
		Route::post('/submitcategory','LegalCategory\LegalCategoryController@submitCategory'); 
		Route::post('/updatecategory','LegalCategory\LegalCategoryController@updateCategory'); 
		//Legal Category Route

		//Act Route
		Route::get('/actlist','LegalCategory\LegalCategoryController@actList'); 
		Route::get('/addact','LegalCategory\LegalCategoryController@addAct'); 
		Route::get('/editact/{id}','LegalCategory\LegalCategoryController@editAct'); 
		Route::post('/submitact','LegalCategory\LegalCategoryController@submitAct'); 
		Route::post('/updateact','LegalCategory\LegalCategoryController@updateAct'); 
		Route::get('/refreshact','LegalCategory\LegalCategoryController@refreshAct'); 
		//Act Route

	});

	Route::prefix('mattermaster')->group(function () {
		Route::get('/','MatterMaster\MatterMasterController@index'); 

		//Matter Group Route
		Route::get('/grouplist/{id}','MatterMaster\MatterMasterController@groupList'); 
		Route::get('/addgroup/{id}','MatterMaster\MatterMasterController@addGroup'); 
		Route::get('/editgroup/{id}','MatterMaster\MatterMasterController@editGroup'); 
		Route::post('/submitgroup','MatterMaster\MatterMasterController@submitGroup'); 
		Route::post('/updategroup','MatterMaster\MatterMasterController@updateGroup'); 
		Route::get('/refviewgrouplist/{company_id}','MatterMaster\MatterMasterController@refViewGroupList'); 
		//Matter Group Route

		//Matter Type Route
		Route::get('/typelist/{id}','MatterMaster\MatterMasterController@typeList'); 
		Route::get('/addtype/{id}','MatterMaster\MatterMasterController@addType'); 
		Route::get('/edittype/{id}','MatterMaster\MatterMasterController@editType'); 
		Route::post('/submittype','MatterMaster\MatterMasterController@submitType'); 
		Route::post('/updatetype','MatterMaster\MatterMasterController@updateType'); 
		Route::get('/refviewtypelist/{company_id}','MatterMaster\MatterMasterController@refViewTypeList'); 
		//Matter Type Route

	});

	Route::prefix('companyconfig')->group(function () {		
		Route::post('/sizefilesubmit','CompanyConfig\CompanyconfigController@sizefilesubmit');
		// Route::post('/filesubmit','CompanyConfig\CompanyconfigController@filesubmit');
	});

	Route::prefix('actmaster')->group(function () {		
		Route::post('/actsubmit','ActMaster\ActMasterController@actSubmit');
		Route::post('/updateact','ActMaster\ActMasterController@updateAct');
		Route::get('/editact/{id}','ActMaster\ActMasterController@editAct'); 
		Route::post('/exportexcel','ActMaster\ActMasterController@exportexcel');
		Route::post('/excelsubmit','ActMaster\ActMasterController@excelsubmit');
		Route::get('/getactlist/{id}','ActMaster\ActMasterController@getActList');
		Route::get('/downloadexcel','ActMaster\ActMasterController@downloadexcel');
	});


	Route::prefix('adminmaster')->group(function () {
		Route::get('/','Companylocation\CompanylocationController@viewlist');
	});

	Route::prefix('companylocation')->group(function () {
		Route::get('/add','Companylocation\CompanylocationController@add');
		Route::get('/locationshow','Companylocation\CompanylocationController@showdepartment');
		Route::get('/locationlistshow','Companylocation\CompanylocationController@locationlistshow');

		Route::get('/state','Companylocation\CompanylocationController@loadState');
		Route::get('/city','Companylocation\CompanylocationController@loadcity');
		Route::post('/locationsubmit','Companylocation\CompanylocationController@locationsubmit');

		Route::get('/compdepartmentadd','Companylocation\CompanyDefaultDepartmentController@compdepartmentadd');
		Route::post('/compdepartmentsubmit','Companylocation\CompanyDefaultDepartmentController@compdepartmentsubmit');
		Route::get('/compdepartmentlistshow','Companylocation\CompanyDefaultDepartmentController@compdepartmentlistshow');
	});

	Route::prefix('edit')->group(function () {
		Route::get('/location/{id}','Edit\EditLocationController@popup');
		Route::post('/location/submit','Edit\EditLocationController@submit');
		Route::get('/location/deactivate/{id}','Edit\EditLocationController@deactivate');
		Route::post('/location/deactivate/submit','Edit\EditLocationController@deactivateSubmit');

		Route::get('/compdefaultdepartment/{id}','Edit\EditDefaultDepartmentController@popup');
		Route::post('/compdefaultdepartment/submit','Edit\EditDefaultDepartmentController@submit');

		Route::get('/department/{id}','Edit\EditDepartmentController@popup');
		Route::post('/department/submit','Edit\EditDepartmentController@submit');
		Route::get('/department/deactivate/{id}','Edit\EditDepartmentController@deactivate');
		Route::post('/department/deactivate/submit','Edit\EditDepartmentController@deactivateSubmit');
	});

	Route::prefix('loginreport')->group(function () {
		Route::get('/','LoginReport\LoginReportController@index');
		Route::get('/report','LoginReport\LoginReportController@report');
		Route::get('/popup/{id}/{days}','LoginReport\LoginReportController@popup');
	});

	Route::prefix('expenses')->group(function () {
		Route::get('/view/{ref_id}/{legal_type_id}','Expenses\ExpensesController@index');
		Route::get('/addpopup/{ref_id}/{legal_type_id}','Expenses\ExpensesController@addpopup');
		Route::get('/editpopup/{id}','Expenses\ExpensesController@editpopup');
		Route::get('/upload/{id}/{type}','Expenses\ExpensesController@upload');
		Route::post('/uploadsubmit','Expenses\ExpensesController@uploadsubmit');
		Route::post('/documentdelete','Expenses\ExpensesController@documentdelete');
		Route::get('/getdoclist/{temp_id}/{type}','Expenses\ExpensesController@getDocList');
		Route::get('/download/{id}','Expenses\ExpensesController@download')->name('expenses.download'); 
		Route::post('/addexpense','Expenses\ExpensesController@addexpense');
		Route::post('/editexpense','Expenses\ExpensesController@editexpense');
	});

	Route::prefix('caseposition')->group(function () {
		Route::get('/addpopup/{case_id}','CasePosition\CasePositionController@addpopup');
		Route::get('/editpopup/{id}','CasePosition\CasePositionController@editpopup');
		Route::post('/addposition','CasePosition\CasePositionController@addposition');
		Route::post('/editposition','CasePosition\CasePositionController@editposition');
	});

	Route::prefix('positionexpense')->group(function () {
		Route::get('/view/{ref_id}/{legal_type_id}','PositionExpense\PositionExpenseController@index');
	});

	Route::prefix('lawyermaster')->group(function () {
		Route::get('/view','LawyerMaster\LawyerMasterController@index');
		Route::get('/popup-list/{level}','LawyerMaster\LawyerMasterController@lawyerPopupList')->name('lawyer.popup.list');
		Route::get('/city','LawyerMaster\LawyerMasterController@city')->name('lawyer.city');
		Route::get('/show','LawyerMaster\LawyerMasterController@show')->name('lawyer.show');
		Route::get('/state','LawyerMaster\LawyerMasterController@state')->name('lawyer.state');
		Route::get('/showfirm','LawyerMaster\LawyerMasterController@showfirm')->name('firm.show');
		Route::get('/addpopup','LawyerMaster\LawyerMasterController@addpopup')->name('lawyer.create');
		Route::post('/firmsubmit','LawyerMaster\LawyerMasterController@firmsubmit')->name('lawfirm.submit');
		Route::post('/firmupdate','LawyerMaster\LawyerMasterController@firmupdate')->name('lawfirm.update');
		Route::post('/lawyersubmit','LawyerMaster\LawyerMasterController@lawyersubmit')->name('lawyer.submit');
		Route::post('/lawyerupdate','LawyerMaster\LawyerMasterController@lawyerupdate')->name('lawyer.update');
		Route::get('/addpopupfirm','LawyerMaster\LawyerMasterController@addpopupfirm')->name('lawfirm.create');
		Route::get('/editpopup/{lawyer_id}','LawyerMaster\LawyerMasterController@editpopup')->name('lawyer.edit');
		Route::get('/editpopupfirm/{firm_id}','LawyerMaster\LawyerMasterController@editpopupfirm')->name('firm.edit');
		Route::get('/cityedit/{ref_id}/{s_id}','LawyerMaster\LawyerMasterController@cityedit')->name('lawyer.cityedit');
		Route::get('/stateedit/{ref_id}/{c_id}','LawyerMaster\LawyerMasterController@stateedit')->name('lawyer.stateedit');
	});


	//user route and middleware use user
	Route::group(['prefix' => 'user', 'middleware' => ['web','user','permission','auth','prevent-back-history','securedevice']], function () { 	
		Route::prefix('dashboard')->group(function () {
			Route::get('/','Dashboard\UserDashboardController@index')->name('user');
			Route::get('set/company/{company_id}','Dashboard\UserDashboardController@setCompany')->name('user.set.compnayId');
			Route::get('role/search','Dashboard\UserDashboardController@roleSearch')->name('user.role.search');	
			Route::get('calender/{month}/{year}/{view_mode}','Dashboard\UserDashboardController@calender')->name('calender.view');	     
			Route::get('search-text/{id}/{text}','Dashboard\UserDashboardController@resultList')->name('search.result.list');	
			Route::get('global-search','Dashboard\UserDashboardController@globalSearch')->name('global.search');  
			Route::get('/casepopup/{from}/{to}/{likelihood}/{risk}/{type}','Dashboard\UserDashboardController@casePopup');     
		});	 
		//routes for calender
		Route::prefix('calender')->group(function () {
			Route::get('/','Calender\CalenderController@view');
			Route::get('/popup/{date}','Calender\CalenderController@popup');
		 
		});	
		// Country
		Route::group(['prefix' => 'country'],function(){
			Route::get('search','Country\CountryController@search')->name('state.search');  
			Route::get('city-search','Country\CountryController@citySearch')->name('city.search');  

			Route::get('state','Country\CountryController@countryState')->name('stateSearch.multiple'); 
			Route::get('stateEdit','Country\CountryController@countryStateEdit')->name('stateSearch.multiple.edit'); 
			Route::get('cityEdit','Country\CountryController@countryCityEdit')->name('citySearch.multiple.city'); 
			Route::get('city','Country\CountryController@countryStateCity')->name('citySearch.multiple'); 
		}); 
		Route::prefix('matter')->group(function () {
			Route::get('/list','Matter\MatterController@index')->name('matter.list'); 
			Route::get('/editmatter/{matter_id}','Matter\MatterController@editmatter')->name('matter.edit'); 
			Route::get('/create','Matter\MatterController@create')->name('matter.create'); 
			Route::get('/show','Matter\MatterController@show')->name('matter.show');  
			Route::post('/store','Matter\MatterController@store')->name('matter.store');  
			Route::post('/update/{matter_id}','Matter\MatterController@update')->name('matter.update');  
			Route::get('/view/{matter_id}/{type}','Matter\MatterController@matterView')->name('matter.view'); 
			Route::get('/matterpopup/{matter_id}','Matter\MatterController@matterpopup'); 
			Route::get('/tree/view/{matter_id}','Matter\MatterController@treeView')->name('matter.tree.view');  
			Route::get('/tree/show/{matter_id}','Matter\MatterController@treeShowData')->name('matter.tree.show.data');  
			Route::get('/tree/menus/{matter_id}','Matter\MatterController@menusShow')->name('matter.tree.menu.show');  
			Route::get('/tree/popup/show','Matter\MatterController@matterTreePopupShow')->name('matter.tree.popup.show'); 
			Route::get('/noticecasehearinglist/{matter_id}/{legal_type_id}','Matter\MatterController@noticecasehearinglist');  
		});
		Route::prefix('cases')->group(function () {
			Route::get('/list','Cases\CasesController@index')->name('cases.list'); 
			Route::get('/getbarchart/{year}','Cases\CasesController@getbarchart'); 
			Route::get('/getpopupbydaterange/{type}/{from}/{to}','Cases\CasesController@getpopupbydaterange');
			Route::get('/listpopupbymonthyear/{type}/{month}/{year}','Cases\CasesController@listpopupbymonthyear');
			Route::get('/list-popup/{type}','Cases\CasesController@listPopup')->name('case.list.popup'); 
			Route::get('/create','Cases\CasesController@create')->name('cases.create');  
			Route::get('/show','Cases\CasesController@show')->name('cases.show');   
			Route::get('/view/{id}','Cases\CasesController@view')->name('cases.view');   
			Route::get('/edit/{id}','Cases\CasesController@edit')->name('cases.edit');   
			Route::post('/update/{id}','Cases\CasesController@update')->name('cases.update');   
			Route::get('/table-list/{matter_id}','Cases\CasesController@showByMatterId')->name('cases.show.by.matterid');  
			Route::post('/store','Cases\CasesController@store')->name('cases.store'); 
			Route::get('/popup/form/{matter_id}/{master_id}/{legal_type_id?}','Cases\CasesController@popupForm')->name('cases.popup.form'); 
			Route::get('/court-category','Cases\CasesController@courtCategory')->name('court.category'); 
			Route::get('/high-court/bench','Cases\CasesController@courtBench')->name('court.bench'); 
			Route::get('/high-court/bench/side','Cases\CasesController@courtBenchSide')->name('bench.side'); 
			Route::get('/high-court/bench/side/stamp','Cases\CasesController@courtBenchSideStamp')->name('bench.side.stamp'); 
			Route::get('/commissions-type','Cases\CasesController@commissionsType')->name('commissions.type'); 
			Route::get('/commissions/state','Cases\CasesController@commissionsState')->name('commissions.state'); 
			Route::get('/state/district','Cases\CasesController@district')->name('state.district'); 
			Route::get('commissions/state/district','Cases\CasesController@commissionsStateDistrict')->name('commissions.state.district'); 
			Route::get('/state/district/court-establishment','Cases\CasesController@courtEstablishment')->name('state.district.court.establishment');
			Route::get('commissionerate-type','Cases\CasesController@commissionerateType')->name('commissionerate.type');
			Route::get('appearing-model-as','Cases\CasesController@appearingModelAs')->name('appearing.model.as'); 
			Route::get('appearing-field','Cases\CasesController@appearingField')->name('appearing.field');  
			Route::get('tribunals-authoritie-state','Cases\CasesController@tribunalsAuthoritieState')->name('tribunals.authoritie.state'); 
			Route::get('tribunals-authoritie-state-section','Cases\CasesController@tribunalsAuthoritieStateSection')->name('tribunals.authoritie.state.section'); 
			Route::get('revenue-district','Cases\CasesController@revenueDistrict')->name('revenue.district'); 
			Route::get('revenue-district-court','Cases\CasesController@revenueDistrictCourt')->name('revenue.district.court'); 
			Route::get('/lawyer/delete/{id}','Cases\CasesController@lawyerDelete')->name('cases.lawyer.delete'); 
			Route::get('/opponent/delete/{id}','Cases\CasesController@opponentDelete')->name('cases.opponent.delete');
			Route::get('/case/type','Cases\CasesController@caseType')->name('case.type'); 
			Route::get('/other/case/type','Cases\CasesController@otherCaseType')->name('other.case.type'); 
			Route::get('barchart/{year}','Cases\CasesController@barchart')->name('case.barchart'); 
			

		});

		Route::prefix('notice')->group(function () {
			Route::get('/list','Notice\NoticeController@index')->name('notice.list'); 
			Route::get('/getpiechart/{month}/{year}','Notice\NoticeController@getpiechart'); 
			Route::get('/getbarchart/{year}','Notice\NoticeController@getbarchart'); 
			Route::get('/list-popup/{type}','Notice\NoticeController@listPopup')->name('notice.list.popup'); 
			Route::get('/listpopupbymonthyear/{type}/{month}/{year}','Notice\NoticeController@listpopupbymonthyear'); 
			Route::get('/create','Notice\NoticeController@create')->name('notice.create'); 
			Route::post('/store','Notice\NoticeController@store')->name('notice.store'); 
			Route::get('/show','Notice\NoticeController@show')->name('notice.show');   
			Route::get('/view/{id}','Notice\NoticeController@view')->name('notice.view'); 
			Route::get('/edit/{id}','Notice\NoticeController@edit')->name('notice.edit'); 
			Route::post('/update/{id}','Notice\NoticeController@update')->name('notice.update'); 
			Route::get('/table-list/{matter_id}','Notice\NoticeController@showByMatterId')->name('notice.show.by.matterid'); 
			Route::get('/popup/form/{matter_id}/{master_id}/{legal_type_id?}','Notice\NoticeController@popupForm')->name('notice.popup.form');
			Route::get('/status-change','Notice\NoticeController@noticeTypeChnage')->name('notice.type.status.change');
			Route::get('/statuschange','Notice\NoticeController@statuschange')->name('statuschange');
			Route::get('/statuschangemultiple/{type_id}','Notice\NoticeController@statuschangemultiple')->name('statuschangemultiple');
			Route::get('/notice-link-matter','Notice\NoticeController@noticeLinkMatter')->name('notice.link.matter');
			Route::post('/notice-link-matter-store','Notice\NoticeController@noticeLinkMatterStore')->name('notice.link.matter.store');

		});

		Route::prefix('nextaction')->group(function () {
			Route::get('/nextactiontable/{id}/{type}','NextAction\NextActionController@index');  
			Route::get('/nextaction-popup-list/{type}','NextAction\NextActionController@listPopup')->name('next.action.list.popup');  
			Route::get('/nextactionaddform/{id}/{type}','NextAction\NextActionController@nextActionAddForm');  
			Route::get('/nextactioneditform/{id}','NextAction\NextActionController@nextActionEditForm');  
			Route::get('/nextactiondoclist/{id}','NextAction\NextActionController@nextactiondoclist');  
			Route::get('/nextactionreopen/{id}','NextAction\NextActionController@nextactionreopen');  
			Route::post('/reopennextaction','NextAction\NextActionController@reopennextaction');  
			Route::post('/nextactionsubmit','NextAction\NextActionController@nextActionSubmit');   
			Route::post('/nextactionupdate','NextAction\NextActionController@nextActionUpdate');
			Route::get('/upload/{id}','NextAction\NextActionController@upload');
			Route::post('/uploadsubmit','NextAction\NextActionController@uploadsubmit');
			Route::post('/documentdelete','NextAction\NextActionController@documentdelete');
			Route::get('/getdoclist/{id}','NextAction\NextActionController@getDocList');
			Route::get('/download/{id}','NextAction\NextActionController@download')->name('nextaction.download'); 
		});

		Route::prefix('position')->group(function () {
			Route::get('/list','Position\PositionController@index')->name('position.list'); 
			Route::get('/create','Position\PositionController@create')->name('position.create');  
			Route::post('/store','Position\PositionController@store')->name('position.store');  
			Route::get('/show','Position\PositionController@show')->name('position.show');  
			Route::get('/view/{id}','Position\PositionController@view')->name('position.view'); 
			Route::get('/update/{id}','Position\PositionController@view')->name('position.update'); 
			Route::get('/table-list/{matter_id}','Position\PositionController@showByMatterId')->name('position.show.by.matterid'); 
			Route::get('/popup/form/{matter_id}/{master_id}/{legal_type_id?}','Position\PositionController@popupForm')->name('position.popup.form');  

		});

		Route::prefix('hearing')->group(function () {
			Route::get('/list','Hearing\HearingController@index')->name('hearing.list'); 
			Route::get('/getpiechart/{year}','Hearing\HearingController@getpiechart'); 
			Route::get('/getbarchart/{year}','Hearing\HearingController@getbarchart'); 
			Route::get('/listpopupbyyearpie/{type}/{year}','Hearing\HearingController@listpopupbyyearpie');
			Route::get('/listpopupbymonthyear/{type}/{month}/{year}','Hearing\HearingController@listpopupbymonthyear'); 
			Route::get('/list-popup/{type}','Hearing\HearingController@listPopup')->name('hearing.list.popup'); 
			Route::get('/show','Hearing\HearingController@show')->name('hearing.show');  
			Route::get('/create','Hearing\HearingController@create')->name('hearing.create');  
			Route::post('/store','Hearing\HearingController@store')->name('hearing.store');
			Route::get('/popup/form/{matter_id}/{master_id}/{legal_type_id}','Hearing\HearingController@popupForm')->name('hearing.popup.form'); 
			Route::get('/view/{hearing_id}','Hearing\HearingController@view')->name('hearing.view');   
			Route::get('/update/{id}','Hearing\HearingController@view')->name('hearing.update');   
			Route::get('/statusdata/{hearing_id}/{status_id}','Hearing\HearingController@statusdata');   
			Route::get('/chnagegainexpousrebox/{hearing_id}/{id}','Hearing\HearingController@chnagegainexpousrebox');   
			Route::get('/edithearing/{hearing_id}','Hearing\HearingController@edithearing');   
			Route::get('/updatehearing/{hearing_id}','Hearing\HearingController@updatehearing');
			Route::post('/editdetails','Hearing\HearingController@editdetails');   
			Route::post('/updatedetails','Hearing\HearingController@updatedetails');   
		});

		Route::prefix('orders')->group(function () {
			Route::get('/list','Orders\OrdersController@index')->name('orders.list'); 
			Route::get('/create','Orders\OrdersController@create')->name('orders.create');  
			Route::post('/store','Orders\OrdersController@store')->name('orders.store');
			Route::get('/popup/form/{matter_id}/{master_id}/{legal_type_id}','Orders\OrdersController@popupForm')->name('orders.popup.form');   
			Route::get('/view/{id}','Orders\OrdersController@view')->name('orders.view'); 
			Route::get('/update/{id}','Orders\OrdersController@view')->name('orders.update'); 
		});
		Route::prefix('judgement')->group(function () {
			Route::get('/list','Judgement\JudgementController@index')->name('judgement.list'); 
			Route::get('/create','Judgement\JudgementController@create')->name('judgement.create');  
			Route::post('/store','Judgement\JudgementController@store')->name('judgement.store');
			Route::get('/popup/form/{matter_id}/{master_id}/{legal_type_id}','Judgement\JudgementController@popupForm')->name('judgement.popup.form');
			Route::get('/view/{id}','Judgement\JudgementController@view')->name('judgement.view');    
			Route::get('/update/{id}','Judgement\JudgementController@view')->name('judgement.update');    
		});
		Route::prefix('settlement')->group(function () {
			Route::get('/list','Settlement\SettlementController@index')->name('settlement.list'); 
			Route::get('/create','Settlement\SettlementController@create')->name('settlement.create');  
			Route::post('/store','Settlement\SettlementController@store')->name('settlement.store');
			Route::get('/popup/form/{matter_id}/{master_id}/{legal_type_id}','Settlement\SettlementController@popupForm')->name('settlement.popup.form'); 
			Route::get('/view/{id}','Settlement\SettlementController@view')->name('settlement.view');    
			Route::get('/update/{id}','Settlement\SettlementController@view')->name('settlement.update');    
		});
		Route::prefix('select-option')->group(function () {
			Route::get('/show/{modelName}','Cases\CasesController@selectOptionCreate')->name('select.option.crate');
			Route::post('/store/{modelName}','Cases\CasesController@selectOptionStore')->name('select.option.store');  
		});
		Route::prefix('document')->group(function () {
			Route::get('/show/{id}/{legal_id}','Document\DocumentController@show')->name('document.view');
			Route::get('/list/{id}','Document\DocumentController@list');
			Route::get('/upload/{id}','Document\DocumentController@upload');
			Route::get('/documentlist','Document\DocumentController@documentlist');
			Route::get('/upload/{id}','Document\DocumentController@upload');
			Route::post('/uploadsubmit','Document\DocumentController@uploadsubmit');
			Route::post('/update','Document\DocumentController@documentUpdate')->name('document.update');
			Route::post('/statuschange','Document\DocumentController@statusChange')->name('document.statuschange');
			Route::post('/documentdelete','Document\DocumentController@documentdelete');
			Route::get('/documentview/{id}','Document\DocumentController@documentview');
			Route::get('/viewhistory/{id}/{category}','Document\DocumentController@viewhistory'); 
			Route::get('/download/{id}','Document\DocumentController@download')->name('document.download'); 
			Route::get('/refreshlist/{id}/{legal_id}','Document\DocumentController@refreshlist')->name('refreshlist'); 
		});

		Route::prefix('activity')->group(function () {
			Route::get('/show/{id}/{table_id}','Activity\ActivityController@show')->name('activity.view'); 
		});
		Route::prefix('report')->group(function () {
			Route::get('/view','Report\ReportController@index')->name('report.view'); 
			 
		});

	});
	//end user route


	//company location admin



});
//end auth route
Route::group(['prefix' => 'securedevice', 'middleware' => ['web','prevent-back-history','auth'],'namespace' => 'App\Modules\Litigation\Controllers\SecureDevice'], function() { 	
	Route::get('/','SecureDeviceController@index')->name('securedevice.show');
	Route::post('/validate','SecureDeviceController@validatedevice')->name('securedevice.submit');
	Route::get('newpasswordreset','SecureDeviceController@newPasswordReset')->name('user.newpasswordreset');
	Route::post('storenewpassword','SecureDeviceController@storeNewPassword')->name('user.storenewpassword');
});



