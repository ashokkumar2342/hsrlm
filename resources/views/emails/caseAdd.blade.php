<!DOCTYPE html>
<html>
<head>
	<title></title>
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
	<p>In the Company {{$entity_name}}, the legal personnel {{$owner}} recently added a new case ID {{$case_no}} (assigned by the Court) filed at {{$court_name}}. </p>

	<p>You would appear as a {{$appearing_model_as}} {{$appearing_field}}. The current status of the case is {{$status}}.</p>

	<p>Please login to Lawrbit Legal Matter Management Solution for more details.</p> 

	@php $footer=$mail['footer']; @endphp
	 @includeIf('emails.footer', ['footer' => $footer])
</div>
		 
</body>
</html>