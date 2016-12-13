@extends('admin.list')

@section('headers')
    <h1>ASCII</h1>
    <h3 class="sub-header">Make ASCII animated again!</h3>
    {{--<a href="/admin/news/new" class="buffer-25 btn btn-md btn-success">Добавить</a>--}}
    <div class="clearfix"></div>
@stop

@section('items')
    @if(sizeof($data))
        @include('admin.iteration.ascii', ['items' => $data])
    @endif
@stop