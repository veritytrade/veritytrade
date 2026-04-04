<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $kind === 'stage' ? 'Shipment update' : 'Logistics update' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Segoe UI,system-ui,-apple-system,Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background-color:#ffffff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 28px 8px 28px;">
                            <p style="margin:0;font-size:13px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#6b7280;">{{ $appName }}</p>
                            <h1 style="margin:12px 0 0 0;font-size:20px;line-height:1.35;font-weight:700;color:#111827;">
                                @if($kind === 'stage')
                                    Your shipment has moved to a new stage
                                @else
                                    New logistics activity on your order
                                @endif
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 28px 24px 28px;">
                            <p style="margin:0 0 16px 0;font-size:15px;line-height:1.6;color:#374151;">
                                Dear {{ $recipientName }},
                            </p>
                            <p style="margin:0 0 16px 0;font-size:15px;line-height:1.6;color:#374151;">
                                @if($kind === 'stage')
                                    We are writing to let you know your order has progressed in our logistics process.
                                @else
                                    Our logistics partner has reported new activity relevant to your order.
                                @endif
                                Full details are available in your secure customer dashboard—tracking codes and sensitive references are never included in email for your security.
                            </p>
                            @if(! empty($orderSummary))
                                <p style="margin:0 0 16px 0;font-size:14px;line-height:1.55;color:#4b5563;background-color:#f9fafb;border-left:4px solid #2563eb;padding:12px 14px;border-radius:0 8px 8px 0;">
                                    <strong style="color:#111827;">Order:</strong> {{ $orderSummary }}
                                </p>
                            @endif
                            @if($kind === 'stage')
                                <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 20px 0;border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:12px 14px;background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;">
                                            <p style="margin:0 0 6px 0;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:#6b7280;">Current stage</p>
                                            <p style="margin:0;font-size:16px;font-weight:600;color:#111827;">{{ $newStageName }}</p>
                                            @if(! empty($previousStageName))
                                                <p style="margin:10px 0 0 0;font-size:13px;color:#6b7280;">Previously: {{ $previousStageName }}</p>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif
                            @if($kind === 'carrier')
                                <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 20px 0;border-collapse:collapse;">
                                    <tr>
                                        <td style="padding:12px 14px;background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;">
                                            <p style="margin:0 0 6px 0;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;color:#6b7280;">Latest update (from carrier)</p>
                                            <p style="margin:0;font-size:15px;line-height:1.5;color:#111827;">{{ $latestLine }}</p>
                                            @if(! empty($latestAt))
                                                <p style="margin:10px 0 0 0;font-size:13px;color:#6b7280;">Recorded time: {{ $latestAt }}</p>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @endif
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px 0;">
                                <tr>
                                    <td>
                                        <a href="{{ $trackingUrl }}" style="display:inline-block;padding:14px 22px;background-color:#16a34a;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;border-radius:8px;">View tracking in dashboard</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 16px 0;font-size:13px;line-height:1.55;color:#6b7280;">
                                Automated logistics emails are sent only while your package is in transit. Once it has been marked <strong>dispatched</strong>, we will not send further updates by email so you can confirm delivery in your dashboard when your package arrives.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.55;color:#6b7280;">
                                If you have questions, please reply to this message or contact us using the details on our website.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px 24px 28px;border-top:1px solid #e5e7eb;background-color:#fafafa;">
                            <p style="margin:0;font-size:11px;line-height:1.5;color:#9ca3af;">
                                This is a transactional message from {{ $appName }} regarding an order you placed. Please do not share links from this email with unknown parties.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
