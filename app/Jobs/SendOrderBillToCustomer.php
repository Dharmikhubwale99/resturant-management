<?php

namespace App\Jobs;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendOrderBillToCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::with(['orderItems.item.taxSetting','orderItems.variant','restaurant','table','customer','payments'])
            ->find($this->orderId);

        if (!$order) { Log::warning('BillJob: order not found', ['id'=>$this->orderId]); return; }

        $number = $this->normalizeIndianMobile($order->mobile ?: ($order->customer->mobile ?? null));
        if (!$number) { Log::info('BillJob: no mobile, skip', ['order'=>$order->id]); return; }

        // Use your existing view (waiter.bill-print) for PDF
        try {
            $pdf = Pdf::loadView('livewire.waiter.bill-print', [
                'order'      => $order,
                'restaurant' => $order->restaurant,
            ]);

            // 57mm width ~ 226.77pt; height generous (adjust as needed)
            $pdf->setPaper([0, 0, 226.77, 1200], 'portrait');

            $fileName = 'Bill-'.($order->bill_number ?? $order->id).'.pdf';
            $path = 'bills/'.$order->id.'/'.$fileName;
            Storage::disk('public')->put($path, $pdf->output());
            $mediaUrl = rtrim(config('app.url'), '/').Storage::url($path);
        } catch (\Throwable $e) {
            Log::error('BillJob: PDF failed', ['order'=>$order->id, 'err'=>$e->getMessage()]);
            $mediaUrl = null;
        }

        $base   = config('whatsapp.base_url');
        $inst   = "687B48CD6CB30";
        $token  = "682974e16d3b2";

        Log::info(['base' => $base ? 'set' : 'null', 'inst'=>$inst ? 'set' : 'null', 'token'=>$token ? 'set' : 'null']);
        $msg = sprintf(
            "Thank you for visiting %s.\nBill #%s • Total ₹%s",
            $order->restaurant->name ?? 'our restaurant',
            $order->bill_number ?? $order->id,
            number_format($order->total_amount ?? 0, 2)
        );

        $params = [
            'number'       => $number,
            'instance_id'  => $inst,
            'access_token' => $token,
            'message'      => $msg,
        ];

        // Prefer media if PDF created
        if ($mediaUrl) {
            $params += [
                'type'      => 'media',
                'media_url' => $mediaUrl,
                'filename'  => $fileName,
            ];
        } else {
            $params += ['type' => 'text'];
        }

        try {
            $resp = Http::timeout(20)->retry(2, 500)->get($base, $params);
            Log::info('BillJob: send resp', ['order'=>$order->id, 'status'=>$resp->status(), 'body'=>$resp->body()]);
        } catch (\Throwable $e) {
            Log::error('BillJob: send failed', ['order'=>$order->id, 'err'=>$e->getMessage()]);
        }
    }

    private function normalizeIndianMobile(?string $m): ?string
    {
        if (!$m) return null;
        $d = preg_replace('/\D+/', '', $m);
        $d = ltrim($d, '0');
        if (strlen($d) === 10) return '91'.$d;
        if (strlen($d) === 12 && str_starts_with($d, '91')) return $d;
        return $d ?: null;
    }
}
