<?php

namespace App\Filament\Resources\TagResource\RelationManagers;

use App\Filament\Resources\StoryResource;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'stories';
    protected static ?string $inverseRelationship = 'tags';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                StoryResource::getTitleFormField(),
                StoryResource::getOriginalUrlFormField(),
                StoryResource::getBodyFormField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                StoryResource::getTitleTableColumn(),
                StoryResource::getBodyTableColumn(),
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
            ]);
    }
}
