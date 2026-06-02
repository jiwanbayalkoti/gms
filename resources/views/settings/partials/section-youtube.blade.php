<div class="card settings-section-card" id="section-youtube">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
        <span><i class="fab fa-youtube text-danger"></i> YouTube</span>
        @if($youtubeConnected)
            <span class="badge badge-success">Connected</span>
        @else
            <span class="badge badge-secondary">Not connected</span>
        @endif
    </div>
    <div class="card-body">
        @if($youtubeConnected && !empty($settings->youtube_channel_id))
            <p class="small text-muted border rounded p-2 bg-light mb-3">Channel: <code>{{ $settings->youtube_channel_id }}</code></p>
        @endif
        <div class="form-group">
            <label for="youtube_client_id">Client ID (Google Cloud)</label>
            <input type="text" class="form-control" id="youtube_client_id" name="youtube_client_id" value="{{ old('youtube_client_id', $settings->youtube_client_id ?? '') }}">
        </div>
        <div class="form-group">
            <label for="youtube_client_secret">Client Secret</label>
            <input type="text" class="form-control" id="youtube_client_secret" name="youtube_client_secret" value="{{ old('youtube_client_secret', $settings->youtube_client_secret ?? '') }}">
            <small class="form-text text-muted">Redirect: <code>{{ route('settings.youtube-callback') }}</code></small>
        </div>
        <div class="form-group mb-0">
            <a href="{{ route('settings.connect-youtube', !empty($settingsGymId) ? ['gym_id' => $settingsGymId] : []) }}" class="btn btn-outline-danger btn-sm"><i class="fab fa-youtube"></i> Connect with YouTube</a>
            <button type="button" class="btn btn-outline-info btn-sm ml-1" id="test-youtube-btn">Test</button>
            <small id="test-youtube-result" class="form-text d-block mt-2"></small>
        </div>
    </div>
</div>
