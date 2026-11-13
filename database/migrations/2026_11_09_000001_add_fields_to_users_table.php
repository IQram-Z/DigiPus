<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('username')->nullable()->unique()->after('name');
            // pengunjung = akun terdaftar yang belum jadi anggota; anggota = punya kartu & boleh booking
            $t->enum('role', ['pengunjung', 'anggota', 'petugas', 'administrator'])->default('pengunjung')->after('password');
            $t->string('status_akun')->default('aktif')->after('role');
            $t->string('provider', 20)->nullable()->after('status_akun');   // google | apple
            $t->string('provider_id')->nullable()->after('provider');
            $t->string('foto')->nullable()->after('provider_id');
            $t->timestamp('pengajuan_at')->nullable()->after('foto');        // pengunjung mengajukan keanggotaan
            $t->unique(['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropUnique(['provider', 'provider_id']);
            $t->dropUnique(['username']);
            $t->dropColumn(['username', 'role', 'status_akun', 'provider', 'provider_id', 'foto', 'pengajuan_at']);
        });
    }
};
