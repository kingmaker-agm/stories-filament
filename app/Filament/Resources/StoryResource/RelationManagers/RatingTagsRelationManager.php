<?php

namespace App\Filament\Resources\StoryResource\RelationManagers;

use App\Filament\Resources\RatingTagResource;
use App\Models\RatingTag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class RatingTagsRelationManager extends RelationManager
{
    protected static string $relationship = 'ratingTags';
    protected static ?string $inverseRelationship = 'stories';

    protected static ?string $recordTitleAttribute = 'name';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                RatingTagResource::getNameFormField(),
                RatingTagResource::getRatingPivotFormField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('rating', 'desc')
            ->columns([
                RatingTagResource::getNameTableColumn(),
                RatingTagResource::getRatingPivotTableColumn()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create New Tag'),
                Tables\Actions\AttachAction::make()
                    ->color('success')
                    ->label("Attach to existing Tag")
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action) => [
                        $action->getRecordSelect(),
                        RatingTagResource::getRatingPivotFormField()
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->icon('heroicon-o-book-open')
                    ->label('Open')
                    ->tooltip('Open in New Tab')
                    ->color('primary')
                    ->url(fn (RatingTag $record) => RatingTagResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(true),
                Tables\Actions\DetachAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->requiresConfirmation(),
            ]);
    }

}
