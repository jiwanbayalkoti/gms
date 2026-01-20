{{-- Create Staff Form - Used in Modal --}}
<form action="{{ route('staff.store') }}" method="POST" id="staffForm" enctype="multipart/form-data">
    @include('staff._form')
</form>

