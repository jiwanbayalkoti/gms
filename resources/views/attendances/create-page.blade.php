@extends('layouts.app')

@section('title', 'Create Attendance')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create Attendance Record</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('attendances.store') }}" method="POST" id="attendanceForm">
                        @include('attendances._form', ['members' => $members ?? [], 'classes' => $classes ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

