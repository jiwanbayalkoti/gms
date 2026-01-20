@extends('layouts.app')

@section('title', 'Assign Diet Plan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Assign Diet Plan: {{ $plan->name }}</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Plan Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $plan->name }}</p>
                    <p><strong>Trainer:</strong> {{ $plan->trainer->name ?? 'N/A' }}</p>
                    @if($plan->description)
                        <p><strong>Description:</strong> {{ $plan->description }}</p>
                    @endif
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Assign to Member</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('diet-plans.assign', $plan->id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="member_id">Member <span class="text-danger">*</span></label>
                            <select class="form-control @error('member_id') is-invalid @enderror" id="member_id" name="member_id" required>
                                <option value="">Select Member</option>
                                @foreach($members ?? [] as $member)
                                    <option value="{{ $member->id }}" {{ old('member_id', $selectedMember && $selectedMember->id == $member->id ? $member->id : '') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ $member->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('member_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Leave empty for no end date</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-user-plus"></i> Assign Plan
                            </button>
                            <a href="{{ route('diet-plans.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

