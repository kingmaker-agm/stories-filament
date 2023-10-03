<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\Actions\OpenUrlAction;
use App\Filament\Resources\StoryResource\RelationManagers\RatingTagsRelationManager;
use App\Filament\Resources\StorySeriesResource\Pages;
use App\Filament\Resources\StorySeriesResource\RelationManagers;
use App\Models\Story;
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

    protected static ?string $recordTitleAttribute = 'title';

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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('stories')
            ->addSelect([
                'user_not_read_story_under_series' => Story::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn((new Story)->qualifyColumn('story_series_id'), (new StorySeries)->qualifyColumn('id'))
                    ->whereRaw("NOT EXISTS (SELECT * FROM story_read WHERE story_read.story_id = stories.id AND story_read.user_id = " . auth()->id() . ")"),
                'user_not_like_story_under_series' => Story::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn((new Story)->qualifyColumn('story_series_id'), (new StorySeries)->qualifyColumn('id'))
                    ->whereRaw("NOT EXISTS (SELECT * FROM story_like WHERE story_like.story_id = stories.id AND story_like.user_id = " . auth()->id() . ")"),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::getTitleTableColumn(),
                self::getNumberOfStoriesTableColumn(),
                Tables\Columns\IconColumn::make('user_read_exists')
                    ->getStateUsing(fn($record) => $record->stories_count && !$record->user_not_read_story_under_series)
                    ->label('Read')
                    ->toggleable()
                    ->alignCenter()
                    ->trueIcon('heroicon-s-book-open')
                    ->falseIcon('heroicon-s-lock-closed')
                    ->trueColor('success')
                    ->falseColor('secondary'),
                Tables\Columns\IconColumn::make('user_like_exists')
                    ->getStateUsing(fn($record) => $record->stories_count && !$record->user_not_like_story_under_series)
                    ->label('Liked')
                    ->toggleable()
                    ->alignCenter()
                    ->trueIcon('heroicon-s-heart')
                    ->falseIcon('heroicon-o-heart')
                    ->trueColor('danger')
                    ->falseColor('secondary'),
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
        return Tables\Columns\TextColumn::make('stories_count')
            ->counts('stories')
            ->toggleable()
            ->wrap()
            ->visibleFrom('md')
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
