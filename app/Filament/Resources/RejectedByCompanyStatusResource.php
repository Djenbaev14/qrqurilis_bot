<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RejectedByCompanyStatusResource\Pages;
use App\Filament\Resources\RejectedByCompanyStatusResource\RelationManagers;
use App\Models\Application;
use App\Models\RejectedByCompanyStatus;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RejectedByCompanyStatusResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationGroup = 'Múrájatlar';
    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        $rejectedByCompanyStatusId = Status::where('status', 'rejected_by_company')->value('id');
        $user = auth()->user();
        
        $query = static::getModel()::where('status_id', $rejectedByCompanyStatusId);

        if ($user->hasRole('company-admin')) {
            $query->where('company_id', $user->company_id);
        }
        
        $count = $query->count();
        return $count;
    }
    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
    public static function getEloquentQuery(): Builder
    {
        $rejectedByCompanyStatusId = Status::where('status', 'rejected_by_company')->value('id');
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->where('status_id', $rejectedByCompanyStatusId);
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
        return auth()->user()->can('view_rejected_by_company_applications');
    }
    public static function getNavigationLabel(): string
    {
        return 'Shirket biykar etti'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Shirket biykar etken status'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Shirket biykar etken statuslar'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRejectedByCompanyStatuses::route('/'),
            'create' => Pages\CreateRejectedByCompanyStatus::route('/create'),
            'edit' => Pages\EditRejectedByCompanyStatus::route('/{record}/edit'),
        ];
    }
}
