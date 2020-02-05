@extends('panel::layouts.master')

@section('content')
    <h2>Messaging</h2>

    @role('admin')
    <ul class="nav nav- mb-4">
        <li class="nav-item {{ active(['panel.addons.messaging.index'])  }}">
            <a class="nav-link pl-0" href="{{route('panel.addons.messaging.index')}}">Latest messages</a>
        </li>
        <li class="nav-item {{ active(['panel.addons.messaging.settings.index'])  }}">
            <a class="nav-link pl-0 btn" href="{{route('panel.addons.messaging.settings.index')}}">Settings</a>
        </li>
    </ul>
    @endrole

    <div class="row mb-5 pb-5">

        <div class="col-sm-10">
            @include('alert::bootstrap')
            <br />
            {!! form($form)  !!}
        </div>
    </div>
@stop