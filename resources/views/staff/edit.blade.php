{{-- Edit Staff Form - Used in Modal --}}
<form action="{{ route('staff.update', $staff->id) }}" method="POST" id="staffForm" enctype="multipart/form-data">
    @include('staff._form')
</form>

