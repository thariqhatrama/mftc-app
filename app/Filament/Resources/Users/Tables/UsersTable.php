<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->colors([
                        'danger' => UserRole::SUPER_ADMIN,
                        'warning' => UserRole::SALES,
                        'info' => UserRole::AUDITOR,
                        'success' => UserRole::PU,
                    ])
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(UserRole::class),
                TernaryFilter::make('is_active'),
            ])
            ->recordActions([
                Impersonate::make()
                    ->label(fn (User $record): string => match (
                        $record->role instanceof UserRole
                            ? $record->role->value
                            : $record->role
                    ) {
                        UserRole::AUDITOR->value => 'Akses sebagai Auditor',
                        UserRole::SALES->value => 'Akses sebagai Sales',
                        default => 'Impersonate',
                    })
                    ->color(fn (User $record): string => match (
                        $record->role instanceof UserRole
                            ? $record->role->value
                            : $record->role
                    ) {
                        UserRole::AUDITOR->value => 'warning',
                        UserRole::SALES->value => 'success',
                        default => 'gray',
                    })
                    ->visible(fn (User $record): bool => $record->canBeImpersonated()
                        && $record->is_active
                        && auth()->id() !== $record->id
                        && ($record->role instanceof UserRole ? $record->role->value : $record->role) !== UserRole::PU->value)
                    ->redirectTo(function (User $record): string {
                        $role = $record->role instanceof UserRole
                            ? $record->role->value
                            : $record->role;

                        return match ($role) {
                            UserRole::AUDITOR->value => '/admin',
                            UserRole::SALES->value => '/admin',
                            default => '/admin',
                        };
                    }),

                Action::make('impersonate_pu')
                    ->label('Akses Dashboard PU')
                    ->icon(Heroicon::OutlinedComputerDesktop)
                    ->color('info')
                    ->visible(fn (User $record): bool => auth()->user()?->canImpersonate()
                        && (($record->role instanceof UserRole ? $record->role->value : $record->role) === UserRole::PU->value)
                        && $record->is_active)
                    ->action(function (User $record) {
                        $token = $record->createToken(
                            'impersonate-'.auth()->id(),
                            ['*'],
                            now()->addHours(2)
                        )->plainTextToken;

                        $returnUrl = urlencode(URL::to('/admin/users'));
                        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

                        return redirect()->away("{$frontendUrl}/impersonate?token={$token}&return_url={$returnUrl}");
                    }),

                EditAction::make()
                    ->modalHeading('Edit User')
                    ->modalWidth('lg')
                    ->successNotificationTitle('User berhasil diperbarui'),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus User')
                    ->modalDescription('User akan dinonaktifkan (soft delete). Data transaksi tetap tersimpan.')
                    ->modalSubmitActionLabel('Ya, Nonaktifkan')
                    ->action(function (User $record): void {
                        if (auth()->id() === $record->id) {
                            Notification::make()
                                ->danger()
                                ->title('Tidak dapat menghapus akun sendiri')
                                ->send();

                            return;
                        }

                        $record->update(['is_active' => false]);

                        Notification::make()
                            ->success()
                            ->title('User dinonaktifkan')
                            ->send();
                    }),
                Action::make('resetPassword')
                    ->label('Reset Password')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        Password::sendResetLink(['email' => $record->email]);

                        Notification::make()
                            ->title('Password reset email sent')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->modalHeading('Tambah User Baru')
                    ->modalWidth('lg')
                    ->successNotificationTitle('User berhasil ditambahkan'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }
}
