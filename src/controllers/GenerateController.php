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

class GenerateController extends Controller {
    
    /**
     * actionCpQr
     * Controller for the cp panel generator
     *
     * @return void
     */
    public function actionCpQr() {
        
        $this->requireCpRequest();

        // Check the form thanks to the model
        $generator = $this->_fillModelFromPost();
        
        // If the model contains errors, bring them to the user
        if ($generator->getErrors()) {
            $this->setFailFlash('Couldn’t generate QR.');
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
            ];
    
            return $this->renderTemplate('qraft/generator', $variables, View::TEMPLATE_MODE_CP);
        }
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
