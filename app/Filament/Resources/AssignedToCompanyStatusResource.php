<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignedToCompanyStatusResource\Pages;
use App\Filament\Resources\AssignedToCompanyStatusResource\RelationManagers;
use App\Models\Application;
use App\Models\AssignedToCompanyStatus;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignedToCompanyStatusResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationGroup = 'Múrájatlar';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    public static function getNavigationBadge(): ?string
    {
        $assignedStatusId = Status::where('status', 'assigned_to_company')->value('id');
        $user = auth()->user();
        
        $query = static::getModel()::where('status_id', $assignedStatusId);

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
        $assignedStatusId = Status::where('status', 'assigned_to_company')->value('id');
        $user = auth()->user();

        $query = parent::getEloquentQuery()
            ->where('status_id', $assignedStatusId);

        // Agar foydalanuvchi company-admin bo'lsa, faqat o'z shirkati ma'lumotlarini ko'radi
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
                Tables\Columns\TextColumn::make('resident.full_name')->label('ФИО'),
                Tables\Columns\TextColumn::make('region.name.qr')->label('Регион'),
                Tables\Columns\TextColumn::make('address')->label('Адрес'),
                Tables\Columns\TextColumn::make('created_at')->label('Дата создания')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->label('Tolıq kóriw'),
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
        return auth()->user()->can('view_assigned_to_company_applications');
    }
    public static function getNavigationLabel(): string
    {
        return 'Shirketke jiberildi'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Shirketke jiberildi'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Shirketke jiberildi'; // Rus tilidagi ko'plik shakli
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignedToCompanyStatuses::route('/'),
            'view' => Pages\ViewAssignedToCompanyStatus::route('/{record}'),
        ];
    }
}
