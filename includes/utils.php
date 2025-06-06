<?php
function verify_signature($request, $secret = 'abc123')
{
    $signature = $request->get_header('X-Signature');

    if (! $signature) {
        return new WP_Error('missing_signature', 'Signature tidak dikirim', ['status' => 401]);
    }

    $body                 = $request->get_body();
    $calculated_signature = hash_hmac('sha256', $body, $secret);

    if (! hash_equals($calculated_signature, $signature)) {
        return new WP_Error('invalid_signature', 'Signature tidak valid', ['status' => 403]);
    }

    return true;
}
