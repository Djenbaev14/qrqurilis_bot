<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    // user select
                Forms\Components\Select::make('user_id')
                    ->label('Пользователь')
                    ->options(\App\Models\User::whereHas('roles', function ($query) {
                        $query->where('name', '!=', 'Super admin');
                    })->pluck('name', 'id'))
                    ->preload()
                    ->searchable()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('addUser')
                            ->label('Добавить пользователя')
                            ->icon('heroicon-o-plus')
                            ->form(
                            [
                                Forms\Components\TextInput::make('name')
                                ->required()
                                ->label('Имя')
                                ->maxLength(255),
                                Forms\Components\TextInput::make('username')
                                    ->label('Логин')
                                    ->required()
                                    ->unique(\App\Models\User::class, 'username')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->label('Пароль')
                                    ->password()
                                    ->required()
                                    ->maxLength(255),
                                Select::make('roles')
                                    ->multiple()
                                    ->label('Роли')
                                    ->options(\Spatie\Permission\Models\Role::where('name', '!=', 'Super admin')->pluck('name', 'id')),
                                ]
                            )
                            ->action(function (array $data, Forms\Components\Actions\Action $action,Set $set) {
                                $user = \App\Models\User::create([
                                    'name' => $data['name'],
                                    'username' => $data['username'],
                                    'password' => bcrypt($data['password']),
                                ]);
                                $user->assignRole('company-admin');
                                $set('user_id', $user->id);
                                    Notification::make()
                                        ->title('Пользователь добавлен')
                                        ->success()
                                        ->send();
                            })
                    ),

            ]);
    }
    

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
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
    
    public static function getNavigationLabel(): string
    {
        return 'Shirketler'; // Rus tilidagi nom
    }
    public static function getModelLabel(): string
    {
        return 'Shirketler'; // Rus tilidagi yakka holdagi nom
    }
    public static function getPluralModelLabel(): string
    {
        return 'Shirketler'; // Rus tilidagi ko'plik shakli
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
