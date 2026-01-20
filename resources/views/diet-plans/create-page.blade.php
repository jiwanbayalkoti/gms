@extends('layouts.app')

@section('title', 'Create Diet Plan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create Diet Plan</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('diet-plans.store') }}" method="POST" id="planForm">
                        @include('diet-plans._form', ['trainers' => $trainers ?? [], 'members' => $members ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

