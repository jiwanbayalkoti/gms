@push('styles')
<style>
    .settings-page {
        scroll-behavior: smooth;
    }
    .settings-page .settings-nav {
        position: sticky;
        top: 1rem;
    }
    .settings-page .settings-nav .list-group-item {
        border-left: 3px solid transparent;
        padding: 0.65rem 1rem;
        font-size: 0.9rem;
        color: #495057;
        transition: background-color 0.25s ease, border-color 0.25s ease, color 0.25s ease, padding-left 0.25s ease;
    }
    .settings-page .settings-nav .list-group-item:hover {
        background-color: rgba(0, 123, 255, 0.05);
        color: #007bff;
    }
    .settings-page .settings-nav .list-group-item.active {
        border-left-color: #007bff;
        border-left-width: 4px;
        padding-left: calc(1rem - 1px);
        background-color: rgba(0, 123, 255, 0.12);
        color: #007bff;
        font-weight: 600;
        box-shadow: inset 0 0 0 1px rgba(0, 123, 255, 0.15);
    }
    .settings-page .settings-section-card {
        scroll-margin-top: 5.5rem;
        margin-bottom: 1.25rem;
        border: 2px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
    }
    .settings-page .settings-section-card.is-active {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.18), 0 6px 20px rgba(0, 123, 255, 0.12);
        transform: translateY(-1px);
    }
    .settings-page .settings-section-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        font-weight: 600;
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    .settings-page .settings-section-card.is-active .card-header {
        background: linear-gradient(90deg, rgba(0, 123, 255, 0.14) 0%, rgba(0, 123, 255, 0.06) 100%);
        color: #004085;
        border-bottom-color: rgba(0, 123, 255, 0.25);
    }
    .settings-page .settings-section-card .card-header i {
        width: 1.25rem;
        margin-right: 0.35rem;
    }
    .settings-page .settings-sub-card {
        border: 1px dashed #dee2e6;
        background: #fafbfc;
    }
    .settings-page .settings-save-bar {
        position: sticky;
        bottom: 0;
        z-index: 10;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 0.25rem;
        padding: 1rem 1.25rem;
        margin-top: 0.5rem;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.06);
    }
    .settings-page .settings-nav-mobile {
        position: sticky;
        top: 0;
        z-index: 1030;
        margin: 0 -0.75rem 1rem;
        padding: 0.5rem 0.75rem;
        background: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    }
    .settings-page .settings-nav-mobile-scroll {
        display: flex;
        flex-wrap: nowrap;
        gap: 0.5rem;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
    }
    .settings-page .settings-nav-mobile-scroll::-webkit-scrollbar {
        display: none;
    }
    .settings-page .settings-nav-pill {
        flex: 0 0 auto;
        display: inline-block;
        padding: 0.45rem 0.85rem;
        font-size: 0.8125rem;
        font-weight: 600;
        line-height: 1.2;
        color: #495057;
        background: #f1f3f5;
        border: 1px solid #dee2e6;
        border-radius: 2rem;
        text-decoration: none !important;
        white-space: nowrap;
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
    }
    .settings-page .settings-nav-pill:hover,
    .settings-page .settings-nav-pill:focus {
        color: #007bff;
        background: rgba(0, 123, 255, 0.08);
        border-color: rgba(0, 123, 255, 0.35);
    }
    .settings-page .settings-nav-pill.active {
        color: #fff;
        background: #007bff;
        border-color: #007bff;
        box-shadow: 0 2px 6px rgba(0, 123, 255, 0.35);
    }
    @media (max-width: 991.98px) {
        .settings-page .settings-section-card {
            scroll-margin-top: 4.25rem;
        }
        .settings-page .settings-section-card.is-active {
            transform: none;
        }
        .settings-page .settings-save-bar {
            padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
        }
    }
</style>
@endpush
