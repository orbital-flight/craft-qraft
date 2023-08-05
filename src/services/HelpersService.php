<?php

/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\services;

use craft\base\Component;
use yii\web\NotFoundHttpException;
use Endroid\QrCode\Color\Color;

class HelpersService extends Component {
        
    /**
     * hexToQrColor
     * Gets a hexadecimal color value and returns a valid Endroid color object
     * Also adds an optional opacity to the color
     *
     * @param  mixed $hex
     * @param  mixed $opacity
     * @return void
     */
    public function hexToQrColor(string $hex, int $opacity = 100): Color {

        $hex = str_replace('#', '', $hex);
                
        // Extract the red, green, and blue components
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Turn opacity to transparency
        $transparency = round((100 - $opacity) * 127 / 100);

        return new Color($r, $g, $b, $transparency); 
    }
    
    /**
     * prettyFileSize
     * Gets the size of a file and returns a human readable representation of it (B / KB / MB)
     *
     * @param  mixed $file
     * @return string
     */
    public function prettyFileSize(string $file): string {
        if (!file_exists($file)) {
            throw new NotFoundHttpException("File not found!");
        }
    
        $fileSize = filesize($file);
        if ($fileSize > 1024) {
            if ($fileSize > 1024 * 1024) {
                return number_format($fileSize / 1024 / 1024) . "MB";
            } else {
                return number_format($fileSize / 1024) . "KB";
            }
        } else {
            return $fileSize . "B";
        }
    }
}
