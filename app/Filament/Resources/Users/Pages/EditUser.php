<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('deactivate')
                ->label('Delete')
                ->icon(Heroicon::Trash)
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn (User $record) => $record->update(['is_active' => false])),
        ];
    }
}
