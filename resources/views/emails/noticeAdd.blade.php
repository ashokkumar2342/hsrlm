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
	<p>For Notice  titled {{$title}} against {{$party_name}}, {{$entity_name}} has added you as part of the legal team.</p>

	<p>The key details of the notice are:</p>

	<p>Notice Type: {{$notice_type}}</p>
	<p>Notice Date: {{date('d M,Y',strtotime($notice_date))}}</p>
	<p>Party Type: {{$party_type}}</p>
	<p>Location: {{$received_at}}</p>
	<p>Notice Owner: {{$owner}}</p>
	<p>Administration Team: {{$legal_team}}</p>

	<p>Kindly login to the Lawrbit Legal Matter Management Solution application for more details and necessary action.</p>


	 

	 
	@php $footer=$mail['footer']; @endphp
	 @includeIf('emails.footer', ['footer' => $footer])
</div>
		 
</body>
</html>