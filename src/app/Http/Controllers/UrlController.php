<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Models\ShortUrlView;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;

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
        
        // Check if URL already exists in the database
        $existingShortUrl = ShortUrl::where('og_url', $originalUrl)->first();
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
        $shortUrl = ShortUrl::where('short_uri', $shortUri)->first();
        
        if (!$shortUrl) {
            abort(404);
        }

        // Record this view
        ShortUrlView::create([
            'short_uri' => $shortUri,
            'time_visited' => now(),
        ]);
        
        return redirect($shortUrl->og_url);
    }

    /**
     * Get analytics for a short URL
     *
     * @param string $shortUri
     * @return JsonResponse
     */
    public function analytics(string $shortUri): JsonResponse
    {
        $shortUrl = ShortUrl::where('short_uri', $shortUri)->first();
        
        if (!$shortUrl) {
            return response()->json([
                'success' => false,
                'error' => 'Short URL not found',
            ], 404);
        }

        $viewCount = $shortUrl->views()->count();
        $latestViews = $shortUrl->views()
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
