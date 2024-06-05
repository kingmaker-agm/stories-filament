<?php

namespace App\Filament\Actions\Story;

use App\Filament\Resources\RatingTagResource\RelationManagers\StoriesRelationManager as RatingTagStoriesRelationManagerAlias;
use App\Filament\Resources\StoryResource\Pages\ListStories;
use App\Filament\Resources\StorySeriesResource\RelationManagers\StoriesRelationManager as StorySeriesStoriesRelationManagerAlias;
use App\Filament\Resources\TagResource\RelationManagers\StoriesRelationManager as TagStoriesRelationManagerAlias;
use App\Models\Story;
use App\Models\Tag;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AttachToTagsBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'attach_tags_to_stories';
    }

    public function getPluralModelLabel(): string
    {
        return 'Attach Tags to Stories';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Attach Tags to Stories');
        $this->color('success');
        $this->icon('heroicon-o-paper-clip');

        $this->requiresConfirmation();
        $this->modalHeading("Attach Tags to Stories");
        $this->modalButton("Attach");
        $this->successNotification(
            Notification::make()
                ->success()
                ->title("Tags Attached")
                ->body("Tags have been attached to the selected Stories.")
        );

        $this->form([
            Select::make('tags')
                ->options(
                    function ($livewire) {
                        /**
                         * @var TagStoriesRelationManagerAlias|RatingTagStoriesRelationManagerAlias|StorySeriesStoriesRelationManagerAlias|ListStories $livewire
                         * @var Builder|Tag $query
                         * @var Collection<int,Story> $records
                         */
                        $records = $livewire->getSelectedTableRecords();
                        $query = Tag::query();

                        $query->where(function (Builder $query) use($records) {
                            foreach ($records as $story) {
                                $query->orWhereDoesntHave(
                                    'stories',
                                    fn(Builder $storyQuery) => $storyQuery->where((new Story)->qualifyColumn('id'), $story->id)
                                );
                            }
                        });

                        return $query->pluck('name', 'id');
                    }
                )
                ->multiple()
                ->searchable()
                ->placeholder('Select Tags')
                ->required()
        ]);
        $this->action(function (): void {
            $this->process(function (Collection $records, array $data): void {
                $records->each(
                    fn (Story $story) => $story->tags()->syncWithoutDetaching($data['tags'])
                );
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
