<?php
return [
    "key" => env("ACCESS_KEY_ID"),
    "secret" => env("ACCESS_KEY_SECRET"),
    "region" => env("REGION_ID"),
    "bucket" => env("OSS_BUCKET", null),
    "location" => env("OSS_LOCATION"),
    "pipeline" => env("PIPELINE_ID", null),
    "snapshot" => [
        "time" => 1000, //起始秒数
        "interval" => 10,
        "num" => 12,
        "height" => 360,
    ]

];
