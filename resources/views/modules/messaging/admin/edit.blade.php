@extends('panel::layouts.master')


@section('content')

    <h2>Messaging</h2>
    @include('alert::bootstrap')
    @role('admin')
    <ul class="nav nav- mb-4">
        <li class="nav-item {{ active(['panel.addons.messaging.index'])  }}">
            <a class="nav-link pl-0" href="{{route('panel.addons.messaging.index')}}">Latest messages</a>
        </li>
        <li class="nav-item {{ active(['panel.addons.messaging.settings.index'])  }}">
            <a class="nav-link pl-0" href="{{route('panel.addons.messaging.settings.index')}}">Settings</a>
        </li>
    </ul>
    @endrole

    <p>The following messages were sent.</p>

    <table class="table table-sm  ">
        <thead class="thead- border-0">
        <tr>
            <th>Message</th>
            <th>Sender</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($messages as $i => $message)
            <tr>
                <td>{{$message->message}}</td>
                <td>{{$message->receiver->display_name}}</td>

                <td>
                    <a href="{{route('panel.addons.messaging.conversation.index', $message)}}" class="text-muted float-right">View conversation <i class="fa fa-chevron-right"></i></a>
                    <a href="{{route('panel.users.edit', $message->user)}}" class="text-muted float-right">Block sender <i class="fa fa-chevron-right"></i></a>
                </td>
            </tr>
        @endforeach

        </tbody>
    </table>

    {{ $messages->links() }}



@stop