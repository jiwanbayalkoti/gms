@extends('layouts.app')

@section('title', 'Social Media')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Social Media Publisher</h2>
            <p class="text-muted mb-0">Create gym announcements and publish to Facebook, Instagram and YouTube.</p>
            <p class="text-muted small mb-0">When publishing, the app uses <strong>that post’s gym</strong> Settings (Connect). <a href="{{ route('settings.index') }}">Settings → Facebook / IG & YouTube</a></p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><strong>Create Post</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('social-media.store') }}" enctype="multipart/form-data" class="no-validation">
                        @csrf
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                        </div>
                        <div class="form-group">
                            <label>Content <span class="text-danger">*</span></label>
                            <textarea name="content" rows="5" class="form-control" required>{{ old('content') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Platforms <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="platforms[]" value="facebook" id="pf">
                                <label class="form-check-label" for="pf">Facebook</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="platforms[]" value="instagram" id="pi">
                                <label class="form-check-label" for="pi">Instagram (requires image)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="platforms[]" value="youtube" id="py">
                                <label class="form-check-label" for="py">YouTube (requires video)</label>
                            </div>
                        </div>
                        @include('social-media.partials.youtube-options')
                        @include('social-media.partials.attachment-uploads', ['prefix' => 'create'])
                        <button class="btn btn-primary" type="submit">Save Draft</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7" id="social-media-posts-list">
            @include('social-media.partials.posts-list')
        </div>
    </div>
</div>

<!-- Clear local files modal -->
<div class="modal fade" id="clearMediaModal" tabindex="-1" role="dialog" aria-labelledby="clearMediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="clearMediaModalLabel">Remove local files</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Remove stored <strong>images and video</strong> from this server to save space?</p>
                <ul class="small text-muted mb-0 pl-3">
                    <li>Post text, status, and publish results stay in the list.</li>
                    <li>Does <strong>not</strong> delete anything on Facebook, Instagram, or YouTube.</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-secondary" id="confirmClearMediaBtn">Yes, clear files</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete post modal -->
<div class="modal fade" id="deletePostModal" tabindex="-1" role="dialog" aria-labelledby="deletePostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePostModalLabel">Delete Social Post</h5>
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
                <button type="button" class="btn btn-danger" id="confirmDeletePostBtn">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let deletePostFormRef = null;
    let clearMediaFormRef = null;

    function openDeletePostModal(form) {
        deletePostFormRef = form;
        $('#deletePostModal').modal('show');
    }

    function openClearMediaModal(form) {
        clearMediaFormRef = form;
        $('#clearMediaModal').modal('show');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const confirmClearBtn = document.getElementById('confirmClearMediaBtn');
        if (confirmClearBtn) {
            confirmClearBtn.addEventListener('click', function() {
                if (!clearMediaFormRef) return;
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Clearing...';
                clearMediaFormRef.submit();
            });
        }

        $('#clearMediaModal').on('hidden.bs.modal', function() {
            clearMediaFormRef = null;
            const btn = document.getElementById('confirmClearMediaBtn');
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Yes, clear files';
            }
        });

        const confirmBtn = document.getElementById('confirmDeletePostBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function() {
                if (!deletePostFormRef) {
                    return;
                }
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
                deletePostFormRef.submit();
            });
        }

        $('#deletePostModal').on('hidden.bs.modal', function() {
            deletePostFormRef = null;
            const btn = document.getElementById('confirmDeletePostBtn');
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Yes, Delete';
            }
        });

        (function initSocialMediaPostsPagination() {
            const listEl = document.getElementById('social-media-posts-list');
            if (!listEl) return;

            const listUrl = @json(route('social-media.index'));

            function setLoading(on) {
                const overlay = listEl.querySelector('#social-media-posts-loading');
                if (!overlay) return;
                overlay.classList.toggle('d-none', !on);
                overlay.classList.toggle('d-flex', on);
            }

            function loadPosts(url) {
                setLoading(true);
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html',
                    },
                    credentials: 'same-origin',
                })
                    .then(function(response) {
                        if (!response.ok) throw new Error('load failed');
                        return response.text();
                    })
                    .then(function(html) {
                        listEl.innerHTML = html;
                        window.history.pushState({}, '', url);
                    })
                    .catch(function() {
                        window.location.assign(url);
                    })
                    .finally(function() {
                        setLoading(false);
                    });
            }

            listEl.addEventListener('click', function(e) {
                const link = e.target.closest('#social-media-pagination a');
                if (!link || !link.href) return;
                e.preventDefault();
                loadPosts(link.href);
            });

            listEl.addEventListener('change', function(e) {
                if (e.target.id !== 'social-media-per-page') return;
                const url = new URL(listUrl, window.location.origin);
                url.searchParams.set('per_page', e.target.value);
                url.searchParams.delete('page');
                loadPosts(url.toString());
            });

            window.addEventListener('popstate', function() {
                if (window.location.pathname.indexOf('social-media') === -1) return;
                loadPosts(window.location.href);
            });
        })();
    });
</script>
@endpush

