@extends('panel::layouts.master')


@section('content')
    <a href="{{ route('panel.addons.messaging.index') }}" class="mb-1"><i class="fa fa-angle-left" aria-hidden="true"></i> back</a>

    <h2>Conversation #{{$id}}</h2>
    @include('alert::bootstrap')
    @role('admin')
    <ul class="nav nav- mb-4">
        <li class="nav-item {{ active(['panel.addons.messaging.index'])  }}">
            <a class="nav-link pl-0" href="{{route('panel.addons.messaging.index')}}">Latest messages</a>
        </li>
        <li class="nav-item {{ active(['panel.addons.messaging.settings.index'])  }}">
            <a class="nav-link pl-0 btn disabled" href="{{route('panel.addons.messaging.settings.index')}}">Settings</a>
        </li>
    </ul>
    @endrole

    <p>The following messages were sent between <a href="{{route('panel.users.edit', $sender)}}">{{$sender->display_name}}</a> and <a href="{{route('panel.users.edit', $recipient)}}">{{$recipient->display_name}}</a>.</p>

    <table class="table table-sm  ">
        <thead class="thead- border-0">
        <tr>
            <th>Message</th>
            <th>Sender</th>
            <th>Date</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($messages as $i => $message)
            <tr>
                <td>{{$message->message}}</td>
                <td><a href="{{route('panel.users.edit', $message->user)}}" class="">{{$message->user->display_name}}</a></td>
                <td>{{$message->created_at->format('j M y, g:i a')}}</td>

                <td>
                    <a href="{{route('panel.users.edit', $message->user)}}" class="text-muted float-right">Block Sender <i class="fa fa-chevron-right"></i></a>
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>




@stop
