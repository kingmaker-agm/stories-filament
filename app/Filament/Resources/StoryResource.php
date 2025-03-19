<?php

namespace App\Filament\Resources;

use App\Actions\Story\LikeStoryAction;
use App\Actions\Story\RateStoryAction;
use App\Actions\Story\ReadStoryAction;
use App\Actions\Story\RemoveStoryRatingAction;
use App\Actions\Story\UnlikeStoryAction;
use App\Actions\Story\UnreadStoryAction;
use App\Filament\Actions\Story\AttachToCategoriesBulkAction;
use App\Filament\Actions\Story\AttachToRatingTagsBulkAction;
use App\Filament\Actions\Story\AttachToTagsBulkAction;
use App\Filament\Actions\Story\DetachFromCategoriesBulkAction;
use App\Filament\Actions\Story\DetachFromRatingTagsBulkAction;
use App\Filament\Actions\Story\DetachFromTagsBulkAction;
use App\Filament\Forms\Components\Actions\OpenUrlAction;
use App\Filament\Resources\StoryResource\Pages;
use App\Filament\Resources\StoryResource\RelationManagers;
use App\Infolists\Components\DividerEntry;
use App\Models\Category;
use App\Models\RatingTag;
use App\Models\Story;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions as InfolistActions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Table;
use Filament\Tables;
use IbrahimBougaoua\FilamentRatingStar\Entries\Components\RatingStar as RatingStarEntry;
use IbrahimBougaoua\FilamentRatingStar\Forms\Components\RatingStar;
use Icetalker\FilamentTableRepeatableEntry\Infolists\Components\TableRepeatableEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Webbingbrasil\FilamentAdvancedFilter\Filters\NumberFilter;
use Yepsua\Filament\Forms\Components\Rating;
use Yepsua\Filament\Tables\Components\RatingColumn;

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
                            ->onColor('danger')
                            ->offColor('gray')
                            ->onIcon('heroicon-s-heart')
                            ->offIcon('heroicon-o-heart')
                            ->label('Liked'),
                        Forms\Components\Toggle::make('user_read_exists')
                            ->onColor('success')
                            ->offColor('gray')
                            ->onIcon('heroicon-s-book-open')
                            ->offIcon('heroicon-s-book-open')
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
                self::getDeleteBulkAction(),
                self::getCategoriesBulkAction(),
                self::getTagsBulkAction(),
                self::getRatingTagsBulkAction(),
            ])
            ->filters([
                self::getSeriesFilter(),
                self::getUserLikedFilter(),
                self::getUserReadFilter(),
                self::getUserRatingFilter(),
                self::getTagsFilter(),
                self::getRatingTagsFilter(),
                self::getCategoryFilter(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $repeatableSeriesStoryMatch = function ($matchValue, $nonMatchValue = null) {
            return function (Story $record) use ($matchValue, $nonMatchValue) {
                /** @var Pages\ViewStory $viewPage */
                $viewPage = Livewire::current();

                /** @var Story $story Story being viewed. */
                $story = $viewPage->getRecord();

                return $story->is($record)
                    ? $matchValue
                    : $nonMatchValue;
            };
        };

        return $infolist
            ->columns([
                'xs' => 1,
                'sm' => 2,
                'lg' => 4
            ])
            ->schema([
                Section::make("Story")
                    ->heading(null)
                    ->columnSpan([
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->schema([
                        Grid::make(columns: [
                                'default' => 4,
                                'md' => 6,
                                'lg' => 8,
                            ])
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Title')
                                    ->color("primary")
                                    ->size('lg')
                                    ->weight(FontWeight::Bold)
                                    ->columnSpan([
                                        'default' => 3,
                                        'md' => 5,
                                        'lg' => 7,
                                    ]),
                                Grid::make([
                                    'default' => 2
                                ])
                                    ->columnSpan(1)
                                    ->schema([
                                        IconEntry::make('userLike.id')
                                            ->label("Liked")
                                            ->tooltip("You liked the Story")
                                            ->columnSpan(1)
                                            ->hiddenLabel()
                                            ->boolean()
                                            ->trueIcon('heroicon-s-heart')
                                            ->falseIcon('heroicon-o-heart')
                                            ->trueColor('danger')
                                            ->falseColor('gray'),
                                        IconEntry::make('userRead.id')
                                            ->label("Read")
                                            ->tooltip("You read the Story")
                                            ->columnSpan(1)
                                            ->hiddenLabel()
                                            ->boolean()
                                            ->trueIcon('heroicon-s-book-open')
                                            ->falseIcon('heroicon-s-book-open')
                                            ->trueColor('success')
                                            ->falseColor('gray')
                                    ]),
                            ]),
                        DividerEntry::make('divider'),
                        TextEntry::make('body')
                            ->hiddenLabel()
                            ->html(),

                        InfolistActions::make([
                            InfolistActions\Action::make('like')
                                ->label('Like')
                                ->icon('heroicon-s-heart')
                                ->color('danger')
                                ->visible(fn (Story $record) => !$record->userLike)
                                ->action(function (Story $record, LikeStoryAction $likeStoryAction) {
                                    $likeStoryAction->execute($record, Auth::user());
                                }),
                            InfolistActions\Action::make('unlike')
                                ->label('Unlike')
                                ->icon('heroicon-o-heart')
                                ->color('danger')
                                ->outlined()
                                ->visible(fn (Story $record) => $record->userLike)
                                ->action(function (Story $record, UnlikeStoryAction $unlikeStoryAction) {
                                    $unlikeStoryAction->execute($record, Auth::user());
                                }),
                            InfolistActions\Action::make('read')
                                ->label('Mark as Read')
                                ->icon('heroicon-s-book-open')
                                ->color('success')
                                ->visible(fn (Story $record) => !$record->userRead)
                                ->action(function (Story $record, ReadStoryAction $readStoryAction) {
                                    $readStoryAction->execute($record, Auth::user());
                                }),
                            InfolistActions\Action::make('unread')
                                ->label('Mark as Unread')
                                ->icon('heroicon-s-bookmark-slash')
                                ->color('success')
                                ->outlined()
                                ->visible(fn (Story $record) => $record->userRead)
                                ->action(function (Story $record, UnreadStoryAction $unreadStoryAction) {
                                    $unreadStoryAction->execute($record, Auth::user());
                                }),
                            InfolistActions\Action::make('rating')
                                ->label('Make Rating')
                                ->icon('heroicon-s-star')
                                ->color('warning')
                                ->outlined()
                                ->modalHeading("Update Story Rating")
                                ->form([
                                    RatingStar::make('rating')
                                        ->label("Story Rating")
                                        ->default(fn (Story $record) => $record->userRating?->rating ?? 0)
                                        ->required()
                                ])
                                ->action(function (Story $record, array $data, RateStoryAction $rateStoryAction) {
                                    $rateStoryAction->execute($record, Auth::user(), $data['rating']);
                                })
                        ]),
                    ]),

                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make("Details")
                            ->columnSpan(1)
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Author')
                                    ->icon('heroicon-o-user')
                                    ->visible(fn ($record) => !!$record->user),
                                TextEntry::make('original_url')
                                    ->label('Original URL')
                                    ->limit(25)
                                    ->icon('heroicon-o-link')
                                    ->visible(fn ($record) => !!$record->original_url)
                                    ->url(
                                        url: fn ($record) => $record->original_url,
                                        shouldOpenInNewTab: true
                                    ),
                                RatingStarEntry::make('userRating.rating')
                                    ->label('User Rating'),
                                TextEntry::make('tags.name')
                                    ->label('Tags')
                                    ->visible(fn (Story $record) => $record->tags->count() > 0)
                                    ->icon('heroicon-o-hashtag')
                                    ->badge()
                                    ->color('success'),
                            ]),

                        TableRepeatableEntry::make('ratingTags')
                            ->label("Ratings")
                            ->hiddenLabel()
                            ->visible(fn (Story $record) => $record->ratingTags()->count() > 0)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Label'),
                                TextEntry::make('pivot.rating')
                                    ->label('Rating')
                            ]),

                        Section::make('Series')
                            ->visible(fn (Story $record) => !! $record->series)
                            ->collapsible()
                            ->schema([
                                RepeatableEntry::make('series.stories')
                                    ->label("Series")
                                    ->hiddenLabel()
                                    ->contained(false)
                                    ->schema([
                                        TextEntry::make('title')
                                            ->label('Title')
                                            ->hiddenLabel()
                                            ->icon($repeatableSeriesStoryMatch('heroicon-s-arrow-right'))
                                            ->iconPosition(IconPosition::Before)
                                            ->weight($repeatableSeriesStoryMatch(FontWeight::Bold))
                                            ->url(fn (Story $record) => StoryResource::getUrl('view', ['record' => $record]))
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SeriesRelationManager::class,
            RelationManagers\RatingTagsRelationManager::class,
            RelationManagers\TagsRelationManager::class,
            RelationManagers\CategoriesRelationManager::class,
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
            ->html()
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
            ->url(fn (Story $record) => $record->story_series_id
                ? StorySeriesResource::getUrl('view', ['record' => $record->story_series_id])
                : null
            )
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

    public static function getCategoryFilter()
    {
        return Tables\Filters\SelectFilter::make('categories')
            ->relationship('categories', 'name')
            ->multiple()
            ->query(function (Builder $query, array $data) {
                $ids = $data['values'];

                return $query->where(function (Builder $query) use ($ids) {
                    foreach ($ids as $id) {
                        $query->whereHas('categories', fn(Builder $query) => $query->where((new Category)->qualifyColumn('id'), $id));
                    }
                });
            })
            ->label('Categories');
    }

    public static function getLikedTableColumn(): Tables\Columns\ToggleColumn
    {
        return Tables\Columns\ToggleColumn::make('user_like_exists')
            ->exists('userLike')
            ->label('Liked')
            ->sortable()
            ->toggleable()
            ->alignCenter()
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
            ->offColor('gray');
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

    public static function getReadTableColumn(): Tables\Columns\ToggleColumn
    {
        return Tables\Columns\ToggleColumn::make('user_read_exists')
            ->exists('userRead')
            ->label('Read')
            ->sortable()
            ->toggleable()
            ->alignCenter()
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
            ->offIcon('heroicon-s-book-open')
            ->onColor('success')
            ->offColor('gray');
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

    public static function getCategoriesBulkAction(): Tables\Actions\BulkActionGroup
    {
        return Tables\Actions\BulkActionGroup::make([
            AttachToCategoriesBulkAction::make(),
            DetachFromCategoriesBulkAction::make(),
        ])
            ->label('Categories')
            ->icon('heroicon-o-bookmark')
            ->outlined()
            ->color('info');
    }

    public static function getTagsBulkAction(): Tables\Actions\BulkActionGroup
    {
        return Tables\Actions\BulkActionGroup::make([
            AttachToTagsBulkAction::make(),
            DetachFromTagsBulkAction::make(),
        ])
            ->label('Tags')
            ->icon('heroicon-o-hashtag')
            ->outlined()
            ->color('success');
    }

    public static function getRatingTagsBulkAction(): Tables\Actions\BulkActionGroup
    {
        return Tables\Actions\BulkActionGroup::make([
            AttachToRatingTagsBulkAction::make(),
            DetachFromRatingTagsBulkAction::make(),
        ])
            ->label('Rating Tags')
            ->icon('heroicon-o-tag')
            ->outlined()
            ->color('warning');
    }

    public static function getDeleteBulkAction(): Tables\Actions\DeleteBulkAction
    {
        return Tables\Actions\DeleteBulkAction::make()
            ->requiresConfirmation();
    }
}
