<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RatingTagResource\Pages;
use App\Filament\Resources\RatingTagResource\RelationManagers;
use App\Models\RatingTag;
use App\Models\Story;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Table;
use Filament\Tables;

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                RepeatableEntry::make('stories')
                    ->hiddenLabel()
                    ->grid([
                        'default' => 1,
                        'sm' => 2,
                        'md' => 3,
                        'xl' => 4,
                    ])
                    ->columns(1)
                    ->schema([
                        Split::make([
                            TextEntry::make('pivot.rating')
                                ->label('Rating')
                                ->hiddenLabel()
                                ->weight(FontWeight::Light)
                                ->grow()
                                ->icon('heroicon-s-star')
                                ->iconPosition(IconPosition::Before)
                                ->iconColor('warning'),
                            IconEntry::make('userLike.id')
                                ->label("Liked")
                                ->tooltip("You liked the Story")
                                ->columnSpan(1)
                                ->grow(false)
                                ->hiddenLabel()
                                ->boolean()
                                ->visible(fn (Story $record) => !!$record->userLike)
                                ->trueIcon('heroicon-s-heart')
                                ->falseIcon('heroicon-o-heart')
                                ->trueColor('danger')
                                ->falseColor('gray'),
                            IconEntry::make('userRead.id')
                                ->label("Read")
                                ->tooltip("You read the Story")
                                ->columnSpan(1)
                                ->grow(false)
                                ->hiddenLabel()
                                ->boolean()
                                ->visible(fn (Story $record) => !!$record->userRead)
                                ->trueIcon('heroicon-s-book-open')
                                ->falseIcon('heroicon-s-book-open')
                                ->trueColor('success')
                                ->falseColor('gray')
                            ]
                        ),
                        TextEntry::make('title')
                            ->hiddenLabel()
                            ->weight(FontWeight::Bold),
                        Actions::make([
                            Actions\Action::make('read')
                                ->color('primary')
                                ->icon('heroicon-o-eye')
                                ->size('sm')
                                ->link()
                                ->url(fn (Story $record) => StoryResource::getUrl('view', ['record' => $record]))
                        ]),
                        TextEntry::make('body')
                            ->hiddenLabel()
                            ->formatStateUsing(fn (string $state) => strip_tags($state))
                            ->words(25)
                    ])
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
            ->grow(false)
            ->sortable();
    }

    public static function getStoriesCountTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('stories_count')
            ->counts('stories')
            ->sortable();
    }
}
