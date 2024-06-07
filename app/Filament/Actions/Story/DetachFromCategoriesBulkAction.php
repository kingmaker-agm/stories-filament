<?php

namespace App\Filament\Actions\Story;

use App\Filament\Resources\RatingTagResource\RelationManagers\StoriesRelationManager as RatingTagStoriesRelationManagerAlias;
use App\Filament\Resources\StoryResource\Pages\ListStories;
use App\Filament\Resources\StorySeriesResource\RelationManagers\StoriesRelationManager as StorySeriesStoriesRelationManagerAlias;
use App\Filament\Resources\TagResource\RelationManagers\StoriesRelationManager as TagStoriesRelationManagerAlias;
use App\Models\Category;
use App\Models\Story;
use App\Models\Tag;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DetachFromCategoriesBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'detach_categories_from_stories';
    }

    public function getPluralModelLabel(): string
    {
        return 'Detach Categories';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Detach Categories');
        $this->color('danger');
        $this->icon('heroicon-o-scissors');

        $this->requiresConfirmation();
        $this->modalHeading("Detach Categories from Stories");
        $this->modalButton("Detach");
        $this->successNotification(
            Notification::make()
                ->danger()
                ->title("Categories Detached")
                ->body("Categories have been detached from the selected Stories.")
        );

        $this->form([
            Select::make('categories')
                ->options(
                    function ($livewire) {
                        /**
                         * @var TagStoriesRelationManagerAlias|RatingTagStoriesRelationManagerAlias|StorySeriesStoriesRelationManagerAlias|ListStories $livewire
                         * @var Collection<int,Story> $records
                         */
                        $records = $livewire->getSelectedTableRecords();
                        return Category::query()
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
                ->placeholder('Select Categories')
                ->required()
        ]);
        $this->action(function (): void {
            $this->process(function (Collection $records, array $data): void {
                $records->each(
                    fn (Story $story) => $story->categories()->detach($data['categories'])
                );
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
