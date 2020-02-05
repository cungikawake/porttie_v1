@extends('panel::layouts.master')

@section('content')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/handsontable@2.0.0/dist/handsontable.full.min.css">
    <script src="https://cdn.jsdelivr.net/npm/handsontable@2.0.0/dist/handsontable.min.js"></script>

    <div class="row">
        <div class="col-md-7">
            <h2 class="mr-5" style="float: left">Translations</h2>
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                <div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{app('laravellocalization')->getSupportedLocales()[$selected_locale]['name']}}
                    </button>
                    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                        @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            <a class="dropdown-item" href="?locale={{ $localeCode }}">{{ $properties['native'] }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <a href="?rescan=1" class="btn btn-block btn-secondary float-right">Rescan</a>
        </div>
        <div class="col-md-3">
            <button type="button" id="save" name="save" class="btn btn-block btn-primary float-right">Save translations</button>
        </div>
    </div>

    <br />
    @include('alert::bootstrap')


    <style>
        .handsontable .htDimmed { color:#777; background-color:#F1F1F1; }
    </style>

    <div id="translations"></div>
    <script>
        var translations = @json($translations);
        var container = document.getElementById('translations');
        var hot = new Handsontable(container, {
            data: translations,
            rowHeaders: false,
            colHeaders:  ['Original','Translation'],
            stretchH: 'all',
            multiSelect: false,
            fillHandle: false,
            colWidths: [50,50],
            selectionMode: 'single',
            columns: [{editor: false, data: 'key', readOnly: true, disableVisualSelection: true, color: 'green'}, {data: 'value', allowHtml: false}]
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function () {
            alertify.logPosition("bottom  right");

            $('#save').bind('click', function() {

                alertify.log("Saving...");
                $.ajax({
                    url: "/panel/addons/translator/{{$selected_locale}}",
                    type: "PUT",
                    data: {translations: hot.getData()},
                    success: function(d) {
                        alertify.success("Saved");
                    }
                });
            });
        });


    </script>

@stop
