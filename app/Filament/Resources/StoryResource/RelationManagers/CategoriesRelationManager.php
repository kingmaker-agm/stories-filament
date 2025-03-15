<?php

namespace App\Filament\Resources\StoryResource\RelationManagers;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';
    protected static ?string $inverseRelationship = 'stories';

    protected static ?string $recordTitleAttribute = 'name';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                CategoryResource::getNameField(),
                CategoryResource::getHiddenUserIdField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                CategoryResource::getNameTableColumn(),
                CategoryResource::getStoriesCountTableColumn(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->label("Attach to existing Category")
                    ->color('success')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-book-open')
                    ->color('primary')
                    ->tooltip('Open in a New Tab')
                    ->url(fn (Category $record) => CategoryResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
