@extends('panel::layouts.master')


@section('content')

    <a href="{{ route('panel.addons.emaileditor.index', ['locale' => request('locale')]) }}" class="mb-1"><i class="fa fa-angle-left" aria-hidden="true"></i> back</a>

    <h2>Email template</h2>

    @include('alert::bootstrap')
    <br />
    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{app('laravellocalization')->getSupportedLocales()[$selected_lang]['name']}}
            </button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                    <a class="dropdown-item" href="?locale={{ $localeCode }}">{{ $properties['native'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
    <br />
    <br />
    {!! form($form) !!}
	
	
</div>

@stop
