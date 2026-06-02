@php
    $youtubePrivacy = old('youtube_privacy', $youtubePrivacy ?? 'public');
    $youtubeFormat = old('youtube_format', $youtubeFormat ?? 'video');
@endphp
<div id="youtube-options-panel" class="card border-danger mb-3" style="display: none;">
    <div class="card-header py-2">
        <strong><i class="fab fa-youtube text-danger"></i> YouTube options</strong>
    </div>
    <div class="card-body py-3">
        <div class="form-group">
            <label for="youtube_privacy">Visibility <span class="text-danger">*</span></label>
            <select name="youtube_privacy" id="youtube_privacy" class="form-control form-control-sm">
                <option value="public" {{ $youtubePrivacy === 'public' ? 'selected' : '' }}>Public - anyone can watch</option>
                <option value="unlisted" {{ $youtubePrivacy === 'unlisted' ? 'selected' : '' }}>Unlisted - link only</option>
                <option value="private" {{ $youtubePrivacy === 'private' ? 'selected' : '' }}>Private - channel only</option>
            </select>
            @error('youtube_privacy')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group mb-0">
            <label class="d-block mb-2">Video type <span class="text-danger">*</span></label>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="youtube_format_video" name="youtube_format" value="video"
                       class="custom-control-input" {{ $youtubeFormat === 'video' ? 'checked' : '' }}>
                <label class="custom-control-label" for="youtube_format_video">Regular video</label>
            </div>
            <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" id="youtube_format_shorts" name="youtube_format" value="shorts"
                       class="custom-control-input" {{ $youtubeFormat === 'shorts' ? 'checked' : '' }}>
                <label class="custom-control-label" for="youtube_format_shorts">YouTube Short</label>
            </div>
            <small class="form-text text-muted">
                Shorts: vertical (9:16), usually under 60 seconds. Adds #Shorts for YouTube classification.
            </small>
            @error('youtube_format')
                <div>{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var youtubeCheckbox = document.getElementById('py');
    var panel = document.getElementById('youtube-options-panel');
    if (!youtubeCheckbox || !panel) return;
    function syncYoutubePanel() {
        panel.style.display = youtubeCheckbox.checked ? 'block' : 'none';
    }
    youtubeCheckbox.addEventListener('change', syncYoutubePanel);
    syncYoutubePanel();
});
</script>
@endpush
