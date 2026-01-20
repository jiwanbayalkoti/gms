{{-- Edit Class Form - Used in Modal --}}
<form action="{{ route('classes.update', $class->id) }}" method="POST" id="classForm">
    @include('classes._form', ['class' => $class, 'trainers' => $trainers])
</form>
