<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - Authorization</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
     
    <!-- Styles --> 
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet"/>
     
</head>
<body class="passport-authorize">
    <div class="container">
        <div class="row">
            <div class="card">
                <div id="app">
                    <passport-clients></passport-clients>
                    <passport-authorized-clients></passport-authorized-clients>
                    <passport-personal-access-tokens></passport-personal-access-tokens>
                </div>
            </div>
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
            <script src="{{ asset('js/app.js') }}"></script>
        </div>
    </div>
     
   
</body>
</html>
