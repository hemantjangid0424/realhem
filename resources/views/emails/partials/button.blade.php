@props([
    'url' => '#',
    'color' => 'primary', // primary | success | outline
    'align' => 'center',
])

@php
    $bg = match ($color) {
        'success' => '#059669',
        'outline' => '#ffffff',
        default => '#2563eb',
    };
    $textColor = $color === 'outline' ? '#2563eb' : '#ffffff';
    $border = $color === 'outline' ? '2px solid #2563eb' : '1px solid '.$bg;
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 28px 0;">
    <tr>
        <td align="{{ $align }}">
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:48px;v-text-anchor:middle;width:240px;" arcsize="20%" stroke="f" fillcolor="{{ $bg }}">
            <w:anchorlock/>
            <center style="color:{{ $textColor }};font-family:sans-serif;font-size:15px;font-weight:bold;">
                {{ $slot }}
            </center>
            </v:roundrect>
            <![endif]-->
            <!--[if !mso]><!-->
            <a href="{{ $url }}" target="_blank" style="display: inline-block; background-color: {{ $bg }}; color: {{ $textColor }}; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 15px; font-weight: 700; text-decoration: none; padding: 14px 32px; border-radius: 12px; border: {{ $border }}; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25); text-align: center; letter-spacing: 0.2px;">
                {{ $slot }}
            </a>
            <!--<![endif]-->
        </td>
    </tr>
</table>

