/**
 * API Helper
 * Centralized API communication helper for both web and mobile
 * Automatically handles authentication (session for web, token for mobile)
 */

(function() {
    'use strict';
    
    var API_BASE_URL = '/api/v1';
    var API_TOKEN_KEY = 'api_token';
    
    /**
     * Get API token from localStorage (for mobile app)
     */
    function getApiToken() {
        return localStorage.getItem(API_TOKEN_KEY);
    }
    
    /**
     * Set API token in localStorage (for mobile app)
     */
    function setApiToken(token) {
        if (token) {
            localStorage.setItem(API_TOKEN_KEY, token);
        } else {
            localStorage.removeItem(API_TOKEN_KEY);
        }
    }
    
    /**
     * Get default headers for API requests
     */
    function getHeaders() {
        var headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
        
        // Add CSRF token for web requests
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
        }
        
        // Add API token for mobile app requests
        var token = getApiToken();
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }
        
        return headers;
    }
    
    /**
     * Convert web route to API route
     */
    function convertToApiRoute(webRoute) {
        // Remove leading slash
        webRoute = webRoute.replace(/^\//, '');
        
        // Map common web routes to API routes
        var routeMap = {
            'dashboard': 'dashboard',
            'profile': 'profile',
            'profile/edit': 'profile/edit',
            'members': 'members',
            'trainers': 'trainers',
            'staff': 'staff',
            'user-management': 'user-management',
            'membership-plans': 'membership-plans',
            'workout-plans': 'workout-plans',
            'diet-plans': 'diet-plans',
            'classes': 'classes',
            'bookings': 'bookings',
            'attendances': 'attendances',
            'check-in': 'check-in',
            'payments': 'payments',
            'notifications': 'notifications',
            'my-notifications': 'my-notifications',
            'events': 'events',
            'pause-requests': 'pause-requests',
            'salaries': 'salaries',
            'salary-payments': 'salary-payments',
            'settings': 'settings',
            'reports/attendance': 'reports/attendance',
            'reports/classes': 'reports/classes',
            'reports/payments': 'reports/payments',
            'reports/members': 'reports/members',
            'bulk-sms': 'bulk-sms'
        };
        
        // Try to find exact match
        if (routeMap[webRoute]) {
            return API_BASE_URL + '/' + routeMap[webRoute];
        }
        
        // Try to find partial match (e.g., 'members/1' -> 'members/1')
        for (var route in routeMap) {
            if (webRoute.startsWith(route + '/')) {
                return API_BASE_URL + '/' + webRoute.replace(route, routeMap[route]);
            }
        }
        
        // Default: assume it's already an API route or convert directly
        if (webRoute.startsWith('api/')) {
            return '/' + webRoute;
        }
        
        return API_BASE_URL + '/' + webRoute;
    }
    
    /**
     * Handle API response
     */
    function handleResponse(response, resolve, reject) {
        if (response.success !== false) {
            resolve(response);
        } else {
            reject(response);
        }
    }
    
    /**
     * Make API request
     */
    function apiRequest(method, url, data, options) {
        options = options || {};
        
        return new Promise(function(resolve, reject) {
            // Convert web route to API route if needed
            var apiUrl = convertToApiRoute(url);
            
            // Prepare request options
            var requestOptions = {
                method: method,
                headers: getHeaders(),
                credentials: 'same-origin' // Include cookies for session auth
            };
            
            // Add body for POST, PUT, PATCH
            if (data && ['POST', 'PUT', 'PATCH'].includes(method)) {
                if (data instanceof FormData) {
                    // For FormData, don't set Content-Type (browser will set it with boundary)
                    delete requestOptions.headers['Content-Type'];
                    requestOptions.body = data;
                } else {
                    requestOptions.body = JSON.stringify(data);
                }
            }
            
            // Add query params for GET, DELETE
            if (data && ['GET', 'DELETE'].includes(method)) {
                var queryParams = new URLSearchParams(data);
                if (queryParams.toString()) {
                    apiUrl += '?' + queryParams.toString();
                }
            }
            
            // Make fetch request
            fetch(apiUrl, requestOptions)
                .then(function(response) {
                    // Check if response is JSON
                    var contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return response.json();
                    } else {
                        return response.text().then(function(text) {
                            return { success: response.ok, message: text, data: text };
                        });
                    }
                })
                .then(function(data) {
                    handleResponse(data, resolve, reject);
                })
                .catch(function(error) {
                    reject({
                        success: false,
                        message: error.message || 'Network error',
                        error: error
                    });
                });
        });
    }
    
    /**
     * API Helper Object
     */
    var API = {
        /**
         * GET request
         */
        get: function(url, params, options) {
            return apiRequest('GET', url, params, options);
        },
        
        /**
         * POST request
         */
        post: function(url, data, options) {
            return apiRequest('POST', url, data, options);
        },
        
        /**
         * PUT request
         */
        put: function(url, data, options) {
            return apiRequest('PUT', url, data, options);
        },
        
        /**
         * PATCH request
         */
        patch: function(url, data, options) {
            return apiRequest('PATCH', url, data, options);
        },
        
        /**
         * DELETE request
         */
        delete: function(url, params, options) {
            return apiRequest('DELETE', url, params, options);
        },
        
        /**
         * Login and store token
         */
        login: function(email, password) {
            return this.post('/api/v1/login', { email: email, password: password })
                .then(function(response) {
                    if (response.token) {
                        setApiToken(response.token);
                    }
                    return response;
                });
        },
        
        /**
         * Logout and clear token
         */
        logout: function() {
            var token = getApiToken();
            setApiToken(null);
            
            if (token) {
                return this.post('/api/v1/logout', {});
            }
            
            return Promise.resolve({ success: true, message: 'Logged out' });
        },
        
        /**
         * Get current user
         */
        getUser: function() {
            return this.get('/api/v1/user');
        },
        
        /**
         * Convert web route to API route (public method)
         */
        convertRoute: convertToApiRoute
    };
    
    // Export to window for global access
    window.API = API;
    
    // Also export for jQuery if available
    if (typeof jQuery !== 'undefined') {
        jQuery.API = API;
    }
    
})();
