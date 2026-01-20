@extends('layouts.app')

@section('title', 'Create Salary')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create Salary</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('salaries.store') }}" method="POST" id="salaryForm">
                        @include('salaries._form', ['employees' => $employees ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

