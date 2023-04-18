<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Actions\OpenUrlAction;
use App\Filament\Resources\StoryResource\Pages;
use App\Filament\Resources\StoryResource\RelationManagers;
use App\Models\Story;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Webbingbrasil\FilamentCopyActions\Forms\Actions\CopyAction;

class StoryResource extends Resource
{
    const FORM_COLUMN_COUNT = 1;

    protected static ?string $model = Story::class;

    protected static ?string $navigationGroup = "Stories";
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(self::FORM_COLUMN_COUNT)
            ->schema([
                self::getTitleFormField(),
                self::getOriginalUrlFormField(),
                self::getUserNameFormField(),
                self::getBodyFormField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::getTitleTableColumn(),
                self::getSeriesTableColumn(),
                self::getBodyTableColumn(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\SeriesRelationManager::class,
            RelationManagers\RatingTagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStories::route('/'),
            'create' => Pages\CreateStory::route('/create'),
            'view' => Pages\ViewStory::route('/{record}'),
            'edit' => Pages\EditStory::route('/{record}/edit'),
        ];
    }

    public static function getTitleTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('title')
            ->sortable()
            ->searchable()
            ->wrap()
            ->limit(100);
    }

    public static function getBodyTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('body')
            ->formatStateUsing(fn(?string $state) => empty($state) ? '' : Str::words(strip_tags($state), 30))
            ->visibleFrom('lg')
            ->wrap();
    }

    public static function getTitleFormField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('title')
            ->maxLength(1024)
            ->required();
    }

    public static function getOriginalUrlFormField(): string|Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('original_url')
            ->suffixAction(OpenUrlAction::make()->openUrlInNewTab())
            ->reactive()
            ->nullable()
            ->maxLength(1024)
            ->url();
    }

    public static function getUserNameFormField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('user.name')
            ->relationship('user', 'name')
            ->dehydrated(false)
            ->visibleOn('view');
    }

    public static function getBodyFormField(): Forms\Components\RichEditor
    {
        return Forms\Components\RichEditor::make('body')
            ->required();
    }

    public static function getSeriesTableColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('story_series_id')
            ->label('Series')
            ->visibleFrom('md')
            ->grow(false)
            ->url(fn ($record) => $record->story_series_id ? StorySeriesResource::getUrl('view', ['record' => $record->story_series_id]) : null)
            ->options([
                'heroicon-o-check' => fn($state) => !!$state,
                'heroicon-o-minus' => fn($state) => $state === null
            ])
            ->colors([
                'success' => fn($state) => !!$state,
                'danger' => fn($state) => $state === null
            ]);
    }
}
