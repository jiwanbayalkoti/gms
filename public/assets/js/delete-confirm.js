/**
 * Delete Confirmation Handler
 * Uses styled Bootstrap modal for delete confirmations
 */

(function() {
    'use strict';
    
    function initWhenReady() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initWhenReady, 100);
            return;
        }
        
        var $ = jQuery;
        
        // Store delete context
        var deleteContext = {
            url: null,
            btn: null,
            row: null,
            rowId: null,
            itemName: null,
            itemType: null,
            originalHtml: null
        };
        
        // Initialize delete confirmation with styled modal
        $(document).ready(function() {
            // Handle delete button clicks
            $(document).on('click', '[data-delete-url]', function(e) {
                e.preventDefault();
                
                var $btn = $(this);
                var deleteUrl = $btn.data('delete-url');
                var itemName = $btn.data('delete-name') || 'this item';
                var itemType = $btn.data('delete-type') || 'item';
                var $row = $btn.closest('tr, .card, .list-group-item');
                var rowId = $btn.data('delete-row-id');
                
                // Store context
                deleteContext.url = deleteUrl;
                deleteContext.btn = $btn;
                deleteContext.row = $row;
                deleteContext.rowId = rowId;
                deleteContext.itemName = itemName;
                deleteContext.itemType = itemType;
                deleteContext.originalHtml = $btn.html();
                
                // Update modal message
                var message = 'Are you sure you want to delete "' + itemName + '"?';
                $('#deleteConfirmMessage').html(
                    '<strong>' + message + '</strong><br><br>' +
                    'This action cannot be undone. All associated data will be permanently deleted.'
                );
                
                // Show modal
                $('#deleteConfirmModal').modal('show');
            });
            
            // Handle confirm delete button click
            $(document).on('click', '#deleteConfirmButton', function() {
                var $confirmBtn = $(this);
                var originalBtnHtml = $confirmBtn.html();
                
                // Disable button and show loading
                $confirmBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...');
                
                // Perform delete using API
                var deleteUrl = deleteContext.url;
                
                // Convert web route to API route if needed
                if (typeof window.API !== 'undefined') {
                    deleteUrl = window.API.convertRoute(deleteUrl);
                } else if (!deleteUrl.startsWith('/api/')) {
                    // Fallback: try to convert manually
                    deleteUrl = deleteUrl.replace(/^\//, '').replace(/\/\d+$/, '');
                    deleteUrl = '/api/v1/' + deleteUrl + '/' + (deleteContext.url.match(/\/(\d+)$/)?.[1] || '');
                }
                
                // Use API helper if available, otherwise fallback to jQuery AJAX
                if (typeof window.API !== 'undefined' && window.API.delete) {
                    window.API.delete(deleteUrl)
                        .then(function(response) {
                            handleDeleteSuccess(response);
                        })
                        .catch(function(error) {
                            handleDeleteError(error);
                        });
                } else {
                    // Fallback to jQuery AJAX
                    $.ajax({
                        url: deleteUrl,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            handleDeleteSuccess(response);
                        },
                        error: function(xhr) {
                            handleDeleteError(xhr.responseJSON || { message: 'Error deleting item' });
                        }
                    });
                }
                
                function handleDeleteSuccess(response) {
                        // Hide modal
                        $('#deleteConfirmModal').modal('hide');
                        
                        // Reset confirm button
                        $confirmBtn.prop('disabled', false).html(originalBtnHtml);
                        
                        // Remove row with animation
                        if (deleteContext.row && deleteContext.row.length) {
                            deleteContext.row.fadeOut(400, function() {
                                $(this).remove();
                                
                                // Check if table/list is empty
                                var $table = deleteContext.row.closest('table');
                                if ($table.length) {
                                    var $tbody = $table.find('tbody');
                                    if ($tbody.length && $tbody.find('tr').length === 0) {
                                        var colCount = $table.find('thead th').length || 6;
                                        $tbody.append(
                                            '<tr><td colspan="' + colCount + '" class="text-center py-4"><p class="text-muted">No items found.</p></td></tr>'
                                        );
                                    }
                                }
                            });
                        } else if (deleteContext.rowId) {
                            // Fallback: try to find row by ID
                            var $rowById = $('#' + deleteContext.rowId);
                            if ($rowById.length) {
                                $rowById.fadeOut(400, function() {
                                    $(this).remove();
                                });
                            }
                        }
                        
                    // Show success alert
                    alert((response.message || deleteContext.itemType + ' deleted successfully.'));
                }
                
                function handleDeleteError(error) {
                    // Reset confirm button
                    $confirmBtn.prop('disabled', false).html(originalBtnHtml);
                    
                    // Show error alert
                    var errorMsg = error.message || 'Error deleting ' + deleteContext.itemType + '. Please try again.';
                    alert(errorMsg);
                }
            });
            
            // Reset context when modal is hidden
            $('#deleteConfirmModal').on('hidden.bs.modal', function() {
                deleteContext.url = null;
                deleteContext.btn = null;
                deleteContext.row = null;
                deleteContext.rowId = null;
                deleteContext.itemName = null;
                deleteContext.itemType = null;
                deleteContext.originalHtml = null;
            });
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenReady);
    } else {
        initWhenReady();
    }
})();

