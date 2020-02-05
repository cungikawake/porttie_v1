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


    <div class="row mb-1">
        <div class="col-md-9 mb-0">
            <div class="btn-group" role="group" aria-label="Basic example">
                <a href="{{ app('request')->fullUrlWithQuery(['file' => 'theme']) }}" class="btn btn-secondary {{ ($selected_file == 'theme')?'active':'' }}">Theme</a>
                <a href="{{ app('request')->fullUrlWithQuery(['file' => 'variables']) }}" class="btn btn-secondary {{ ($selected_file == 'variables')?'active':'' }}">Variables</a>
                <a href="{{ app('request')->fullUrlWithQuery(['file' => 'swatch']) }}" class="btn btn-secondary {{ ($selected_file == 'swatch')?'active':'' }}">Swatch</a>
            </div>

            <div class="btn-group dropright" style="display: none">
                <button type="button" class="btn btn-info btn-sm dropdown-toggle shadow-none " data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Presets
                </button>
                <div class="dropdown-menu">
                    @foreach($swatches as $swatch)
                        <a class="dropdown-item confirmation" href="/panel/addons/csseditor?swatch={{strtolower($swatch->name)}}">{{$swatch->name}} - {{$swatch->description}}</a>
                    @endforeach
                </div>
            </div>

        </div>
        <div class="col-md-3 mb-0">

            <form action="/panel/addons/csseditor" method="POST">
                @csrf
                <input type="hidden" name="file" value="{{$selected_file}}" style="display: none;" />
                <input type="hidden" name="theme" value="{{$theme}}" style="display: none;" />
                <textarea name="editor" style="display: none;">{{$content}}</textarea>
                <button type="submit" class="btn btn-block btn-primary float-right">Save file</button>
            </form>

        </div>
    </div>

    <div id="editor" style="width: 100%; height: 90vh; display: none;">{{$content}}</div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/mode-scss.js" type="text/javascript" charset="utf-8"></script>
    <script>
        $('#editor').show();
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/scss");
        editor.setOptions({
            fontFamily: "Courier New",
            fontSize: "14px"
			});
        editor.getSession().setUseWrapMode(true);


        var textarea = $('textarea[name="editor"]');
        editor.getSession().on("change", function () {
            textarea.val(editor.getSession().getValue());
        });

        $('.confirmation').on('click', function () {
            return confirm('This will overwrite your existing styles - Are you sure you want to continue?');
        });
    </script>
	<br />
	<br />
	
	  <a class="btn btn-link text-danger pl-0 pb-0" href="#collapseDelete" data-toggle="collapse" data-target="#collapseDelete" aria-expanded="false" aria-controls="collapseDelete">
    Reset to default CSS
  </a>
	<div class="collapse" id="collapseDelete">
	
  <div class="card card-body">
	You will loose your CSS changes. This action is non-recoverable.<br /><br />
	
	<div class="row"> 
	<div class="col-sm-6">
	  
  <form method="post" action="/panel/addons/csseditor/1">
    {{ method_field('DELETE') }}
    {!! csrf_field() !!}
	<button type="submit" class="btn btn-danger btn-block">Yes - reset CSS</button>
	</form>
	</div>
	<div class="col-sm-6">
  <button class="btn btn-success btn-block" type="button" data-toggle="collapse" data-target="#collapseDelete" aria-expanded="false" aria-controls="collapseDelete">
    Cancel
  </button>
  </div>
  </div>
  </div>
  
  	<br />
	<br />
	
	
</div>

@stop
