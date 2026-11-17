<?php

namespace Theaarch\Oceanpay\Contracts;

/**
 * A driver that describes the settings a provider channel of it needs.
 */
interface HasSettingsRules
{
    /**
     * Get the validation rules for a provider channel's settings, keyed by setting.
     *
     * @return array<string, mixed>
     */
    public function settingsRules(): array;
}
