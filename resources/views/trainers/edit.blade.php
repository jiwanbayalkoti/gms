{{-- Edit Trainer Form - Used in Modal --}}
<form action="{{ route('trainers.update', $trainer->id) }}" method="POST" enctype="multipart/form-data">
    @include('trainers._form', ['trainer' => $trainer])
</form>
