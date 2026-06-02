@extends('layouts.app')

@section('title', 'View Social Post')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('social-media.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left"></i> Back to Social Media
            </a>
            <h2 class="mb-1">{{ $post->title ?: 'Social post' }}</h2>
            <p class="text-muted mb-0">
                Status: <span class="badge {{ $post->statusBadgeClass() }}">{{ $post->statusLabel() }}</span>
                @if($post->published_at)
                    · Published {{ $post->published_at->format('Y-m-d H:i') }}
                @endif
                @if($post->creator)
                    · By {{ $post->creator->name ?? 'User #' . $post->created_by }}
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Content</strong>
                    <a href="{{ route('social-media.edit', $post) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    @if($post->title)
                        <h5>{{ $post->title }}</h5>
                    @endif
                    <div class="mb-3" style="white-space: pre-wrap;">{{ $post->content }}</div>

                    <p class="mb-1"><strong>Platforms:</strong> {{ implode(', ', $post->platforms ?? []) ?: '—' }}</p>
                    @if(in_array('youtube', $post->platforms ?? [], true))
                        <p class="mb-1"><strong>YouTube visibility:</strong> {{ ucfirst($post->youtube_privacy ?? 'public') }}</p>
                        <p class="mb-0"><strong>YouTube type:</strong> {{ ($post->youtube_format ?? 'video') === 'shorts' ? 'Short' : 'Regular video' }}</p>
                    @endif
                </div>
            </div>

            @if($post->publish_results && count($post->publish_results))
                <div class="card mt-3">
                    <div class="card-header"><strong>Publish results</strong></div>
                    <div class="card-body">
                        @foreach($post->publish_results as $platform => $result)
                            <div class="mb-2 pb-2 border-bottom">
                                <strong>{{ ucfirst($platform) }}:</strong>
                                @if(($result['success'] ?? false) === true)
                                    <span class="text-success">Success</span>
                                @else
                                    <span class="text-danger">{{ $result['message'] ?? 'Failed' }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            @php $gallery = $post->getMediaPathsList(); @endphp
            @if(count($gallery))
                <div class="card mb-3">
                    <div class="card-header"><strong>Images</strong></div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap" style="gap:10px;">
                            @foreach($gallery as $imgPath)
                                <a href="{{ asset('storage/' . ltrim($imgPath, '/')) }}" target="_blank" rel="noopener">
                                    <img src="{{ asset('storage/' . ltrim($imgPath, '/')) }}" alt="" class="img-fluid rounded border" style="max-height: 200px;max-width:100%;object-fit:cover;">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            @if($post->video_path)
                <div class="card mb-3">
                    <div class="card-header"><strong>Video</strong></div>
                    <div class="card-body">
                        <video controls class="w-100 rounded" style="max-height: 280px;">
                            <source src="{{ asset('storage/' . ltrim($post->video_path, '/')) }}">
                        </video>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    @if($post->hasStoredMedia())
                        <p class="small text-muted mb-2">Local files: {{ $post->storedMediaSummary() }}</p>
                        <form method="POST" action="{{ route('social-media.clear-media', $post) }}" class="clear-media-form-show mb-2">
                            @csrf
                            <button type="button" class="btn btn-outline-secondary btn-block" onclick="openClearMediaModalShow(this.form)">
                                <i class="fas fa-broom"></i> Clear local files
                            </button>
                        </form>
                    @else
                        <p class="small text-muted mb-2">Local files cleared (post data kept on server).</p>
                    @endif
                    @if($post->canPublish())
                        <form method="POST" action="{{ route('social-media.publish', $post) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-paper-plane"></i> Publish
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('social-media.destroy', $post) }}" class="delete-post-form-show">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-outline-danger btn-block" onclick="openDeletePostModalShow(this.form)">
                            <i class="fas fa-trash"></i> Delete post
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="clearMediaModalShow" tabindex="-1" role="dialog" aria-labelledby="clearMediaModalShowLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="clearMediaModalShowLabel">Remove local files</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Remove stored <strong>images and video</strong> from this server to save space?</p>
                <ul class="small text-muted mb-0 pl-3">
                    <li>Post text, status, and publish results stay in the system.</li>
                    <li>Does <strong>not</strong> delete anything on Facebook, Instagram, or YouTube.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-secondary" id="confirmClearMediaBtnShow">Yes, clear files</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePostModalShow" tabindex="-1" role="dialog" aria-labelledby="deletePostModalShowLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePostModalShowLabel">Delete Social Post</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle text-danger mr-3" style="font-size: 24px;"></i>
                    <div>
                        <p class="mb-2">Delete this post from the system?</p>
                        <small class="text-muted">Removes the full record and any local files. Does not remove posts on Facebook, Instagram, or YouTube.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeletePostBtnShow">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let deletePostFormShowRef = null;
    let clearMediaFormShowRef = null;

    function openClearMediaModalShow(form) {
        clearMediaFormShowRef = form;
        $('#clearMediaModalShow').modal('show');
    }

    function openDeletePostModalShow(form) {
        deletePostFormShowRef = form;
        $('#deletePostModalShow').modal('show');
    }
    document.addEventListener('DOMContentLoaded', function() {
        const confirmClearBtn = document.getElementById('confirmClearMediaBtnShow');
        if (confirmClearBtn) {
            confirmClearBtn.addEventListener('click', function() {
                if (!clearMediaFormShowRef) return;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
                clearMediaFormShowRef.submit();
            });
        }

        const confirmBtn = document.getElementById('confirmDeletePostBtnShow');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (!deletePostFormShowRef) return;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                deletePostFormShowRef.submit();
            });
        }

        $('#clearMediaModalShow').on('hidden.bs.modal', function() {
            clearMediaFormShowRef = null;
            const btn = document.getElementById('confirmClearMediaBtnShow');
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Yes, clear files';
            }
        });

        $('#deletePostModalShow').on('hidden.bs.modal', function() {
            deletePostFormShowRef = null;
            const btn = document.getElementById('confirmDeletePostBtnShow');
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Yes, Delete';
            }
        });
    });
</script>
@endpush
@endsection
