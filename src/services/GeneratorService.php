<?php

/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft\services;

use Craft;
use craft\base\Component;
use yii\web\BadRequestHttpException;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\WebPWriter;
use Endroid\QrCode\Writer\SvgWriter;
use orbitalflight\qraft\Qraft;
use orbitalflight\qraft\models\GeneratorModel;

use function Symfony\Component\String\b;

class GeneratorService extends Component {
        
    /**
     * createQr
     * A) Choose the appropriate writer
     * B) Check if provided params belong or not to the PRO version
     * C) Generate the QR file according to provided params
     * D) Save the QR file in the temporary folder and return the file path
     * 
     * @param  mixed $generator
     * @return string
     */
    public function createQr(GeneratorModel $generator): string {

        // A) Choose the appropriate writer
        switch ($generator->format) {
            case 'png':
                $writer = new PngWriter();
                break;
            case 'svg':
                $writer = new SvgWriter();
                break;
            case 'webp':
                $writer = new WebPWriter();
                break;
            default:
                throw new BadRequestHttpException("Invalid format");
        }

        // B) Check if provided params belong or not to the PRO version
        if (!$this->payCheckMate($generator)) {
            // throw new BadRequestHttpException("yOu rUiNEd mY kiD bDAy");
            throw new BadRequestHttpException("Sorry, customization is a PRO feature only.");
        }

        // C) Generate the QR file according to provided params
        // - Colors
        $foregroundColor = Qraft::$plugin->helpers->hexToQrColor($generator->foregroundColor, $generator->foregroundOpacity);
        $backgroundColor = Qraft::$plugin->helpers->hexToQrColor($generator->backgroundColor, $generator->noBackground ? 0 : 100);

        // - Logo
        if ($generator->logo) {
            $logoId = $generator->logo;
            if (Craft::$app->getElements()->getElementTypeById($logoId) !== 'craft\elements\Asset') {
                throw new BadRequestHttpException('Provided logo is not of type craft\elements\Asset');
            }

            $logo = Craft::$app->getElements()->getElementById($generator->logo);
            if ($logo->kind !== 'image') {
                throw new BadRequestHttpException('Logo is not an image');
            }

            if ($logo->format === 'svg') {
                throw new BadRequestHttpException('SVG logo is not supported');
            }
        }

        $builder = Builder::create()
            ->writer($writer)
            ->writerOptions([])
            ->data($generator->content)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size($generator->size)
            ->margin(0)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->foregroundColor($foregroundColor)
            ->backgroundColor($backgroundColor);

        // Add logo
        if ($generator->logo) {
            $builder->logoPath($logo->url);
            if ($logo->width > $logo->height) {
                $builder->logoResizeToWidth($generator->size / 4);
            } else {
                $builder->logoResizeToHeight($generator->size / 4);
            }
        }

        $result = $builder->validateResult(false)->build();

        // Save the result in Craft temporary folder.
        $craftTempPath = Craft::$app->getPath()->getTempPath();
        if (!is_dir($craftTempPath . "/QRaft/")) {
            mkdir($craftTempPath . "/QRaft/");
        }
        $filePath = $craftTempPath . "/QRaft/QRaft-" . uniqid() . "." . $generator->format;
        file_put_contents($filePath, $result->getString());

        return $filePath;
    }
    
    /**
     * getDefaultGenerator
     * Sends the default GeneratorModel
     *
     * @return GeneratorModel
     */
    public function getDefaultGenerator(): GeneratorModel {
        $settings = Qraft::$plugin->getSettings();

        $generator = new GeneratorModel();
        $generator->size = $settings->defaultSize;
        $generator->format = $settings->defaultFormat;

        return $generator;
    }
    
    /**
     * payCheckMate
     * Returns false when using pro feature while version is lite.
     * 
     * @param  mixed $request
     * @return bool
     */
    public function payCheckMate(GeneratorModel $request): bool {
        
        if (Qraft::v("pro", '<')) {
            $defaultGenerator = $this->getDefaultGenerator();

            if ( // Check the request against the default Model
                $request->foregroundColor != $defaultGenerator->foregroundColor ||
                $request->backgroundColor != $defaultGenerator->backgroundColor ||
                $request->foregroundOpacity != $defaultGenerator->foregroundOpacity ||
                $request->logo != $defaultGenerator->logo ||
                $request->noBackground != $defaultGenerator->noBackground
            ) {
                return false;
            }
        }

        return true;
    }
}
