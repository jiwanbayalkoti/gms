@extends('layouts.app')

@section('title', 'Edit Attendance')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Edit Attendance Record</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('attendances.update', $attendance->id) }}" method="POST" id="attendanceForm">
                        @method('PUT')
                        @include('attendances._form', ['attendance' => $attendance, 'members' => $members ?? [], 'classes' => $classes ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

