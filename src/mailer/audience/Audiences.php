<?php

namespace BarrelStrength\Sprout\mailer\audience;

use BarrelStrength\Sprout\mailer\components\audiences\SubscriberListAudienceType;
use BarrelStrength\Sprout\mailer\components\audiences\UserGroupAudienceType;
use BarrelStrength\Sprout\mailer\components\elements\audience\AudienceElement;
use BarrelStrength\Sprout\mailer\MailerModule;
use craft\base\Component;
use craft\events\RegisterComponentTypesEvent;

class Audiences extends Component
{
    public const EVENT_REGISTER_AUDIENCE_TYPES = 'registerSproutAudienceTypes';

    /**
     * @var AudienceType[]
     */
    protected array $audienceTypes = [];

    public function getAudienceTypes(): array
    {
        $audienceTypes[] = UserGroupAudienceType::class;

        $settings = MailerModule::getInstance()->getSettings();

        if ($settings->enableSubscriberLists) {
            $audienceTypes[] = SubscriberListAudienceType::class;
        }

        $event = new RegisterComponentTypesEvent([
            'types' => $audienceTypes,
        ]);

        $this->trigger(self::EVENT_REGISTER_AUDIENCE_TYPES, $event);

        return $event->types;
    }

    public function getAudienceTypeInstances(): array
    {
        $this->audienceTypes = $this->getAudienceTypes();

        $audiences = [];

        foreach ($this->audienceTypes as $audienceType) {
            $audiences[$audienceType] = new $audienceType();
        }

        return $audiences;
    }

    public function getAudienceRecipients($audienceIds): array
    {
        if (empty($audienceIds)) {
            return [];
        }

        $recipients = [];

        foreach ($audienceIds as $audienceId) {
            /** @var AudienceElement $audience */
            $audience = AudienceElement::findOne($audienceId);

            $recipients = [...$recipients, ...$audience->getAudience()->getRecipients()];
        }

        return $recipients;
    }
}
