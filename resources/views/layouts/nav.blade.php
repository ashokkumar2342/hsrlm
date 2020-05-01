 <!-- Left Sidebar Menu -->
<div class="fixed-sidebar-left">
	@php $menu = getMenu(); @endphp
	@if(count($menu)>0)
 	<ul class="nav navbar-nav side-nav nicescroll-bar" style="font-size:12px;">
		@foreach($menu as $va=>$key)
		@if(isset($key["subMenu"]) && count($key["subMenu"])>0)
 		<li>
 			<a href="javascript:void(0);" class="{{ getActiveMenuClass($key['url']) }}" data-toggle="collapse" data-target="#menu_{{$va}}">
 				<div class="pull-left">
 					<i class="{{ $key['class']}}"></i>
					<span class="right-nav-text">{{ $key['title'] }}</span>
 				</div>
 				<div class="pull-right">
					<i class="zmdi zmdi-caret-down"></i>
				</div>
				<div class="clearfix"></div>
 			</a>
 			<ul id="menu_{{$va}}" class="{{ getCollapseIn($key['url']) }} collapse collapse-level-1">
 				@foreach($key["subMenu"] as $vaSub=>$keySub)
 					<li>
						<a class="{{ getSubMenuActive($keySub['url'])}}" href="{{ url($keySub['url'])}}">{{$keySub['title']}}</a>
					</li>
				@endforeach
			</ul>
 		</li>
 		@else
 		<li>
			<a href="{{ url($key['url']) }}" class="{{ getActiveMenuClass($key['url']) }}">
				<div class="pull-left">
					<i class="{{ $key['class'] }}"></i>
					<span class="right-nav-text">{{ $key['title'] }}</span>
				</div>
				<div class="clearfix"></div>
			</a>
		</li>
		@endif
		@endforeach
 	</ul>
 	@endif
</div>


 <!-- /Left Sidebar Menu -->