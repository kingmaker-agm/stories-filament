<?php

namespace App\Filament\Resources\StorySeriesResource\RelationManagers;

use App\Filament\Actions\Story\AttachToRatingTagsBulkAction;
use App\Filament\Actions\Story\AttachToTagsBulkAction;
use App\Filament\Actions\Story\DetachFromRatingTagsBulkAction;
use App\Filament\Actions\Story\DetachFromTagsBulkAction;
use App\Filament\Resources\StoryResource;
use App\Models\Story;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'stories';

    protected static ?string $inverseRelationship = 'series';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(StoryResource::FORM_COLUMN_COUNT)
            ->schema([
                StoryResource::getTitleFormField(),
                StoryResource::getUserNameFormField(),
                StoryResource::getOriginalUrlFormField(),
                StoryResource::getBodyFormField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('story_series_order', 'asc')
            ->columns([
                StoryResource::getTitleTableColumn(),
                Tables\Columns\TextColumn::make('story_series_order')
                    ->label('Order')
                    ->toggleable()
                    ->sortable(),
                StoryResource::getLikedTableColumn(),
                StoryResource::getReadTableColumn(),
                StoryResource::getUserRatingTableColumn(),
                StoryResource::getBodyTableColumn(),
            ])
            ->reorderable('story_series_order')
            ->filters([
                StoryResource::getTagsFilter(),
                StoryResource::getRatingTagsFilter(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->color('success'),
                Tables\Actions\AssociateAction::make()
                    ->color('primary'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label("Open")
                    ->icon('heroicon-o-book-open')
                    ->url(fn (Story $record) => StoryResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab()
                    ->tooltip("Open in new tab"),
                Tables\Actions\DissociateAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DissociateBulkAction::make(),
                AttachToTagsBulkAction::make(),
                DetachFromTagsBulkAction::make(),
                AttachToRatingTagsBulkAction::make(),
                DetachFromRatingTagsBulkAction::make(),
            ]);
    }
}
