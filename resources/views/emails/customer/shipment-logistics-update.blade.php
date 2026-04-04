@php
    $brandLegal = (string) config('app.brand_legal_name', 'Verity Trade Global Limited');
    $logoUrl = asset('images/invoice/logo.png');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $kind === 'stage' ? 'Shipment update' : 'Logistics update' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#e8ecf1;font-family:'Segoe UI',system-ui,-apple-system,BlinkMacSystemFont,'Helvetica Neue',Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#334155;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#e8ecf1;padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;background-color:#ffffff;border-radius:16px;border:1px solid #d8dee6;box-shadow:0 12px 40px rgba(15,23,42,0.08);overflow:hidden;">
                    <tr>
                        <td style="padding:0;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);border-bottom:3px solid #059669;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:32px 28px 20px 28px;">
                                        <img src="{{ $logoUrl }}" alt="{{ $brandLegal }}" width="200" style="display:block;margin:0 auto;max-width:200px;height:auto;border:0;outline:none;text-decoration:none;" />
                                        <p style="margin:16px 0 0 0;font-size:13px;font-weight:600;letter-spacing:0.02em;color:#64748b;line-height:1.4;">{{ $brandLegal }}</p>
                                        <p style="margin:6px 0 0 0;font-size:11px;font-weight:500;letter-spacing:0.12em;text-transform:uppercase;color:#94a3b8;">{{ $appName }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px 10px 32px;">
                            <h1 style="margin:0;font-size:22px;line-height:1.35;font-weight:700;color:#0f172a;letter-spacing:-0.02em;">
                                @if($kind === 'stage')
                                    Your shipment has moved to a new stage
                                @else
                                    New logistics activity on your order
                                @endif
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:6px 32px 28px 32px;">
                            <p style="margin:0 0 18px 0;font-size:15px;line-height:1.65;color:#475569;">
                                Dear {{ $recipientName }},
                            </p>
                            <p style="margin:0 0 18px 0;font-size:15px;line-height:1.65;color:#475569;">
                                @if($kind === 'stage')
                                    We are writing to let you know your order has progressed in our logistics process.
                                @else
                                    Our logistics partner has reported new activity relevant to your order.
                                @endif
                                For your security, tracking codes and sensitive references are only shown in your secure customer dashboard—not in email.
                            </p>
                            @if(! empty($orderSummary))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 22px 0;">
                                    <tr>
                                        <td style="padding:14px 16px;background-color:#f1f5f9;border-radius:10px;border-left:4px solid #059669;">
                                            <p style="margin:0;font-size:14px;line-height:1.55;color:#475569;">
                                                <strong style="color:#0f172a;">Order</strong><span style="color:#94a3b8;"> · </span>{{ $orderSummary }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @endif
                            @if($kind === 'stage')
                                <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 22px 0;border-collapse:separate;border-spacing:0;">
                                    <tr>
                                        <td style="padding:16px 18px;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                                            <p style="margin:0 0 8px 0;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;">Current stage</p>
                                            <p style="margin:0;font-size:17px;font-weight:700;color:#0f172a;line-height:1.35;">{{ $newStageName }}</p>
                                            @if(! empty($previousStageName))
                                                <p style="margin:12px 0 0 0;font-size:13px;color:#64748b;line-height:1.5;">Previously: <span style="color:#475569;">{{ $previousStageName }}</span></p>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif
                            @if($kind === 'carrier')
                                <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 22px 0;border-collapse:separate;border-spacing:0;">
                                    <tr>
                                        <td style="padding:16px 18px;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;">
                                            <p style="margin:0 0 8px 0;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;">Latest update (carrier)</p>
                                            <p style="margin:0;font-size:15px;line-height:1.55;color:#0f172a;">{{ $latestLine }}</p>
                                            @if(! empty($latestAt))
                                                <p style="margin:12px 0 0 0;font-size:13px;color:#64748b;">Recorded: {{ $latestAt }}</p>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 24px 0;">
                                <tr>
                                    <td align="center" style="padding:4px 0;">
                                        <a href="{{ $trackingUrl }}" style="display:inline-block;padding:15px 28px;background-color:#059669;color:#ffffff !important;text-decoration:none;font-size:15px;font-weight:600;border-radius:10px;letter-spacing:0.01em;">View tracking in dashboard</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 16px 0;font-size:13px;line-height:1.6;color:#64748b;">
                                Automated logistics notifications are sent only while your shipment is <strong style="color:#475569;font-weight:600;">in transit</strong>. After your order is marked <strong style="color:#475569;font-weight:600;">dispatched</strong>, we do not send further email updates—please check your dashboard for final delivery status.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
                                If you need assistance, reply to this email or contact us through the details on our website.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 28px 32px;border-top:1px solid #e2e8f0;background:linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%);">
                            <p style="margin:0 0 10px 0;font-size:11px;line-height:1.55;color:#94a3b8;text-align:center;">
                                {{ $brandLegal }} &middot; Transactional notice regarding your order
                            </p>
                            <p style="margin:0;font-size:11px;line-height:1.55;color:#94a3b8;text-align:center;">
                                This message was sent by {{ $appName }}. Do not share dashboard links with unknown parties.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
