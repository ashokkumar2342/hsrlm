<head>
	@php 
	$domain=domainConfig();
	@endphp
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<title>Dashboard</title>
	
	<meta name="author" content="hencework"/>
	<!-- Favicon -->
	<link rel="shortcut icon" href="favicon.ico">
	<link rel="icon" href="{{ URL::asset($domain->fav) }}" type="image/x-icon">
	<link href={!! asset('vendors/bower_components/datatables/media/css/jquery.dataTables.min.css') !!} rel="stylesheet" type="text/css">
	<!-- Custom CSS -->
	<link href={!! asset('dist/css/style.css') !!} rel="stylesheet" type="text/css">
	<link href={!! asset('vendors/bower_components/bootstrap-select/dist/css/bootstrap-select.min.css') !!} rel="stylesheet" type="text/css">
	<link href={!! asset('vendors/bower_components/jquery-toast-plugin/dist/jquery.toast.min.css') !!} rel="stylesheet" type="text/css">
	<link href={!! asset('vendors/bower_components/select2/dist/css/select2.min.css') !!} rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.11/css/all.css" integrity="sha384-p2jx59pefphTFIpeqCcISO9MdVfIm4pNnsL08A6v5vaQc4owkQqxMV8kg4Yvhaw/" crossorigin="anonymous">
	<link href={!! asset('dist/css/upload/uploadfile.css') !!} rel="stylesheet" type="text/css">

<style>
.modal-body {
    overflow: auto;
    max-height: calc(100vh - 150px) !important;
}
.multisizesmall
{
	font-size:x-small;
	height:30px;
}
td.hide-show {
    background: url({!! asset('img/details_open.png') !!}) no-repeat center center;
}
tr.shown td.hide-show {
    background: url({!! asset('img/details_close.png') !!}) no-repeat center center;
}

.loader {
    background: url({!! asset('img/loader.gif') !!});
}
   
.fa-edit{
	cursor: pointer; 
	color: #2ecd99d9;
	font-size: 20px;
}
.fa-file{
	cursor: pointer; 
	font-size: 20px;
}
.fa-refresh{
	cursor: pointer; 
	font-size: 20px;
}
.fa-envelope{
	cursor: pointer; 
	color: #2e8bcdd9;
	font-size: 20px;	
}
.fa-eye{
	cursor: pointer; 
	color: #2ecd99d9;
	font-size: 20px;
}
.fa-trash{
	cursor: pointer; 
	color: #2ecd99d9;
	font-size: 20px;
}.fa-download{
	cursor: pointer; 
	color: #2ecd99d9;
	font-size: 20px;
}
.fa-upload {
    cursor: pointer;
    color: #5bc0de;
    font-size: 20px;
}

.fa-check{
	cursor: pointer; 
	color: #2ecd99d9;
	font-size: 20px;
}
.fa-close{
	cursor: pointer; 
	color: red;
	font-size: 20px;
}.fa-save{
	cursor: pointer; 
	color: red;
	font-size: 20px;
}

.fa-ban{
	cursor: pointer; 
	font-size: 20px;
}
.fa-history{
	cursor: pointer; 
	float: right;
}
.attachment{
	height: 30px !important;
	padding: 5px 25px !important;
}
.cursor{
	cursor: pointer; 
	color:#2d4e94;
	text-decoration: underline;
}
.cursor:hover{
	cursor: pointer; 
	color:#2d4e94;
	text-decoration: underline;
}
.text-blue{
	color:#2d4e94;
}
.fa-share-alt{
	cursor: pointer;  
	font-size: 20px;
}
.fa-link{
	cursor: pointer;  
	font-size: 20px;
}

td.details-control {
    background: url({!! asset('img/details_open.png') !!}) no-repeat center center !important;
    cursor: pointer;
    

}
tr.shown td.details-control {

    background: url({!! asset('img/details_close.png') !!}) no-repeat center center !important;
}
 
.plusImg{
	background: url({!! asset('img/details_open.png') !!}) no-repeat center center !important;
    cursor: pointer;
}
.subImg{
	 background: url({!! asset('img/details_close.png') !!}) no-repeat center center !important;
}

table{
	font-size: 85%;
}

body{
	font-size:85% !important;
	overflow-x: hidden !important;
}

.daterangepicker th {
    background: #fff !important;
    border-radius: 0px !important;
}

.bs-placeholder,.form-control{
	font-size: 13px !important;
}

.ptl_ptr_0{
	padding-left: 5px !important;
	padding-right: 5px !important;
}

.ptl_0{
	padding-left: 5px !important;
}
.ptr_0{
	padding-right: 5px !important;
}
.pad_all{
	padding: 0px !important;
	font-size: 15px !important;
    border-bottom: 1px solid transparent !important; 
}
.accordion-struct.panel-group .panel .panel-heading{
	padding: 6px 12px !important;
    border-top-left-radius: 3px !important; 
    border-top-right-radius: 3px !important;
    text-transform: uppercase;
    letter-spacing: 1.1px;
    font-size: 13px;
    font-weight: 600;
    border-top-left-radius: 0px !important;
    border-top-right-radius: 0px !important;
}

.accordion-struct.panel-group .panel .panel-heading a{
	text-transform: uppercase !important;
	padding: 5px 15px !important;
	letter-spacing: 1.1px;
}

.pdb_0{
	padding-bottom: 0px !important;
}

.mrb_0{
	margin-bottom: 0px !important;
}

.pdt_0{
	padding-top: 0px !important;
}

.custom_panel_border{
	border: 1px dashed;
    border-color: rgb(204, 204, 204);
    border-top: none;
}

.accordion-struct.panel-group .panel{
	margin-top: 5px; 
}

.panel_heading_custom{
    background-color: #d6d6d6 !important;
}

h6, .panel-title {
    text-transform: inherit !important;
}
.cardcurvy
{
	border-radius: 0px;
    box-shadow: 0 5px 15px #9E9E9E;
}
.cardcurvy:hover
{
    box-shadow: 0 5px 15px #191818;
}

div.modal-dialog div.modal-content:hover {
    box-shadow: 0 5px 15px #9E9E9E;
}

.font_size{
	font-size: 100% !important;
}

.bs-placeholder, .form-control {
    font-size: 14px !important;
}

.change_style{
	background-color: #fff !important;
	border: none !important; 
}
.select2-dropdown {
  z-index: 9001 !important;
}
.select2-container .select2-selection--single{
	height: 30px !important;
}
.bootstrap-select .disabled{
	background-color: #eee !important; 
}
</style>
	@stack('links')
	@yield('externalCss') 
</head>