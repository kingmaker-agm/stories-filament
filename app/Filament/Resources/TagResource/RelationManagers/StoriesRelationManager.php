<?php

namespace App\Filament\Resources\TagResource\RelationManagers;

use App\Filament\Actions\Story\AttachToCategoriesBulkAction;
use App\Filament\Actions\Story\AttachToRatingTagsBulkAction;
use App\Filament\Actions\Story\AttachToTagsBulkAction;
use App\Filament\Actions\Story\DetachFromCategoriesBulkAction;
use App\Filament\Actions\Story\DetachFromRatingTagsBulkAction;
use App\Filament\Actions\Story\DetachFromTagsBulkAction;
use App\Filament\Resources\StoryResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'stories';
    protected static ?string $inverseRelationship = 'tags';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                StoryResource::getTitleFormField(),
                StoryResource::getOriginalUrlFormField(),
                StoryResource::getBodyFormField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                StoryResource::getTitleTableColumn(),
                StoryResource::getLikedTableColumn(),
                StoryResource::getReadTableColumn(),
                StoryResource::getUserRatingTableColumn(),
                StoryResource::getBodyTableColumn()
                    ->toggleable(),
            ])
            ->filters([
                StoryResource::getUserLikedFilter(),
                StoryResource::getUserReadFilter(),
                StoryResource::getRatingTagsFilter(),
                StoryResource::getCategoryFilter(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label("Create Story"),
                Tables\Actions\AttachAction::make()
                    ->color('success')
                    ->label('Attach Story')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-book-open')
                    ->url(fn ($record) => StoryResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\DetachAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->requiresConfirmation(),
                AttachToCategoriesBulkAction::make(),
                DetachFromCategoriesBulkAction::make(),
                AttachToTagsBulkAction::make(),
                DetachFromTagsBulkAction::make(),
                AttachToRatingTagsBulkAction::make(),
                DetachFromRatingTagsBulkAction::make(),
            ]);
    }
}
