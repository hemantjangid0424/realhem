<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageWatermarkService
{
    /**
     * Common system TrueType font paths across macOS, Linux, and Windows.
     *
     * @var array<int, string>
     */
    private const FONT_CANDIDATES = [
        // macOS
        '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
        '/System/Library/Fonts/Supplemental/Arial.ttf',
        '/System/Library/Fonts/Geneva.ttf',
        '/Library/Fonts/Arial Bold.ttf',
        '/Library/Fonts/Arial.ttf',
        // Linux (Debian, Ubuntu, CentOS, Alpine)
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        // Windows
        'C:\\Windows\\Fonts\\arialbd.ttf',
        'C:\\Windows\\Fonts\\arial.ttf',
    ];

    /**
     * Process an uploaded image, apply brand watermark, and store to disk.
     *
     * @return string Stored relative file path
     */
    public function applyAndStore(UploadedFile $file, string $directory = 'properties', string $disk = 'public'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $filename = Str::uuid().'.'.$extension;
        $relativePath = trim($directory, '/').'/'.$filename;

        // Try watermarking. If GD fails, fallback to normal store to protect user flow.
        try {
            $watermarkedContent = $this->renderWatermark($file->getRealPath(), $extension);
            if ($watermarkedContent !== null) {
                Storage::disk($disk)->put($relativePath, $watermarkedContent);

                return $relativePath;
            }
        } catch (\Throwable $e) {
            Log::warning('Image watermarking failed, falling back to original upload: '.$e->getMessage(), [
                'file' => $file->getClientOriginalName(),
            ]);
        }

        // Fallback: store unmodified file
        $file->storeAs($directory, $filename, $disk);

        return $relativePath;
    }

    /**
     * Apply watermark in-place to an existing image file on disk.
     */
    public function applyToFile(string $filePath): bool
    {
        if (! file_exists($filePath) || ! is_readable($filePath) || ! is_writable($filePath)) {
            return false;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        try {
            $content = $this->renderWatermark($filePath, $extension);
            if ($content !== null) {
                file_put_contents($filePath, $content);

                return true;
            }
        } catch (\Throwable $e) {
            Log::warning('Watermark in-place failed: '.$e->getMessage(), ['path' => $filePath]);
        }

        return false;
    }

    /**
     * Render the watermark onto the given image and return raw binary image contents.
     */
    protected function renderWatermark(string $sourcePath, string $extension): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        // 1. Create GD image resource based on mime / extension
        $image = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($sourcePath),
            'png' => @imagecreatefrompng($sourcePath),
            'webp' => @imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (! $image) {
            return null;
        }

        // 2. Fix EXIF orientation for photos taken on phones (JPEG)
        if (in_array($extension, ['jpg', 'jpeg'], true) && function_exists('exif_read_data')) {
            $image = $this->autoRotateFromExif($image, $sourcePath);
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width < 50 || $height < 50) {
            imagedestroy($image);

            return null;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        // 3. Resolve brand text and font
        $brandText = (string) Setting::get('company_name', config('app.name', 'RealHem'));
        if (trim($brandText) === '') {
            $brandText = 'RealHem';
        }

        $fontPath = $this->resolveFontPath();

        // 4. Draw watermarks
        if ($fontPath !== null) {
            $this->drawTtfWatermarks($image, $width, $height, $brandText, $fontPath);
        } else {
            $this->drawBuiltinWatermarks($image, $width, $height, $brandText);
        }

        // 5. Output image stream
        ob_start();
        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, null, 90),
            'png' => imagepng($image, null, 8),
            'webp' => imagewebp($image, null, 90),
            default => imagejpeg($image, null, 90),
        };
        $output = ob_get_clean();

        imagedestroy($image);

        return $output !== false ? $output : null;
    }

    /**
     * Draw TrueType anti-aliased watermarks:
     * - Subtle diagonal watermark in center.
     * - Sleek semi-transparent pill badge in bottom-right corner.
     */
    protected function drawTtfWatermarks(\GdImage $image, int $width, int $height, string $brandText, string $fontPath): void
    {
        // -------------------------------------------------------------
        // A. Subtle diagonal watermark across the center
        // -------------------------------------------------------------
        $centerFontSize = max(16, (int) round($width * 0.05));
        $angle = 25;

        // Soft drop-shadow + text with ~78% transparency (alpha 100 out of 127)
        $centerShadow = (int) imagecolorallocatealpha($image, 0, 0, 0, 108);
        $centerText = (int) imagecolorallocatealpha($image, 255, 255, 255, 96);

        $bbox = @imagettfbbox($centerFontSize, $angle, $fontPath, $brandText);
        if ($bbox !== false) {
            $textWidth = abs($bbox[4] - $bbox[0]);
            $textHeight = abs($bbox[5] - $bbox[1]);
            $cx = (int) round(($width - $textWidth) / 2);
            $cy = (int) round(($height + $textHeight) / 2);

            @imagettftext($image, $centerFontSize, $angle, $cx + 2, $cy + 2, $centerShadow, $fontPath, $brandText);
            @imagettftext($image, $centerFontSize, $angle, $cx, $cy, $centerText, $fontPath, $brandText);
        }

        // -------------------------------------------------------------
        // B. Sleek bottom-right watermark pill badge
        // -------------------------------------------------------------
        $badgeFontSize = max(11, (int) round($width * 0.022));
        $badgeBbox = @imagettfbbox($badgeFontSize, 0, $fontPath, $brandText);

        if ($badgeBbox !== false) {
            $bw = abs($badgeBbox[2] - $badgeBbox[0]);
            $bh = abs($badgeBbox[7] - $badgeBbox[1]);

            $padX = max(8, (int) round($badgeFontSize * 0.7));
            $padY = max(5, (int) round($badgeFontSize * 0.45));
            $margin = max(12, (int) round($width * 0.02));

            $boxW = $bw + ($padX * 2);
            $boxH = $bh + ($padY * 2);
            $boxX = $width - $boxW - $margin;
            $boxY = $height - $boxH - $margin;

            // Semi-transparent dark slate backdrop pill (~70% opaque)
            $pillBg = (int) imagecolorallocatealpha($image, 15, 23, 42, 40);
            imagefilledrectangle($image, $boxX, $boxY, $boxX + $boxW, $boxY + $boxH, $pillBg);

            // Subtle border
            $pillBorder = (int) imagecolorallocatealpha($image, 255, 255, 255, 95);
            imagerectangle($image, $boxX, $boxY, $boxX + $boxW, $boxY + $boxH, $pillBorder);

            // Crisp white text inside pill
            $pillTextColor = (int) imagecolorallocatealpha($image, 255, 255, 255, 8);
            $textX = $boxX + $padX;
            $textY = $boxY + $padY + $bh;
            @imagettftext($image, $badgeFontSize, 0, $textX, $textY, $pillTextColor, $fontPath, $brandText);
        }
    }

    /**
     * Fallback watermark rendering when no TrueType font file is present.
     */
    protected function drawBuiltinWatermarks(\GdImage $image, int $width, int $height, string $brandText): void
    {
        $font = 5; // Built-in GD font 5 (9x15 px)
        $charWidth = imagefontwidth($font);
        $charHeight = imagefontheight($font);

        $textWidth = strlen($brandText) * $charWidth;
        $margin = 15;

        $boxW = $textWidth + 16;
        $boxH = $charHeight + 10;
        $boxX = $width - $boxW - $margin;
        $boxY = $height - $boxH - $margin;

        // Background pill
        $pillBg = (int) imagecolorallocatealpha($image, 15, 23, 42, 45);
        imagefilledrectangle($image, $boxX, $boxY, $boxX + $boxW, $boxY + $boxH, $pillBg);

        // Text
        $textColor = (int) imagecolorallocatealpha($image, 255, 255, 255, 10);
        imagestring($image, $font, $boxX + 8, $boxY + 5, $brandText, $textColor);
    }

    /**
     * Locate the first available TrueType font file.
     */
    protected function resolveFontPath(): ?string
    {
        // Custom project font if set
        $customFont = resource_path('fonts/watermark.ttf');
        if (file_exists($customFont)) {
            return $customFont;
        }

        foreach (self::FONT_CANDIDATES as $candidate) {
            if (file_exists($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Auto-rotate JPEG image if phone camera EXIF orientation tag is set.
     */
    protected function autoRotateFromExif(\GdImage $image, string $filePath): \GdImage
    {
        try {
            $exif = @exif_read_data($filePath);
            if (! empty($exif['Orientation'])) {
                $rotated = match ($exif['Orientation']) {
                    3 => imagerotate($image, 180, 0),
                    6 => imagerotate($image, -90, 0),
                    8 => imagerotate($image, 90, 0),
                    default => null,
                };

                if ($rotated instanceof \GdImage) {
                    imagedestroy($image);

                    return $rotated;
                }
            }
        } catch (\Throwable) {
            // Ignore EXIF parse errors
        }

        return $image;
    }
}
