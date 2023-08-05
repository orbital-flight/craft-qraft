<?php
/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\variables;

use Craft;
use craft\helpers\Template;
use orbitalflight\qraft\Qraft;
use orbitalflight\qraft\models\GeneratorModel;

class QraftVariable {
        
    /**
     * getDefault
     * Fetch a default GeneratorModel
     *
     * @return GeneratorModel
     */
    public function getDefault(): GeneratorModel {
        return Qraft::$plugin->generator->getDefaultGenerator();
    }
    
    /**
     * qr
     * Allows users to generate their qr from the frontend
     *
     * @param  mixed $content
     * @param  mixed $options
     * @return void
     */
    public function qr(string $content, array $options = null) {

        $settings = Qraft::getInstance()->getSettings();

        // 1) Fill a model with the request
        $generator = new GeneratorModel();
        $generator->content = $content;
        $generator->size = (isset($options['size']) ? $options['size'] : $settings->defaultSize);
        $generator->format = (isset($options['format'])) ? strtolower($options['format']) : $settings->defaultFormat;
        $generator->foregroundColor = (isset($options['foregroundColor'])) ? str_replace('#', '', $options['foregroundColor']) : $generator->foregroundColor;
        $generator->backgroundColor = (isset($options['backgroundColor'])) ? str_replace('#', '', $options['backgroundColor']) : $generator->backgroundColor;
        $generator->foregroundOpacity = (isset($options['foregroundOpacity'])) ? $options['foregroundOpacity'] : $generator->foregroundOpacity;
        $generator->noBackground = (isset($options['noBackground'])) ? (bool)$options['noBackground'] : $generator->noBackground;
        
        // Enforce pro version
        if (!Qraft::$plugin->generator->payCheckMate($generator)) {
            return "QRaft – Sorry, customization is a PRO feature only.";
        }

        // Validate and return errors if necessary
        $generator->validate();
        if ($generator->getErrors()) {
            $errors = 'QRaft – Error while generating the QR code : <br>';
            foreach ($generator->getErrors() as $property => $error) {
                $errors .= $property . ': ' . $error[0] . '<br>';
            }

            return Template::raw($errors);
        }

        // 4) Generate qr code
        $qrUrl = Qraft::$plugin->generator->createQr($generator);
        $qrFile = Craft::$app->assetManager->getPublishedUrl($qrUrl);

        // 5) Return an <img> tag
        $alt = (isset($options['alt'])) ? htmlspecialchars($options['alt']) : "QR Code provided by the QRaft plugin";
        $class = (isset($options['class'])) ? htmlspecialchars($options['class']) : "";
        $img = '<img src="'. $qrFile .'" alt="'. $alt .'" class="'. $class .'">';

        return Template::raw($img);
    }
}