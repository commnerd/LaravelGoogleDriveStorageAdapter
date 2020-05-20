<?php

return [
    "driver" => "google_drive",

    "force_default" => env("STORAGE_GOOGLE_DRIVE_FORCE_DEFAULT", false),

    "client_id" => env("STORAGE_GOOGLE_DRIVE_CLIENT_ID"),

    "client_secret" => env("STORAGE_GOOGLE_DRIVE_CLIENT_SECRET"),

    "refresh_token" => env("STORAGE_GOOGLE_DRIVE_REFRESH_TOKEN"),

    "root" => env("STORAGE_GOOGLE_DRIVE_FOLDER_ID"),
];
