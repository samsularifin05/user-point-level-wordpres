<?php

define('MY_API_SECRET_KEY', 'b3r4sput1h');

function verify_signature($request)
{
    $secret    = MY_API_SECRET_KEY;
    $timestamp = $request->get_header('X-Timestamp');
    $signature = $request->get_header('X-Signature');

    // Validasi header
    if (! $timestamp || ! $signature) {
        return new WP_Error('missing_signature', 'Missing Signature', ['status' => 401]);
    }

    // Validasi timestamp max 5 menit dari sekarang
    if (abs(time() - intval($timestamp)) > 300) {
        return new WP_Error('timestamp_expired', 'Timestamp expired', ['status' => 401]);
    }

    // Hitung ulang signature
    $data                 = $secret . $timestamp;
    $calculated_signature = hash_hmac('sha256', $data, $secret);

    if (! hash_equals($calculated_signature, $signature)) {
        return new WP_Error('invalid_signature', 'Signature tidak valid', ['status' => 403]);
    }

    return true;
}
