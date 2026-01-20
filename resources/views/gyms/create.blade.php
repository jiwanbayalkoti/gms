{{-- Create Gym Form - Used in Modal --}}
<form action="{{ route('gyms.store') }}" method="POST" enctype="multipart/form-data">
    @include('gyms._form')
</form>

