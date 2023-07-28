<?php

namespace App\Filament\Resources;

use App\Actions\Story\LikeStoryAction;
use App\Actions\Story\RateStoryAction;
use App\Actions\Story\ReadStoryAction;
use App\Actions\Story\RemoveStoryRatingAction;
use App\Actions\Story\UnlikeStoryAction;
use App\Actions\Story\UnreadStoryAction;
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
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Webbingbrasil\FilamentAdvancedFilter\Filters\NumberFilter;
use Yepsua\Filament\Forms\Components\Rating;

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
                Forms\Components\Grid::make(['sm' => 2, 'md' => 3])
                    ->schema([
                        Forms\Components\Toggle::make('user_like_exists')
                            ->onColor('success')
                            ->offColor('danger')
                            ->label('Liked'),
                        Forms\Components\Toggle::make('user_read_exists')
                            ->onColor('success')
                            ->offColor('danger')
                            ->label('Read'),
                        Rating::make('user_rating_min_rating')
                            ->label('User Rating')
                            ->min(1)
                            ->max(5),
                    ]),
                self::getBodyFormField(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::getTitleTableColumn(),
                self::getLikedTableColumn(),
                self::getReadTableColumn(),
                self::getUserRatingTableColumn(),
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
                self::getUserLikedFilter(),
                self::getUserReadFilter(),
                self::getUserRatingFilter(),
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

    public static function resolveSingleRecord($key): Model
    {
        return Story::query()
            ->withExists('userLike')
            ->withExists('userRead')
            ->withMin('userRating', 'rating')
            ->with([
                'series',
                'tags',
                'ratingTags',
            ])
            ->findOrFail($key);
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
            ->toolbarButtons([
                'bold',
                'italic',
                'underline',
                'strike',
                'link',
                'h2',
                'h3',
                'h4',
                'blockquote',
                'codeBlock',
                'bulletList',
                'orderedList',
                'redo',
                'undo',
            ])
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

    public static function getLikedTableColumn(): ToggleIconColumn
    {
        return ToggleIconColumn::make('user_like_exists')
            ->exists('userLike')
            ->label('Liked')
            ->sortable()
            ->toggleable()
            ->alignCenter()
            ->getStateUsing(fn(Story $record) => $record->user_like_exists)
            ->updateStateUsing(function (Story $record, $state) {
                if ($state) {
                    $likeStoryAction = new LikeStoryAction;
                    $likeStoryAction->execute($record, auth()->user());
                } else {
                    $unlikeStoryAction = new UnlikeStoryAction;
                    $unlikeStoryAction->execute($record, auth()->user());
                }

                return $state;
            })
            ->onIcon('heroicon-s-heart')
            ->offIcon('heroicon-o-heart')
            ->onColor('danger')
            ->offColor('danger');
    }

    public static function getUserRatingTableColumn(): Tables\Columns\SelectColumn
    {
        return Tables\Columns\SelectColumn::make('user_rating_max_rating')
            ->max('userRating', 'rating')
            ->label('Rating')
            ->options([
                1 => '1 Star',
                2 => '2 Stars',
                3 => '3 Stars',
                4 => '4 Stars',
                5 => '5 Stars',
            ])
            ->placeholder('No Rating')
            ->updateStateUsing(function (Story $record, $state) {
                if (empty($state)) {
                    $removeStoryRatingAction = new RemoveStoryRatingAction;
                    $removeStoryRatingAction->execute($record, auth()->user());
                } else {
                    $rateStoryAction = new RateStoryAction;
                    $rateStoryAction->execute($record, auth()->user(), $state);
                }

                return $state;
            })
            ->sortable()
            ->toggleable();
    }

    public static function getUserLikedFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('user_like_exists')
            ->label('Liked')
            ->queries(
                true: fn(Builder $query) => $query->whereHas('userLike'),
                false: fn(Builder $query) => $query->whereDoesntHave('userLike'),
            );
    }

    public static function getUserRatingFilter(): NumberFilter
    {
        return NumberFilter::make('user_rating_min_rating')
            ->label('User Rating')
            ->query(function (Builder $query, array $data) {
                $clause = $data['clause'];
                $value = $data['value'];
                $from = $data['from'];
                $until = $data['until'];

                switch ($clause) {
                    case 'equal':
                    case 'not_equal':
                    case 'greater_equal':
                    case 'less_equal':
                    case 'greater_than':
                    case 'less_than':
                        !empty($value) && $query->whereHas(
                            'userRating',
                            fn(Builder $query) => $query->where(
                                'rating',
                                match ($clause) {
                                    'equal' => '=',
                                    'not_equal' => '!=',
                                    'greater_equal' => '>=',
                                    'less_equal' => '<=',
                                    'greater_than' => '>',
                                    'less_than' => '<',
                                },
                                $value)
                        );
                        break;
                    case 'between':
                        !empty($from) && !empty($until) && $query->whereHas(
                            'userRating',
                            fn(Builder $query) => $query
                                ->where('rating', '>=', $from ?? 0)
                                ->where('rating', '<=', $until ?? 100)
                        );
                        break;
                    case 'set':
                        $query->whereHas('userRating');
                        break;
                    case 'not_set':
                        $query->whereDoesntHave('userRating');
                        break;
                }
                return $query;
            })
            ->indicateUsing(function (array $state, NumberFilter $filter): string|array {
                $clause = $state['clause'];
                $value = $state['value'];
                $from = $state['from'];
                $until = $state['until'];

                if (!empty($clause)) {
                    return match ($clause) {
                        NumberFilter::CLAUSE_SET, NumberFilter::CLAUSE_NOT_SET => collect([
                            $filter->getLabel(),
                            $filter->clauses()[$clause],
                        ])->implode(' '),
                        NumberFilter::CLAUSE_BETWEEN => (!empty($from) && !empty($until)) ? collect([
                            $filter->getLabel(),
                            $filter->clauses()[$clause],
                            $from,
                            'and',
                            $until,
                        ])->implode(' ') : [],
                        default => !empty($value)
                            ? collect([
                                $filter->getLabel(),
                                $filter->clauses()[$clause],
                                $value,
                            ])->implode(' ')
                            : [],
                    };
                }

                return [];
            });
    }

    public static function getReadTableColumn(): ToggleIconColumn
    {
        return ToggleIconColumn::make('user_read_exists')
            ->exists('userRead')
            ->label('Read')
            ->sortable()
            ->toggleable()
            ->alignCenter()
            ->getStateUsing(fn(Story $record) => $record->user_read_exists)
            ->updateStateUsing(function (Story $record, $state) {
                if ($state) {
                    $readStoryAction = new ReadStoryAction();
                    $readStoryAction->execute($record, auth()->user());
                } else {
                    $unreadStoryAction = new UnreadStoryAction();
                    $unreadStoryAction->execute($record, auth()->user());
                }

                return $state;
            })
            ->onIcon('heroicon-s-book-open')
            ->offIcon('heroicon-s-lock-closed')
            ->onColor('success')
            ->offColor('secondary');
    }

    public static function getUserReadFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('user_read_exists')
            ->label('Read')
            ->queries(
                true: fn(Builder $query) => $query->whereHas('userRead'),
                false: fn(Builder $query) => $query->whereDoesntHave('userRead'),
            );
    }
}
