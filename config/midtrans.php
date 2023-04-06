<?php

return [
    "sandbox_server_key" => env("MIDTRANS_SERVER_KEY_SANDBOX"),
    "production_server_key" => env("MIDTRANS_SERVER_KEY_PRODUCTION"),
    "sandbox_url" => "https://api.sandbox.midtrans.com/v2/charge",
    "production_url" => "https://api.midtrans.com/v2/charge"
];