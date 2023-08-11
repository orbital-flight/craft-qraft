<?php
/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\models;

use Craft;
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
            [['defaultSize'], 'in','range' => range(76, 1000), 'message' => Craft::t('qraft', 'The file size must be within 76 and 1000 pixels.')],
            [['defaultFormat'], 'in','range' => ['png', 'webp', 'svg'], 'message' => Craft::t('qraft', 'Invalid file format.')],
        ];
    }
}
