@extends('layouts.app')

@section('title', 'Users Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Users Management</h2>
                <div id="addButtonContainer">
                    @if($category === 'members' && (Auth::user()->hasPermission('members.create') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal" data-action="create" data-role="member" data-route="{{ route('members.create') }}">
                            <i class="fas fa-plus"></i> Add New Member
                        </button>
                    @elseif($category === 'trainers' && (Auth::user()->hasPermission('trainers.create') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal" data-action="create" data-role="trainer" data-route="{{ route('trainers.create') }}">
                            <i class="fas fa-plus"></i> Add New Trainer
                        </button>
                    @elseif($category === 'staff' && (Auth::user()->hasPermission('staff.create') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#userModal" data-action="create" data-role="staff" data-route="{{ route('staff.create') }}">
                            <i class="fas fa-plus"></i> Add New Staff
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('user-management.index', ['category' => $category]) }}" id="userManagementFilterForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Name, Email, Phone...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="resetUserManagementFilters()">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="category" value="{{ $category }}">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Tabs -->
    <div class="row mb-3">
        <div class="col-md-12">
            <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
                @if(Auth::user()->hasPermission('members.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ $category === 'members' ? 'active' : '' }}" 
                       href="javascript:void(0)"
                       data-category="members"
                       data-route="{{ route('user-management.index', ['category' => 'members']) }}">
                        <i class="fas fa-users"></i> Members 
                        <span class="badge badge-primary" id="membersCountBadge">{{ $membersCount }}</span>
                    </a>
                </li>
                @endif
                @if(Auth::user()->hasPermission('trainers.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ $category === 'trainers' ? 'active' : '' }}" 
                       href="javascript:void(0)"
                       data-category="trainers"
                       data-route="{{ route('user-management.index', ['category' => 'trainers']) }}">
                        <i class="fas fa-user-tie"></i> Trainers 
                        <span class="badge badge-info" id="trainersCountBadge">{{ $trainersCount }}</span>
                    </a>
                </li>
                @endif
                @if(Auth::user()->hasPermission('staff.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin())
                <li class="nav-item">
                    <a class="nav-link {{ $category === 'staff' ? 'active' : '' }}" 
                       href="javascript:void(0)"
                       data-category="staff"
                       data-route="{{ route('user-management.index', ['category' => 'staff']) }}">
                        <i class="fas fa-users-cog"></i> Staff 
                        <span class="badge badge-success" id="staffCountBadge">{{ $staffCount }}</span>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="usersTable">
                            <thead>
                                <tr id="tableHeaderRow">
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    @if($category === 'staff')
                                        <th class="staff-type-column">Staff Type</th>
                                    @endif
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                @include('user-management._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewUserModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="userForm" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="userModalLabel">Add New User</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="userModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.delete-confirm-modal')
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle Tab Switching (AJAX - No Page Refresh)
    $('#categoryTabs a.nav-link').on('click', function(e) {
        e.preventDefault();
        
        var $tab = $(this);
        var category = $tab.data('category');
        var route = $tab.data('route');
        var $tableBody = $('#usersTableBody');
        var $table = $('#usersTable');
        
        // Don't do anything if already active
        if ($tab.hasClass('active')) {
            return;
        }
        
        // Update active tab
        $('#categoryTabs a.nav-link').removeClass('active');
        $tab.addClass('active');
        
        // Update hidden input for category
        $('input[name="category"]').val(category);
        
        // Show loading in table
        var colCount = $table.find('thead tr th').length;
        $tableBody.html('<tr><td colspan="' + colCount + '" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        // Get current filter values
        var filterData = {
            category: category,
            status: $('#status').val() || '',
            search: $('#search').val() || ''
        };
        
        // Update URL without page refresh
        var url = new URL(route);
        if (filterData.status) url.searchParams.set('status', filterData.status);
        if (filterData.search) url.searchParams.set('search', filterData.search);
        window.history.pushState({category: category}, '', url);
        
        // Update Add button based on category (preserve text and icon)
        var $addButton = $('#addButtonContainer').find('.btn-primary[data-toggle="modal"]');
        var buttonTexts = {
            'members': '<i class="fas fa-plus"></i> Add New Member',
            'trainers': '<i class="fas fa-plus"></i> Add New Trainer',
            'staff': '<i class="fas fa-plus"></i> Add New Staff'
        };
        var buttonRoutes = {
            'members': '{{ route("members.create") }}',
            'trainers': '{{ route("trainers.create") }}',
            'staff': '{{ route("staff.create") }}'
        };
        
        if ($addButton.length) {
            // Preserve button text and icon
            $addButton.html(buttonTexts[category]);
            $addButton.attr('data-role', category);
            $addButton.attr('data-route', buttonRoutes[category]);
        }
        
        // Load content via AJAX
        $.ajax({
            url: route,
            type: 'GET',
            data: filterData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.html) {
                    // Update table header based on category (preserve all text)
                    var $thead = $table.find('thead tr#tableHeaderRow');
                    var $staffTypeHeader = $thead.find('th.staff-type-column');
                    
                    if (category === 'staff') {
                        // Add Staff Type header if not exists
                        if ($staffTypeHeader.length === 0) {
                            // Find Phone column and insert Staff Type after it
                            var $phoneTh = $thead.find('th').filter(function() {
                                return $(this).text().trim() === 'Phone';
                            });
                            if ($phoneTh.length) {
                                $phoneTh.after('<th class="staff-type-column">Staff Type</th>');
                            }
                        }
                    } else {
                        // Remove Staff Type header if exists
                        if ($staffTypeHeader.length > 0) {
                            $staffTypeHeader.remove();
                        }
                    }
                    
                    // Update table body
                    $tableBody.html(response.html);
                    
                    // Update counts if provided
                    if (response.counts) {
                        if (response.counts.members !== undefined) {
                            $('#membersCountBadge').text(response.counts.members);
                        }
                        if (response.counts.trainers !== undefined) {
                            $('#trainersCountBadge').text(response.counts.trainers);
                        }
                        if (response.counts.staff !== undefined) {
                            $('#staffCountBadge').text(response.counts.staff);
                        }
                    }
                } else {
                    // Fallback: reload page if response format is unexpected
                    window.location.href = route;
                }
            },
            error: function(xhr) {
                console.error('Error loading tab content:', xhr);
                // Fallback: reload page on error
                window.location.href = route;
            }
        });
    });
    
    // Handle Add/Edit Modal
    $('#userModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var role = button.data('role');
        var route = button.data('route');
        var modal = $(this);
        var modalBody = modal.find('#userModalBody');
        var modalTitle = modal.find('#userModalLabel');
        
        var roleNames = {
            'member': 'Member',
            'trainer': 'Trainer',
            'staff': 'Staff'
        };
        
        if (action === 'create') {
            modalTitle.text('Add New ' + roleNames[role]);
        } else {
            modalTitle.text('Edit ' + roleNames[role]);
        }
        
        modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.ajax({
            url: route,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            },
            success: function(response) {
                var formHtml = response.trim();
                if (formHtml.includes('<form')) {
                    var $temp = $('<div>').html(response);
                    formHtml = $temp.find('form').html() || response;
                }
                
                modalBody.html(formHtml);
                
                // Set form action based on role and action
                var form = $('#userForm');
                if (action === 'create') {
                    if (role === 'member') {
                        form.attr('action', '{{ route("members.store") }}');
                    } else if (role === 'trainer') {
                        form.attr('action', '{{ route("trainers.store") }}');
                    } else if (role === 'staff') {
                        form.attr('action', '{{ route("staff.store") }}');
                    }
                } else {
                    var userId = button.data('user-id');
                    if (role === 'member') {
                        form.attr('action', '{{ route("members.update", ":id") }}'.replace(':id', userId));
                    } else if (role === 'trainer') {
                        form.attr('action', '{{ route("trainers.update", ":id") }}'.replace(':id', userId));
                    } else if (role === 'staff') {
                        form.attr('action', '{{ route("staff.update", ":id") }}'.replace(':id', userId));
                    }
                    form.find('input[name="_method"]').remove();
                    form.append('<input type="hidden" name="_method" value="PUT">');
                }
                
                if (!form.find('input[name="_token"]').length) {
                    form.prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                }
            },
            error: function(xhr) {
                console.error('Error loading form:', xhr);
                modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
            }
        });
    });
    
    // Handle form submission
    $(document).on('submit', '#userForm', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();
        form.find('.alert').remove();
        
        var method = form.find('input[name="_method"]').val();
        if (method) {
            formData.append('_method', method);
        }
        
        // Convert form action to API route if needed
        var formAction = form.attr('action');
        var apiUrl = (typeof window.API !== 'undefined') 
            ? window.API.convertRoute(formAction) 
            : formAction;
        
        var method = form.find('input[name="_method"]').val() || 'POST';
        
        // Use API helper if available
        if (typeof window.API !== 'undefined') {
            var apiMethod = method === 'PUT' ? 'put' : (method === 'PATCH' ? 'patch' : 'post');
            if (window.API[apiMethod]) {
                window.API[apiMethod](apiUrl, formData)
                    .then(function(response) {
                        $('#userModal').modal('hide');
                        var message = response.message || 'User saved successfully.';
                        alert(message);
                        setTimeout(function() {
                            window.applyUserManagementFilters();
                        }, 500);
                    })
                    .catch(function(error) {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                        handleFormErrors(error, form);
                    });
                return;
            }
        }
        
        // Fallback to jQuery AJAX
        $.ajax({
            url: apiUrl,
            type: method === 'PUT' ? 'PUT' : (method === 'PATCH' ? 'PATCH' : 'POST'),
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                $('#userModal').modal('hide');
                var message = (typeof response === 'object' && response.message) ? response.message : 'User saved successfully.';
                alert(message);
                
                // Reload table content via AJAX instead of full page reload
                setTimeout(function() {
                    window.applyUserManagementFilters();
                }, 500);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                var errorData = xhr.responseJSON || {};
                handleFormErrors(errorData, form, xhr.status);
            }
        });
        
        function handleFormErrors(error, form, status) {
            if (status === 422 || error.errors) {
                var errors = error.errors || {};
                var errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                
                $.each(errors, function(field, messages) {
                    if (Array.isArray(messages)) {
                        $.each(messages, function(i, message) {
                            errorHtml += '<li>' + message + '</li>';
                            var input = form.find('[name="' + field + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + message + '</div>');
                        });
                    } else {
                        errorHtml += '<li>' + messages + '</li>';
                    }
                });
                
                errorHtml += '</ul></div>';
                form.prepend(errorHtml);
            } else {
                form.prepend('<div class="alert alert-danger">' + (error.message || 'An error occurred. Please try again.') + '</div>');
            }
        }
    });
    
    // Handle View Modal
    $('#viewUserModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var route = button.data('route');
        var modal = $(this);
        var modalBody = modal.find('#viewUserModalBody');
        
        modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.ajax({
            url: route,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (typeof response === 'object' && response.html) {
                    modalBody.html(response.html);
                } else {
                    var $response = $('<div>').html(response);
                    var content = $response.find('.row').html() || $response.find('.container-fluid').html() || response;
                    modalBody.html(content);
                }
            },
            error: function(xhr) {
                console.error('Error loading user:', xhr);
                modalBody.html('<div class="alert alert-danger">Error loading user details. Please try again.</div>');
            }
        });
    });
    
    function showAlert(type, message) {
        // Use JavaScript alert box for all messages
        alert(message);
    }
});

// Auto-filter for user management
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyUserManagementFilters = function() {
        // Get current active category from tab
        var activeCategory = $('#categoryTabs a.nav-link.active').data('category') || '{{ $category }}';
        var route = $('#categoryTabs a.nav-link.active').data('route') || '{{ route("user-management.index", ["category" => $category]) }}';
        
        var formData = {
            status: $('#status').val(),
            search: $('#search').val(),
            category: activeCategory,
        };
        
        // Show loading
        var $tableBody = $('#usersTableBody');
        var colCount = $('#usersTable thead tr th').length;
        $tableBody.html('<tr><td colspan="' + colCount + '" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        // Use API if available, otherwise fallback to web route
        var apiUrl = (typeof window.API !== 'undefined') 
            ? window.API.convertRoute(route) 
            : route;
        
        // Use API helper if available
        if (typeof window.API !== 'undefined' && window.API.get) {
            window.API.get(apiUrl, formData)
                .then(function(response) {
                    handleFilterSuccess(response, route, formData);
                })
                .catch(function(error) {
                    console.error('Filter error:', error);
                    window.location.reload();
                });
        } else {
            // Fallback to jQuery AJAX
            $.ajax({
                url: apiUrl,
                type: 'GET',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    handleFilterSuccess(response, route, formData);
                },
                error: function() {
                    window.location.reload();
                }
            });
        }
        
        function handleFilterSuccess(response, route, formData) {
            // Update URL
            var url = new URL(route);
            Object.keys(formData).forEach(function(key) {
                if (formData[key]) {
                    url.searchParams.set(key, formData[key]);
                } else {
                    url.searchParams.delete(key);
                }
            });
            window.history.pushState({}, '', url);
            
            // Handle API response format (response.data.users) or web response format (response.html)
            if (response.success && response.data && response.data.users) {
                // API response format - need to render table body
                // For now, reload to get HTML, or we could generate HTML client-side
                // This is a limitation - we'd need to create a view partial or use a template
                window.location.reload();
            } else if (response.success && response.html) {
                // Web response format
                $tableBody.html(response.html);
                
                // Update counts if provided
                if (response.counts || (response.data && response.data.counts)) {
                    var counts = response.counts || response.data.counts;
                    if (counts) {
                        if (counts.members !== undefined) {
                            $('#membersCountBadge').text(counts.members);
                        }
                        if (counts.trainers !== undefined) {
                            $('#trainersCountBadge').text(counts.trainers);
                        }
                        if (counts.staff !== undefined) {
                            $('#staffCountBadge').text(counts.staff);
                        }
                    }
                }
            } else {
                window.location.reload();
            }
        }
    };
    
    $(document).ready(function() {
        // Auto-filter on select change (immediate)
        $('#status').on('change', function() {
            clearTimeout(filterTimeout);
            window.applyUserManagementFilters();
        });
        
        // Auto-filter on search input with debounce (500ms delay)
        $('#search').on('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyUserManagementFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetUserManagementFilters = function() {
            $('#status').val('');
            $('#search').val('');
            window.history.pushState({}, '', '{{ route("user-management.index", ["category" => $category]) }}');
            window.applyUserManagementFilters();
        };
    });
})();
</script>
@endpush

