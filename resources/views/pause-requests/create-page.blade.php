@extends('layouts.app')

@section('title', 'Request Membership Pause')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Request Membership Pause</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @include('pause-requests.create')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

