<!DOCTYPE html>
<html lang="en">
    <head>
        <?php 
        $domain=domainConfig();
        ?>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <title>User Login</title>
        
        
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
    <body>
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
                                        <img class="brand-img"  src={!! asset($domain->logo)!!} alt="brand Logo" style="padding-bottom: 30px;"  /> 
                                    </a>
                                </div>
                                <div class="auth-form  ml-auto mr-auto no-float">
                                    <div class="row well well-sm">
                                        <div class="col-sm-12 col-xs-12">

                                            <div class="mb-30">
                                                
                                              
                                            </div>
                                            @if (isset($errors) && count($errors))
                                            <div class="form-group">
                                                <div class="alert alert-danger alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                {{ $errors->first()}}
                                                </div>
                                            </div>
                                            @endif
                                            @if(Session::has('message'))
                                              <div class="form-group">
                                                <div class="alert alert-success alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times; </button>
                                                {{ Session::get('message')}}
                                                </div>
                                            </div>
                                            @endif 
                                            
                                            <div class="form-wrap">
                                                <form name="login_form" method="post" action="{{ route('user.login') }}">
                                                    @csrf
                                                    <div class="form-group">
                                                        <label class="control-label mb-10" for="exampleInputEmail_2">Email address</label>
                                                        <input type="email" class="form-control" required="" id="exampleInputEmail_2" placeholder="Enter email" name="email" data-validation="email"  data-validation-error-msg="Please enter the valid email" value="{{old('email')}}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="pull-left control-label mb-10" for="exampleInputpwd_2">Password</label>       
                                                        <div class="clearfix"></div>
                                                        <input type="password" class="form-control" required="" id="exampleInputpwd_2" name="password"  data-validation="required"  data-validation-error-msg="Please enter the password" placeholder="Password">
                                                    </div>
                                                    
                                                    
                                                    <div class="form-group text-center">
                                                        <button type="submit" class="btn btn-info btn-success btn-rounded">sign in</button>
                                                    </div>
                                                    <a data-toggle="modal" href=""  class="capitalize-font txt-primary block mb-10 pull-left font-12" data-target="#exampleModal">Forgot Password</a>
                                                   
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
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h5 class="modal-title" id="exampleModalLabel1">Forgot Password</h5>

                        </div>

                        <form name="forgot_password" id="forgot_password" action="{{route('user.password.email')}}" method="post" autocomplete="off"  >
                            @csrf
                            <div class="modal-body">                                
                                <div class="form-group">
                                    <label for="email" class="control-label mb-10">Email:</label>
                                    <input type="text" name="email" class="form-control" id="email">
                                </div>                          
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <input type="submit" name="Send" value="Send" class="btn btn-primary">
                                {{-- <input type="submit" name="submit" class="btn btn-success mr-10 mb-30" id="submit" value="Update"/> --}}
                            </div>
                        </form>
                    </div>
                </div>
            </div>  
            <!-- /Model close -->   
        </div>
        <!-- /#wrapper -->
   
        <!-- JavaScript -->
        
        <!-- jQuery -->
        <script src={!! asset('vendors/bower_components/jquery/dist/jquery.min.js')!!}></script>
        
        <!-- Bootstrap Core JavaScript -->
        <script src={!! asset('vendors/bower_components/bootstrap/dist/js/bootstrap.min.js')!!}></script>
        <script src={!! asset('vendors/bower_components/jasny-bootstrap/dist/js/jasny-bootstrap.min.js')!!}></script>
        
        <!-- Slimscroll JavaScript -->
        <script src={!! asset('dist/js/jquery.slimscroll.js')!!}></script>
        
        <!-- Init JavaScript -->
        <script src={!! asset('dist/js/init.js')!!}></script>
        <script type="text/javascript" src="{{asset('js/validation/core_validation.min.js')}}"></script>
        <script>$.validate();</script>
    </body>
</html>
