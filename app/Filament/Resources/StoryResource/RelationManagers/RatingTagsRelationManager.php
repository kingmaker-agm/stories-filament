<?php

namespace App\Filament\Resources\StoryResource\RelationManagers;

use App\Filament\Resources\RatingTagResource;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RatingTagsRelationManager extends RelationManager
{
    protected static string $relationship = 'ratingTags';
    protected static ?string $inverseRelationship = 'stories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                RatingTagResource::getNameFormField(),
                RatingTagResource::getRatingPivotFormField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Actions\DetachAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->requiresConfirmation(),
            ]);
    }

}
