<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InProgressStatusResource\Pages;
use App\Filament\Resources\InProgressStatusResource\RelationManagers;
use App\Models\Application;
use App\Models\InProgressStatus;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InProgressStatusResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationGroup = 'Múrájatlar';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        $inProgressId = Status::where('status', 'in_progress')->value('id');
        $user = auth()->user();
        
        $query = static::getModel()::where('status_id', $inProgressId);

        if ($user->hasRole('company-admin')) {
            $query->where('company_id', $user->company_id);
        }
        
        $count = $query->count();
        return $count;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    
    public static function getEloquentQuery(): Builder
    {
        $inProgressStatusId = Status::where('status', 'in_progress')->value('id');
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->where('status_id', $inProgressStatusId);
        if ($user->hasRole('company-admin')) {
            $query->where('company_id', $user->company_id);
        }
        return $query;
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
            ->actions([
                Tables\Actions\ViewAction::make()
                ->label('Tolıq kóriw'),
            ]);
    }
    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_in_progress_applications');
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    public static function getNavigationLabel(): string
    {
        return 'Islep shıǵılmaqta'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Islep shıǵılmaqta'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Islep shıǵılmaqta'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInProgressStatuses::route('/'),
            'view' => Pages\ViewInProgressStatus::route('/{record}'),
            'create' => Pages\CreateInProgressStatus::route('/create'),
            'edit' => Pages\EditInProgressStatus::route('/{record}/edit'),
        ];
    }
}
