	<?php //print_r(getUserData()); exit(); ?>
<!DOCTYPE html>
<html lang="en">

	@include('layouts.head')
	
	<body id="body_id">
		<!--Preloader-->
		<div class="preloader-it">
			<div class="la-anim-1"></div>
		</div>
		<!--/Preloader-->
		<!--  main wrapper -->
		<div class="wrapper theme-1-active box-layout pimary-color-green">

		<!-- Header starts -->
		@include('layouts.header')
		<!-- Header ends -->

		<!-- Navbar starts -->
		@include('layouts.nav')
		
		<!-- Navbar ends -->
		
		<!-- Main Content -->
		<div class="page-wrapper">
		
			@yield('content')
			<!-- Footer Start -->
			@include('layouts.footer')
			<!-- Footer end -->
		</div>
		<!-- Main Content End -->
		</div>
		<!-- /# main wrapper -->
		@include('layouts.modal')

		<!-- JavaScript -->
		
		<!-- jQuery -->
		<script src={!! asset('vendors/bower_components/jquery/dist/jquery.min.js')!!}></script>
		<script src={!! asset('js/upload/jquery.form.js?ver=1') !!}></script>
		
		<!-- Bootstrap Core JavaScript -->
		<script src={!! asset('vendors/bower_components/bootstrap/dist/js/bootstrap.min.js')!!}></script>
		
		<!-- Slimscroll JavaScript -->
		<script src={!! asset('dist/js/jquery.slimscroll.js')!!}></script>
	
		<!-- Fancy Dropdown JS -->
		<script src={!! asset('dist/js/dropdown-bootstrap-extended.js')!!}></script>
		
		<!-- Owl JavaScript -->
		<script src={!! asset('vendors/bower_components/owl.carousel/dist/owl.carousel.min.js')!!}></script>
	
		<!-- Switchery JavaScript -->
		<script src={!! asset('vendors/bower_components/switchery/dist/switchery.min.js')!!}></script>
		<script src={!! asset('vendors/bower_components/datatables/media/js/jquery.dataTables.min.js')!!}></script>
		<script src={!! asset('vendors/bower_components/datatables/media/js/dataTables.colReorder.min.js')!!}></script>

	
		<!-- Init JavaScript -->
		<script src={!! asset('dist/js/init.js')!!}></script>
		<script src={!! asset('js/validation/common.js?ver=') !!}{{date('Y-m-d')}}></script>
		<script src={!! asset('js/customscript.js?ver=') !!}{{date('Y-m-d')}}></script>
		<script src={!! asset('js/upload/jquery.uploadfile.min.js?ver=1') !!}></script>
		<script src={!! asset('js/upload/jquery.form.js?ver=1') !!}></script>
		<script src={!! asset('vendors/bower_components/bootstrap-select/dist/js/bootstrap-select.min.js')!!}></script>
		<script src={!! asset('vendors/bower_components/select2/dist/js/select2.full.min.js') !!}></script> 
		<script src={!! asset('vendors/bower_components/jquery-toast-plugin/dist/jquery.toast.min.js') !!}></script>
		<script src="{{asset('vendors/bower_components/summernote/dist/summernote.min.js')}}"></script> 


		<script src={!! asset('vendors/bower_components/datatables.net-buttons/js/dataTables.buttons.min.js') !!}></script>
	<script src={!! asset('vendors/bower_components/datatables.net-buttons/js/buttons.flash.min.js') !!}></script>
	<script src={!! asset('vendors/bower_components/jszip/dist/jszip.min.js') !!}></script>
	<script src={!! asset('vendors/bower_components/pdfmake/build/pdfmake.min.js') !!}></script>
	<script src={!! asset('vendors/bower_components/pdfmake/build/vfs_fonts.js') !!}></script>
	
	<script src={!! asset('vendors/bower_components/datatables.net-buttons/js/buttons.html5.min.js') !!}></script>
	<script src={!! asset('vendors/bower_components/datatables.net-buttons/js/buttons.print.min.js') !!}></script>
	<script src={!! asset('dist/js/export-table-data.js') !!}></script>
	<script src={!! asset('vendors/chart.js/Chart.min.js') !!}></script>
		
<script>
$(document).ready(function(){
    $('.multiselect').selectpicker(); 
    $('.select2').select2({
    	 width: 'resolve' 
    });
});

</script>
<script type="text/javascript"> 
	var table = $('#example').DataTable({
       colReorder: true,
    });
    // $('.modal').on("hide.bs.modal", function () {  
    // 		setTimeout(function(){ 
    // 		if($('.modal.in').length){ 
    // 			$("body").css('overflow','hidden');			 
    // 		}
    // 		else{ 
    // 			$("body").css('overflow','auto');
    // 		} 
    // 		}, 100);	 
    // });
    // $('.modal').on("show.bs.modal", function () {  
    // 	$("body").css('overflow','hidden'); 
    // });

    $('.modal').on("hidden.bs.modal", function (e) {
	    if($('.modal:visible').length)
	    {
	        $('.modal-backdrop').first().css('z-index', parseInt($('.modal:visible').last().css('z-index')) - 10);
	        $('body').addClass('modal-open');
	    }
	}).on("show.bs.modal", function (e) {
	    if($('.modal:visible').length)
	    {
	        $('.modal-backdrop.in').first().css('z-index', parseInt($('.modal:visible').last().css('z-index')) + 10);
	        $(this).css('z-index', parseInt($('.modal-backdrop.in').first().css('z-index')) + 10);
	    }
	});
</script>
@yield('externalScript')
@stack('scripts')
	</body>
</html>