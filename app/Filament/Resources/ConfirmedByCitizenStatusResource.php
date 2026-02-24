<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfirmedByCitizenStatusResource\Pages;
use App\Filament\Resources\ConfirmedByCitizenStatusResource\RelationManagers;
use App\Models\Application;
use App\Models\ConfirmedByCitizenStatus;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConfirmedByCitizenStatusResource extends Resource
{
    protected static ?string $model = Application::class;

    
    protected static ?string $navigationGroup = 'Múrájatlar';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        $confirmedByCitizenStatusId = Status::where('status', 'confirmed_by_citizen')->value('id');
        $user = auth()->user();
        
        $query = static::getModel()::where('status_id', $confirmedByCitizenStatusId);

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
        $confirmedByCitizenStatusId = Status::where('status', 'confirmed_by_citizen')->value('id');
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->where('status_id', $confirmedByCitizenStatusId);
        if ($user->hasRole('company-admin')) {
            $query->where('company_id', $user->company_id);
        }
        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
        return auth()->user()->can('view_confirmed_by_citizen_applications');
    }
    public static function getNavigationLabel(): string
    {
        return 'Puqara tastıyıqladı'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Puqara tastıyıqladı'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Puqara tastıyıqladı'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConfirmedByCitizenStatuses::route('/'),
            'create' => Pages\CreateConfirmedByCitizenStatus::route('/create'),
            'edit' => Pages\EditConfirmedByCitizenStatus::route('/{record}/edit'),
        ];
    }
}
