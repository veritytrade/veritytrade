<?php

namespace App\Support;

/**
 * Maps carrier/logistics free-text (and optional API meta) to tracking stage positions and Nigeria subsections.
 */
final class CarrierTrackClassifier
{
    /**
     * @param  array<string, int>  $posByName  TrackingStage name => position from DB
     * @return array{stage: int, subsection: ?string, ignore: bool}
     */
    public static function classify(string $textEn, string $textCn, ?array $meta, array $posByName): array
    {
        $posProcessing = (int) ($posByName['Processing'] ?? 1);
        $posSent = (int) ($posByName['Sent to Logistics'] ?? 2);
        $posArrivedLogistics = (int) ($posByName['Arrived Logistics'] ?? 3);
        $posFlying = (int) ($posByName['Flying to Nigeria'] ?? 4);
        $posArrivedNigeria = (int) ($posByName['Arrived Nigeria'] ?? 5);
        $posFinalDestination = (int) ($posByName['Sent to Final Destination'] ?? 6);
        $posDelivered = (int) ($posByName['Delivered'] ?? 7);

        if (is_array($meta)) {
            $hint = strtolower(trim((string) ($meta['stage_hint'] ?? '')));
            if ($hint !== '') {
                $fromHint = self::mapStageHint(
                    $hint,
                    $posSent,
                    $posArrivedLogistics,
                    $posFlying,
                    $posArrivedNigeria,
                    $posFinalDestination,
                    $posDelivered
                );
                if ($fromHint !== null) {
                    $subsection = isset($meta['subsection']) && is_string($meta['subsection'])
                        ? $meta['subsection']
                        : null;

                    return ['stage' => $fromHint, 'subsection' => $subsection, 'ignore' => false];
                }
            }
        }

        $raw = trim($textEn.' '.$textCn);
        $t = strtolower(trim(preg_replace('/\s+/u', ' ', $raw) ?? ''));

        if ($t === '') {
            return ['stage' => $posSent, 'subsection' => null, 'ignore' => false];
        }

        // Billing / noise — hide from timeline
        if (
            str_contains($t, 'dear customer') ||
            str_contains($t, 'bill information') ||
            str_contains($t, 'company account') ||
            str_contains($t, 'warehouse address') ||
            str_contains($t, 'reserve the right to auction') ||
            str_contains($t, 'ask rate and confirm shipping money')
        ) {
            return ['stage' => $posSent, 'subsection' => null, 'ignore' => true];
        }

        // Nigeria: customer picked up at warehouse (not final home delivery)
        if (str_contains($t, 'picked up goods') || str_contains($t, 'customer has picked up')) {
            return ['stage' => $posArrivedNigeria, 'subsection' => 'Agent pickup', 'ignore' => false];
        }

        // Verity agent collected from logistics warehouse
        if (str_contains($t, 'package collected by verity agent') || str_contains($t, 'collected by agent')) {
            return ['stage' => $posArrivedNigeria, 'subsection' => 'Agent pickup', 'ignore' => false];
        }

        // Final delivery / completion (tight — avoid matching China "signed receipt" noise)
        if (self::looksLikeDeliveredComplete($t)) {
            return ['stage' => $posDelivered, 'subsection' => null, 'ignore' => false];
        }

        // Last mile in Nigeria → "Sent to Final Destination" (between Arrived Nigeria and Delivered)
        if (self::looksLikeFinalMileNigeria($t)) {
            return ['stage' => $posFinalDestination, 'subsection' => null, 'ignore' => false];
        }

        if (str_contains($t, 'to sign for it')) {
            return ['stage' => $posArrivedNigeria, 'subsection' => 'At logistics warehouse', 'ignore' => false];
        }

        // Nigeria airport / customs / warehouse (not last-mile)
        if (
            str_contains($t, 'lagos') ||
            str_contains($t, 'nigeria') ||
            str_contains($t, 'clearance') ||
            str_contains($t, 'airport express')
        ) {
            if (str_contains($t, 'pickup warehouse') || (str_contains($t, 'warehouse') && ! str_contains($t, 'guangzhou') && ! str_contains($t, 'baiyun'))) {
                return ['stage' => $posArrivedNigeria, 'subsection' => 'At logistics warehouse', 'ignore' => false];
            }

            if (str_contains($t, 'clearance') || str_contains($t, 'clearing') || str_contains($t, 'customs')) {
                return ['stage' => $posArrivedNigeria, 'subsection' => 'Customs clearance', 'ignore' => false];
            }

            return ['stage' => $posArrivedNigeria, 'subsection' => 'At Nigeria airport', 'ignore' => false];
        }

        // China / HK hub / ground handling (before flight)
        if (
            str_contains($t, 'truck') ||
            str_contains($t, 'guangzhou') ||
            str_contains($t, 'baiyun') ||
            str_contains($t, 'hong kong') ||
            str_contains($t, 'hongkong') ||
            str_contains($t, 'collected by [') ||
            str_contains($t, 'received express goods') ||
            str_contains($t, 'collected') ||
            str_contains($t, 'custom declaration') ||
            str_contains($t, 'inspection') ||
            str_contains($t, 'cannot pass') ||
            str_contains($t, 'security check') ||
            str_contains($t, 'contraband') ||
            str_contains($t, 'returned by the airport') ||
            str_contains($t, 'transferred to hong kong airport') ||
            str_contains($t, 'take-off')
        ) {
            return ['stage' => $posArrivedLogistics, 'subsection' => null, 'ignore' => false];
        }

        if (str_contains($t, 'flying') || str_contains($t, 'addis') || str_contains($t, 'hamad international airport')) {
            return ['stage' => $posFlying, 'subsection' => null, 'ignore' => false];
        }

        return ['stage' => $posSent, 'subsection' => null, 'ignore' => false];
    }

    private static function mapStageHint(
        string $hint,
        int $posSent,
        int $posArrivedLogistics,
        int $posFlying,
        int $posArrivedNigeria,
        int $posFinalDestination,
        int $posDelivered
    ): ?int {
        return match (true) {
            str_contains($hint, 'processing') => null,
            str_contains($hint, 'sent') && str_contains($hint, 'logistics') => $posSent,
            $hint === 'sent_to_logistics' => $posSent,
            str_contains($hint, 'logistics') && (str_contains($hint, 'arrived') || str_contains($hint, 'warehouse') || str_contains($hint, 'china')) => $posArrivedLogistics,
            $hint === 'arrived_logistics' => $posArrivedLogistics,
            str_contains($hint, 'flying') || str_contains($hint, 'in_transit') || str_contains($hint, 'transit') => $posFlying,
            str_contains($hint, 'nigeria') || $hint === 'arrived_nigeria' => $posArrivedNigeria,
            str_contains($hint, 'final') || str_contains($hint, 'last_mile') || str_contains($hint, 'dispatch') => $posFinalDestination,
            str_contains($hint, 'delivered') || str_contains($hint, 'complete') => $posDelivered,
            default => null,
        };
    }

    private static function looksLikeDeliveredComplete(string $t): bool
    {
        $phrases = [
            'already signed',
            'successfully delivered',
            'has been delivered',
            'delivery completed',
            'parcel delivered',
            'package delivered',
            'goods delivered',
            'delivered successfully',
            'delivered to recipient',
            'delivered to customer',
            'recipient signed',
            'receiver signed',
            'signed for the parcel',
            'signed for your parcel',
            'received by customer',
            'received by recipient',
        ];
        foreach ($phrases as $p) {
            if (str_contains($t, $p)) {
                return true;
            }
        }

        if (str_contains($t, 'undeliver') || str_contains($t, 'not delivered') || str_contains($t, 'failed delivery')) {
            return false;
        }

        if (str_contains($t, 'being delivered') || str_contains($t, 'out for delivery')) {
            return false;
        }

        if (str_contains($t, 'delivered')) {
            return true;
        }

        return false;
    }

    private static function looksLikeFinalMileNigeria(string $t): bool
    {
        $phrases = [
            'out for delivery',
            'out for dispatch',
            'delivery rider',
            'delivery man',
            'last mile',
            'on the way to you',
            'courier is',
            'your courier',
            'dispatch to',
            'dispatched for delivery',
            'sent to final',
            'doorstep',
            'address delivery',
        ];
        foreach ($phrases as $p) {
            if (str_contains($t, $p)) {
                return true;
            }
        }

        return false;
    }
}
