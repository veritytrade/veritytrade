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
    <title>Your shipment is on the move</title>
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
                                    <td align="center" style="padding:32px 28px 24px 28px;">
                                        <img src="{{ $logoUrl }}" alt="{{ $brandLegal }}" width="200" style="display:block;margin:0 auto;max-width:200px;height:auto;border:0;outline:none;text-decoration:none;" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px 32px 8px 32px;">
                            <h1 style="margin:0;font-size:22px;line-height:1.35;font-weight:700;color:#0f172a;letter-spacing:-0.02em;">
                                Your shipment is on the move
                            </h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 32px 32px;">
                            <p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">
                                Dear {{ $recipientName }},
                            </p>

                            @if($kind === 'stage')
                                <p style="margin:0 0 {{ ! empty($previousStageName) ? '10px' : '20px' }} 0;font-size:17px;line-height:1.45;font-weight:600;color:#0f172a;">
                                    {{ $newStageName }}
                                </p>
                                @if(! empty($previousStageName))
                                    <p style="margin:0 0 20px 0;font-size:15px;line-height:1.65;color:#475569;">
                                        Previously: {{ $previousStageName }}
                                    </p>
                                @endif
                            @else
                                <p style="margin:0 0 20px 0;font-size:17px;line-height:1.55;font-weight:600;color:#0f172a;">
                                    {{ $latestLine }}
                                </p>
                            @endif

                            @if(! empty($orderSummary))
                                <p style="margin:0 0 26px 0;font-size:15px;line-height:1.65;color:#475569;">
                                    Order<span style="color:#94a3b8;"> · </span>{{ $orderSummary }}
                                </p>
                            @endif

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0;">
                                <tr>
                                    <td align="left" style="padding:0;">
                                        <a href="{{ $trackingUrl }}" style="display:inline-block;padding:15px 28px;background-color:#059669;color:#ffffff !important;text-decoration:none;font-size:15px;font-weight:600;border-radius:10px;letter-spacing:0.01em;">Track your shipment</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:36px 0 0 0;padding-top:22px;border-top:1px solid #e2e8f0;text-align:left;line-height:1.55;">
                                <span style="display:inline-block;font-family:Georgia,'Times New Roman',serif;font-size:17px;font-weight:400;color:#059669;line-height:1;vertical-align:1px;margin-right:5px;">@</span><span style="font-size:14px;font-weight:600;color:#334155;letter-spacing:0.025em;">{{ $brandLegal }}</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
