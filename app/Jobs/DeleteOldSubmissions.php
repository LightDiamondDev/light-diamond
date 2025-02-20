<?php

namespace App\Jobs;

use App\Models\MaterialSubmission;
use App\Services\Material\MaterialSubmissionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DeleteOldSubmissions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly MaterialSubmissionService $submissionService)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        MaterialSubmission::closed()
            ->where('updated_at', '<', Carbon::now()->subWeeks(2))
            ->get()
            ->each([$this->submissionService, 'delete']);
    }
}
