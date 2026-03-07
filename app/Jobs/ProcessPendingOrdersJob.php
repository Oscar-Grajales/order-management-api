<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class ProcessPendingOrdersJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $lock = Cache::lock('process-orders-lock', 60);

        if (!$lock->get()) {
            return;
        }

        try {
            $orders = Order::query()
                ->where('status', 'pending')
                ->get();

            foreach ($orders as $order) {
                $order->update([
                    'status' => 'processed'
                ]);
            }
        } finally {
            $lock->release();
        }
    }
}
