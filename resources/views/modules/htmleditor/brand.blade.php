@extends('panel::layouts.master')


@section('content')

    <h2>HTML &amp; CSS Editor</h2>
    @include('alert::bootstrap')
    <ul class="nav nav- mb-4">
        <li class="nav-item {{ active(['panel.addons.htmleditor.index'])  }}">
            <a class="nav-link pl-0" href="{{route('panel.addons.htmleditor.index')}}">HTML Editor</a>
        </li>
        <li class="nav-item {{ active(['panel.addons.csseditor.index'])  }}">
            <a class="nav-link pl-0" href="{{route('panel.addons.csseditor.index')}}">CSS Editor</a>
        </li>        
		<li class="nav-item {{ active(['panel.addons.brandeditor.index'])  }}">
            <a class="nav-link pl-0" href="{{route('panel.addons.brandeditor.index')}}">Brand Editor</a>
        </li>
		<li class="nav-item {{ active(['panel.addons.customsnippets.index'])  }}">
            <a class="nav-link pl-0" href="{{route('panel.addons.customsnippets.index')}}">Custom Snippets</a>
        </li>
    </ul>
	
	<script src="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.0.3/dist/js/bootstrap-colorpicker.min.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-colorpicker@3.0.3/dist/css/bootstrap-colorpicker.min.css">
	<script src="https://cdn.jsdelivr.net/npm/select2@4.0.6-rc.1/dist/js/select2.min.js"></script>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.6-rc.1/dist/css/select2.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.css">
	
            <form action="/panel/addons/brandeditor" class="mb-5 pb-5" method="POST">
                @csrf
				
	<h4>Fonts</h4>
	<table class="table table-sm table-borderless">
  <tbody>
  @foreach($fonts as $font_style => $font_title)
    <tr>
      <th scope="row" style="width: 120px"><label class="col-form-label  text-truncate">
	  {{ucfirst($font_title)}}
        </label></th>
		<td>
		{{ Form::select($font_style, $fonts_list, setting('style.'.$font_style, @$brand[$font_style]), ['class' => 'form-control font-selector']) }}
		</td>
    </tr>
	@endforeach
	
	<tr>
      <th scope="row"><label class="col-form-label  text-truncate">Base Font Size</label></th>
      <td>
		  <div class="input-group mb-3">
  <input type="number" class="form-control" name="font-size-base" value="{{setting('style.font-size-base', @$brand[$color])}}" placeholder="{{@$brand[$color]}}">
  <div class="input-group-append">
    <span class="input-group-text" id="basic-addon2">px</span>
  </div>
</div>
		  
		  </td>
    </tr>
		
  </tbody>
</table>
	
	<h4>Colors</h4>
	<table class="table table-sm table-borderless">
  <tbody>
  @foreach($colors as $color => $color_name)
    <tr>
      <th scope="row" style="width: 120px"><label class="col-form-label  text-truncate">
	  {{ucfirst($color_name)}} 
        </label></th>
      <td>
	  
		  <div class="colorpicker-mk input-group" title="Using format option">
		    <span class="input-group-append">
    <span class=" input-group-text colorpicker-input-addon"><i class="shadow"></i></span>
  </span>
	  <input type="text" class="form-control" name="{{$color}}" value="{{setting('style.'.$color, @$brand[$color])}}" placeholder="{{@$brand[$color]}}">

</div>
		  
		  </td>
    </tr>
	@endforeach
  </tbody>
</table>	

				
	<!--<h4>Options</h4>
	<table class="table table-sm table-borderless">
	  <tbody>
		<tr>
			<th scope="row" style="width: 120px"><label class="col-form-label  text-truncate">Enable Rounded</label></th>
			<td>
			{{ Form::checkbox('enable-rounded', 'true',  setting('style.enable-rounded') == 'true'?1:0, ['class' => 'form-control ']) }}
			</td>
		</tr>
	  </tbody>
	</table>-->

	<button type="submit" class="btn btn-primary float-left">Save styles</button>

	
</form>

<style>
	img {  
  position: relative;
}

/* style this to fit your needs */
/* and remove [alt] to apply to all images*/
img[alt]:after {  
  display: block;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: #fff;
  font-family: 'Helvetica';
  font-weight: 300;
  line-height: 2;  
  text-align: center;
  content: attr(alt);
}
</style>
<script>
	
	function formatState (state) {
		if (!state.id) {
			return state.text;
		}
		var baseUrl = "https://marketplace-kit.s3.amazonaws.com/fonts/images";
		var $state = $(
		'<span class="font-img"><img src="' + baseUrl + '/' + state.element.value + '.png" alt="'+state.text+'" /></span>'
		);
		return $state;
	};

  $(function () {
    $('.colorpicker-mk').colorpicker({
      format: 'hex'
    });

	$( ".font-selector" ).select2({
		theme: "bootstrap4",
		allowClear: true,
		tags: true,
		templateResult: formatState
	});
  });
</script>	

@stop
