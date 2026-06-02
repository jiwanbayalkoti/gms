/**
 * Legacy / optional loader — thumbnail UI now runs inline in
 * resources/views/social-media/partials/attachment-uploads.blade.php (FileReader + strip styles).
 * Kept for reference; index/edit no longer include this file by default.
 */
(function () {
    'use strict';

    function safeSyncMediaFiles(mediaInput, mediaBuffer) {
        if (!mediaInput || !mediaBuffer.length) {
            return;
        }
        try {
            var dt = new DataTransfer();
            mediaBuffer.forEach(function (f) {
                dt.items.add(f);
            });
            mediaInput.files = dt.files;
        } catch (e) {
            /* Ignore — previews still work; submission may rely on browser support */
        }
    }

    function safeSyncVideoFile(videoInput, videoFile) {
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

    function wireRoot(root) {
        if (!root || root.getAttribute('data-social-wired') === '1') {
            return;
        }

        var prefix = root.getAttribute('data-prefix');
        if (!prefix) {
            return;
        }

        var mediaInput = root.querySelector('#' + prefix + '-media-input');
        var mediaPick = root.querySelector('#' + prefix + '-media-pick-btn');
        var newPrevWrap = root.querySelector('#' + prefix + '-new-media-previews');
        var videoInput = root.querySelector('#' + prefix + '-video-input');
        var videoPick = root.querySelector('#' + prefix + '-video-pick-btn');
        var newVideoWrap = root.querySelector('#' + prefix + '-new-video-wrap');
        var newVideoEl = root.querySelector('#' + prefix + '-new-video-el');
        var newVideoRemove = root.querySelector('#' + prefix + '-new-video-remove');

        if (!mediaInput || !newPrevWrap) {
            return;
        }

        root.setAttribute('data-social-wired', '1');

        var mediaBuffer = [];
        var videoFile = null;

        function renderNewMediaPreviews() {
            newPrevWrap.innerHTML = '';
            mediaBuffer.forEach(function (file) {
                var url = URL.createObjectURL(file);
                var wrap = document.createElement('div');
                wrap.className = 'position-relative border rounded p-1 bg-white';

                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-danger btn-sm position-absolute';
                btn.style.cssText = 'top:-8px;right:-8px;width:28px;height:28px;padding:0;line-height:1;border-radius:50%;z-index:2;';
                btn.innerHTML = '\u00D7';
                btn.title = 'Remove';

                var img = document.createElement('img');
                img.alt = '';
                img.style.cssText = 'height:88px;width:auto;max-width:130px;object-fit:cover;display:block;';
                img.src = url;

                btn.addEventListener('click', function () {
                    URL.revokeObjectURL(url);
                    mediaBuffer = mediaBuffer.filter(function (f) {
                        return f !== file;
                    });
                    safeSyncMediaFiles(mediaInput, mediaBuffer);
                    renderNewMediaPreviews();
                });

                wrap.appendChild(btn);
                wrap.appendChild(img);
                newPrevWrap.appendChild(wrap);
            });
        }

        if (mediaPick) {
            mediaPick.addEventListener('click', function (e) {
                e.preventDefault();
                mediaInput.click();
            });
        }

        mediaInput.addEventListener('change', function () {
            var list = mediaInput.files;
            var i;
            for (i = 0; i < list.length; i++) {
                mediaBuffer.push(list[i]);
            }
            safeSyncMediaFiles(mediaInput, mediaBuffer);
            renderNewMediaPreviews();
            /* Delay clearing so FileList sync completes in all browsers */
            setTimeout(function () {
                try {
                    mediaInput.value = '';
                } catch (err) {}
            }, 0);
        });

        function showNewVideoPreview() {
            if (!newVideoWrap || !newVideoEl) {
                return;
            }
            if (!videoFile) {
                newVideoWrap.classList.add('d-none');
                newVideoWrap.style.display = '';
                newVideoEl.removeAttribute('src');
                return;
            }
            newVideoWrap.classList.remove('d-none');
            newVideoWrap.style.display = 'inline-block';
            if (newVideoEl.src && newVideoEl.src.indexOf('blob:') === 0) {
                URL.revokeObjectURL(newVideoEl.src);
            }
            newVideoEl.src = URL.createObjectURL(videoFile);
            if (typeof newVideoEl.load === 'function') {
                newVideoEl.load();
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
                    safeSyncVideoFile(videoInput, videoFile);
                    showNewVideoPreview();
                    var savedVid = document.getElementById(prefix + '-saved-video-wrap');
                    if (savedVid) {
                        savedVid.classList.add('d-none');
                    }
                }
            });
        }

        if (newVideoRemove && newVideoEl && videoInput && newVideoWrap) {
            newVideoRemove.addEventListener('click', function () {
                if (newVideoEl.src && newVideoEl.src.indexOf('blob:') === 0) {
                    URL.revokeObjectURL(newVideoEl.src);
                }
                videoFile = null;
                videoInput.value = '';
                safeSyncVideoFile(videoInput, null);
                newVideoEl.removeAttribute('src');
                newVideoWrap.classList.add('d-none');
                newVideoWrap.style.display = '';
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
                var wrap = document.getElementById(prefix + '-saved-video-wrap');
                var flag = document.getElementById(prefix + '-remove-video-flag');
                if (flag) {
                    flag.removeAttribute('disabled');
                }
                if (wrap) {
                    wrap.style.opacity = '0.35';
                }
                savedVidBtn.disabled = true;
            });
        }

        var form = mediaInput.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                safeSyncMediaFiles(mediaInput, mediaBuffer);
                safeSyncVideoFile(videoInput, videoFile);
            });
        }
    }

    function initAll() {
        document.querySelectorAll('.social-attachment-uploads').forEach(function (root) {
            wireRoot(root);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
