<?php

namespace App\Filament\Actions\Story;

use App\Filament\Resources\RatingTagResource;
use App\Filament\Resources\RatingTagResource\RelationManagers\StoriesRelationManager as RatingTagStoriesRelationManagerAlias;
use App\Filament\Resources\StoryResource\Pages\ListStories;
use App\Filament\Resources\StorySeriesResource\RelationManagers\StoriesRelationManager as StorySeriesStoriesRelationManagerAlias;
use App\Filament\Resources\TagResource\RelationManagers\StoriesRelationManager as TagStoriesRelationManagerAlias;
use App\Models\RatingTag;
use App\Models\Story;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AttachToRatingTagsBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'attach_rating_tags_to_stories';
    }

    public function getPluralModelLabel(): string
    {
        return 'Attach Rating Tags to Stories';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Attach Rating Tags to Stories');
        $this->color('success');
        $this->icon('heroicon-o-paper-clip');

        $this->requiresConfirmation();
        $this->modalHeading("Attach Rating Tags to Stories");
        $this->modalButton("Attach");
        $this->successNotification(
            Notification::make()
                ->success()
                ->title("Rating Tags Attached")
                ->body("Rating Tags have been attached to the selected Stories.")
        );

        $this->form([
            Repeater::make('tags')
                ->label('Rating Tags')
                ->createItemButtonLabel('Add more Rating Tag')
                ->columnSpan('full')
                ->disableItemMovement()
                ->schema([
                    Select::make('rating_tag_id')
                        ->options(
                            function ($livewire) {
                                /**
                                 * @var TagStoriesRelationManagerAlias|RatingTagStoriesRelationManagerAlias|StorySeriesStoriesRelationManagerAlias|ListStories $livewire
                                 * @var Builder|RatingTag $query
                                 * @var Collection<int,Story> $records
                                 */
                                $records = $livewire->getSelectedTableRecords();
                                $query = RatingTag::query();

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
                        ->searchable()
                        ->placeholder('Select Rating Tags')
                        ->required(),
                    RatingTagResource::getRatingPivotFormField(),
                ]),
        ]);
        $this->action(function (): void {
            $this->process(function (Collection $records, array $data): void {
                $records->each(
                    fn (Story $story) => $story->ratingTags()
                        ->syncWithoutDetaching(
                            collect($data['tags'])
                                ->mapWithKeys(
                                    fn ($tag) => [
                                        $tag['rating_tag_id'] => [
                                            'rating' => $tag['rating'],
                                        ],
                                    ]
                                )
                                ->toArray()
                        )
                );
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
