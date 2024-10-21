<?php

namespace App\Filament\Fields;

use Filament\Forms\Components\Field;

class Repeater extends \Filament\Forms\Components\Repeater
{
    public function getSimpleField(): ?Field
    {
        return $this->evaluate($this->simpleField);
    }
}
