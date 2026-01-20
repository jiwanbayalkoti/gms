@extends('layouts.app')

@section('title', 'Create Booking')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create New Booking</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                        @include('bookings._form', ['members' => $members ?? [], 'classes' => $classes ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

