{{-- Create Trainer Form - Used in Modal --}}
<form action="{{ route('trainers.store') }}" method="POST" enctype="multipart/form-data">
    @include('trainers._form')
</form>
