@extends('app')

@section('embed')
    <link rel="stylesheet" href="{{ elixir('css/homePage.css') }}">
@stop

@section('content')
    <h1>{{$page->header}}</h1>
    <div class="buffer-25">
        {!! $page->page_content !!}
    </div>
    {!! Form::open(['method' => 'POST', 'class' => 'buffer-50 claimForm', 'role' => 'form', 'files' => 'true']) !!}
    @include('partials.errors')
    <div class="form-group">
        {!! Form::label('input', 'Input file', ['for' => 'file_AsciiFile']) !!}
        {!! Form::file('file_AsciiFile', ['id' => 'file_AsciiFile']) !!}
        <p class="help-block">Any correct image.</p>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    {!! Form::close() !!}
    <div class="buffer-50 last-claims">
    </div>
    <div class="buffer-50">
        @if (!empty($latest_ascii))
            <h3>Le latest magnificient pieces of art:</h3>
            @foreach($latest_ascii as $ascii)
                @include('partials.embeddedAscii', ['ascii' => $ascii, 'embed' => false])
            @endforeach
        @endif
    </div>
    <script>
        $(".claimForm").submit(function (e) {
            if ($(e.currentTarget).find('button[type="submit"]').hasClass('disabled')) return false;
            if (!document.querySelector('[name="file_AsciiFile"]').files.length) return false;
            $(e.currentTarget).find('button[type="submit"]').addClass('disabled');
            m.ajax({
                url: '/ajax/ascii/claim/',
                data: new FormData(e.currentTarget),
                contentType: false,
                processData: false,
                user_success: function (data) {
                    if (data && data.uuid) {
                        inform_user(data.uuid);
                        start_worker(data.uuid);
                        $(e.currentTarget).find('button[type="submit"]').removeClass('disabled');
                        $(e.currentTarget).find('.help-block').text('Any correct image').removeClass('error');
                    } else {
                        console.info('didn\'t');
                    }
                },
                user_fail: function (data, errors) {
                    $(e.currentTarget).find('.help-block').text('Wrong file!').addClass('error');
                    $(e.currentTarget).find('button[type="submit"]').removeClass('disabled');
                },
                progress: function (e) {
                    console.info(e);
                }
            });
            return false;
        });

        var inform_user = function (uuid) {
            $('.last-claims').prepend('<p class="processed" data-uuid="' + uuid + '"><a target="_blank" href="/' + uuid + '">Processed: ' + uuid + ' <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span></a></p>');
        };

        var start_worker = function (uuid) {
            var r = m.ajax({
                url: '/ajax/ascii/worker/',
                data: {
                    uuid: uuid
                },
                user_success: function (data) {
                    console.info(data.time);
                    var item = $('[data-uuid="' + data.ready + '"]');
                    $(item).removeClass('processed').addClass('done').find('a').html('Done: ' + uuid + ' <span class="glyphicon glyphicon-share-alt" aria-hidden="true"></span>').css('color', 'green');
                }
            });
//            setTimeout(r.abort, 1000);
        };
    </script>
    @include('partials.metrics')
@stop