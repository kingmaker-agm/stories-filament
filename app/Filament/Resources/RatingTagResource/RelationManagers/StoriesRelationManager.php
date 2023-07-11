<?php

namespace App\Filament\Resources\RatingTagResource\RelationManagers;

use App\Filament\Resources\RatingTagResource;
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
    protected static ?string $inverseRelationship = 'ratingTags';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                StoryResource::getTitleFormField(),
                RatingTagResource::getRatingPivotFormField(),
                StoryResource::getOriginalUrlFormField(),
                StoryResource::getBodyFormField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                StoryResource::getTitleTableColumn(),
                RatingTagResource::getRatingPivotTableColumn()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label("Create Story"),
                Tables\Actions\AttachAction::make()
                    ->color('success')
                    ->label('Attach Story')
                    ->preloadRecordSelect()
                    ->form(fn (Tables\Actions\AttachAction $action) => [
                        $action->getRecordSelect(),
                        RatingTagResource::getRatingPivotFormField()
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
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