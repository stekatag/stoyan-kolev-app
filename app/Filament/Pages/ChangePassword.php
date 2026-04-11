<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePassword extends Page implements HasForms {
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'change-password';

    protected string $view = 'filament.pages.change-password';

    public ?array $data = [];

    public static function canAccess(): bool {
        $user = Auth::user();

        return $user instanceof User
            ? $user->canAccessChangePassword()
            : false;
    }

    public function mount(): void {
        abort_unless(static::canAccess(), 403);

        $this->form->fill();
    }

    public function form(Schema $schema): Schema {
        return $schema
            ->components([
                TextInput::make('current_password')
                    ->label('Current Password')
                    ->password()
                    ->revealable()
                    ->required(),
                TextInput::make('new_password')
                    ->label('New Password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->same('password_confirmation'),
                TextInput::make('password_confirmation')
                    ->label('Confirm New Password')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void {
        $state = $this->form->getState();
        $user = Auth::user();

        if (!($user instanceof User) || !Hash::check((string) $state['current_password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'data.current_password' => 'The current password is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make((string) $state['new_password']),
        ]);

        session()->put('password_hash_' . Auth::getDefaultDriver(), $user->password);
        $this->form->fill();

        Notification::make()
            ->title('Password updated successfully.')
            ->success()
            ->send();
    }
}
