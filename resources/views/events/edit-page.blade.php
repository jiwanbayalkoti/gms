@extends('layouts.app')

@section('title', 'Edit Event')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Edit Event</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('events.update', $event->id) }}" method="POST">
                        @method('PUT')
                        @include('events._form')
                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('events.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Event
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

