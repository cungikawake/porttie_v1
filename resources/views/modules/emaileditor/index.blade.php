@extends('panel::layouts.master')


@section('content')

    <h2>Email templates</h2>
    @include('alert::bootstrap')
    <br />

    <table class="table table-sm  ">
        <thead class="thead- border-0">
        <tr>
            <th>Template</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($email_templates as $i => $email_template)
            <tr>
                <td>{{$email_template->name}}</td>

                <td>
                    <a href="{{route('panel.addons.emaileditor.edit', $email_template->filename)}}" class="text-muted float-right">Edit <i class="fa fa-chevron-right"></i></a>

                </td>
            </tr>
        @endforeach

        </tbody>
    </table>



@stop
