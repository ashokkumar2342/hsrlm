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
	<p>{{$legal_name}} {{$owner}}  has assigned {{$category}} task to you under {{$legal_name=='Notice'?'':'Case '}}  
			@if ($legal_name == 'Case' || $legal_name == 'Hearing')
			 No {{$ref_no}}, Filed at {{$court_name}}
			@endif 

		 titled {{$title}}. <p>

	<p>It must be completed by {{date('d M,Y',strtotime($target_date))}}</p>
	<p>Please expedite</p> 

	 
	@php $footer=$mail['footer']; @endphp
	 @includeIf('emails.footer', ['footer' => $footer])
</div>
		 
</body>
</html>