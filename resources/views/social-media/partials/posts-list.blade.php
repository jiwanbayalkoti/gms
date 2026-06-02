<div class="card" id="social-media-posts-card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
        <strong>Post History</strong>
        <span class="text-muted small mb-0" id="social-media-posts-count">
            @if($posts->total() > 0)
                {{ $posts->total() }} {{ \Illuminate\Support\Str::plural('post', $posts->total()) }}
            @endif
        </span>
    </div>
    <div class="card-body p-0 position-relative">
        <div id="social-media-posts-loading" class="position-absolute w-100 h-100 d-none align-items-center justify-content-center bg-white" style="z-index: 5; opacity: 0.85;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Platforms</th>
                        <th>Status</th>
                        <th>Local files</th>
                        <th>Result</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>{{ $post->title ?: \Illuminate\Support\Str::limit($post->content, 35) }}</td>
                            <td>{{ implode(', ', $post->platforms ?? []) }}</td>
                            <td><span class="badge {{ $post->statusBadgeClass() }}">{{ $post->statusLabel() }}</span></td>
                            <td class="small">
                                @if($post->hasStoredMedia())
                                    <span class="text-dark">{{ $post->storedMediaSummary() }}</span>
                                @else
                                    <span class="text-muted">Cleared</span>
                                @endif
                            </td>
                            <td>
                                @if($post->publish_results)
                                    <div>
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
                                <div class="d-flex flex-wrap align-items-center" style="gap: 6px;">
                                    <a href="{{ route('social-media.show', $post) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('social-media.edit', $post) }}" class="btn btn-sm btn-warning">Edit</a>
                                    @if($post->canPublish())
                                        <form method="POST" action="{{ route('social-media.publish', $post) }}" class="m-0">
                                            @csrf
                                            <button class="btn btn-sm btn-success" type="submit">Publish</button>
                                        </form>
                                    @endif
                                    @if($post->hasStoredMedia())
                                        <form method="POST" action="{{ route('social-media.clear-media', $post) }}" class="clear-media-form m-0">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openClearMediaModal(this.form)" title="Remove files from server only">
                                                Clear files
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('social-media.destroy', $post) }}" class="delete-post-form m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger" onclick="openDeletePostModal(this.form)">Delete post</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No posts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
            <label class="small text-muted mb-0 mr-2 mb-2 mb-sm-0">
                Per page
                <select id="social-media-per-page" class="form-control form-control-sm d-inline-block ml-1" style="width: auto;">
                    @foreach([10, 15, 20, 50] as $size)
                        <option value="{{ $size }}" @selected(($perPage ?? 15) == $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </label>
            @if($posts->total() > 0)
                <span class="small text-muted mb-2 mb-sm-0" id="social-media-posts-range">
                    Showing {{ $posts->firstItem() }}–{{ $posts->lastItem() }} of {{ $posts->total() }}
                </span>
            @endif
        </div>
        @if($posts->hasPages())
            <div class="w-100 d-flex justify-content-center justify-content-md-end mt-1" id="social-media-pagination">
                {{ $posts->links() }}
            </div>
        @else
            <div id="social-media-pagination"></div>
        @endif
    </div>
</div>
