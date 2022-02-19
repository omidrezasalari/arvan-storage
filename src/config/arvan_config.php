<?php
return [

    'aws_key' => env("AWS_KEY", "place access key here"),
    'aws_secret_key' => env("AWS_SECRET_KEY", "place secret key here"),
    'end_point' => env('ENDPOINT', "http://objects.dreamhost.com"),


    'bindClass' => Omidrezasalari\ArvanStorage\ArvanStorage::class,
];
