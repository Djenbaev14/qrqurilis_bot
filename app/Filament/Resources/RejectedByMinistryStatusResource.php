<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RejectedByMinistryStatusResource\Pages;
use App\Filament\Resources\RejectedByMinistryStatusResource\RelationManagers;
use App\Models\Application;
use App\Models\RejectedByMinistryStatus;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RejectedByMinistryStatusResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationGroup = 'Múrájatlar';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $rejectedByMinistryId = \App\Models\Status::where('status', 'rejected_by_ministry')->value('id');

        return static::getModel()::where('status_id', $rejectedByMinistryId)->count();
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        $rejectedByMinistryId = Status::where('status', 'rejected_by_ministry')->value('id');

        return parent::getEloquentQuery()
            ->where('status_id', $rejectedByMinistryId);
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
    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_rejected_by_ministry_applications');
    }
    public static function getNavigationLabel(): string
    {
        return 'Ministrlik biykar etken múrájatlar'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Ministrlik biykar etken múrájat'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Ministrlik biykar etken múrájatlar'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRejectedByMinistryStatuses::route('/'),
            'view' => Pages\ViewRejectedByMinistry::route('/{record}'),
            'create' => Pages\CreateRejectedByMinistryStatus::route('/create'),
            'edit' => Pages\EditRejectedByMinistryStatus::route('/{record}/edit'),
        ];
    }
}
