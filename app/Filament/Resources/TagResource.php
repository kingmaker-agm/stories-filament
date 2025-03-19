<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TagResource\Pages;
use App\Filament\Resources\TagResource\RelationManagers;
use App\Models\Story;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
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
use Illuminate\Validation\Rules\Unique;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = "Taxonomy";
    protected static ?string $navigationIcon = 'heroicon-o-hashtag';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::getPrimaryNameField(),
                self::getSecondaryNameField(),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                self::getPrimaryTableColumn(),
                self::getSecondaryTableColumn(),
                self::getNameTableColumn(),
                self::getStoriesCountTableColumn(),
            ])
            ->filters([
                //
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
                            ->words(25),
                        Split::make([
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
            'index' => Pages\ListTags::route('/'),
            'create' => Pages\CreateTag::route('/create'),
            'view' => Pages\ViewTag::route('/{record}'),
            'edit' => Pages\EditTag::route('/{record}/edit'),
        ];
    }

    public static function getPrimaryNameField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('primary')
            ->autofocus()
            ->required()
            ->maxLength(255)
            ->datalist(
                fn() => Tag::query()
                    ->distinct()
                    ->pluck('primary')
            )
            ->unique(
                ignoreRecord: true,
                modifyRuleUsing: function (Unique $rule, callable $get) {
                    $primary = $get('primary');
                    $secondary = $get('secondary');

                    if (empty($secondary)) {
                        return $rule->where('primary', $primary);
                    } else {
                        return $rule->where('primary', $primary)
                            ->where('secondary', $secondary);
                    }
                }
            );
    }

    public static function getSecondaryNameField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('secondary')
            ->maxLength(255)
            ->unique(
                ignoreRecord: true,
                modifyRuleUsing: fn(Unique $rule, callable $get) => $rule
                    ->where('primary', $get('primary'))
                    ->where('secondary', $get('secondary'))
            );
    }

    public static function getPrimaryTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('primary')
            ->visibleFrom('md')
            ->sortable()
            ->searchable();
    }

    public static function getSecondaryTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('secondary')
            ->visibleFrom('md')
            ->sortable()
            ->searchable();
    }

    public static function getNameTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->hiddenFrom('md')
            ->sortable()
            ->searchable();
    }

    public static function getStoriesCountTableColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('stories_count')
            ->counts('stories')
            ->sortable();
    }
}
