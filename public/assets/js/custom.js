/**
 * Custom JavaScript for Gym Management System
 * Compatible with AdminLTE 3
 * 
 * This script waits for jQuery to load before executing
 */

(function() {
    'use strict';
    
    // Function to initialize when jQuery is ready
    function initWhenReady() {
        if (typeof jQuery === 'undefined') {
            // Wait 100ms and try again
            setTimeout(initWhenReady, 100);
            return;
        }
        
        var $ = jQuery;
        
        // Now jQuery is available, initialize everything
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Initialize popovers
            $('[data-toggle="popover"]').popover();
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Confirm delete actions
            $('form[onsubmit*="confirm"]').on('submit', function(e) {
                if (!confirm('Are you sure you want to delete this item?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }
    
    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenReady);
    } else {
        // DOM already loaded, start immediately
        initWhenReady();
    }
    
    /**
     * Format currency
     */
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }
    
    /**
     * Format date
     */
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    /**
     * Format datetime
     */
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    /**
     * Show loading spinner
     */
    function showLoading() {
        if (typeof jQuery !== 'undefined') {
            jQuery('body').append('<div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div class="spinner-border text-light" role="status"><span class="sr-only">Loading...</span></div></div>');
        }
    }
    
    /**
     * Hide loading spinner
     */
    function hideLoading() {
        if (typeof jQuery !== 'undefined') {
            jQuery('#loading-overlay').remove();
        }
    }
    
    /**
     * AJAX form submission helper
     */
    function submitFormAjax(formSelector, successCallback, errorCallback) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is required for submitFormAjax');
            return;
        }
        
        var $ = jQuery;
        
        $(formSelector).on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const formData = new FormData(this);
            const url = form.attr('action');
            const method = form.find('input[name="_method"]').val() || form.attr('method') || 'POST';
            
            showLoading();
            
            $.ajax({
                url: url,
                type: method,
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    if (successCallback) {
                        successCallback(response);
                    } else {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    if (errorCallback) {
                        errorCallback(xhr);
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });
    }
    
    /**
     * DataTable initialization helper (if DataTables is included)
     */
    function initDataTable(tableSelector, options) {
        options = options || {};
        if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
            var $ = jQuery;
            const defaultOptions = {
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[0, 'desc']],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            };
            
            return $(tableSelector).DataTable($.extend(defaultOptions, options));
        }
    }
    
    // Export functions for use in other scripts
    window.GymManagement = {
        formatCurrency: formatCurrency,
        formatDate: formatDate,
        formatDateTime: formatDateTime,
        showLoading: showLoading,
        hideLoading: hideLoading,
        submitFormAjax: submitFormAjax,
        initDataTable: initDataTable
    };
})();
