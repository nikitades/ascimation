@if ($ascii->frames && is_array(json_decode($ascii->frames)))
    <div class="ascii buffer-50">
        <div class="frames" id="ribbon{{$ascii->id}}">
            <a href="/{{$ascii->uuid}}">
                @foreach(json_decode($ascii->frames) as $frame_str)
                    <pre class="frame frame__small" style="width: auto; display: none;">{{$frame_str}}</pre>
                @endforeach
            </a>
        </div>
        <a href="/{{$ascii->uuid}}" class="btn btn-default btn-xs">Check this out</a>
        <script>
            global.onload.push(function () {
                run_ascii($('#ribbon{{$ascii->id}}'), {{$ascii->framerate ?: 240}}, true);
            });
        </script>
    </div>
@endif