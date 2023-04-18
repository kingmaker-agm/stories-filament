<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Actions\OpenUrlAction;
use App\Filament\Resources\StoryResource\RelationManagers\RatingTagsRelationManager;
use App\Filament\Resources\StorySeriesResource\Pages;
use App\Filament\Resources\StorySeriesResource\RelationManagers;
use App\Models\StorySeries;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class StorySeriesResource extends Resource
{
    const FORM_COLUMN = 1;
    protected static ?string $model = StorySeries::class;

    protected static ?string $navigationGroup = "Stories";
    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(self::FORM_COLUMN)
            ->schema([
                self::getTitleFormField(),
                self::getDescriptionFormField(),
                self::getOriginalUrlField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::getTitleTableColumn(),
                self::getNumberOfStoriesTableColumn(),
                self::getDescriptionTableColumn(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StoriesRelationManager::class,
            RatingTagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStorySeries::route('/'),
            'create' => Pages\CreateStorySeries::route('/create'),
            'view' => Pages\ViewStorySeries::route('/{record}'),
            'edit' => Pages\EditStorySeries::route('/{record}/edit'),
        ];
    }

    public static function getTitleFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('title')
            ->required()
            ->maxLength(1024);
    }

    public static function getDescriptionFormField(): Forms\Components\RichEditor
    {
        return Forms\Components\RichEditor::make('description');
    }

    public static function getOriginalUrlField(): string|Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('original_url')
            ->suffixAction(OpenUrlAction::make()->openUrlInNewTab())
            ->reactive()
            ->nullable()
            ->maxLength(255)
            ->url();
    }

    public static function getTitleTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('title')
            ->searchable()
            ->sortable()
            ->wrap()
            ->limit(100);
    }

    public static function getNumberOfStoriesTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('number_of_stories')
            ->wrap()
            ->label('Number of Stories')
            ->formatStateUsing(fn(StorySeries $record) => (string) $record->stories()->count());
    }

    public static function getDescriptionTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('description')
            ->formatStateUsing(fn(?string $state) => empty($state) ? '' : Str::words(strip_tags($state), 30))
            ->wrap();
    }
}
