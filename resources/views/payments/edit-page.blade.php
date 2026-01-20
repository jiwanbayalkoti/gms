@extends('layouts.app')

@section('title', 'Edit Payment')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Edit Payment</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('payments.update', $payment->id) }}" method="POST" id="paymentForm">
                        @method('PUT')
                        @include('payments._form', ['payment' => $payment, 'members' => $members ?? [], 'plans' => $plans ?? []])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

