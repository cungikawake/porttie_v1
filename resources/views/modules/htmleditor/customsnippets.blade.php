@extends('panel::layouts.master')


@section('content')

    <h2>Custom Snippets</h2>
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

	
<form action="/panel/addons/customsnippets" class="mb-5 pb-5" method="POST">
	@csrf
	
	<div class="form-group">
		<label for="comment">Header:</label>
		<textarea class="form-control" rows="5" name="snippet_header">{{setting('snippet_header')}}</textarea>
	</div>

	<div class="form-group">
		<label for="comment">Footer:</label>
		<textarea class="form-control" rows="5" name="snippet_footer">{{setting('snippet_footer')}}</textarea>
	</div>

	<button type="submit" class="btn btn-primary float-left">Save snippets</button>
	
</form>
@stop
