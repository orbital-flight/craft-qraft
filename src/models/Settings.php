<?php
/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\models;

use craft\base\Model;

/**
 * QRaft settings
 */
class Settings extends Model {
        
    /**
     * @var bool Whether to show the cp section
     */
    public $showCpSection = true;

    // Default QR values
    public $defaultSize = 500;
    public $defaultFormat = "png";

    protected function defineRules(): array {
        return [
            [['defaultSize'], 'required'],
            [['defaultSize'], 'integer'],
            [['defaultSize'], 'in','range' => range(76, 1000), 'message' => 'The default size must be within 76 and 1000 pixels.'],
            [['defaultFormat'], 'in','range' => ['png', 'webp', 'svg'], 'message' => 'Invalid file format.'],
        ];
    }
}
