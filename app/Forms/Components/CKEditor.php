<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class CKEditor extends Field
{
    protected string $view = 'forms.components.c-k-editor';

    protected function setUp(): void
    {
        parent::setUp();
        $this->default('');
        $this->dehydrated(false);
        $this->hiddenLabel();
    }
}
