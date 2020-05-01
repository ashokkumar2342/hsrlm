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
	<p>Please be informed that you have to appear in the upcoming hearing as per the following details:</p>
	<ul style="list-style-type:none;">
		 
		<li>Case No.: {{$case_no}}<li>
		<li>Case title  {{$title}}</li>	
		<li>Hearing On: {{date('d M,Y',strtotime($hearing_date))}}</li>	
		<li>Court: {{$court_detail}}</li>	
		<li>Coram Detail: {{$coram_detail}}</li>	
	 
	
	</ul> 
	<p>Plaese be present.</p>
	<p>Kindly login to Lawrbit Legal Matter Management Solution for more details.</p>  
	 
	@php $footer=$mail['footer']; @endphp
	 @includeIf('emails.footer', ['footer' => $footer])
</div>
		 
</body>
</html>