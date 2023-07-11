<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RatingTagResource\Pages;
use App\Filament\Resources\RatingTagResource\RelationManagers;
use App\Models\RatingTag;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RatingTagResource extends Resource
{
    protected static ?string $model = RatingTag::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = "Taxonomy";
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                self::getNameFormField()
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('stories');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::getNameTableColumn(),
                Tables\Columns\TextColumn::make('stories_count')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StoriesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRatingTags::route('/'),
            'create' => Pages\CreateRatingTag::route('/create'),
            'view' => Pages\ViewRatingTag::route('/{record}'),
            'edit' => Pages\EditRatingTag::route('/{record}/edit'),
        ];
    }

    public static function getNameFormField(): Forms\Components\TextInput
    {
        $model = new RatingTag;

        return Forms\Components\TextInput::make('name')
            ->required()
            ->maxLength(255)
            ->unique($model->getTable(), 'name');
    }

    public static function getNameTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->sortable()
            ->searchable()
            ->wrap();
    }

    public static function getRatingPivotFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('rating')
            ->integer()
            ->required()
            ->minValue(1)
            ->maxValue(10);
    }

    public static function getRatingPivotTableColumn()
    {
        return Tables\Columns\TextInputColumn::make('rating')
            ->sortable();
    }
}
