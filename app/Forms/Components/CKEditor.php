<?php

namespace App\Forms\Components;

use App\Models\Setting;
use Filament\Forms\Components\Field;

class CKEditor extends Field
{
    protected string $view = 'forms.components.c-k-editor';

    public $settings = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->settings = Setting::getAllSettings();
    }

    public function getSettings(): array
    {
        return $this->settings;
    }
}
