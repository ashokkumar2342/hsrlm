<!DOCTYPE html>
<html>
<head>
	<title>User Activation</title>
	 <style type="text/css">
	body{
		padding:20px;
	}
		p{
			font-size: 11px;
		}
		li{
			font-size: 11px;
		}
		ul {
	    	padding-left:15px;margin-top:-10px; 
		}
		#logo{ 
			padding-bottom: 40px;
		}
		#main-div{
			/*border: solid 1px #000;*/
			padding:20px;
			background-color: rgb(240,240,240);
			font-family: Tahoma;

		}

</style>
</head>

<body> 
<div id="main-div">
	<div id="logo"> 
	<img src="{{$mail['logo']}}" height="50px" align="right" width="auto" alt="logo">
</div>
	</br>
	<p>Dear {{$name}},</p>
	<p>Congratulations, you have been added as user in {{$mail['keyword']}} Litigation Management Tool <strong></strong>.</p>
	<p>To finish your account activation, click on following link, use OTP and set up your password.</br>
	  <a href="{{$link}}">{{$link}}</a></p>
	<p>OTP: <strong>{{$otp}}</strong></p>
	<p>Notes:</p>
	<ul>
	<li>Above link expires in 24 hours.</li>
	<li>If you can't click on the link, copy and paste it into your Web browser.</li>
	<li>If you have any questions, contact your {{$mail['keyword']}} administrator.</li>
	</ul>
 
	@php $footer=$mail['footer']; @endphp
	 @includeIf('emails.footer', ['footer' => $footer])
</div>
		 
</body>
</html>