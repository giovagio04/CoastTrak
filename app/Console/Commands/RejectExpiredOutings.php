<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Outing;

class RejectExpiredOutings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outings:reject-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reject pending outings that have been pending for more than 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredOutingsCount = Outing::where('status', 'pending')
            ->where('created_at', '<=', now()->subHours(24))
            ->update(['status' => 'rejected']);

        $this->info("Rejected {$expiredOutingsCount} expired pending outings.");
    }
}
