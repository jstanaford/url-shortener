<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Analytics Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-5xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold text-center text-blue-600 mb-8">URL Analytics Dashboard</h1>
            
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Check Analytics for a Short URL</h2>
                <div class="flex space-x-2">
                    <input type="text" id="shortUri" placeholder="Enter short URI (e.g., abc123)" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    <button id="checkButton" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Check Analytics
                    </button>
                </div>
            </div>
            
            <div id="loading" class="hidden text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Loading analytics data...</p>
            </div>
            
            <div id="result" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">URL Information</h3>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Short URL:</span>
                                <a id="shortUrl" class="block mt-1 text-blue-600 break-all" href="#" target="_blank"></a>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Original URL:</span>
                                <a id="originalUrl" class="block mt-1 text-blue-600 break-all" href="#" target="_blank"></a>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Created:</span>
                                <p id="createdAt" class="mt-1"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">View Statistics</h3>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm font-medium text-gray-500">Total Views:</span>
                                <p id="viewCount" class="mt-1 text-2xl font-bold text-blue-600"></p>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-500">Latest View:</span>
                                <p id="latestView" class="mt-1"></p>
                            </div>
                            <div>
                                <a id="refreshButton" href="#" class="text-sm text-blue-600 hover:underline">
                                    Refresh Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Latest Views</h3>
                    <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        View #
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Time Visited
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="viewsTable" class="bg-white divide-y divide-gray-200">
                                <!-- Views will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Test URL</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Use the button below to test visiting the URL and see if the view count updates correctly.
                    </p>
                    <div class="flex space-x-2">
                        <button id="visitButton" 
                            class="flex-1 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Visit URL to Test Analytics
                        </button>
                    </div>
                </div>
            </div>
            
            <div id="error" class="hidden bg-red-50 p-4 rounded-lg border border-red-200 text-red-600">
                Error message will appear here
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const shortUriInput = document.getElementById('shortUri');
            const checkButton = document.getElementById('checkButton');
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            const error = document.getElementById('error');
            const refreshButton = document.getElementById('refreshButton');
            const visitButton = document.getElementById('visitButton');
            
            // Check analytics when button is clicked
            checkButton.addEventListener('click', fetchAnalytics);
            
            // Also check when Enter is pressed in the input
            shortUriInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    fetchAnalytics();
                }
            });
            
            // Refresh data button
            refreshButton.addEventListener('click', function(e) {
                e.preventDefault();
                fetchAnalytics();
            });
            
            // Visit URL button
            visitButton.addEventListener('click', function() {
                const shortUri = shortUriInput.value.trim();
                if (!shortUri) return;
                
                // Open the short URL in a new tab
                window.open(`/s/${shortUri}`, '_blank');
                
                // Wait a moment then refresh the analytics
                setTimeout(fetchAnalytics, 1500);
            });
            
            function fetchAnalytics() {
                const shortUri = shortUriInput.value.trim();
                if (!shortUri) {
                    showError('Please enter a short URI');
                    return;
                }
                
                // Show loading, hide results and errors
                loading.classList.remove('hidden');
                result.classList.add('hidden');
                error.classList.add('hidden');
                
                fetch(`/api/analytics/${shortUri}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Short URL not found');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.error || 'Failed to fetch analytics');
                    }
                    
                    // Update the UI with the analytics data
                    document.getElementById('shortUrl').textContent = data.short_url;
                    document.getElementById('shortUrl').href = data.short_url;
                    document.getElementById('originalUrl').textContent = data.original_url;
                    document.getElementById('originalUrl').href = data.original_url;
                    document.getElementById('createdAt').textContent = new Date(data.created_at).toLocaleString();
                    document.getElementById('viewCount').textContent = data.view_count;
                    
                    // Format latest views table
                    const viewsTable = document.getElementById('viewsTable');
                    viewsTable.innerHTML = '';
                    
                    if (data.latest_views && data.latest_views.length > 0) {
                        document.getElementById('latestView').textContent = new Date(data.latest_views[0].time_visited).toLocaleString();
                        
                        data.latest_views.forEach((view, index) => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${index + 1}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    ${new Date(view.time_visited).toLocaleString()}
                                </td>
                            `;
                            viewsTable.appendChild(row);
                        });
                    } else {
                        document.getElementById('latestView').textContent = 'No views yet';
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td colspan="2" class="px-6 py-4 text-center text-sm text-gray-500">
                                No views recorded yet
                            </td>
                        `;
                        viewsTable.appendChild(row);
                    }
                    
                    // Show the results
                    loading.classList.add('hidden');
                    result.classList.remove('hidden');
                })
                .catch(err => {
                    showError(err.message);
                    loading.classList.add('hidden');
                });
            }
            
            function showError(message) {
                error.textContent = message;
                error.classList.remove('hidden');
                result.classList.add('hidden');
            }
        });
    </script>
</body>
</html> 