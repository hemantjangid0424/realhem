@props([
    'title' => null,
])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
    <tr>
        <td align="center" style="padding: 24px 0 12px;">
            <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}" target="_blank" style="text-decoration: none; display: inline-block;">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                    <tr>
                        <td style="vertical-align: middle; padding-right: 12px;">
                            <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); background-color: #2563eb; border-radius: 12px; text-align: center; line-height: 44px; color: #ffffff; font-weight: 900; font-size: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; letter-spacing: -0.5px; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);">
                                RH
                            </div>
                        </td>
                        <td style="vertical-align: middle; text-align: left;">
                            <div style="font-size: 24px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px; line-height: 1.1; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                                RealHem
                            </div>
                            <div style="font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.6px; text-transform: uppercase; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin-top: 2px;">
                                India's Premier Real Estate Portal
                            </div>
                        </td>
                    </tr>
                </table>
            </a>
        </td>
    </tr>
</table>

