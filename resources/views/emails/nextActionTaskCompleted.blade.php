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
	<p>Dear {{$owner}},</p>
	<p>Following {{$legal_name}} Task has been completed:</p>
	<ul style="list-style-type:none;">
		@if ($legal_name == 'Case' || $legal_name == 'Hearing')
		<li>Case No {{$ref_no}}<li>
		<li>Case titled  {{$title}}</li>	
		@else
		<li>Notice  {{$title}}</li>
		@endif
		<li> {{$legal_name}} Task Assigned Category {{$category}}</li>
		<li>Assigned to {{$name}}</li>
		<li>Target Completion Date {{date('d M,Y',strtotime($completion_date))}}</li>
	
	</ul> 
	<p>This is for your information and successive action.</p>  
	 
	@php $footer=$mail['footer']; @endphp
	 @includeIf('emails.footer', ['footer' => $footer])
</div>
		 
</body>
</html>