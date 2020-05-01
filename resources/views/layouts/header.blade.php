<!-- Top Menu Items -->
@php 
$domain=domainConfig();
$role = getUserType();
@endphp
<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="mobile-only-brand pull-left">
		<div class="nav-header pull-left">
			<div class="logo-wrap" style="padding-top: 7px !important; ">
				<a href="{{ URL::to('/') }}">
					<img class="brand-img" src="{{url('companylogo')}}" height="50" width="180" alt="brand"/>
				</a>
			</div>
		</div>	
	<a id="toggle_nav_btn" class="toggle-left-nav-btn inline-block ml-20 pull-left" href="javascript:void(0);"><i class="zmdi zmdi-menu"></i></a>
	<a id="toggle_mobile_search" data-toggle="collapse" data-target="#search_form" class="mobile-only-view" href="javascript:void(0);"><i class="zmdi zmdi-search"></i></a>
	<a id="toggle_mobile_nav" class="mobile-only-view" href="javascript:void(0);"><i class="zmdi zmdi-more"></i></a>
	<div style="float: left; position: relative;">
	@if (getUserType()==3)
		<form action="{{ route('search.result.list',[Crypt::encrypt(0),Crypt::encrypt('byForm')]) }}" id="search_form" role="search" class="top-nav-search collapse pull-left" autocomplete="off">
		<div class="input-group"> 
			<input type="text" name="title" class="form-control" placeholder="Search"  onkeyup="callAjax(this,'{{ route('global.search') }}','searchResult')" id="search" onclick="showDiv('search_result_div')"  onfocusout="setTimeout(function(){ hideDiv('search_result_div') }, 1000);"> 
			<span class="input-group-btn">
			<button type="button" class="btn  btn-default"  data-target="#search_form" data-toggle="collapse" aria-label="Close" aria-expanded="true"><i class="zmdi zmdi-search"></i></button>
			</span>
		</div>
	</form>  
	@endif
	
	<div style="position: absolute;
    border: 1px solid #ccc;
    width: calc(42% - 137px);
    box-shadow: 2px 2px 2px #ccc;
    z-index: 1000;
    background: #fff;
    margin-left: 25px;
    margin-top: 65px;display: none; width: 320px; max-height:400px; overflow: auto;"  id="search_result_div">
	       <ul id="searchResult">
	           
	       </ul>
	   </div>
	</div>
</div>
	<div id="mobile_only_nav" class="mobile-only-nav pull-right">
		<ul class="nav navbar-right top-nav pull-right"> 
			<li class="dropdown app-drp">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="zmdi zmdi-apps top-nav-icon"></i></a>
				<ul class="dropdown-menu app-dropdown" data-dropdown-in="slideInRight" data-dropdown-out="flipOutX">
					<li>
						<div class="app-nicescroll-bar">
							<ul class="app-icon-wrap pa-10"> 
								@if (getCompanyIdName()->count()!=1)
								 @php
									$userCompanyIdNames=getCompanyIdName(); 
								 @endphp
								 @if (count($userCompanyIdNames)>0)
								 	 @foreach($userCompanyIdNames as $company)
								 	 <li class=" {{ $company->company_id==getSetCompanyId()?'bg-success':'' }}">
								 	 	<a href="{{ route('user.set.compnayId',Crypt::encrypt($company->company_id)) }}" class="connection-item selected">
								 	 	<i class="zmdi zmdi-landscape txt-warning"></i>
								 	 	<span class="block">{{$company->name}}</span>
								 	 	</a>
								 	 </li> 
								 	 @endforeach
								 @endif 		
									 
								@endif
								
								 
							</ul>
						</div>	
					</li>
					 
				</ul>
			</li>
			<li class="dropdown alert-drp">
				<a href="#" class="dropdown-toggle" onclick="fetchNotifications()"  data-toggle="dropdown"><i class="zmdi zmdi-notifications top-nav-icon"></i><span id="notification_counter" class="top-nav-icon-badge">{{ countNotificationCenter() }}</span></a>
				<ul  class="dropdown-menu alert-dropdown not-hide-alert-box" data-dropdown-in="bounceIn" data-dropdown-out="bounceOut">
					<li>
						<div class="notification-box-head-wrap">
							<span class="notification-box-head pull-left inline-block" id="notification_icon">notifications</span>
							<a class="txt-danger pull-right clear-notifications inline-block" href="javascript:void(0)" replace-value="top-nav-icon-badge" remove-class="bg-success" success-popup="true"  onclick="callAjax(this,'{{ route('notification.mark.all') }}')"> {{ countNotificationCenter()!==0?'Mark all as Read':'' }}  </a>
							<div class="clearfix"></div>
							<hr class="light-grey-hr ma-0"/>
						</div>
					</li>
					<li>
						<div class="streamline message-nicescroll-bar notifications endless-pagination" id="notification_list" onscroll="fetchNotifications()"  data-next-page="{{ route('notification.next.page') }}">
						 

						</div>
					</li>
					<li id="notification_wait" style="display: none"> 
					<img class="center-block" width="30px" src="{{ asset('img/loading.gif')}}" > 
					</li>
					<li>
						<div class="notification-box-bottom-wrap">
							<hr class="light-grey-hr ma-0"/>
							
							<a class="block text-center read-all" id="read-all_btn" href="{{ route('notification.show.notification') }}"> view all </a>
							<div class="clearfix"></div>
						</div>
					</li>
				</ul>
			</li>
			 
			<!-- <li class="dropdown alert-drp">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="zmdi zmdi-notifications top-nav-icon"></i><span class="top-nav-icon-badge">5</span></a>
				<ul  class="dropdown-menu alert-dropdown" data-dropdown-in="bounceIn" data-dropdown-out="bounceOut" style="overflow: inherit !important; max-height: fit-content !important;">
					<li>
						<div class="notification-box-head-wrap">
							<span class="notification-box-head pull-left inline-block">notifications</span>
							<a class="txt-danger pull-right clear-notifications inline-block" href="javascript:void(0)"> clear all </a>
							<div class="clearfix"></div>
							<hr class="light-grey-hr ma-0"/>
						</div>
					</li>
					<li>
						<div class="streamline message-nicescroll-bar" style="overflow-y: scroll;">
							<div class="sl-item">
								<a href="javascript:void(0)">
									<div class="icon bg-green">
										<i class="zmdi zmdi-flag"></i>
									</div>
									<div class="sl-content">
										<span class="inline-block capitalize-font  pull-left truncate head-notifications">
										New subscription created</span>
										<span class="inline-block font-11  pull-right notifications-time">2pm</span>
										<div class="clearfix"></div>
										<p class="truncate">Your customer subscribed for the basic plan. The customer will pay $25 per month.</p>
									</div>
								</a>	
							</div>
							<hr class="light-grey-hr ma-0"/>
							<div class="sl-item">
								<a href="javascript:void(0)">
									<div class="icon bg-yellow">
										<i class="zmdi zmdi-trending-down"></i>
									</div>
									<div class="sl-content">
										<span class="inline-block capitalize-font  pull-left truncate head-notifications txt-warning">Server #2 not responding</span>
										<span class="inline-block font-11 pull-right notifications-time">1pm</span>
										<div class="clearfix"></div>
										<p class="truncate">Some technical error occurred needs to be resolved.</p>
									</div>
								</a>	
							</div>
							<hr class="light-grey-hr ma-0"/>
							<div class="sl-item">
								<a href="javascript:void(0)">
									<div class="icon bg-blue">
										<i class="zmdi zmdi-email"></i>
									</div>
									<div class="sl-content">
										<span class="inline-block capitalize-font  pull-left truncate head-notifications">2 new messages</span>
										<span class="inline-block font-11  pull-right notifications-time">4pm</span>
										<div class="clearfix"></div>
										<p class="truncate"> The last payment for your G Suite Basic subscription failed.</p>
									</div>
								</a>	
							</div>
							<hr class="light-grey-hr ma-0"/>
							<div class="sl-item">
								<a href="javascript:void(0)">
									<div class="sl-avatar">
										<img class="img-responsive" src="{{ URL::asset('dist/img/avatar.jpg ') }}" alt="avatar"/>
									</div>
									<div class="sl-content">
										<span class="inline-block capitalize-font  pull-left truncate head-notifications">Sandy Doe</span>
										<span class="inline-block font-11  pull-right notifications-time">1pm</span>
										<div class="clearfix"></div>
										<p class="truncate">Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit</p>
									</div>
								</a>	
							</div>
							<hr class="light-grey-hr ma-0"/>
							<div class="sl-item">
								<a href="javascript:void(0)">
									<div class="icon bg-red">
										<i class="zmdi zmdi-storage"></i>
									</div>
									<div class="sl-content">
										<span class="inline-block capitalize-font  pull-left truncate head-notifications txt-danger">99% server space occupied.</span>
										<span class="inline-block font-11  pull-right notifications-time">1pm</span>
										<div class="clearfix"></div>
										<p class="truncate">consectetur, adipisci velit.</p>
									</div>
								</a>	
							</div>
						</div>
					</li>
					<li>
						<div class="notification-box-bottom-wrap">
							<hr class="light-grey-hr ma-0"/>
							<a class="block text-center read-all" href="javascript:void(0)"> read all </a>
							<div class="clearfix"></div>
						</div>
					</li>
				</ul>
			</li> -->
			<li class="dropdown auth-drp">
				<a href="#" class="dropdown-toggle pr-0" data-toggle="dropdown"><img src="{{ route('userpic.show') }}" alt="{{ Auth::user()->name }}" class="user-auth-img img-circle profilepicimg"/><span class="user-online-status"></span></a>
				<ul class="dropdown-menu user-auth-dropdown" data-dropdown-in="flipInX" data-dropdown-out="flipOutX">
					<li>
						<a href="{{ route('user.profile') }}"><i class="zmdi zmdi-account"></i><span>Profile</span></a>
					</li> 
					<li class="divider"></li>
					<!-- <li class="sub-menu show-on-hover">
						<a href="#" class="dropdown-toggle pr-0 level-2-drp"><i class="zmdi zmdi-check text-success"></i> available</a>
						<ul class="dropdown-menu open-left-side">
							<li>
								<a href="#"><i class="zmdi zmdi-check text-success"></i><span>available</span></a>
							</li>
							<li>
								<a href="#"><i class="zmdi zmdi-circle-o text-warning"></i><span>busy</span></a>
							</li>
							<li>
								<a href="#"><i class="zmdi zmdi-minus-circle-outline text-danger"></i><span>offline</span></a>
							</li>
						</ul>	
					</li>
					<li class="divider"></li> -->
					<li>
						<a href="{{ route('user.logout.get') }}"><i class="zmdi zmdi-power"></i><span>Log Out</span></a>
					</li>
				</ul>
			</li>
		</ul>
	</div>	
</nav>
<!-- /Top Menu Items -->


