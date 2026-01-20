{{-- Create Class Form - Used in Modal --}}
<form action="{{ route('classes.store') }}" method="POST" id="classForm">
    @include('classes._form', ['trainers' => $trainers])
</form>
