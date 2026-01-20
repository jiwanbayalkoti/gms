{{-- Edit Gym Form - Used in Modal --}}
<form action="{{ route('gyms.update', $gym->id) }}" method="POST" enctype="multipart/form-data">
    @include('gyms._form', ['gym' => $gym])
</form>

