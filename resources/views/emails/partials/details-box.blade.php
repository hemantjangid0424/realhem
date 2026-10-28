@props([
    'title' => null,
])
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 24px 0; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;">
    @if ($title)
        <tr>
            <td style="padding: 14px 20px; background-color: #f1f5f9; border-bottom: 1px solid #e2e8f0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 13px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ $title }}
            </td>
        </tr>
    @endif
    <tr>
        <td style="padding: 18px 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            {{ $slot }}
        </td>
    </tr>
</table>

