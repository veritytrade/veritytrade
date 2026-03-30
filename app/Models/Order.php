<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'invoice_id',
        'current_stage_id',
        'tracking_code',
        'supplier_platform',
        'supplier_order_number',
        'supplier_logistics_code',
        'mapping_status',
        'mapped_at',
        'mapped_by',
    ];

    protected $casts = [
        'outstanding_balance_ngn' => 'decimal:2',
        'mapped_at' => 'datetime',
    ];

    public const SUPPLIER_PLATFORMS = [
        'pinduoduo' => 'Pinduoduo',
        'zhuanzhuan' => 'Zhuanzhuan',
        'taobao' => 'Taobao',
        'jindong' => 'Jindong',
        'xiangyu' => 'Xiangyu',
        '1688' => '1688',
        'others' => 'Others',
    ];

    public static function supplierPlatforms(): array
    {
        return self::SUPPLIER_PLATFORMS;
    }

    /** True if this order pays the N10k logistics (outside Lagos) */
    public function getPaysLogisticsAttribute(): bool
    {
        return $this->logistics_type === 'outside_lagos';
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if (empty($order->status)) {
                $order->status = 'processing';
            }
        });
    }

    /** Parse WhatsApp-style description: supports both legacy and new format with emojis/bullets */
    public static function parseDescription(string $description): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $description)));
        $productName = null;
        $memory = null;
        $storage = null;
        $price = null;
        $appearancePct = null;
        $defectGrade = null;

        // Strip bullets (•) and leading emoji for parsing
        $cleanLine = static fn (string $s) => preg_replace('/^[\s•]*[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]?\s*/u', '', trim($s));

        foreach ($lines as $line) {
            if (preg_match('/^Model:\s*(.+)$/i', $line, $m)) {
                $productName = trim($m[1]);
                break;
            }
        }
        if ($productName === null && ! empty($lines)) {
            $first = $cleanLine($lines[0]);
            if ($first !== '' && ! preg_match('/^(?:Specifications?|Condition|Price):/i', $first)) {
                $productName = $first;
            }
        }

        foreach ($lines as $line) {
            $line = $cleanLine($line);
            if (preg_match('/^Storage:\s*(.+)$/i', $line, $m)) {
                $storage = trim($m[1]);
                break;
            }
        }
        foreach ($lines as $line) {
            $line = $cleanLine($line);
            if (preg_match('/^(?:Memory|RAM):\s*(.+)$/i', $line, $m)) {
                $memory = trim($m[1]);
                break;
            }
        }
        // Prefer Storage (e.g. 256 GB) for invoice; use memory/RAM only if no storage
        if ($storage === null && $memory !== null) {
            $storage = $memory;
        }

        foreach ($lines as $line) {
            $line = $cleanLine($line);
            if (preg_match('/^(?:Price|Cost|Amount):\s*(.+)$/i', $line, $m)) {
                $raw = trim($m[1]);
                $price = self::parsePriceString($raw);
                break;
            }
        }
        if ($price === null) {
            foreach ($lines as $line) {
                if (preg_match('/(?:₦|NGN|N)\s*([\d,.]+\s*[kKmM]?)/u', $line, $m)) {
                    $price = self::parsePriceString(trim($m[0]));
                    if ($price !== null) {
                        break;
                    }
                }
            }
        }

        foreach ($lines as $line) {
            $line = $cleanLine($line);
            if (preg_match('/^Appearance:\s*(\d+)\s*%/i', $line, $m)) {
                $appearancePct = (int) $m[1];
                break;
            }
        }
        if ($appearancePct === null) {
            foreach ($lines as $line) {
                if (preg_match('/(\d+)\s*%\s*(?:appearance|Like New)/i', $line, $m)) {
                    $appearancePct = (int) $m[1];
                    break;
                }
            }
        }

        foreach ($lines as $line) {
            $line = $cleanLine($line);
            if (preg_match('/Grade\s+([A-Da-dSs])/i', $line, $gm)) {
                $defectGrade = 'Grade ' . strtoupper($gm[1]);
                break;
            }
        }

        return [
            'product_name' => $productName ?: '',
            'memory' => $memory,
            'storage' => $storage,
            'price_ngn' => $price,
            'has_price_in_description' => $price !== null,
            'appearance_pct' => $appearancePct,
            'defect_grade' => $defectGrade,
        ];
    }

    /** Parse price string: supports ₦440k, 1,030,000, 450.5k, etc. */
    protected static function parsePriceString(string $raw): ?float
    {
        $raw = preg_replace('/[\s,]/', '', $raw);
        if (preg_match('/^[₦NGN]*([\d.]+)\s*([kKmM])?/u', $raw, $m)) {
            $num = (float) $m[1];
            $suffix = strtoupper($m[2] ?? '');
            if ($suffix === 'K') {
                $num *= 1000;
            } elseif ($suffix === 'M') {
                $num *= 1000000;
            }
            return $num;
        }
        $digits = preg_replace('/[^\d.]/', '', $raw);
        return $digits !== '' ? (float) $digits : null;
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

    /** Invoice requests for this order's shipment (may include other users in same shipment) */
    public function invoiceRequests(): HasMany
    {
        return $this->hasMany(InvoiceRequest::class, 'shipment_id', 'shipment_id');
    }

    /** The invoice request for this order's user (same shipment + user) */
    public function getInvoiceRequestAttribute(): ?InvoiceRequest
    {
        if ($this->relationLoaded('invoiceRequests')) {
            return $this->invoiceRequests->firstWhere('user_id', $this->user_id);
        }
        return InvoiceRequest::where('shipment_id', $this->shipment_id)
            ->where('user_id', $this->user_id)
            ->first();
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
        if ($stage && $stage->name === 'Delivered') {
            return 'delivered';
        }
        if ($shipmentId || ($stage && (int) $stage->position >= 2)) {
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
        if ($stage && $stage->name === 'Delivered') {
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
        return $pos >= 6; // Sent to Final Destination (6) or Delivered (7)
    }
}
