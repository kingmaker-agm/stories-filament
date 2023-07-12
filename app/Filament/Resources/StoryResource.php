<?php

namespace App\Filament\Resources;

use App\Filament\Actions\Story\AttachToRatingTagsBulkAction;
use App\Filament\Actions\Story\AttachToTagsBulkAction;
use App\Filament\Actions\Story\DetachFromRatingTagsBulkAction;
use App\Filament\Actions\Story\DetachFromTagsBulkAction;
use App\Filament\Forms\Components\Actions\OpenUrlAction;
use App\Filament\Resources\StoryResource\Pages;
use App\Filament\Resources\StoryResource\RelationManagers;
use App\Models\RatingTag;
use App\Models\Story;
use App\Models\Tag;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class StoryResource extends Resource
{
    const FORM_COLUMN_COUNT = 1;

    protected static ?string $model = Story::class;
    protected static ?string $recordTitleAttribute = 'title';

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
                self::getTagsTableColumn(),
                self::getRatingTagsTableColumn(),
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
                AttachToTagsBulkAction::make(),
                DetachFromTagsBulkAction::make(),
                AttachToRatingTagsBulkAction::make(),
                DetachFromRatingTagsBulkAction::make(),
            ])
            ->filters([
                self::getSeriesFilter(),
                self::getTagsFilter(),
                self::getRatingTagsFilter(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SeriesRelationManager::class,
            RelationManagers\RatingTagsRelationManager::class,
            RelationManagers\TagsRelationManager::class,
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

    public static function getTagsTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('tags_count')
            ->counts('tags')
            ->label('Tags')
            ->toggleable(isToggledHiddenByDefault: true)
            ->sortable();
    }

    public static function getRatingTagsTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('rating_tags_count')
            ->counts('ratingTags')
            ->label('Rating Tags')
            ->toggleable(isToggledHiddenByDefault: true)
            ->sortable();
    }

    public static function getSeriesTableColumn(): Tables\Columns\IconColumn
    {
        return Tables\Columns\IconColumn::make('series_exists')
            ->label('Series')
            ->visibleFrom('md')
            ->grow(false)
            ->toggleable()
            ->exists('series')
            ->url(fn (Story $record) => $record->story_series_id ? StorySeriesResource::getUrl('view', $record->story_series_id) : null)
            ->options([
                'heroicon-o-check' => true,
                'heroicon-o-minus' => false,
            ])
            ->colors([
                'success' => true,
                'danger' => false,
            ]);
    }

    public static function getSeriesFilter(): string|Tables\Filters\TernaryFilter|null
    {
        return Tables\Filters\TernaryFilter::make('story_series_id')
            ->nullable()
            ->label('Part of Series');
    }

    public static function getTagsFilter(): string|null|Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('tags')
            ->relationship('tags', 'name')
            ->multiple()
            ->query(function (Builder $query, array $data) {
                $ids = $data['values'];

                return $query->where(function (Builder $query) use ($ids) {
                    foreach ($ids as $id) {
                        $query->whereHas('tags', fn(Builder $query) => $query->where((new Tag)->qualifyColumn('id'), $id));
                    }
                });
            })
            ->label('Tags');
    }

    public static function getRatingTagsFilter(): string|null|Tables\Filters\SelectFilter
    {
        return Tables\Filters\SelectFilter::make('rating_tags')
            ->relationship('ratingTags', 'name')
            ->multiple()
            ->query(function (Builder $query, array $data) {
                $ids = $data['values'];

                return $query->where(function (Builder $query) use ($ids) {
                    foreach ($ids as $id) {
                        $query->whereHas('ratingTags', fn(Builder $query) => $query->where((new RatingTag)->qualifyColumn('id'), $id));
                    }
                });
            })
            ->label('Rating Tags');
    }
}
