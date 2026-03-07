<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class ProcessOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Order $order)
    {
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->order->id))->expireAfter(60),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->order->status === 'processed') {
            return;
        }

        // simulate invoice processing
        sleep(2);

        $this->order->update([
            'status' => 'processed'
        ]);
    }
}
