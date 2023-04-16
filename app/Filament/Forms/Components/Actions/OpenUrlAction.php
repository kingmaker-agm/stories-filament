<?php

namespace App\Filament\Forms\Components\Actions;

use Filament\Forms\Components\Actions\Action as BaseAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;

class OpenUrlAction extends BaseAction
{
    protected null | string | \Closure $openMessage = "Open URL";
    protected null | string | \Closure $openInNewTabMessage = "Open URL in new tab";

    public static function getDefaultName(): ?string
    {
        return 'open_url';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->icon('heroicon-o-globe-alt')
            ->extraAttributes(fn () => [
                'x-data' => new HtmlString(Js::from([
                    'url' => $this->getUrl(),
                    'openMessage' => $this->getOpenMessage(),
                    'openInNewTabMessage' => $this->getOpenInNewTabMessage(),
                ])),
                'x-on:click' => $this->shouldOpenUrlInNewTab()
                    ? 'if(url) { window.open(url); }'
                    : 'if(url) { window.location.replace(url); }',
                'x-on:mouseenter' => '$tooltip(' . ($this->shouldOpenUrlInNewTab() ? 'openInNewTabMessage' : 'openMessage') . ')',
            ]);
    }

    public function openMessage(string|\Closure $openMessage): static
    {
        $this->openMessage = $openMessage;

        return  $this;
    }

    public function getOpenMessage(): ?string
    {
        return $this->evaluate($this->openMessage);
    }

    public function openInNewTabMessage(string|\Closure $openInNewTabMessage): static
    {
        $this->openInNewTabMessage = $openInNewTabMessage;

        return  $this;
    }

    public function getOpenInNewTabMessage(): ?string
    {
        return $this->evaluate($this->openInNewTabMessage);
    }
}
