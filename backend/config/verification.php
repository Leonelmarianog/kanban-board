<?php

return [
    'token_expiration_seconds' => (int) env('VERIFICATION_TOKEN_EXPIRATION', 900),
    'email_change_token_expiration_seconds' => (int) env('EMAIL_CHANGE_TOKEN_EXPIRATION', 3600),
];
