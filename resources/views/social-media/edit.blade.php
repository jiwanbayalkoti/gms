@extends('layouts.app')

@section('title', 'Edit Social Post')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('social-media.show', $post) }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left"></i> Back to view
            </a>
            <a href="{{ route('social-media.index') }}" class="btn btn-outline-secondary btn-sm mb-2 ml-1">
                List
            </a>
            <h2>Edit post</h2>
            <p class="text-muted mb-0">Update draft content and platforms. Status: <span class="badge {{ $post->statusBadgeClass() }}">{{ $post->statusLabel() }}</span></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('social-media.update', $post) }}" enctype="multipart/form-data" class="no-validation">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title) }}">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Content <span class="text-danger">*</span></label>
                            <textarea name="content" rows="6" class="form-control @error('content') is-invalid @enderror" required>{{ old('content', $post->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Platforms <span class="text-danger">*</span></label>
                            @php
                                $selected = old('platforms', $post->platforms ?? []);
                                if (!is_array($selected)) {
                                    $selected = [];
                                }
                            @endphp
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="platforms[]" value="facebook" id="pf" {{ in_array('facebook', $selected, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="pf">Facebook</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="platforms[]" value="instagram" id="pi" {{ in_array('instagram', $selected, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="pi">Instagram (requires image)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="platforms[]" value="youtube" id="py" {{ in_array('youtube', $selected, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="py">YouTube (requires video)</label>
                            </div>
                            @error('platforms')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        @error('media')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror
                        @error('video')
                            <div class="text-danger small mb-2">{{ $message }}</div>
                        @enderror

                        @include('social-media.partials.youtube-options', [
                            'youtubePrivacy' => $post->youtube_privacy ?? 'public',
                            'youtubeFormat' => $post->youtube_format ?? 'video',
                        ])
                        @include('social-media.partials.attachment-uploads', ['prefix' => 'edit', 'post' => $post])

                        <button class="btn btn-primary" type="submit">Save changes</button>
                        <a href="{{ route('social-media.show', $post) }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
