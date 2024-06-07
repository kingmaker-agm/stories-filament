<?php

namespace App\Filament\Actions\Story;

use App\Filament\Resources\RatingTagResource\RelationManagers\StoriesRelationManager as RatingTagStoriesRelationManagerAlias;
use App\Filament\Resources\StoryResource\Pages\ListStories;
use App\Filament\Resources\StorySeriesResource\RelationManagers\StoriesRelationManager as StorySeriesStoriesRelationManagerAlias;
use App\Filament\Resources\TagResource\RelationManagers\StoriesRelationManager as TagStoriesRelationManagerAlias;
use App\Models\Category;
use App\Models\Story;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AttachToCategoriesBulkAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'attach_categories_to_stories';
    }

    public function getPluralModelLabel(): string
    {
        return 'Attach Categories';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Attach Categories');
        $this->color('success');
        $this->icon('heroicon-o-paper-clip');

        $this->requiresConfirmation();
        $this->modalHeading("Attach Categories to Stories");
        $this->modalButton("Attach");
        $this->successNotification(
            Notification::make()
                ->success()
                ->title("Categories Attached")
                ->body("Categories have been attached to the selected Stories.")
        );

        $this->form([
            Select::make('categories')
                ->options(
                    function ($livewire) {
                        /**
                         * @var TagStoriesRelationManagerAlias|RatingTagStoriesRelationManagerAlias|StorySeriesStoriesRelationManagerAlias|ListStories $livewire
                         * @var Builder|Category $query
                         * @var Collection<int,Story> $records
                         */
                        $records = $livewire->getSelectedTableRecords();
                        $query = Category::query();

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
                ->placeholder('Select Categories')
                ->required()
        ]);
        $this->action(function (): void {
            $this->process(function (Collection $records, array $data): void {
                $records->each(
                    fn (Story $story) => $story->categories()->syncWithoutDetaching($data['categories'])
                );
            });

            $this->success();
        });

        $this->deselectRecordsAfterCompletion();
    }
}
