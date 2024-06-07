<?php

namespace App\Filament\Actions\Story;

use App\Filament\Resources\RatingTagResource\RelationManagers\StoriesRelationManager as RatingTagStoriesRelationManagerAlias;
use App\Filament\Resources\StoryResource\Pages\ListStories;
use App\Filament\Resources\StorySeriesResource\RelationManagers\StoriesRelationManager as StorySeriesStoriesRelationManagerAlias;
use App\Filament\Resources\TagResource\RelationManagers\StoriesRelationManager as TagStoriesRelationManagerAlias;
use App\Models\RatingTag;
use App\Models\Story;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DetachFromRatingTagsBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'detach_rating_tags_from_stories';
    }

    public function getPluralModelLabel(): string
    {
        return 'Detach Rating Tags';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Detach Rating Tags');
        $this->color('danger');
        $this->icon('heroicon-o-scissors');

        $this->requiresConfirmation();
        $this->modalHeading("Detach Rating Tags from Stories");
        $this->modalButton("Detach");
        $this->successNotification(
            Notification::make()
                ->danger()
                ->title("Rating Tags Detached")
                ->body("Rating Tags have been detached from the selected Stories.")
        );

        $this->form([
            Select::make('tags')
                ->options(
                    function ($livewire) {
                        /**
                         * @var TagStoriesRelationManagerAlias|RatingTagStoriesRelationManagerAlias|StorySeriesStoriesRelationManagerAlias|ListStories $livewire
                         * @var Collection<int,Story> $records
                         */
                        $records = $livewire->getSelectedTableRecords();
                        return RatingTag::query()
                            ->whereHas(
                                'stories',
                                fn (Builder $storyQuery) => $storyQuery
                                    ->whereIn((new Story)->qualifyColumn('id'), $records->pluck('id'))
                            )
                            ->pluck('name', 'id');
                    }
                )
                ->multiple()
                ->searchable()
                ->placeholder('Select Rating Tags')
                ->required()
        ]);
        $this->action(function (): void {
            $this->process(function (Collection $records, array $data): void {
                $records->each(
                    fn (Story $story) => $story->ratingTags()->detach($data['tags'])
                );
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
