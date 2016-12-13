@extends('app')

@section('embed')
    <link rel="stylesheet" href="{{ elixir('css/asciiPage.css') }}">
    <script type="text/javascript" src="https://vk.com/js/api/share.js?93" charset="windows-1251"></script>
@stop

@section('content')
    <div class="buffer-50">
        @if (!empty($items))
            <h3>Le list of magnificient pieces of art:</h3>
            @foreach($items as $ascii)
                @include('partials.embeddedAscii', ['ascii' => $ascii])
            @endforeach
        @endif
    </div>
    <ul class="pagination">
        @for ($cur_page = 1; $cur_page < ceil($total / $on_page) + 1; $cur_page++)
            <? $current = $page == $cur_page; ?>
            <li class="{{$current ? 'active' : ''}}">
                <a href="<?= $current ? 'javascript: void(0)' : '/latest/' . $cur_page  ?>">{{$cur_page}}</a>
            </li>
        @endfor
    </ul>
@stop