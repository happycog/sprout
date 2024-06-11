<?php

namespace BarrelStrength\Sprout\forms\forms;

use BarrelStrength\Sprout\forms\components\elements\SubmissionElement;
use BarrelStrength\Sprout\forms\components\formfields\MissingFormField;
use BarrelStrength\Sprout\forms\formfields\CustomFormField;
use BarrelStrength\Sprout\forms\formfields\FormFieldInterface;
use Craft;
use craft\base\FieldInterface;
use craft\base\FieldLayoutElement;
use craft\helpers\ArrayHelper;
use craft\helpers\Cp;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;

class FormBuilderHelper
{
    public static function getFieldData(string $fieldUid = null): ?FieldInterface
    {
        if ($fieldUid) {
            $field = Craft::$app->getFields()->getFieldByUid($fieldUid);
        }

        return $field ?? new MissingFormField();
    }

    public static function createSubmissionFieldLayoutFromConfig(array $config): FieldLayout
    {
        $tabConfigs = ArrayHelper::remove($config, 'tabs');
        $layout = new FieldLayout($config);
        $layout->type = SubmissionElement::class;

        if (is_array($tabConfigs)) {
            $layout->setTabs(array_values(array_map(
                static fn(array $tabConfig) => self::createSubmissionFieldLayoutTabFromConfig($layout, ['layout' => $layout] + $tabConfig),
                $tabConfigs,
            )));
        } else {
            $layout->setTabs([]);
        }

        return $layout;
    }

    public static function appendFormFieldUiData(FieldLayout $layout): array
    {
        $tabs = $layout->getTabs();
        $fieldLayoutElements = [];

        foreach ($tabs as $tab) {
            /** @var FieldLayoutElement $fieldLayoutElements */
            $fieldLayoutElements = array_map(static function($fieldLayoutElement) {
                $field = $fieldLayoutElement->getField();
                $formFieldUiData = self::getFormFieldUiData($field);
                $fieldLayoutElement->formFieldUi = $formFieldUiData;

                return $fieldLayoutElement;
            }, $tab->getElements());
        }

        return $fieldLayoutElements;
    }

    public static function createSubmissionFieldLayoutTabFromConfig(FieldLayout $fieldLayout, array $config): FieldLayoutTab
    {
        $elements = $config['elements'] ?? $config['fields'] ?? [];
        unset($config['elements'], $config['fields']);

        $tab = new FieldLayoutTab($config);

        $fieldLayoutElements = [];

        foreach ($elements as $layoutElementConfig) {
            $fieldConfig = $layoutElementConfig['formField'] ?? null;
            $fieldType = $fieldConfig['type'];
            $fieldSettings = $fieldConfig['settings'] ?? [];
            unset(
                $fieldConfig['type'],
                $fieldConfig['tabUid'], // Why is this set at all?
                $fieldConfig['settings'],
            );

            $field = new $fieldType($fieldConfig);
            $field->setAttributes($fieldSettings, false);

            $fieldLayoutElement = new CustomFormField($field);
            $fieldLayoutElement->layout = $fieldLayout;
            $fieldLayoutElement->required = $layoutElementConfig['required'] === true;
            $fieldLayoutElement->width = $layoutElementConfig['width'];
            $fieldLayoutElement->uid = $layoutElementConfig['uid'];
            $fieldLayoutElement->formField = $layoutElementConfig['formField'] ?? null;

            $fieldLayoutElements[] = $fieldLayoutElement;
        }

        $tab->setElements($fieldLayoutElements);

        return $tab;
    }

    public static function getFormFieldData(mixed $field): array
    {
        return [
            'type' => $field::class,
            'name' => $field->name ?? $field::displayName(),
            'handle' => $field->handle, // Default created in JS
            'instructions' => $field->instructions,
            'settings'  => $field->getSettings(),
        ];
    }

    /**
     * Avoid calling this method in non-Form Builder scenarios. It will try to render
     * the exampleHtml template and throw an error.
     */
    public static function getFormFieldUiData(FormFieldInterface $field): array
    {
        $svg = Cp::iconSvg($field->selectorIcon());

        $exampleInputHtml = $field->getExampleInputHtml();

        $formFieldUiData['displayName'] = $field::displayName();
        $formFieldUiData['defaultHandle'] = StringHelper::toHandle($field::displayName());
        $formFieldUiData['icon'] = Cp::iconSvg($svg, $field::displayName());
        $formFieldUiData['exampleInputHtml'] = $exampleInputHtml;

        return $formFieldUiData;
    }

    public function getFieldValue($field, $value): ?FormRecord
    {
        return FormRecord::findOne([
            $field => $value,
        ]);
    }
}
