@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{"Welcome to Our Application Guest"}}</h1>
        <p>This is a demo page to showcase translations and Blade components</p>

        @if(session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @else
            <div class="alert alert-warning" role="alert">
                {{ 'You are currently not logged in.'}}
            </div>
        @endif

        @lang('Some great content')

        @trans('Some other contnet')
    </div>
@endsection
