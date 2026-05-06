@extends('layouts.app')

@section('title', 'Social Media')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Social Media Publisher</h2>
            <p class="text-muted mb-0">Create gym announcements and publish to Facebook, Instagram and YouTube.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><strong>Create Post</strong></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('social-media.store') }}" enctype="multipart/form-data">
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
                        <div class="form-group">
                            <label>Image (optional)</label>
                            <input type="file" name="media" class="form-control-file" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label>Video (optional)</label>
                            <input type="file" name="video" class="form-control-file" accept="video/*">
                        </div>
                        <button class="btn btn-primary" type="submit">Save Draft</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header"><strong>Post History</strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Platforms</th>
                                    <th>Status</th>
                                    <th>Result</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td>{{ $post->title ?: \Illuminate\Support\Str::limit($post->content, 35) }}</td>
                                        <td>{{ implode(', ', $post->platforms ?? []) }}</td>
                                        <td><span class="badge badge-secondary">{{ $post->status }}</span></td>
                                        <td>
                                            @if($post->publish_results)
                                                <div style="max-width: 360px;">
                                                    @foreach($post->publish_results as $platform => $result)
                                                        <div class="mb-1">
                                                            <strong>{{ ucfirst($platform) }}:</strong>
                                                            @if(($result['success'] ?? false) === true)
                                                                <span class="text-success">Success</span>
                                                            @else
                                                                <span class="text-danger">{{ $result['message'] ?? 'Failed' }}</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex" style="gap: 6px;">
                                                <form method="POST" action="{{ route('social-media.publish', $post) }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success" type="submit">Publish</button>
                                                </form>
                                                <form method="POST" action="{{ route('social-media.destroy', $post) }}" class="delete-post-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger" type="button" onclick="openDeletePostModal(this.form)">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No posts yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(method_exists($posts, 'links'))
                    <div class="card-footer">{{ $posts->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
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
                        <p class="mb-2">Are you sure you want to delete this post?</p>
                        <small class="text-muted">This action will permanently remove the post and uploaded media files.</small>
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

@push('scripts')
<script>
    let deletePostFormRef = null;

    function openDeletePostModal(form) {
        deletePostFormRef = form;
        $('#deletePostModal').modal('show');
    }

    document.addEventListener('DOMContentLoaded', function() {
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
    });
</script>
@endpush
@endsection

