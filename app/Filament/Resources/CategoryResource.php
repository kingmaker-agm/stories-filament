<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = "Taxonomy";
    protected static ?string $navigationIcon = 'heroicon-o-bookmark';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::getNameField(),
                self::getHiddenUserIdField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::getNameTableColumn(),
                self::getStoriesCountTableColumn(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'view' => Pages\ViewCategory::route('/{record}'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getNameField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('name')
            ->label('Name')
            ->required()
            ->autofocus()
            ->placeholder('Enter the category name')
            ->maxLength(255)
            ->unique(
                callback: function (Unique $rule) {
                    if (Auth::check()) {
                        $rule->where('user_id', Auth::id());
                    }
                },
                ignoreRecord: true
            );
    }

    public static function getHiddenUserIdField(): Forms\Components\Hidden
    {
        return Forms\Components\Hidden::make('user_id')
            ->default(fn() => Auth::id());
    }

    public static function getNameTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable();
    }

    public static function getStoriesCountTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('stories_count')
            ->counts('stories')
            ->label('Stories')
            ->sortable();
    }
}
