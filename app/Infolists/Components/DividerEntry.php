<?php

namespace App\Infolists\Components;

use Closure;
use Filament\Infolists\Components\Entry;
use Illuminate\Contracts\Support\Htmlable;

class DividerEntry extends Entry
{
    protected string $view = 'infolists.components.divider-entry';

    protected bool | Closure $isLabelHidden = true;
}
