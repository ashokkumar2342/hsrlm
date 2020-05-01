<!DOCTYPE html>
<html lang="en">
	<head>
		@php 
        $domain=domainConfig();
        @endphp
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<title>Dashboard</title>
		
		
		<!-- Favicon -->
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="icon" href="{{URL::asset($domain->fav)}}" type="image/x-icon">
		
		<!-- vector map CSS -->
		<link href={!! asset('vendors/bower_components/jasny-bootstrap/dist/css/jasny-bootstrap.min.css')!!} rel="stylesheet" type="text/css"/>
		
		
		
		<!-- Custom CSS -->
		<link href={!! asset('dist/css/style.css')!!} rel="stylesheet" type="text/css">
         <style type="text/css">
        html, body{height:100%;} 
            #outer{
            min-height:100%;
            }
            .intro {
                min-height: 100vh;
                background-image: url({{ asset($domain->image) }});
                background-size: cover;
                object-fit: cover;
                background-repeat: no-repeat;
                background-position: center;
                display: flex; /* NEW */
            }
            .well {
                min-height: 20px;

                padding: 20px;
                margin-bottom: 20px;
                background-color: #fff;
                border: 1px solid #e3e3e3;
                border-radius: 4px;
                -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
                box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
                
            }
            .auth-form {
            width: 350px
               
            }
            .sp-logo-wrap{
                padding-top: 50px;
                padding-right: 20px;
            }
            .control-label{
                
                font-size: 12px;
                color: #54698d;
                font-family: SFS, Arial, sans-serif;
                margin: 0 0 8px 0;
                line-height: inherit;
            }
            
        </style>
	</head>
    <body id="body_id">
        <!--Preloader-->
        <div class="preloader-it">
            <div class="la-anim-1"></div>
        </div>
        <!--/Preloader-->
        
        <div class="wrapper pa-0">
            
            
            <!-- Main Content -->
            <div class="page-wrapper pa-0 ma-0 auth-page"  style="background-color: #f4f6f9">
                <div class="container-fluid pa-0 ma-0">
                    <!-- Row -->
                    <div class="col-lg-6">
                      
                            
                     
                        <div class="table-struct full-width full-height">

                            <div class="table-cell vertical-align-middle auth-form-wrap">
                                <div class="text-center">
                                    <a href={!! url('/')!!}>
                                        <img class="brand-img"  src={!! asset($domain->logo)!!} alt="brand" style="padding-bottom: 30px;"  /> 
                                    </a>
                                </div>
                                <div class="auth-form  ml-auto mr-auto no-float">
                                    <div class="row well well-sm">

                                        <div class="col-sm-12 col-xs-12">
                                        	 
                                            
                                           
                                            
                                            <div class="form-wrap">
                                           <form name="usser_change_password" id="usser_change_password" action="{{ route('user.storepassword') }}" method="post" autocomplete="off" class="add_form" no-reset="true" redirect-to="{{url('/login')}}">
												{{ csrf_field()}}
												<div class="form-body overflow-hide"> 
													<div class="form-group">
														<label class="control-label mb-10" for="exampleInputpwd_01">OTP</label>
														<div class="input-group">
															<div class="input-group-addon"><i class="icon-lock"></i></div>
															<input type="password" name="otp" class="form-control" id="otp" placeholder="Enter otp" pattern="[0-9]{6}" title="Must contain  6 digit" required>
															<input type="hidden" value="{{$token}}" name="token" id="token">
														</div>
													</div>
													<div class="form-group">
														<label class="control-label mb-10" for="exampleInputpwd_01">New Password</label>
														<div class="input-group">
															<div class="input-group-addon"><i class="icon-lock"></i></div>
															<input type="password" name="password" class="form-control" id="password" placeholder="Enter new password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 6 or more characters" required>
														</div>
													</div> 
													<div class="form-group">
														<label class="control-label mb-10" for="exampleInputpwd_01">Confirm password</label>
														<div class="input-group">
															<div class="input-group-addon"><i class="icon-lock"></i></div>
															<input type="password" name="passwordconfirmation" class="form-control" id="passwordconfirmation" placeholder="Confirm Password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" title="Must contain at least one number and one uppercase and lowercase letter, and at least 6 or more characters" oninput="check(this)" required>
														</div>
													</div>
												</div>
												<div class="form-actions mt-10">			
													<button type="submit" class="btn btn-success mr-10 mb-30">Create Password</button>
												</div>				
											</form>
                                            </div>
                                        </div>  
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Footer -->
                        <footer class="footer container-fluid text-center pl-30 pr-30">
                            <div class="row">
                                <div class="col-sm-12">
                                    <p>&copy; {{date("Y")}} {{$domain->right_reserve}}. All rights reserved.</p>
                                </div>
                            </div>
                        </footer>
                        <!-- /Footer -->
                    </div>
                    <div class="col-lg-6 hidden-xs pa-0 ma-0 intro">                      
                        
                        
                            <!-- START carousel-->
                           {{--  <div id="carousel-example-captions-1" data-ride="carousel" class="carousel slide">
                                <ol class="carousel-indicators">
                                   <li data-target="#carousel-example-captions-1" data-slide-to="0" class="active"></li>
                                   <li data-target="#carousel-example-captions-1" data-slide-to="1"></li>
                                   <li data-target="#carousel-example-captions-1" data-slide-to="2"></li>
                                </ol>
                                <div role="listbox" class="carousel-inner">
                                   <div class="item active"> <img src="{{ asset('img/MM_Brochure_Background.jpg') }}" alt="First slide image"> </div>
                                   <div class="item"> <img src="{{ asset('img/MM_Brochure_Background1.jpg') }}" alt="Second slide image"> </div>
                                   <div class="item"> <img src="{{ asset('img/MM_Brochure_Background2.jpg') }}" alt="Third slide image"> </div>
                                </div>
                            </div> --}}
                            <!-- END carousel-->
                     
                    
                    <!-- /Row -->   
                </div>
                
            </div>
            <!-- /Main Content -->
            <!-- /Model Forget Password -->
             
            <!-- /Model close -->   
        </div>
        <!-- /#wrapper -->
   
        <!-- JavaScript -->
        
        <!-- jQuery -->
        <script src={!! asset('vendors/bower_components/jquery/dist/jquery.min.js')!!}></script>
        
        <!-- Bootstrap Core JavaScript -->
        <script src={!! asset('vendors/bower_components/bootstrap/dist/js/bootstrap.min.js')!!}></script>
        <script src={!! asset('vendors/bower_components/jasny-bootstrap/dist/js/jasny-bootstrap.min.js')!!}></script>
        <script src={!! asset('vendors/bower_components/jquery-toast-plugin/dist/jquery.toast.min.js') !!}></script> 
        
        <!-- Slimscroll JavaScript -->
        <script src={!! asset('dist/js/jquery.slimscroll.js')!!}></script>
        
        <!-- Init JavaScript -->
        <script src={!! asset('dist/js/init.js')!!}></script>
        <script type="text/javascript" src="{{asset('js/validation/core_validation.min.js')}}"></script>
        <script src={!! asset('js/validation/common.js?ver=1') !!}></script>
        <script src={!! asset('js/customscript.js?ver=1') !!}></script>
        <script>$.validate();</script>
    </body>
</html>
