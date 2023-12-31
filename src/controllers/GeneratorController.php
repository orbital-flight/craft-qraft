<?php

/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\controllers;

use Craft;
use craft\web\Controller;
use craft\web\View;
use orbitalflight\qraft\Qraft;
use orbitalflight\qraft\models\GeneratorModel;

class GeneratorController extends Controller {
    
    /**
     * actionCpQr
     * Controller for the cp panel generator
     *
     * @return void
     */
    public function actionCpQr() {
        
        $this->requireCpRequest();

        // Get and check a model from the from
        $generator = $this->_fillModelFromPost();
        
        // If the model contains errors, bring them to the user
        if ($generator->getErrors()) {
            $this->setFailFlash(Craft::t('qraft', 'Couldn’t generate QR.'));
            return $this->renderTemplate('qraft/generator', ['generator' => $generator], View::TEMPLATE_MODE_CP);
        } else {
            // Create and return the QR code and its informations
            $qrUrl = Qraft::$plugin->generator->createQr($generator);
            $qrFileSize = Qraft::$plugin->helpers->prettyFileSize($qrUrl);

            $variables = [
                'qrFile' => Craft::$app->assetManager->getPublishedUrl($qrUrl),
                'qrUrl' => $qrUrl,
                'qrFileSize' => $qrFileSize,
                'qrFileExtension' => $generator->format,
                'qrPreset' => '',
                'generator' => $generator
            ];
    
            return $this->renderTemplate('qraft/generator', $variables, View::TEMPLATE_MODE_CP);
        }
    }

    /**
     * actionDownload
     * Download the QR Code as a file after its generation.
     */
    public function actionDownload() {
        
        $this->requireCpRequest();
        
        $filePath = Craft::$app->getRequest()->getRequiredBodyParam('qrUrl');
        
        // Get the file extenstion
        $parts = explode('.', $filePath);
        $fileExtension = end($parts);
        $fileName = "QRaft-" . uniqid() . "." . $fileExtension;

        return $this->response->sendFile($filePath, $fileName);
    }
    
    /**
     * actionModify
     * Recovers the previous customizations and pass them to the generator template
     * Also removes temporary files
     *
     * @return void
     */
    public function actionModify() {

        $this->requireCpRequest();
        
        // Remove all unwanted files
        $oldFileTemp = Craft::$app->getRequest()->getRequiredBodyParam('qrUrl');
        if (file_exists($oldFileTemp)) {
            unlink($oldFileTemp);
        }

        $oldFileCp = Craft::$app->getRequest()->getRequiredBodyParam('qrFile');
        if (file_exists($oldFileCp)) {
            unlink($oldFileCp);
        }

        // Check the form thanks to the model
        $generator = $this->_fillModelFromPost();
        $variables = [];
        
        // If the model contains errors, set a fail flash message
        if ($generator->getErrors()) {
            $this->setFailFlash(Craft::t('qraft', 'Couldn’t get previous presets.'));
        } else {
            // Provide the model if it comes clean
            $variables = ['generator' => $generator];
        }
        
        // Bring the user to the generator page
        return $this->renderTemplate('qraft/generator', $variables, View::TEMPLATE_MODE_CP);
    }

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
     * _fillModelFromPost
     * Assigns the post values in a new GeneratorModel and validates it
     *
     * @return GeneratorModel
     */
    private function _fillModelFromPost(): GeneratorModel {
        
        $this->requirePostRequest();

        $settings = Qraft::$plugin->getSettings();
        $request = Craft::$app->getRequest();
        $generator = new GeneratorModel();

        $generator->content = $request->getBodyParam('content', $generator->content);
        $generator->size = (int)$request->getBodyParam('size', $settings->defaultSize);
        $generator->format = $request->getBodyParam('format', $settings->defaultFormat);
        $generator->foregroundColor = $request->getBodyParam('foregroundColor', $generator->foregroundColor);
        $generator->backgroundColor = $request->getBodyParam('backgroundColor', $generator->backgroundColor);
        $generator->foregroundOpacity = $request->getBodyParam('foregroundOpacity', $generator->foregroundOpacity);
        $generator->noBackground = (bool)$request->getBodyParam('noBackground', $generator->noBackground);        
        $generator->logo = (int)$request->getBodyParam('logo', null);
        $generator->validate();

        return $generator;
    }
}
