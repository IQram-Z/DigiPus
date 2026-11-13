<?php

namespace App\Support;

/** Fungsi bantu OAuth tanpa dependensi Laravel (mudah diuji). */
class OAuth
{
    public static function b64u(string $s): string
    {
        return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    }

    /** Isi (payload) JWT sebagai array. Dipakai untuk id_token yang diterima langsung dari token endpoint lewat TLS. */
    public static function jwtPayload(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) return [];
        $json = base64_decode(strtr($parts[1], '-_', '+/'), true);
        return $json ? (json_decode($json, true) ?: []) : [];
    }

    /** Tanda tangan ECDSA format DER -> format mentah R||S (64 byte) yang dipakai JWT ES256. */
    public static function derToRaw(string $der, int $len = 32): string
    {
        $o = 2;
        if (ord($der[1]) & 0x80) $o += ord($der[1]) & 0x7f;      // panjang bentuk panjang
        $o++;                                                    // 0x02
        $rl = ord($der[$o++]); $r = substr($der, $o, $rl); $o += $rl;
        $o++;                                                    // 0x02
        $sl = ord($der[$o++]); $s = substr($der, $o, $sl);
        $r = str_pad(ltrim($r, "\x00"), $len, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), $len, "\x00", STR_PAD_LEFT);
        return $r . $s;
    }

    /** client_secret Apple = JWT ES256 yang ditandatangani dengan kunci .p8. */
    public static function appleClientSecret(string $teamId, string $clientId, string $keyId, string $pem, int $ttl = 86400 * 150): string
    {
        $now = time();
        $head = self::b64u(json_encode(['alg' => 'ES256', 'kid' => $keyId, 'typ' => 'JWT']));
        $body = self::b64u(json_encode(['iss' => $teamId, 'iat' => $now, 'exp' => $now + $ttl, 'aud' => 'https://appleid.apple.com', 'sub' => $clientId]));

        $key = openssl_pkey_get_private($pem);
        if (! $key) throw new \RuntimeException('Kunci privat Apple (.p8) tidak valid.');
        if (! openssl_sign("$head.$body", $der, $key, OPENSSL_ALGO_SHA256)) throw new \RuntimeException('Gagal menandatangani client_secret Apple.');

        return "$head.$body." . self::b64u(self::derToRaw($der));
    }
}
