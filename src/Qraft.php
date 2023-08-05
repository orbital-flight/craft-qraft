<?php
/**
 * @copyright Copyright (c) Orbital Flight
 */

namespace orbitalflight\qraft;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;
use orbitalflight\qraft\models\Settings;
use orbitalflight\qraft\services\GeneratorService;
use orbitalflight\qraft\services\HelpersService;
use orbitalflight\qraft\variables\QraftVariable;

/**
 * QRaft plugin
 *
 * @method static Qraft getInstance()
 * @method Settings getSettings()
 * @author Orbital Flight <flightorbital@gmail.com>
 * @copyright Orbital Flight
 * @license https://craftcms.github.io/license/ Craft License
 */
class Qraft extends Plugin {
    public static $plugin;
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;
    const EDITION_LITE = 'lite';
    const EDITION_PRO = 'pro';

    public static function editions(): array {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }
    
    public static function config(): array {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void {
        parent::init();
        self::$plugin = $this;

        $settings = $this->getSettings();
        $this->hasCpSection = $settings->showCpSection;

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            // ...
        });

        // Register services
        $this->setComponents([
            "generator" => GeneratorService::class,
            "helpers" => HelpersService::class,
        ]);

        // Register variable
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
            $variable = $event->sender;
            $variable->set('qraft', QraftVariable::class);

        });
    }

    protected function createSettingsModel(): ?Model {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string {
        return Craft::$app->view->renderTemplate('qraft/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
    }

    public function getCpNavItem(): ?array {
        $item = parent::getCpNavItem();

        // TODO: CPanel subnav section will be added with the presets release
        // if (Qraft::v(Qraft::EDITION_PRO)) {
        //     $item['subnav']['generator'] = ['label' => "Generator", 'url' => 'qraft/generator'];
        //     $item['subnav']['presets'] = ['label' => "Presets", 'url' => 'qraft/generator/presets.twig'];
        // }

        return $item;
    }

    // HELPERS 
    // ----------------------------------------------------------------
    public static function v ($version, $operator = '='): bool {
		return Qraft::getInstance()->is($version, $operator);
	}
}
