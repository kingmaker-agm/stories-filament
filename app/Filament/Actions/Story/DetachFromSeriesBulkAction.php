<?php

namespace App\Filament\Actions\Story;

use App\Models\Story;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class DetachFromSeriesBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'detach_series_from_stories';
    }

    public function getPluralModelLabel(): string
    {
        return 'Detach Series';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Detach Series');
        $this->color('danger');
        $this->icon('heroicon-o-scissors');

        $this->requiresConfirmation();
        $this->modalHeading("Detach Stories from Series");
        $this->modalButton("Detach");
        $this->successNotification(
            Notification::make()
                ->danger()
                ->title("Stories Detached")
                ->body("Stories have been detached from the Series.")
        );

        $this->action(function (): void {
            $this->process(function (Collection $records): void {
                $records->each(
                    function (Story $story): void {
                        $story->update([
                            'story_series_id' => null,
                            'story_series_order' => null
                        ]);
                    }
                );
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
