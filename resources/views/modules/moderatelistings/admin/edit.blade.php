@extends('panel::layouts.master')

@section('content')
    <a href="{{ route('panel.addons.moderatelistings.index', ['locale' => request('locale')]) }}" class="mb-1"><i class="fa fa-angle-left" aria-hidden="true"></i> back</a>

    <div class="row mb-3">
        <div class="col-sm-8">
            <h2  class="mt-xxs">Reviewing report</h2>
            <small>Reported by: {{$report->user->display_name}} @if($report->listing)<a href="{{$report->listing->url}}" target="_blank">(view)</a>@endif</small>
        </div>
        <div class="col-sm-4">

        </div>

    </div>

    <div class="row">

        <div class="col-sm-12">

          <div class="panel panel-default">
              <div class="panel-body">


                  {!! form_start($form) !!}
                  {!! form_end($form) !!}
              </div>
            </div>
    </div>


@endsection
