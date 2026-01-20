@extends('layouts.app')

@section('title', 'Edit Workout Plan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Edit Workout Plan</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('workout-plans.update', $plan->id) }}" method="POST" id="planForm">
                        @method('PUT')
                        @include('workout-plans._form', ['plan' => $plan, 'trainers' => $trainers ?? [], 'members' => $members ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

