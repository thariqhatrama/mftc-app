<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('08##########'),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::PU,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function role(UserRole $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => $role,
        ]);
    }

    public function super_admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SUPER_ADMIN,
        ]);
    }

    public function sales(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SALES,
        ]);
    }

    public function auditor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::AUDITOR,
        ]);
    }

    public function pu(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::PU,
        ]);
    }
}
