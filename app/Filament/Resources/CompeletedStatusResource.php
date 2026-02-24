<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompeletedStatusResource\Pages;
use App\Filament\Resources\CompeletedStatusResource\RelationManagers;
use App\Models\Application;
use App\Models\CompeletedStatus;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompeletedStatusResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationGroup = 'Múrájatlar';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        $completedStatusId = Status::where('status', 'completed')->value('id');
        $user = auth()->user();
        
        $query = static::getModel()::where('status_id', $completedStatusId);

        if ($user->hasRole('company-admin')) {
            $query->where('company_id', $user->company_id);
        }
        
        $count = $query->count();
        return $count;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    
    public static function getEloquentQuery(): Builder
    {
        $completedStatusId = Status::where('status', 'completed')->value('id');
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->where('status_id', $completedStatusId);
        if ($user->hasRole('company-admin')) {
            $query->where('company_id', $user->company_id);
        }
        return $query;
    }

    public static function getNavigationLabel(): string
    {
        return 'Jumıs juwmaqlandı'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Jumıs juwmaqlandı'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Jumıs juwmaqlandı'; // Rus tilidagi ko'plik shakli
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
    public static function canViewAny(): bool
    {
        return auth()->user()->can('view_completed_applications');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompeletedStatuses::route('/'),
            'view' => Pages\ViewCompletedStatus::route('/{record}'),
            'create' => Pages\CreateCompeletedStatus::route('/create'),
            'edit' => Pages\EditCompeletedStatus::route('/{record}/edit'),
        ];
    }
}
