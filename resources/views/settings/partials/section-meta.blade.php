<div class="card settings-section-card" id="section-meta">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
        <span><i class="fab fa-facebook text-primary"></i> Facebook & Instagram</span>
        @if($metaConnected)
            <span class="badge badge-success">Connected</span>
        @else
            <span class="badge badge-secondary">Not connected</span>
        @endif
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Each gym has its own connection. Save App ID and Secret, then click Connect.</p>

        @if(!empty($metaOAuthRedirectUri))
            @if(!empty($metaUrlHostMismatch))
                <div class="alert alert-danger small mb-3">
                    <strong>URL mismatch.</strong> You opened this site as <code>{{ $metaBrowserOrigin ?? request()->getSchemeAndHttpHost() }}</code>
                    but <code>.env</code> has <code>APP_URL={{ config('app.url') }}</code>.
                    Meta will reject Connect until both match. Either always use the same address in the browser, or set
                    <code>APP_URL={{ $metaBrowserOrigin ?? '' }}</code> and add that domain in Meta (below).
                </div>
            @endif

            <div class="alert alert-info border small mb-3">
                <strong class="d-block mb-1">Fix “Can't load URL / domain isn't included”</strong>
                <p class="mb-2">Open <a href="https://developers.facebook.com/apps/" target="_blank" rel="noopener">Meta for Developers</a> → your app. Add <strong>Facebook Login</strong> product if missing.</p>

                <p class="mb-1"><strong>1. App settings → Basic → App Domains</strong> (no <code>http://</code>, no port):</p>
                <ul class="mb-2 pl-3">
                    @foreach(($metaAppDomains ?? []) as $domain)
                        <li><code>{{ $domain }}</code></li>
                    @endforeach
                </ul>

                <p class="mb-1"><strong>2. Facebook Login → Settings</strong></p>
                <ul class="mb-2 pl-3">
                    <li>Client OAuth login: <strong>Yes</strong></li>
                    <li>Web OAuth login: <strong>Yes</strong></li>
                    <li><strong>Valid OAuth Redirect URIs</strong> — use HTTPS and add this exact line (copy all):</li>
                </ul>
                <p class="mb-2 p-2 bg-white border rounded">
                    <code id="meta-oauth-redirect-uri-active" style="word-break: break-all;">{{ $metaOAuthRedirectUri }}</code>
                    <button type="button" class="btn btn-xs btn-outline-secondary ml-1" onclick="navigator.clipboard.writeText(document.getElementById('meta-oauth-redirect-uri-active').textContent)">Copy</button>
                </p>
                @php
                    $alternateMetaUris = collect($metaOAuthRedirectUris ?? [])->reject(fn ($uri) => $uri === $metaOAuthRedirectUri)->values();
                @endphp
                @if($alternateMetaUris->isNotEmpty())
                    <p class="mb-1 text-muted">If you use another public HTTPS URL, add these too in the same field:</p>
                    <ul class="mb-2 pl-3 small text-muted">
                        @foreach($alternateMetaUris as $uri)
                            <li><code style="word-break: break-all;">{{ $uri }}</code></li>
                        @endforeach
                    </ul>
                @endif

                <p class="mb-1"><strong>3. Settings → Basic → Website</strong> (add platform “Website” if needed):</p>
                <p class="mb-0"><code>{{ rtrim(config('app.url'), '/') }}</code> /</p>
            </div>
        @endif

        <div class="form-group">
            <label for="facebook_app_id">Meta App ID</label>
            <input type="text" class="form-control" id="facebook_app_id" name="facebook_app_id" value="{{ old('facebook_app_id', $settings->facebook_app_id ?? '') }}">
        </div>
        <div class="form-group">
            <label for="facebook_app_secret">Meta App Secret</label>
            <input type="text" class="form-control" id="facebook_app_secret" name="facebook_app_secret" value="{{ old('facebook_app_secret', $settings->facebook_app_secret ?? '') }}">
        </div>
        @if($metaConnected)
            <p class="small text-warning border rounded p-2 bg-light mb-2 mb-md-3">
                If publish or Test shows <strong>token expired (code 190)</strong>, click <strong>Connect with Meta</strong> again — no need to re-enter App ID/Secret.
            </p>
            <p class="small text-muted border rounded p-2 bg-light mb-3">
                Page: <code>{{ $settings->facebook_page_id }}</code>
                @if(!empty($settings->instagram_business_account_id))
                    · IG: <code>{{ $settings->instagram_business_account_id }}</code>
                @endif
            </p>
        @endif
        <div class="form-group mb-0">
            <a href="{{ route('settings.connect-meta', !empty($settingsGymId) ? ['gym_id' => $settingsGymId] : []) }}" class="btn btn-outline-primary btn-sm">
                <i class="fab fa-facebook"></i> Connect with Meta
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm ml-1" id="inspect-meta-token-btn">Inspect</button>
            <button type="button" class="btn btn-outline-info btn-sm ml-1" id="test-facebook-btn">Test</button>
            <small id="inspect-meta-token-result" class="form-text d-block mt-2"></small>
            <small id="test-facebook-result" class="form-text d-block"></small>
        </div>
    </div>
</div>
