@push('scripts')
<script>
function toggleSmsProviderFields() {
    var provider = document.getElementById('sms_provider').value;
    ['twilio_settings', 'sparrow_settings', 'textlocal_settings'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
    });
    var active = document.getElementById(provider + '_settings');
    if (active) active.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function() {
    toggleSmsProviderFields();

    var navLinks = document.querySelectorAll('.settings-nav-link');
    var sections = document.querySelectorAll('.settings-section-card');
    var mobileNav = document.querySelector('.settings-nav-mobile');
    var isClickScrolling = false;
    var clickScrollTimer = null;

    function getScrollOffset() {
        var mobileBar = mobileNav ? mobileNav.offsetHeight : 0;
        var base = window.matchMedia('(max-width: 991.98px)').matches ? 12 : 100;
        return base + mobileBar;
    }

    function scrollActivePillIntoView(link) {
        if (!link || !link.classList.contains('settings-nav-pill')) return;
        var row = link.closest('.settings-nav-mobile-scroll');
        if (!row) return;
        var linkLeft = link.offsetLeft;
        var linkWidth = link.offsetWidth;
        var rowWidth = row.clientWidth;
        var target = linkLeft - (rowWidth / 2) + (linkWidth / 2);
        row.scrollTo({ left: Math.max(0, target), behavior: 'smooth' });
    }

    function scrollToSection(target, sectionId) {
        if (!target) return;
        var top = target.getBoundingClientRect().top + window.pageYOffset - getScrollOffset();
        window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    }

    function setActiveSection(sectionId) {
        if (!sectionId) return;
        var activeLink = null;
        navLinks.forEach(function(link) {
            var match = link.getAttribute('href') === '#' + sectionId;
            link.classList.toggle('active', match);
            if (match) activeLink = link;
        });
        sections.forEach(function(section) {
            section.classList.toggle('is-active', section.id === sectionId);
        });
        scrollActivePillIntoView(activeLink);
    }

    function getActiveSectionId() {
        var offset = getScrollOffset();
        var currentId = sections[0] ? sections[0].id : null;
        sections.forEach(function(section) {
            var rect = section.getBoundingClientRect();
            if (rect.top <= offset) {
                currentId = section.id;
            }
        });
        return currentId;
    }

    function updateActiveFromScroll() {
        if (isClickScrolling) return;
        setActiveSection(getActiveSectionId());
    }

    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var href = link.getAttribute('href');
            if (!href || href.charAt(0) !== '#') return;
            var id = href.slice(1);
            var target = document.getElementById(id);
            if (!target) return;

            setActiveSection(id);
            isClickScrolling = true;
            clearTimeout(clickScrollTimer);

            scrollToSection(target, id);

            clickScrollTimer = setTimeout(function() {
                isClickScrolling = false;
                setActiveSection(id);
            }, 700);
        });
    });

    window.addEventListener('scroll', function() {
        if (isClickScrolling) return;
        window.requestAnimationFrame(updateActiveFromScroll);
    }, { passive: true });

    setActiveSection(getActiveSectionId());

    var inspectBtn = document.getElementById('inspect-meta-token-btn');
    if (inspectBtn) {
        inspectBtn.addEventListener('click', function() {
            var resultEl = document.getElementById('inspect-meta-token-result');
            resultEl.className = 'form-text d-block mt-2 text-muted';
            resultEl.textContent = 'Inspecting…';
            fetch('{{ route("settings.meta-debug-token") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({})
            }).then(function(r) { return r.json(); }).then(function(payload) {
                resultEl.className = 'form-text d-block mt-2 ' + (payload.success && !(payload.message || '').includes('still includes') ? 'text-success' : 'text-danger');
                resultEl.textContent = payload.message || 'Done';
            }).catch(function() {
                resultEl.className = 'form-text d-block mt-2 text-danger';
                resultEl.textContent = 'Unable to inspect token.';
            });
        });
    }

    var testBtn = document.getElementById('test-facebook-btn');
    if (testBtn) {
        testBtn.addEventListener('click', function() {
            var resultEl = document.getElementById('test-facebook-result');
            resultEl.className = 'form-text text-muted';
            resultEl.textContent = 'Testing...';
            fetch('{{ route("settings.test-facebook") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({})
            }).then(function(r) { return r.json(); }).then(function(payload) {
                resultEl.className = 'form-text d-block ' + (payload.success ? 'text-success' : 'text-danger');
                resultEl.textContent = payload.success ? payload.message + ' Page: ' + (payload.data && payload.data.name ? payload.data.name : 'N/A') : (payload.message || 'Failed');
            }).catch(function() {
                resultEl.className = 'form-text text-danger';
                resultEl.textContent = 'Unable to test Facebook.';
            });
        });
    }

    var testYtBtn = document.getElementById('test-youtube-btn');
    if (testYtBtn) {
        testYtBtn.addEventListener('click', function() {
            var resultEl = document.getElementById('test-youtube-result');
            resultEl.className = 'form-text d-block mt-2 text-muted';
            resultEl.textContent = 'Testing...';
            fetch('{{ route("settings.test-youtube") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({})
            }).then(function(r) { return r.json(); }).then(function(payload) {
                resultEl.className = 'form-text d-block mt-2 ' + (payload.success ? 'text-success' : 'text-danger');
                resultEl.textContent = payload.message || (payload.success ? 'OK' : 'Failed');
            }).catch(function() {
                resultEl.className = 'form-text d-block mt-2 text-danger';
                resultEl.textContent = 'Unable to test YouTube.';
            });
        });
    }
});
</script>
@endpush
