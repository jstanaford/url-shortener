<?php

namespace App\Jobs;

use App\Models\ShortUrlView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RecordUrlView implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $shortUri;
    protected $timeVisited;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $shortUri, $timeVisited)
    {
        $this->shortUri = $shortUri;
        $this->timeVisited = $timeVisited;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Record the view
        ShortUrlView::create([
            'short_uri' => $this->shortUri,
            'time_visited' => $this->timeVisited,
        ]);
        
        // Clear analytics cache to ensure fresh data
        Cache::forget('analytics:' . $this->shortUri);
    }
} 