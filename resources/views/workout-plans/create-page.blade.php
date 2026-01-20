@extends('layouts.app')

@section('title', 'Create Workout Plan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create Workout Plan</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('workout-plans.store') }}" method="POST" id="planForm">
                        @include('workout-plans._form', ['trainers' => $trainers ?? [], 'members' => $members ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

