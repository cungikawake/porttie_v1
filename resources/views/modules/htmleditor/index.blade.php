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

    <div class="row mb-2">
        <div class="col-md-9">

            <div class="input-group">
                <label class="input-group-text" for="inputGroupSelect01">Select a file: </label>

                <div class="input-group-append">
                    <div class="btn-group dropright">
                    <button class="btn btn-info dropdown-toggle shadow-none  " type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{$selected_file}}
                    </button>
                    <div class="dropdown-menu  scrollable-menu" aria-labelledby="dropdownMenuButton">

                        @foreach($theme_files as $theme_file)
                            <a class="dropdown-item" href="/panel/addons/htmleditor?file={{$theme_file}}">{{$theme_file}}</a>
                        @endforeach
                    </div>
                </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <form action="/panel/addons/htmleditor?file={{$selected_file}}" method="POST">
                @csrf
                <input type="hidden" name="theme" value="{{$theme}}" style="display: none;" />
                <input type="hidden" name="file" value="{{$selected_file}}" style="display: none;" />
                <textarea name="editor" style="display: none;">{{$content}}</textarea>
                <button type="submit" class="btn btn-block btn-primary float-right">Save file</button>
            </form>
        </div>
    </div>

    <div id="editor" style="width: 100%; height: 90vh; display: none;">{{$content}}</div>
<style>
    .scrollable-menu {
        height: auto;
        max-height: 400px;
        overflow-x: hidden;
    }

    .btn-info:not(:disabled):not(.disabled):active:focus, .btn-info:not(:disabled):not(.disabled).active:focus, .show>.btn-info.dropdown-toggle:focus {
        /*box-shadow: none;*/
    }
</style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/ace.js" type="text/javascript" charset="utf-8"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.3.3/mode-twig.js" type="text/javascript" charset="utf-8"></script>
    <script>
        $('#editor').show();
        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/twig");
        editor.setOptions({
            fontFamily: "Courier New",
            fontSize: "14px"
        });
        editor.getSession().setUseWrapMode(true);


        var textarea = $('textarea[name="editor"]');
        editor.getSession().on("change", function () {
            textarea.val(editor.getSession().getValue());
        });
    </script>
	
		  <a class="btn btn-link text-danger pl-0 pb-0" href="#collapseDelete" data-toggle="collapse" data-target="#collapseDelete" aria-expanded="false" aria-controls="collapseDelete">
    Reset to original file
  </a>
	<div class="collapse" id="collapseDelete">
	
  <div class="card card-body">
	You will loose your HTML changes. This action is non-recoverable.<br /><br />
	
	<div class="row"> 
	<div class="col-sm-6">
	  
  <form method="post" action="/panel/addons/htmleditor/1">
    {{ method_field('DELETE') }}
    {!! csrf_field() !!}
	<input type="hidden" value="{{$selected_file}}" name="file" />
	<button type="submit" class="btn btn-danger btn-block">Yes - reset {{$selected_file}}</button>
	</form>
	</div>
	<div class="col-sm-6">
  <button class="btn btn-success btn-block" type="button" data-toggle="collapse" data-target="#collapseDelete" aria-expanded="false" aria-controls="collapseDelete">
    Cancel
  </button>
  </div>
  </div>
  </div>
  
@stop
