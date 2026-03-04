@extends('layouts.app')

@section('content')
    @include('tickets.partials.modal-styles')

    <div class="container py-4">
        @include('tickets.partials.modal-panel', ['mode' => 'show', 'ticket' => $ticket])
    </div>
@endsection
