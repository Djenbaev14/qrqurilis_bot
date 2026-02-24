<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewStatusResource\Pages;
use App\Filament\Resources\NewStatusResource\RelationManagers;
use App\Models\Application;
use App\Models\NewStatus;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NewStatusResource extends Resource
{
    protected static ?string $model = Application::class;


    protected static ?string $navigationGroup = 'Múrájatlar';
    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        $newStatusId = \App\Models\Status::where('status', 'new')->value('id');

        return static::getModel()::where('status_id', $newStatusId)->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        $newStatusId = Status::where('status', 'new')->value('id');

        return parent::getEloquentQuery()
            ->where('status_id', $newStatusId);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_new_applications');
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID'),
                Tables\Columns\TextColumn::make('resident.full_name')->label('FIO'),
                Tables\Columns\TextColumn::make('region.name.qr')->label('Rayon'),
                Tables\Columns\TextColumn::make('address')->label('Address'),
                Tables\Columns\TextColumn::make('created_at')->label('Kelib tusken waqti')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->label('Tolıq kóriw'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationLabel(): string
    {
        return 'Jańa kelip túsken múrájatlar'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Jańa kelip túsken múrájat'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Jańa kelip túsken múrájatlar'; // Rus tilidagi ko'plik shakli
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewStatuses::route('/'),
            'view' => Pages\ViewNewStatus::route('/{record}'),
            'edit' => Pages\EditNewStatus::route('/{record}/edit'),
        ];
    }
}
