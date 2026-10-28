<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top: 32px;">
    <tr>
        <td align="center" style="padding: 12px 20px 40px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
            <!-- Quick navigation links -->
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom: 18px;">
                <tr>
                    <td style="padding: 0 10px;">
                        <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}/listings" target="_blank" style="color: #475569; font-size: 12px; font-weight: 600; text-decoration: none;">Browse Properties</a>
                    </td>
                    <td style="color: #cbd5e1; font-size: 12px;">•</td>
                    <td style="padding: 0 10px;">
                        <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}/post-property" target="_blank" style="color: #475569; font-size: 12px; font-weight: 600; text-decoration: none;">Post Free Listing</a>
                    </td>
                    <td style="color: #cbd5e1; font-size: 12px;">•</td>
                    <td style="padding: 0 10px;">
                        <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}/contact-us" target="_blank" style="color: #475569; font-size: 12px; font-weight: 600; text-decoration: none;">Contact Us</a>
                    </td>
                    <td style="color: #cbd5e1; font-size: 12px;">•</td>
                    <td style="padding: 0 10px;">
                        <a href="{{ config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:3000')) }}/privacy-policy" target="_blank" style="color: #475569; font-size: 12px; font-weight: 600; text-decoration: none;">Privacy Policy</a>
                    </td>
                </tr>
            </table>

            <!-- Security advisory -->
            <div style="background-color: #f1f5f9; border-radius: 10px; padding: 12px 16px; margin: 0 auto 18px; max-width: 520px; text-align: center;">
                <p style="margin: 0; color: #64748b; font-size: 11px; line-height: 1.5;">
                    <strong style="color: #334155;">Security Notice:</strong> RealHem representatives will never ask for your passwords, OTP codes, credit/debit card numbers, or UPI PINs via email or SMS.
                </p>
            </div>

            <!-- Copyright and address -->
            <p style="margin: 0 0 6px; color: #94a3b8; font-size: 11px; line-height: 1.5;">
                &copy; {{ date('Y') }} RealHem India Pvt. Ltd. All rights reserved.
            </p>
            <p style="margin: 0; color: #94a3b8; font-size: 11px; line-height: 1.5;">
                Bengaluru &bull; Mumbai &bull; Delhi NCR &bull; Hyderabad &bull; Pune &bull; Ahmedabad
            </p>
        </td>
    </tr>
</table>

