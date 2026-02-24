<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function afterCreate(): void
    {
        $record = $this->getRecord(); // Yangi yaratilgan Company
        $userId = $record->user_id; // Formada tanlangan user_id

        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $user->update([
                    'company_id' => $record->id
                ]);
            }
        }
    }
}
