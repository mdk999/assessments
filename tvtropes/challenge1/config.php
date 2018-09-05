<?php
return [
    'database' => [
        'host' => '127.0.0.1',
        'name' => 'tvdb',
        'user' => 'root',
        'pass' => 'welcome1'
    ],
    'jwt' => [
        'endpoint' => 'https://api.thetvdb.com',
        'refresh' => 72000, //api key lasts for 24hrs. We refresh at this interval(23hrs)
        'imgprefix' => 'http://thetvdb.com/banners/'
    ],
    'jwtcreds' => [
        'apikey' => 'LENC7CQ2WUH4PB87',
        'userkey' =>'17MSCXAHS7FO09C5',
        'username' => 'jfrs506zo'
        ]
];