@extends('app')

@section('embed')
    <link rel="stylesheet" href="{{ elixir('css/regularPage.css') }}">
@stop

@section('precontent')
    @include('pages.partials.breadcrumbs')
@stop

@section('content')
    @yield('before-body')
    <h3>{{$page->header}}</h3>
    <div class="page_body decentered-container">
        {!! $page->page_content !!}
    </div>
    @yield('after-body')
@stop