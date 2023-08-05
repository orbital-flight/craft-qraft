<?php
/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\models;

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
        return $this->name;
    }

    // Rules
    public function rules(): array {
        return [
            [['content', 'size', 'format', 'foregroundColor', 'backgroundColor'], 'required'],
            [['size'], 'in','range' => range(76, 1000), 'message' => 'The default size must be within 76 and 1000 pixels.'],
            [['format'], 'in','range' => ['png', 'webp', 'svg'], 'message' => 'Invalid file format.'],
            [['foregroundColor', 'backgroundColor'], 'match', 'pattern' => '/^([0-9a-fA-F]{3}){1,2}$/', 'message' => 'Invalid color format.'],
            [['foregroundOpacity'], 'in','range' => range(0, 100), 'message' => 'Opacity must be within 0 and 100%.'],
        ];
    }
}