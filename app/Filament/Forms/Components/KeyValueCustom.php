<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\KeyValue;

class KeyValueCustom extends KeyValue
{
    protected string $view = 'filament.forms.components.key-value-custom';

    protected string|Closure|null $keyColumnWidth = null;

    protected string|Closure|null $valueColumnWidth = null;

    public function keyColumnWidth(string|Closure|null $largura): static
    {
        $this->keyColumnWidth = $largura;

        return $this;
    }

    public function keyWidth(string|Closure|null $largura): static
    {
        return $this->keyColumnWidth($largura);
    }

    public function valueColumnWidth(string|Closure|null $largura): static
    {
        $this->valueColumnWidth = $largura;

        return $this;
    }

    public function valueWidth(string|Closure|null $largura): static
    {
        return $this->valueColumnWidth($largura);
    }

    public function getKeyColumnWidth(): ?string
    {
        return $this->evaluate($this->keyColumnWidth);
    }

    public function getValueColumnWidth(): ?string
    {
        return $this->evaluate($this->valueColumnWidth);
    }
}
