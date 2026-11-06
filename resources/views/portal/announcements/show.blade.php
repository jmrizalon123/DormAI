@extends('layouts.app')
@section('title', $announcement->title)
@section('page-title', $announcement->title)
@section('page-subtitle', $announcement->published_at?->format('F j, Y g:i A'))
@section('content')
    <div class="da-panel p-4">{!! nl2br(e($announcement->body)) !!}</div>
@endsection
