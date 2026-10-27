<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class SendOrderConfirmation implements ShouldQueue
{
    use Queueable;
    public int $orderId;
    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with(['customer', 'items.product'])->find($this->orderId);
        if (!$order) {
            Log::warning('Order confirmation skipped: order not found.', ['order_id' => $this->orderId,]);
            return;
        }
        Log::info('Order confirmation processed successfully.', ['order_id' => $order->id, 'customer_email' => $order->customer->email ?? null, 'grand_total' => $order->grand_total,]);
    }
}
