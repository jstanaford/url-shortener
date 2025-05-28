<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Models\ShortUrlView;
use App\Jobs\RecordUrlView;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UrlController extends Controller
{
    /**
     * Shorten a URL
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function shorten(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 400);
        }

        $originalUrl = $request->input('url');
        $urlHash = md5($originalUrl);
        
        // Check if URL already exists in the database using the hash for faster lookup
        $existingShortUrl = ShortUrl::where('og_url_hash', $urlHash)->first();
        if ($existingShortUrl) {
            return response()->json([
                'success' => true,
                'short_url' => url('/s/'.$existingShortUrl->short_uri),
                'short_uri' => $existingShortUrl->short_uri,
                'original_url' => $existingShortUrl->og_url,
            ]);
        }

        // Generate a new short URL
        $shortUri = $this->generateUniqueShortUri();
        
        // Create the new short URL record
        $shortUrl = ShortUrl::create([
            'og_url' => $originalUrl,
            'short_uri' => $shortUri,
            'og_url_hash' => $urlHash,
        ]);

        return response()->json([
            'success' => true,
            'short_url' => url('/s/'.$shortUri),
            'short_uri' => $shortUri,
            'original_url' => $shortUrl->og_url,
        ], 201);
    }

    /**
     * Redirect to the original URL
     *
     * @param string $shortUri
     * @return RedirectResponse
     */
    public function redirect(string $shortUri): RedirectResponse
    {
        // Try to get the original URL from cache first
        $originalUrl = Cache::remember('short_url:'.$shortUri, 3600, function () use ($shortUri) {
            $shortUrl = ShortUrl::where('short_uri', $shortUri)->first();
            return $shortUrl ? $shortUrl->og_url : null;
        });
        
        if (!$originalUrl) {
            abort(404);
        }

        // Important: Always clear the analytics cache when recording a new view
        Cache::forget('analytics:'.$shortUri);
        
        // Detect if request is coming from browser or test script
        $userAgent = request()->header('User-Agent');
        $isBrowser = $userAgent && (
            strpos($userAgent, 'Mozilla') !== false || 
            strpos($userAgent, 'Chrome') !== false || 
            strpos($userAgent, 'Safari') !== false || 
            strpos($userAgent, 'Edge') !== false || 
            strpos($userAgent, 'Firefox') !== false
        );
        
        // Log the request info for debugging
        Log::info('URL Redirect', [
            'short_uri' => $shortUri,
            'user_agent' => $userAgent,
            'is_browser' => $isBrowser,
            'is_testing' => App::environment('testing')
        ]);
        
        // Record the view synchronously for testing or curl requests
        if (App::environment('testing') || strpos($userAgent, 'curl') !== false) {
            ShortUrlView::create([
                'short_uri' => $shortUri,
                'time_visited' => now(),
            ]);
        } 
        // For browser requests, we also process synchronously for reliability
        elseif ($isBrowser) {
            ShortUrlView::create([
                'short_uri' => $shortUri,
                'time_visited' => now(),
            ]);
            
            // Immediately invalidate cache so analytics are updated
            Cache::forget('analytics:'.$shortUri);
        }
        // For API requests, we can use the queue
        else {
            RecordUrlView::dispatch($shortUri, now());
        }
        
        return redirect($originalUrl);
    }

    /**
     * Get analytics for a short URL
     *
     * @param string $shortUri
     * @return JsonResponse
     */
    public function analytics(string $shortUri): JsonResponse
    {
        // Always clear existing cache for analytics request to get fresh data
        Cache::forget('analytics:'.$shortUri);
        
        // Get fresh data for analytics, but cache for a short time
        return Cache::remember('analytics:'.$shortUri, 2, function () use ($shortUri) {
            $shortUrl = ShortUrl::where('short_uri', $shortUri)->first();
            
            if (!$shortUrl) {
                return response()->json([
                    'success' => false,
                    'error' => 'Short URL not found',
                ], 404);
            }

            // Direct DB query for accurate, fresh counts
            $viewCount = DB::table('short_url_views')
                ->where('short_uri', $shortUri)
                ->count();
                
            $latestViews = DB::table('short_url_views')
                ->where('short_uri', $shortUri)
                ->orderBy('time_visited', 'desc')
                ->limit(10)
                ->get(['time_visited']);

            return response()->json([
                'success' => true,
                'short_url' => url('/s/'.$shortUri),
                'original_url' => $shortUrl->og_url,
                'created_at' => $shortUrl->created_at,
                'view_count' => $viewCount,
                'latest_views' => $latestViews,
            ]);
        });
    }

    /**
     * Get analytics for all short URLs
     *
     * @return JsonResponse
     */
    public function allAnalytics(): JsonResponse
    {
        $shortUrls = ShortUrl::all();
        
        $analytics = [];
        
        foreach ($shortUrls as $shortUrl) {
            $viewCount = $shortUrl->views()->count();
            $latestView = $shortUrl->views()
                ->orderBy('time_visited', 'desc')
                ->first();
                
            $analytics[$shortUrl->short_uri] = [
                'short_url' => url('/s/'.$shortUrl->short_uri),
                'original_url' => $shortUrl->og_url,
                'created_at' => $shortUrl->created_at,
                'view_count' => $viewCount,
                'last_viewed' => $latestView ? $latestView->time_visited : null,
            ];
        }
        
        return response()->json([
            'success' => true,
            'total_urls' => count($shortUrls),
            'urls' => $analytics,
        ]);
    }

    /**
     * Generate a unique short URI
     *
     * @return string
     */
    private function generateUniqueShortUri(): string
    {
        do {
            $shortUri = Str::random(6);
            $exists = ShortUrl::where('short_uri', $shortUri)->exists();
        } while ($exists);
        
        return $shortUri;
    }
}
