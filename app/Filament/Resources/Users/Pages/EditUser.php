<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\URL;
use STS\FilamentImpersonate\Actions\Impersonate;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $roleValue = $record->role instanceof UserRole ? $record->role->value : $record->role;

        return [
            Impersonate::make()
                ->record($record)
                ->visible($roleValue !== UserRole::PU->value)
                ->redirectTo(match ($roleValue) {
                    UserRole::AUDITOR->value => '/admin/audit-assignments',
                    UserRole::SALES->value => '/admin/invoices',
                    default => '/admin',
                }),

            Action::make('impersonate_pu')
                ->label('Akses Dashboard PU')
                ->icon(Heroicon::OutlinedComputerDesktop)
                ->color('info')
                ->visible($roleValue === UserRole::PU->value)
                ->action(function () use ($record) {
                    $token = $record->createToken(
                        'impersonate-'.auth()->id(),
                        ['*'],
                        now()->addHours(2)
                    )->plainTextToken;

                    $returnUrl = urlencode(URL::to('/admin/users'));
                    $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

                    return redirect()->away("{$frontendUrl}/impersonate?token={$token}&return_url={$returnUrl}");
                }),

            Action::make('deactivate')
                ->label('Delete')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn (User $record) => $record->update(['is_active' => false])),
        ];
    }
}
