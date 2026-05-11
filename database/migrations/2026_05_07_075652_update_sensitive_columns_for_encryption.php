<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('full_name')->change();
            $table->text('phone')->nullable()->change();
        });

        Schema::table('business_profiles', function (Blueprint $table): void {
            $table->text('company_name')->change();
            $table->text('contact_person')->nullable()->change();
            $table->text('contact_phone')->nullable()->change();
            $table->text('nib')->nullable()->change();
        });

        DB::table('users')->orderBy('id')->chunk(100, function ($users): void {
            foreach ($users as $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'full_name' => $this->encryptIfPlain($user->full_name),
                        'phone' => $this->encryptIfPlain($user->phone),
                    ]);
            }
        });

        DB::table('business_profiles')->orderBy('id')->chunk(100, function ($profiles): void {
            foreach ($profiles as $profile) {
                DB::table('business_profiles')
                    ->where('id', $profile->id)
                    ->update([
                        'company_name' => $this->encryptIfPlain($profile->company_name),
                        'address' => $this->encryptIfPlain($profile->address),
                        'contact_person' => $this->encryptIfPlain($profile->contact_person),
                        'contact_phone' => $this->encryptIfPlain($profile->contact_phone),
                        'nib' => $this->encryptIfPlain($profile->nib),
                    ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('users')->orderBy('id')->chunk(100, function ($users): void {
            foreach ($users as $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'full_name' => $this->decryptIfEncrypted($user->full_name),
                        'phone' => $this->decryptIfEncrypted($user->phone),
                    ]);
            }
        });

        DB::table('business_profiles')->orderBy('id')->chunk(100, function ($profiles): void {
            foreach ($profiles as $profile) {
                DB::table('business_profiles')
                    ->where('id', $profile->id)
                    ->update([
                        'company_name' => $this->decryptIfEncrypted($profile->company_name),
                        'address' => $this->decryptIfEncrypted($profile->address),
                        'contact_person' => $this->decryptIfEncrypted($profile->contact_person),
                        'contact_phone' => $this->decryptIfEncrypted($profile->contact_phone),
                        'nib' => $this->decryptIfEncrypted($profile->nib),
                    ]);
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->string('full_name')->change();
            $table->string('phone', 20)->nullable()->change();
        });

        Schema::table('business_profiles', function (Blueprint $table): void {
            $table->string('company_name')->change();
            $table->string('contact_person')->nullable()->change();
            $table->string('contact_phone', 20)->nullable()->change();
            $table->string('nib', 100)->nullable()->change();
        });
    }

    private function encryptIfPlain(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            Crypt::decryptString($value);

            return $value;
        } catch (Throwable) {
            return Crypt::encryptString($value);
        }
    }

    private function decryptIfEncrypted(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }
};
