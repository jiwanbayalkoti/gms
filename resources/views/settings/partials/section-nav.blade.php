@php
    $settingsNavItems = [
        ['id' => 'section-profile', 'label' => 'Gym Profile', 'short' => 'Profile'],
        ['id' => 'section-features', 'label' => 'Features', 'short' => 'Features'],
        ['id' => 'section-sms', 'label' => 'SMS', 'short' => 'SMS'],
        ['id' => 'section-meta', 'label' => 'Facebook / IG', 'short' => 'Meta'],
        ['id' => 'section-youtube', 'label' => 'YouTube', 'short' => 'YouTube'],
        ['id' => 'section-pause', 'label' => 'Pause', 'short' => 'Pause'],
    ];
    $navVariant = $variant ?? 'desktop';
@endphp

@if($navVariant === 'mobile')
    <nav class="settings-nav-mobile d-lg-none" aria-label="Settings sections">
        <div class="settings-nav-mobile-scroll">
            @foreach($settingsNavItems as $item)
                <a href="#{{ $item['id'] }}"
                   class="settings-nav-link settings-nav-pill">{{ $item['short'] }}</a>
            @endforeach
        </div>
    </nav>
@else
    <nav class="settings-nav d-none d-lg-block" aria-label="Settings sections">
        <div class="list-group list-group-flush shadow-sm rounded">
            @foreach($settingsNavItems as $loopIndex => $item)
                <a href="#{{ $item['id'] }}"
                   class="list-group-item list-group-item-action settings-nav-link {{ $loopIndex === 0 ? 'active' : '' }}">{{ $item['label'] }}</a>
            @endforeach
        </div>
    </nav>
@endif
