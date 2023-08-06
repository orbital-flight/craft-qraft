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
     * Requests a qr code and returns it as a raw <img> tag
     *
     * @param  mixed $content
     * @param  mixed $options
     * @return void
     */
    public function qr(string $content, array $options = null) {

        $qrFile = Qraft::$plugin->generator->qr($content, $options);
        if (!file_exists($qrFile)) {
            return $qrFile;
        }

        $qrUrl = Craft::$app->assetManager->getPublishedUrl($qrFile);

        $alt = (isset($options['alt'])) ? htmlspecialchars($options['alt']) : "QR Code provided by the QRaft plugin";
        $class = (isset($options['class'])) ? htmlspecialchars($options['class']) : "";
        $img = '<img src="'. $qrUrl .'" alt="'. $alt .'" class="'. $class .'">';

        return Template::raw($img);
    }
}