<?php

/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\controllers;

use Craft;
use craft\web\Controller;
use craft\web\View;

class AfterController extends Controller {
    
    /**
     * actionNew
     * Bring to user back to the previous page, with the used preset if available
     * 
     */
    public function actionNew() {
        $variables = [
            'preset' => '' // TODO: this
        ];

        return $this->renderTemplate('qraft/generator', $variables, View::TEMPLATE_MODE_CP);
    }
        
    /**
     * actionDownload
     * Download the QR Code as a file after its generation.
     */
    public function actionDownload() {
        $filePath = Craft::$app->getRequest()->getRequiredBodyParam('qrUrl');
        
        // Get the file extenstion
        $parts = explode('.', $filePath);
        $fileExtension = end($parts);
        $fileName = "QRaft-" . uniqid() . "." . $fileExtension;

        return $this->response->sendFile($filePath, $fileName);
    }
}
