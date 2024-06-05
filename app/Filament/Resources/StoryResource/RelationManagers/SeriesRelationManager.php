<?php

namespace App\Filament\Resources\StoryResource\RelationManagers;

use App\Filament\Resources\StorySeriesResource;
use App\Models\Story;
use App\Models\StorySeries;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SeriesRelationManager extends RelationManager
{
    protected static string $relationship = 'series';

    protected static ?string $inverseRelationship = 'stories';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->columns(StorySeriesResource::FORM_COLUMN)
            ->schema([
                StorySeriesResource::getTitleFormField(),
                StorySeriesResource::getDescriptionFormField(),
                StorySeriesResource::getOriginalUrlField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                StorySeriesResource::getTitleTableColumn(),
                StorySeriesResource::getNumberOfStoriesTableColumn(),
                StorySeriesResource::getDescriptionTableColumn(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hidden(fn (self $livewire) => $livewire->getOwnerRecord()->story_series_id)
                    ->label("Add to new Series")
                    ->color('primary')
                    ->after(function (self $livewire) {
                        /** @var Story $story */
                        $story = $livewire->getOwnerRecord();
                        $newly_created_series = StorySeries::latest()->first();
                        $story->update(['story_series_id' => $newly_created_series->id]);
                    }),
                Tables\Actions\Action::make('attach_to_existing_series')
                    ->hidden(fn (self $livewire) => $livewire->getOwnerRecord()->story_series_id)
                    ->label('Add to existing Series')
                    ->color('success')
                    ->button()
                    ->form([
                        Forms\Components\Select::make('id')
                            ->label('Series')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn (string $search) => StorySeries::where('title', 'LIKE', "%$search%")
                                    ->pluck('title', 'id')
                            )
                    ])
                    ->action(function (array $data, self $livewire) {
                        /** @var Story $story */
                        $story = $livewire->getOwnerRecord();
                        $story->update([
                            'story_series_id' => $data['id']
                        ]);
                    }),
                Tables\Actions\Action::make('change_association')
                    ->visible(fn (self $livewire) => $livewire->getOwnerRecord()->story_series_id)
                    ->label('Move to another Series')
                    ->form(function (self $livewire) {
                        /** @var Story $story */
                        $story = $livewire->getOwnerRecord();
                        return [
                            Forms\Components\Select::make('id')
                                ->label('Series')
                                ->required()
                                ->searchable()
                                ->getSearchResultsUsing(
                                    fn (string $search) => StorySeries::where('title', 'LIKE', "%$search%")
                                        ->whereNot('id', $story->story_series_id)
                                        ->pluck('title', 'id')
                                )
                        ];
                    })
                    ->action(function (self $livewire, array $data) {
                        /** @var Story $story */
                        $story = $livewire->getOwnerRecord();
                        $story->update(['story_series_id' => $data['id']]);
                    })
                    ->button()
                    ->color('primary'),
                Tables\Actions\Action::make('remove_from_series')
                    ->visible(fn (self $livewire) => $livewire->getOwnerRecord()->story_series_id)
                    ->action(fn (self $livewire) => $livewire->getOwnerRecord()->update(['story_series_id' => null]))
                    ->label('Remove from Series')
                    ->requiresConfirmation()
                    ->button()
                    ->color('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label("Open")
                    ->icon('heroicon-o-book-open')
                    ->url(fn (StorySeries $record) => StorySeriesResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab()
                    ->tooltip("Open in new tab"),
            ]);
    }
}
