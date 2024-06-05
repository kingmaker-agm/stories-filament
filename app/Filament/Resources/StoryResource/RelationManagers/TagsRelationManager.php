<?php

namespace App\Filament\Resources\StoryResource\RelationManagers;

use App\Filament\Resources\TagResource;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';
    protected static ?string $inverseRelationship = 'stories';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TagResource::getPrimaryNameField(),
                TagResource::getSecondaryNameField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TagResource::getPrimaryTableColumn(),
                TagResource::getSecondaryTableColumn(),
                TagResource::getNameTableColumn(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create New Tag'),
                Tables\Actions\AttachAction::make()
                    ->color('success')
                    ->label("Attach to existing Tag")
                    ->preloadRecordSelect()
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-book-open')
                    ->color('primary')
                    ->tooltip('Open in a New Tab')
                    ->url(fn (Tag $record) => TagResource::getUrl('view', ['record' => $record]))
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
