<?php

namespace App\Services\Pos;

use App\Enums\CustomerType;
use App\Enums\CurrencyCode;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\PosSession;
use App\Services\Billing\PaymentService;
use App\Services\Orders\OrderService;
use Illuminate\Support\Facades\DB;

class PosService
{
    public function openSession(int $userId, float $openingBalance = 0): PosSession
    {
        return PosSession::create([
            'user_id' => $userId,
            'opened_at' => now(),
            'opening_balance' => $openingBalance,
            'status' => 'open',
        ]);
    }

    public function closeSession(PosSession $session, float $closingBalance): PosSession
    {
        $session->update([
            'closed_at' => now(),
            'closing_balance' => $closingBalance,
            'status' => 'closed',
        ]);

        return $session;
    }

    public function quickSale(array $payload): Order
    {
        return DB::transaction(function () use ($payload): Order {
            $order = app(OrderService::class)->create([
                'service_area_id' => $payload['service_area_id'] ?? null,
                'customer_id' => $payload['customer_id'] ?? null,
                'customer_type' => $payload['customer_id'] ? CustomerType::WalkInIdentified->value : CustomerType::WalkInAnonymous->value,
                'status' => OrderStatus::Closed->value,
                'currency' => strtoupper((string) ($payload['currency'] ?? CurrencyCode::default())),
                'created_by' => $payload['created_by'] ?? null,
                'items' => $payload['items'] ?? [],
                'notes' => $payload['notes'] ?? 'POS quick sale',
            ]);

            app(PaymentService::class)->payOrderDirectly($order, [
                'amount' => $order->total,
                'method' => $payload['payment_method'] ?? 'cash',
                'recorded_by' => $payload['created_by'] ?? null,
                'currency' => $payload['currency'] ?? $order->currency ?? CurrencyCode::default(),
            ]);

            return $order;
        });
    }
}
