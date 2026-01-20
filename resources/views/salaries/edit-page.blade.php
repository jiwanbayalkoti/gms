@extends('layouts.app')

@section('title', 'Edit Salary')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Edit Salary</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('salaries.update', $salary->id) }}" method="POST" id="salaryForm">
                        @include('salaries._form', ['salary' => $salary, 'employees' => $employees ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

