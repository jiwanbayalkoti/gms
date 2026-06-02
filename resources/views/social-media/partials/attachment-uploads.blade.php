{{--
  $prefix: 'create' | 'edit'
  $post: optional (edit only)
  Thumbnails: inline script runs immediately after this markup (no stack timing issues).
--}}
@php
    $existingImages = isset($post) ? $post->getMediaPathsList() : [];
    $existingVideo = isset($post) && $post->video_path ? $post->video_path : null;
@endphp

<style>
    .social-thumb-strip-{{ $prefix }} {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-start;
        margin-top: 10px;
        min-height: 24px;
        padding: 10px;
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 8px;
    }
    .social-thumb-strip-{{ $prefix }} .social-thumb-cell {
        position: relative;
        width: 96px;
        height: 96px;
        flex-shrink: 0;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #fff;
    }
    .social-thumb-strip-{{ $prefix }} .social-thumb-cell img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .social-thumb-strip-{{ $prefix }} .social-thumb-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 26px;
        height: 26px;
        padding: 0;
        line-height: 1;
        border-radius: 50%;
        z-index: 3;
        border: none;
        background: rgba(220, 53, 69, 0.95);
        color: #fff;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(0,0,0,.2);
    }
    .social-video-strip-{{ $prefix }} {
        margin-top: 10px;
        padding: 12px;
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 8px;
        max-width: 100%;
    }
    .social-video-strip-{{ $prefix }} .social-video-inner {
        position: relative;
        display: inline-block;
        max-width: 100%;
        vertical-align: top;
    }
    .social-video-strip-{{ $prefix }} .social-video-poster-wrap {
        position: relative;
        width: 96px;
        height: 96px;
        flex-shrink: 0;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #111;
    }
    .social-video-strip-{{ $prefix }} .social-video-poster-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .social-video-strip-{{ $prefix }} .social-video-poster-remove {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 26px;
        height: 26px;
        padding: 0;
        line-height: 1;
        border-radius: 50%;
        z-index: 5;
        border: none;
        background: rgba(220, 53, 69, 0.95);
        color: #fff;
        font-size: 15px;
        cursor: pointer;
    }
    .social-video-strip-{{ $prefix }} video.social-video-preview-el {
        max-height: 180px;
        max-width: 100%;
        width: auto;
        height: auto;
        display: block;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        background: #000;
    }
    .social-video-strip-{{ $prefix }} .social-video-remove-new {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 30px;
        height: 30px;
        padding: 0;
        line-height: 1;
        border-radius: 50%;
        z-index: 4;
        border: none;
        background: rgba(220, 53, 69, 0.95);
        color: #fff;
        font-size: 18px;
        cursor: pointer;
        box-shadow: 0 1px 4px rgba(0,0,0,.25);
    }
</style>

<div id="social-attachment-root-{{ $prefix }}" class="social-attachment-uploads" data-prefix="{{ $prefix }}">
    <div class="form-group">
        <label class="mb-1">Images <small class="text-muted">(optional, multiple)</small></label>
        <input type="file" id="{{ $prefix }}-media-input" name="media[]" multiple accept="image/*" class="d-none" tabindex="-1">
        <div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $prefix }}-media-pick-btn">
                <i class="fas fa-images"></i> Choose images
            </button>
        </div>
        <small class="form-text text-muted">Thumbnails appear below after you choose files. Use × to remove before saving.</small>

        @if($prefix === 'edit' && count($existingImages))
            <div class="d-flex flex-wrap mt-2" id="{{ $prefix }}-existing-media" style="gap:10px;">
                @foreach($existingImages as $path)
                    <div class="position-relative border rounded p-1 bg-light existing-saved-img" data-stored-path="{{ e($path) }}">
                        <button type="button" class="btn btn-danger btn-sm position-absolute social-img-remove-saved" style="top:-8px;right:-8px;width:28px;height:28px;padding:0;line-height:1;border-radius:50%;z-index:2;" title="Remove">&times;</button>
                        <img src="{{ asset('storage/' . ltrim($path, '/')) }}" alt="" style="height:88px;width:auto;max-width:130px;object-fit:cover;display:block;">
                        <input type="hidden" name="remove_media[]" value="{{ $path }}" disabled class="remove-media-token">
                    </div>
                @endforeach
            </div>
        @endif

        <p class="small text-muted mb-0 mt-2 {{ $prefix }}-thumb-label" id="{{ $prefix }}-thumb-label" style="display:none;">Selected thumbnails</p>
        <div class="social-thumb-strip-{{ $prefix }}" id="{{ $prefix }}-new-media-previews"></div>
    </div>

    <div class="form-group">
        <label class="mb-1">Video <small class="text-muted">(optional — YouTube / Facebook publish)</small></label>
        <input type="file" id="{{ $prefix }}-video-input" name="video" accept="video/*,.mp4,.webm,.mov,.mkv,.avi" class="d-none" tabindex="-1">
        <div>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $prefix }}-video-pick-btn">
                <i class="fas fa-video"></i> Choose video
            </button>
        </div>
        <small class="form-text text-muted">After you choose a file, a preview appears below. Use × to remove it.</small>

        @if($prefix === 'edit' && $existingVideo)
            <p class="small text-muted mb-1 mt-2">Saved video</p>
            <div class="social-video-strip-{{ $prefix }} mb-2" id="{{ $prefix }}-saved-video-wrap">
                <div class="social-video-inner">
                    <button type="button" class="social-video-remove-saved" style="position:absolute;top:8px;right:8px;width:30px;height:30px;padding:0;line-height:1;border-radius:50%;z-index:4;border:none;background:rgba(220,53,69,.95);color:#fff;font-size:18px;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,.25);" title="Remove saved video">&times;</button>
                    <video controls playsinline preload="metadata" class="w-100">
                        <source src="{{ asset('storage/' . ltrim($existingVideo, '/')) }}">
                    </video>
                </div>
                <input type="hidden" name="remove_video" value="1" id="{{ $prefix }}-remove-video-flag" disabled>
            </div>
            <small class="form-text text-muted d-block mb-1">Choosing a new video and saving will replace this file.</small>
        @endif

        <p class="small text-muted mb-0 mt-2 font-weight-bold" id="{{ $prefix }}-video-thumb-label" style="display:none;">Video preview</p>
        <div class="social-video-strip-{{ $prefix }} d-none" id="{{ $prefix }}-new-video-strip">
            <div class="d-flex flex-wrap align-items-start" style="gap:12px;">
                <div class="social-video-poster-wrap" id="{{ $prefix }}-video-poster-wrap">
                    <button type="button" class="social-video-poster-remove" id="{{ $prefix }}-video-poster-remove" title="Remove">&times;</button>
                    <img src="" alt="" id="{{ $prefix }}-video-poster-img" width="96" height="96" style="display:none;">
                    <span class="d-flex align-items-center justify-content-center small p-1 text-center" id="{{ $prefix }}-video-poster-placeholder" style="width:96px;height:96px;font-size:11px;color:#aaa;background:#222;">Loading…</span>
                </div>
                <div class="social-video-inner flex-grow-1" style="min-width:0;">
                    <video id="{{ $prefix }}-new-video-el" class="social-video-preview-el" controls playsinline preload="metadata"></video>
                </div>
            </div>
            <div class="small text-muted mt-2 mb-0" id="{{ $prefix }}-video-file-meta"></div>
        </div>
    </div>
</div>

<script>
(function () {
    var prefix = @json($prefix);
    var root = document.getElementById('social-attachment-root-' + prefix);
    if (!root || root.getAttribute('data-inline-wired') === '1') {
        return;
    }
    root.setAttribute('data-inline-wired', '1');

    function gid(id) {
        return document.getElementById(id);
    }

    var mediaInput = gid(prefix + '-media-input');
    var mediaPick = gid(prefix + '-media-pick-btn');
    var newPrevWrap = gid(prefix + '-new-media-previews');
    var thumbLabel = gid(prefix + '-thumb-label');
    var videoInput = gid(prefix + '-video-input');
    var videoPick = gid(prefix + '-video-pick-btn');
    var newVideoStrip = gid(prefix + '-new-video-strip');
    var newVideoEl = gid(prefix + '-new-video-el');
    var videoInner = root.querySelector('#' + prefix + '-new-video-strip .social-video-inner');
    var videoPosterRemove = gid(prefix + '-video-poster-remove');
    var posterImg = gid(prefix + '-video-poster-img');
    var posterPlaceholder = gid(prefix + '-video-poster-placeholder');
    var videoThumbLabel = gid(prefix + '-video-thumb-label');
    var videoFileMeta = gid(prefix + '-video-file-meta');

    var imagesOk = !!(mediaInput && newPrevWrap);

    var mediaBuffer = [];
    var videoFile = null;
    var blobUrls = [];
    var videoBlobUrl = null;

    function revokeAllBlobs() {
        blobUrls.forEach(function (u) {
            try {
                URL.revokeObjectURL(u);
            } catch (e) {}
        });
        blobUrls = [];
    }

    function safeSyncMediaFiles() {
        if (!mediaInput || !mediaBuffer.length) {
            return;
        }
        try {
            var dt = new DataTransfer();
            mediaBuffer.forEach(function (f) {
                dt.items.add(f);
            });
            mediaInput.files = dt.files;
        } catch (e) {}
    }

    function safeSyncVideoFile() {
        if (!videoInput) {
            return;
        }
        try {
            var vdt = new DataTransfer();
            if (videoFile) {
                vdt.items.add(videoFile);
            }
            videoInput.files = vdt.files;
        } catch (e) {}
    }

    function renderImageThumbnails() {
        if (!newPrevWrap) {
            return;
        }
        revokeAllBlobs();
        newPrevWrap.innerHTML = '';

        if (thumbLabel) {
            thumbLabel.style.display = mediaBuffer.length ? 'block' : 'none';
        }

        mediaBuffer.forEach(function (file) {
            var cell = document.createElement('div');
            cell.className = 'social-thumb-cell';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'social-thumb-remove';
            btn.innerHTML = '\u00D7';
            btn.title = 'Remove';

            var img = document.createElement('img');
            img.alt = '';

            /* FileReader thumbnails work broadly; on failure fall back to blob URL */
            if (window.FileReader) {
                var fr = new FileReader();
                fr.onload = function (ev) {
                    img.src = ev.target.result;
                };
                fr.onerror = function () {
                    tryFallbackBlob(file, img);
                };
                fr.readAsDataURL(file);
            } else {
                tryFallbackBlob(file, img);
            }

            function tryFallbackBlob(f, imgEl) {
                try {
                    var u = URL.createObjectURL(f);
                    blobUrls.push(u);
                    imgEl.src = u;
                } catch (err) {
                    imgEl.alt = 'No preview';
                    imgEl.style.background = '#eee';
                }
            }

            btn.addEventListener('click', function () {
                mediaBuffer = mediaBuffer.filter(function (x) {
                    return x !== file;
                });
                renderImageThumbnails();
                safeSyncMediaFiles();
            });

            cell.appendChild(btn);
            cell.appendChild(img);
            newPrevWrap.appendChild(cell);
        });
    }

    if (imagesOk && mediaPick && mediaInput) {
        mediaPick.addEventListener('click', function (e) {
            e.preventDefault();
            mediaInput.click();
        });
        mediaInput.addEventListener('change', function () {
            var list = mediaInput.files;
            var i;
            for (i = 0; i < list.length; i++) {
                mediaBuffer.push(list[i]);
            }
            safeSyncMediaFiles();
            renderImageThumbnails();
            setTimeout(function () {
                try {
                    mediaInput.value = '';
                } catch (err) {}
            }, 0);
        });
    }

    function formatBytes(n) {
        if (n === undefined || n === null || isNaN(n)) {
            return '';
        }
        var u = ['B', 'KB', 'MB', 'GB'];
        var i = 0;
        var x = n;
        while (x >= 1024 && i < u.length - 1) {
            x /= 1024;
            i++;
        }
        return (i ? x.toFixed(1) : x) + ' ' + u[i];
    }

    function revokeVideoBlob() {
        if (videoBlobUrl) {
            try {
                URL.revokeObjectURL(videoBlobUrl);
            } catch (e) {}
            videoBlobUrl = null;
        }
    }

    function rebuildPreviewVideoElement() {
        if (!videoInner) {
            return;
        }
        var nv = document.createElement('video');
        nv.id = prefix + '-new-video-el';
        nv.className = 'social-video-preview-el';
        nv.controls = true;
        nv.setAttribute('playsinline', '');
        nv.setAttribute('preload', 'metadata');
        var old = gid(prefix + '-new-video-el');
        if (old && old.parentNode === videoInner) {
            videoInner.replaceChild(nv, old);
        } else {
            videoInner.innerHTML = '';
            videoInner.appendChild(nv);
        }
        newVideoEl = nv;
    }

    function captureVideoPosterFromEl() {
        var vel = newVideoEl;
        if (!posterImg || !posterPlaceholder || !vel) {
            return;
        }
        var safetyTimer;
        function clearSafety() {
            if (safetyTimer) {
                clearTimeout(safetyTimer);
                safetyTimer = null;
            }
        }
        function failPlaceholder(msg) {
            clearSafety();
            posterPlaceholder.textContent = msg || 'No thumbnail';
            posterPlaceholder.style.display = 'flex';
            posterImg.style.display = 'none';
        }
        function drawFrame() {
            try {
                var vw = vel.videoWidth;
                var vh = vel.videoHeight;
                if (!vw || !vh) {
                    failPlaceholder('Play ▶');
                    return;
                }
                var canvas = document.createElement('canvas');
                var tw = 192;
                var th = Math.round((vh / vw) * tw) || 192;
                if (th > 192) {
                    th = 192;
                    tw = Math.round((vw / vh) * th);
                }
                canvas.width = tw;
                canvas.height = th;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(vel, 0, 0, tw, th);
                posterImg.src = canvas.toDataURL('image/jpeg', 0.88);
                posterImg.style.display = 'block';
                posterPlaceholder.style.display = 'none';
                clearSafety();
            } catch (err) {
                failPlaceholder('Play ▶');
            }
        }
        var seekDone = false;
        safetyTimer = setTimeout(function () {
            if (!seekDone && posterPlaceholder && posterPlaceholder.textContent === 'Loading…') {
                seekDone = true;
                vel.removeEventListener('seeked', onSeeked);
                vel.removeEventListener('canplay', onCanPlay);
                failPlaceholder('Play ▶');
            }
        }, 5000);
        function onSeeked() {
            if (seekDone) {
                return;
            }
            seekDone = true;
            clearSafety();
            vel.removeEventListener('seeked', onSeeked);
            drawFrame();
        }
        function onCanPlay() {
            vel.removeEventListener('canplay', onCanPlay);
            try {
                var t = 0.15;
                if (vel.duration && isFinite(vel.duration) && vel.duration > 0) {
                    t = Math.min(1.5, Math.max(0.05, vel.duration * 0.15));
                }
                vel.currentTime = t;
            } catch (err) {
                if (!seekDone) {
                    seekDone = true;
                    clearSafety();
                    drawFrame();
                }
            }
        }
        function onErr() {
            vel.removeEventListener('error', onErr);
            clearSafety();
            failPlaceholder('Cannot preview');
        }
        vel.addEventListener('canplay', onCanPlay);
        vel.addEventListener('seeked', onSeeked);
        vel.addEventListener('error', onErr);
    }

    function showVideoPreview() {
        if (!newVideoStrip || !newVideoEl) {
            return;
        }
        if (!videoFile) {
            newVideoStrip.classList.add('d-none');
            newVideoStrip.style.display = '';
            revokeVideoBlob();
            newVideoEl.removeAttribute('src');
            if (posterImg) {
                posterImg.removeAttribute('src');
                posterImg.style.display = 'none';
            }
            if (posterPlaceholder) {
                posterPlaceholder.textContent = 'Loading…';
                posterPlaceholder.style.display = 'flex';
            }
            if (videoThumbLabel) {
                videoThumbLabel.style.display = 'none';
            }
            if (videoFileMeta) {
                videoFileMeta.textContent = '';
            }
            return;
        }
        if (videoThumbLabel) {
            videoThumbLabel.style.display = 'block';
        }
        newVideoStrip.classList.remove('d-none');
        newVideoStrip.style.display = 'block';
        if (posterPlaceholder) {
            posterPlaceholder.textContent = 'Loading…';
            posterPlaceholder.style.display = 'flex';
        }
        if (posterImg) {
            posterImg.style.display = 'none';
        }
        if (videoFileMeta) {
            videoFileMeta.textContent = videoFile.name + (videoFile.size ? ' · ' + formatBytes(videoFile.size) : '');
        }
        revokeVideoBlob();
        rebuildPreviewVideoElement();
        try {
            videoBlobUrl = URL.createObjectURL(videoFile);
            newVideoEl.src = videoBlobUrl;
            if (typeof newVideoEl.load === 'function') {
                newVideoEl.load();
            }
            captureVideoPosterFromEl();
        } catch (e) {
            if (posterPlaceholder) {
                posterPlaceholder.textContent = 'Error';
                posterPlaceholder.style.display = 'flex';
            }
        }
    }

    if (videoPick && videoInput) {
        videoPick.addEventListener('click', function (e) {
            e.preventDefault();
            videoInput.click();
        });
        videoInput.addEventListener('change', function () {
            if (videoInput.files && videoInput.files[0]) {
                videoFile = videoInput.files[0];
                safeSyncVideoFile();
                showVideoPreview();
                var savedVid = gid(prefix + '-saved-video-wrap');
                if (savedVid) {
                    savedVid.classList.add('d-none');
                }
            }
        });
    }

    function clearSelectedVideo() {
        revokeVideoBlob();
        videoFile = null;
        if (videoInput) {
            try {
                videoInput.value = '';
            } catch (e) {}
        }
        safeSyncVideoFile();
        if (newVideoEl) {
            newVideoEl.removeAttribute('src');
        }
        if (newVideoStrip) {
            newVideoStrip.classList.add('d-none');
            newVideoStrip.style.display = '';
        }
        if (posterImg) {
            posterImg.removeAttribute('src');
            posterImg.style.display = 'none';
        }
        if (posterPlaceholder) {
            posterPlaceholder.textContent = 'Loading…';
            posterPlaceholder.style.display = 'none';
        }
        if (videoThumbLabel) {
            videoThumbLabel.style.display = 'none';
        }
        if (videoFileMeta) {
            videoFileMeta.textContent = '';
        }
    }

    if (videoPosterRemove) {
        videoPosterRemove.addEventListener('click', function () {
            clearSelectedVideo();
        });
    }

    root.querySelectorAll('#' + prefix + '-existing-media .social-img-remove-saved').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var item = btn.closest('.existing-saved-img');
            if (!item) {
                return;
            }
            var hidden = item.querySelector('.remove-media-token');
            if (hidden) {
                hidden.removeAttribute('disabled');
            }
            item.style.opacity = '0.35';
            btn.disabled = true;
        });
    });

    var savedVidBtn = root.querySelector('#' + prefix + '-saved-video-wrap .social-video-remove-saved');
    if (savedVidBtn) {
        savedVidBtn.addEventListener('click', function () {
            var wrap = gid(prefix + '-saved-video-wrap');
            var flag = gid(prefix + '-remove-video-flag');
            if (flag) {
                flag.removeAttribute('disabled');
            }
            if (wrap) {
                wrap.style.opacity = '0.35';
            }
            savedVidBtn.disabled = true;
        });
    }

    var form = (mediaInput && mediaInput.closest('form')) || (videoInput && videoInput.closest('form'));
    if (form) {
        form.addEventListener('submit', function () {
            safeSyncMediaFiles();
            safeSyncVideoFile();
        });
    }
})();
</script>
