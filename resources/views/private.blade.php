@extends('layouts.app')

@section('content')
        
        <private-chat :auth="{{auth()->user()}}"></private-chat>
        
@endsection