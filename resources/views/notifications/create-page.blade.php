@extends('layouts.app')

@section('title', 'Create Notification')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create Notification</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('notifications.store') }}" method="POST" id="notificationForm">
                        @include('notifications._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

