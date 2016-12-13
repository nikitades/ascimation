@extends('app')

@section('embed')
    <link rel="stylesheet" href="{{ elixir('css/asciiPage.css') }}">
    <script type="text/javascript" src="https://vk.com/js/api/share.js?93" charset="windows-1251"></script>
    @if ($ascii->ready)
        <meta property="og:title" content="Le magnificient piece of art"/>
        <meta property="og:image" content="/images/stash/rendered_frames/{{$ascii->image->filename_ascii}}"/>
        <meta property="og:description" content="The GIF animation converted to dynamic ASCII"/>
    @endif
@stop

@section('content')
    @if (!$ascii->ready)
        <h1>Le magnificient piece of art is not ready yet. Please refresh later.</h1>
    @else
        <h1>Le magnificient piece of art:</h1>
        <div class="frames buffer-25">
            @if ($ascii->frames && is_array(json_decode($ascii->frames)))
                @foreach(json_decode($ascii->frames) as $frame_str)
                    <pre class="frame" style="width: auto; display: none;">{{$frame_str}}</pre>
                @endforeach
            @endif
        </div>
        <div class="buffer-25">
            <h3>Le source of le magnificient piece of art:</h3>
            <img src="{{'/files/stash/' . $ascii->image->filename}}" alt="Le art source">
        </div>
        <div class="buffer-25 gif_creation">
            @if ($ascii->gif_ready == 0)
                <h4>Le make ascii gif again!</h4>
                <a href="javascript: void(0);" class="btn btn-xs btn-warning make_gif_again"
                   data-uuid="{{$ascii->uuid}}">Make!</a>
                <script>
                    $(function () {
                        $('.make_gif_again').click(function (e) {
                            var parent = $(e.currentTarget).parents('.gif_creation');
                            parent.html('<h4>Le reverse ASCII GIF version is being processed...</h4>');
                            m.ajax({
                                url: '/ajax/ascii/generateNewImage/',
                                data: {uuid: $(e.currentTarget).data('uuid')},
                                user_success: function (data) {
                                    console.log($(this));
                                    parent.html('<h4>Le reverse ASCII GIF version:</h4><img src="' + data.src + '"/>');
                                },
                                user_fail: function (data) {
                                    parent.find('h4').text('Something went wrong :C');
                                }
                            })
                        });
                    })
                </script>
            @elseif ($ascii->gif_ready == 1)
                <h4>Le reverse ASCII GIF version is being processed...</h4>
            @elseif ($ascii->gif_ready == 2)
                <h4>Le IMAGE ASCII GIF version:</h4>
                <img src="/images/stash/rendered_frames/{{$ascii->image->filename_ascii_gif}}"/>
            @endif
        </div>
        <div class="buffer-25">
            <h3>Please le share this with your friends:</h3>
            <!-- Put this script tag to the <head> of your page -->
            <script type="text/javascript">
                <!--
                document.write(VK.Share.button('http://nikitades.sknt.ru/{{$ascii->uuid}}'));
                -->
            </script>
        </div>
        <script>
            global.onload.push(function () {
                run_ascii($('.frames'), {{  $ascii->framerate ?: 240 }});
            });
        </script>
    @endif
@stop