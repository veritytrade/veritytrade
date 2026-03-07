<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'product_name',
        'spec_summary',
        'full_description',
        'total_amount_ngn',
        'outstanding_balance_ngn',
        'payment_status',
        'logistics_type',
        'status',
        'shipment_id',
        'current_stage_id',
        'tracking_code',
        'verity_tracking_code',
    ];

    protected $casts = [
        'outstanding_balance_ngn' => 'decimal:2',
    ];

    /** True if this order pays the N10k logistics (outside Lagos) */
    public function getPaysLogisticsAttribute(): bool
    {
        return $this->logistics_type === 'outside_lagos';
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->verity_tracking_code)) {
                $order->verity_tracking_code = self::generateVerityCode();
            }
            if (empty($order->status)) {
                $order->status = 'processing';
            }
        });
    }

    /** Parse WhatsApp-style description: Model, Price, first line as product name */
    public static function parseDescription(string $description): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $description)));
        $productName = null;
        $price = null;
        foreach ($lines as $line) {
            if (preg_match('/^Model:\s*(.+)$/i', $line, $m)) {
                $productName = trim($m[1]);
                break;
            }
        }
        if ($productName === null && ! empty($lines)) {
            $productName = $lines[0];
        }
        foreach ($lines as $line) {
            if (preg_match('/^Price:\s*(.+)$/i', $line, $m)) {
                $price = preg_replace('/[^\d.]/', '', trim($m[1]));
                $price = $price !== '' ? (float) $price : null;
                break;
            }
        }
        return [
            'product_name' => $productName ?: '',
            'price_ngn' => $price,
            'has_price_in_description' => $price !== null,
        ];
    }

    public function paymentSlips(): HasMany
    {
        return $this->hasMany(OrderPaymentSlip::class)->orderBy('sort_order');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function canCustomerEdit(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function canCustomerDelete(): bool
    {
        return $this->status === 'pending_approval';
    }

    /** Customer-facing status label ( Processing for both pending_approval and processing ) */
    public function getCustomerStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending_approval', 'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status ?? 'Processing'),
        };
    }

    public static function generateVerityCode(): string
    {
        $year = (int) date('Y');
        $prefix = "VT-{$year}-";
        $codes = DB::table('orders')
            ->where('verity_tracking_code', 'like', $prefix . '%')
            ->pluck('verity_tracking_code');
        $max = 0;
        foreach ($codes as $code) {
            $num = (int) substr($code, strlen($prefix));
            if ($num > $max) {
                $max = $num;
            }
        }
        return $prefix . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function currentStageOverride(): BelongsTo
    {
        return $this->belongsTo(TrackingStage::class, 'current_stage_id');
    }

    /** Effective stage: order override or shipment stage */
    public function effectiveStage(): ?TrackingStage
    {
        if ($this->current_stage_id) {
            return $this->currentStageOverride;
        }
        if ($this->shipment_id && $this->shipment) {
            return $this->shipment->currentStage;
        }
        return null;
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(Tracking::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceRequest(): HasOne
    {
        return $this->hasOne(InvoiceRequest::class, 'shipment_id', 'shipment_id');
    }

    /**
     * Derive correct status from shipment and stage.
     * Ignores manual selection: status must reflect actual tracking stage.
     */
    public static function deriveStatusFromStage(?int $shipmentId, ?int $currentStageId, ?Shipment $shipment = null): string
    {
        $stage = null;
        if ($currentStageId) {
            $stage = TrackingStage::find($currentStageId);
        } elseif ($shipmentId && $shipment) {
            $stage = $shipment->currentStage;
        } elseif ($shipmentId) {
            $shipment = Shipment::with('currentStage')->find($shipmentId);
            $stage = $shipment?->currentStage;
        }
        if ($stage && (int) $stage->position === 6) {
            return 'delivered';
        }
        if ($shipmentId || ($stage && (int) $stage->position >= 1)) {
            return 'shipped';
        }
        return 'processing';
    }

    public function assignToShipment(?Shipment $shipment): void
    {
        $this->shipment_id = $shipment?->id;
        $this->current_stage_id = null; // inherit from shipment
        $this->status = $shipment
            ? self::deriveStatusFromStage($shipment->id, null, $shipment)
            : 'processing';
        $this->save();
    }

    public function syncStatusFromStage(): void
    {
        $stage = $this->effectiveStage();
        if ($stage && (int) $stage->position === 6) {
            $this->update(['status' => 'delivered']);
        }
    }

    public function canCustomerConfirmDelivery(): bool
    {
        if (in_array($this->status, ['delivered', 'cancelled'])) {
            return false;
        }
        $stage = $this->effectiveStage();
        if (!$stage) {
            return false;
        }
        $pos = (int) $stage->position;
        return $pos >= 5; // Dispatched (5) or Delivered (6)
    }
}
