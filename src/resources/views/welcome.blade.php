<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
            <h1 class="text-3xl font-bold text-center text-blue-600 mb-8">URL Shortener</h1>
            
            <div class="mb-8">
                <form id="shortenForm" class="space-y-4">
                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-700">Enter a long URL to shorten</label>
                        <input type="url" id="url" name="url" required 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="https://example.com/very/long/url">
                    </div>
                    <div>
                        <button type="submit" 
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Shorten URL
                        </button>
                    </div>
                </form>
            </div>
            
            <div id="result" class="hidden">
                <div class="bg-gray-100 p-4 rounded-md">
                    <h2 class="text-lg font-medium text-gray-800 mb-2">Your shortened URL:</h2>
                    <div class="flex items-center">
                        <input type="text" id="shortUrl" readonly
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white">
                        <button id="copyButton" 
                            class="ml-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Copy
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        View analytics at: <a id="analyticsUrl" class="text-blue-600 hover:underline" href="#" target="_blank"></a>
                    </p>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <a href="/dashboard" class="text-blue-600 hover:underline">View Analytics Dashboard</a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('shortenForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const url = document.getElementById('url').value;
            
            try {
                const response = await fetch('/api/shorten', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ url }),
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const shortUrl = data.short_url;
                    const shortUri = data.short_uri;
                    
                    document.getElementById('shortUrl').value = shortUrl;
                    
                    const analyticsUrl = window.location.origin + '/api/analytics/' + shortUri;
                    document.getElementById('analyticsUrl').textContent = analyticsUrl;
                    document.getElementById('analyticsUrl').href = analyticsUrl;
                    
                    document.getElementById('result').classList.remove('hidden');
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
        
        document.getElementById('copyButton').addEventListener('click', () => {
            const shortUrlInput = document.getElementById('shortUrl');
            shortUrlInput.select();
            document.execCommand('copy');
            
            const copyButton = document.getElementById('copyButton');
            const originalText = copyButton.textContent;
            copyButton.textContent = 'Copied!';
            
            setTimeout(() => {
                copyButton.textContent = originalText;
            }, 2000);
        });
    </script>
</body>
</html> 