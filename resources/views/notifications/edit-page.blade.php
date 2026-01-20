@extends('layouts.app')

@section('title', 'Edit Notification')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Edit Notification</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('notifications.update', $notification->id) }}" method="POST" id="notificationForm">
                        @method('PUT')
                        @include('notifications._form', ['notification' => $notification])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

