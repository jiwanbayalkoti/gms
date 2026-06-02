@extends('layouts.app')

@section('title', 'Settings')

@php
    $metaConnected = !empty($settings->facebook_page_access_token) && !empty($settings->facebook_page_id);
    $youtubeConnected = !empty($settings->youtube_refresh_token) || !empty($settings->youtube_access_token);
    $smsProvider = old('sms_provider', $settings->sms_provider ?? 'twilio');
@endphp

@include('settings.partials.styles')

@section('content')
<div class="container-fluid settings-page">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-1">Gym Settings</h2>
            <p class="text-muted small mb-0 d-none d-lg-block">Settings are organized in sections below. Use the menu to jump to each block.</p>
            <p class="text-muted small mb-0 d-lg-none">Tap a section at the top to jump. Swipe the menu if needed.</p>
            @if(isset($gyms) && $gyms->isNotEmpty())
                <form method="GET" action="{{ route('settings.index') }}" class="form-inline mt-2">
                    <label for="settings_gym_id" class="mr-2 font-weight-bold">Gym:</label>
                    <select name="gym_id" id="settings_gym_id" class="form-control form-control-sm" onchange="this.form.submit()">
                        @foreach($gyms as $gym)
                            <option value="{{ $gym->id }}" {{ (int)($settingsGymId ?? 0) === (int)$gym->id ? 'selected' : '' }}>{{ $gym->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    @endif

    <form id="settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @if(!empty($settingsGymId))<input type="hidden" name="gym_id" value="{{ $settingsGymId }}">@endif
        <div class="row">
        <div class="col-lg-3 mb-3 d-none d-lg-block">
            @include('settings.partials.section-nav', ['variant' => 'desktop'])
        </div>
        <div class="col-lg-9">
            @include('settings.partials.section-nav', ['variant' => 'mobile'])
            <div class="card settings-section-card" id="section-profile">
                <div class="card-header"><i class="fas fa-building text-primary"></i> Gym Profile</div>
                <div class="card-body">
                        <div class="form-group">
                            <label for="gym_name">Gym Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('gym_name') is-invalid @enderror" id="gym_name" name="gym_name" value="{{ old('gym_name', $settings->gym_name ?? '') }}" required>
                            @error('gym_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $settings->email ?? '') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $settings->phone ?? '') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $settings->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="logo">Logo</label>
                            <div id="logo-preview-container">
                                @if($settings->logo ?? null)
                                    <div class="mb-2 logo-preview-container">
                                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="img-thumbnail" width="100">
                                    </div>
                                @endif
                            </div>
                            <input type="file" class="form-control-file @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primary_color">Primary Color <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control @error('primary_color') is-invalid @enderror" id="primary_color" name="primary_color" value="{{ old('primary_color', $settings->primary_color ?? '#007bff') }}" required>
                                    @error('primary_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="secondary_color">Secondary Color <span class="text-danger">*</span></label>
                                    <input type="color" class="form-control @error('secondary_color') is-invalid @enderror" id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $settings->secondary_color ?? '#6c757d') }}" required>
                                    @error('secondary_color')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="footer_text">Footer Text</label>
                            <input type="text" class="form-control @error('footer_text') is-invalid @enderror" id="footer_text" name="footer_text" value="{{ old('footer_text', $settings->footer_text ?? '') }}">
                            @error('footer_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="card settings-section-card" id="section-features">
                    <div class="card-header"><i class="fas fa-toggle-on text-success"></i> Features & Email</div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_online_booking" name="enable_online_booking" value="1" {{ old('enable_online_booking', $settings->enable_online_booking ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_online_booking">
                                    Enable Online Booking
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_online_payments" name="enable_online_payments" value="1" {{ old('enable_online_payments', $settings->enable_online_payments ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_online_payments">
                                    Enable Online Payments
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_sms_notifications" name="enable_sms_notifications" value="1" {{ old('enable_sms_notifications', $settings->enable_sms_notifications ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_sms_notifications">
                                    Enable SMS Notifications
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card settings-section-card" id="section-sms">
                    <div class="card-header"><i class="fas fa-sms text-warning"></i> SMS Configuration</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="sms_provider">SMS Provider <span class="text-danger">*</span></label>
                            <select class="form-control @error('sms_provider') is-invalid @enderror" id="sms_provider" name="sms_provider" onchange="toggleSmsProviderFields()">
                                <option value="twilio" {{ old('sms_provider', $settings->sms_provider ?? 'twilio') == 'twilio' ? 'selected' : '' }}>Twilio (International - Free $15.50 credit)</option>
                                <option value="sparrow" {{ old('sms_provider', $settings->sms_provider ?? 'twilio') == 'sparrow' ? 'selected' : '' }}>Sparrow SMS (Nepal - Recommended for Nepal)</option>
                                <option value="textlocal" {{ old('sms_provider', $settings->sms_provider ?? 'twilio') == 'textlocal' ? 'selected' : '' }}>TextLocal (India/Nepal - Closed)</option>
                            </select>
                            <small class="form-text text-muted">Choose your SMS service provider</small>
                            @error('sms_provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Twilio Settings --}}
                        <div id="twilio_settings" style="display: {{ old('sms_provider', $settings->sms_provider ?? 'twilio') == 'twilio' ? 'block' : 'none' }};">
                            <hr>
                            <h5>Twilio Settings</h5>
                            
                            <div class="form-group">
                                <label for="twilio_account_sid">Twilio Account SID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('twilio_account_sid') is-invalid @enderror" id="twilio_account_sid" name="twilio_account_sid" value="{{ old('twilio_account_sid', $settings->twilio_account_sid ?? '') }}" placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                                <small class="form-text text-muted">
                                    <strong>How to get:</strong><br>
                                    1. Sign up at <a href="https://www.twilio.com/try-twilio" target="_blank">Twilio.com</a> (free trial credit available)<br>
                                    2. Copy <strong>Account SID</strong> from the dashboard<br>
                                    3. Paste it here
                                </small>
                                @error('twilio_account_sid')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="twilio_auth_token">Twilio Auth Token <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('twilio_auth_token') is-invalid @enderror" id="twilio_auth_token" name="twilio_auth_token" value="{{ old('twilio_auth_token', $settings->twilio_auth_token ?? '') }}" placeholder="Your Auth Token">
                                <small class="form-text text-muted">In Twilio Dashboard, open <strong>Auth Token</strong> (click to reveal)</small>
                                @error('twilio_auth_token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="twilio_from_number">Twilio Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('twilio_from_number') is-invalid @enderror" id="twilio_from_number" name="twilio_from_number" value="{{ old('twilio_from_number', $settings->twilio_from_number ?? '') }}" placeholder="+1234567890">
                                <small class="form-text text-muted">
                                    Twilio Dashboard → <strong>"Phone Numbers"</strong> → Get a number (Free trial number available)<br>
                                    Format: <strong>+[country code][number]</strong> (e.g., +14155552671)
                                </small>
                                @error('twilio_from_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Sparrow SMS Settings --}}
                        <div id="sparrow_settings" style="display: {{ old('sms_provider', $settings->sms_provider ?? 'twilio') == 'sparrow' ? 'block' : 'none' }};">
                            <hr>
                            <h5>Sparrow SMS Settings (Nepal)</h5>
                            
                            <div class="form-group">
                                <label for="sparrow_sms_token">Sparrow SMS Token <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sparrow_sms_token') is-invalid @enderror" id="sparrow_sms_token" name="sparrow_sms_token" value="{{ old('sparrow_sms_token', $settings->sparrow_sms_token ?? '') }}" placeholder="Enter your Sparrow SMS Token">
                                <small class="form-text text-muted">
                                    <strong>How to get:</strong><br>
                                    1. Sign up at <a href="https://sparrowsms.com" target="_blank">SparrowSMS.com</a><br>
                                    2. Copy <strong>API Token</strong> from the dashboard<br>
                                    3. Paste it here<br>
                                    <span class="text-info"><i class="fas fa-info-circle"></i> Pricing: NPR 0.50–1.50 per SMS</span>
                                </small>
                                @error('sparrow_sms_token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="sparrow_sms_from">Sparrow SMS From (Sender ID) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sparrow_sms_from') is-invalid @enderror" id="sparrow_sms_from" name="sparrow_sms_from" value="{{ old('sparrow_sms_from', $settings->sparrow_sms_from ?? 'SMS') }}" placeholder="SMS" maxlength="11">
                                <small class="form-text text-muted">
                                    <strong>Sender ID:</strong> Your registered Sparrow SMS sender ID (max 11 characters)<br>
                                    <span class="text-info">Default: <strong>SMS</strong> (for testing)</span>
                                </small>
                                @error('sparrow_sms_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- TextLocal Settings --}}
                        <div id="textlocal_settings" style="display: {{ old('sms_provider', $settings->sms_provider ?? 'twilio') == 'textlocal' ? 'block' : 'none' }};">
                            <hr>
                            <h5>TextLocal Settings (Service Closed)</h5>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> <strong>TextLocal India closed on October 31, 2025.</strong> Please use Twilio instead.
                            </div>

                            <div class="form-group">
                                <label for="textlocal_api_key">TextLocal API Key</label>
                                <input type="text" class="form-control @error('textlocal_api_key') is-invalid @enderror" id="textlocal_api_key" name="textlocal_api_key" value="{{ old('textlocal_api_key', $settings->textlocal_api_key ?? '') }}" placeholder="Enter your TextLocal API Key" disabled>
                                <small class="form-text text-muted">TextLocal service is no longer available</small>
                                @error('textlocal_api_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="textlocal_sender_id">TextLocal Sender ID</label>
                                <input type="text" class="form-control @error('textlocal_sender_id') is-invalid @enderror" id="textlocal_sender_id" name="textlocal_sender_id" value="{{ old('textlocal_sender_id', $settings->textlocal_sender_id ?? 'TXTLCL') }}" placeholder="TXTLCL" maxlength="6" disabled>
                                <small class="form-text text-muted">TextLocal service is no longer available</small>
                                @error('textlocal_sender_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_email_notifications" name="enable_email_notifications" value="1" {{ old('enable_email_notifications', $settings->enable_email_notifications ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_email_notifications">
                                    Enable Email Notifications
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

                @include('settings.partials.section-meta')
                @include('settings.partials.section-youtube')

                <div class="card settings-section-card" id="section-pause">
                    <div class="card-header"><i class="fas fa-pause-circle text-secondary"></i> Membership Pause</div>
                    <div class="card-body">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enable_pause_feature" name="enable_pause_feature" value="1" {{ old('enable_pause_feature', $settings->enable_pause_feature ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_pause_feature">
                                    Enable Membership Pause Feature
                                </label>
                            </div>
                            <small class="form-text text-muted">Allow members to request pause/extension of their membership plan</small>
                        </div>

                        <div class="form-group">
                            <label for="minimum_pause_days">Minimum Pause Days <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('minimum_pause_days') is-invalid @enderror" id="minimum_pause_days" name="minimum_pause_days" value="{{ old('minimum_pause_days', $settings->minimum_pause_days ?? 7) }}" min="1" required>
                            @error('minimum_pause_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Minimum number of days required for a pause request</small>
                        </div>

                    </div>
                </div>

                <div class="settings-save-bar d-flex flex-wrap align-items-center justify-content-between">
                    <span class="text-muted small mb-2 mb-md-0">All sections are saved together when you click Update.</span>
                    <button type="submit" class="btn btn-primary btn-lg mb-0"><i class="fas fa-save mr-1"></i> Update Settings</button>
                </div>
            </div>
        </div>
    </form>
</div>
@include('settings.partials.scripts')

@endsection

