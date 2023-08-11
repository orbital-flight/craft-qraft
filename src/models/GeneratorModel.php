<?php
/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\models;

use Craft;
use craft\base\Model;

class GeneratorModel extends Model {
    
    // Properties
    public string $title;
    public string $content = '';
    public int $size;
    public string $format;
    public string $foregroundColor = '000000';
    public string $backgroundColor = 'ffffff';
    public int $foregroundOpacity = 100;
    public bool $noBackground = false;
    public int $logo = 0;

    // Methods
    public function __toString() {
        return $this->title;
    }

    // Rules
    public function rules(): array {
        return [
            [['content', 'size', 'format', 'foregroundColor', 'backgroundColor'], 'required'],
            [['size'], 'in','range' => range(76, 1000), 'message' => Craft::t('qraft', 'The file size must be within 76 and 1000 pixels.')],
            [['format'], 'in','range' => ['png', 'webp', 'svg'], 'message' => Craft::t('qraft', 'Invalid file format.')],
            [['foregroundColor', 'backgroundColor'], 'match', 'pattern' => '/^([0-9a-fA-F]{3}){1,2}$/', 'message' => Craft::t('qraft', 'Invalid color format.')],
            [['foregroundOpacity'], 'in','range' => range(0, 100), 'message' => Craft::t('qraft', 'Opacity must be within 0 and 100%.')],
        ];
    }
}