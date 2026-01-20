@extends('layouts.app')

@section('title', 'Create Payment')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Create Payment</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
                        @include('payments._form', ['members' => $members ?? [], 'plans' => $plans ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

