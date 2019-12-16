<?php
return [
    'database' => [
        'host' => '127.0.0.1',
        'name' => 'uccldb',
        'user' => 'root',
        'pass' => 'welcome1'
    ],
   'url' => 'https://portland.craigslist.org/d/apts-housing-for-rent/search/apa',
   'testing'=> true, //if testing use the local file instead of pulling from the live url
   'test_file'=>'test.html', //local file used for testing.
   'check_interval'=>30, //difference in minutes between last modified and next check. Reduce the frequency of request as to not get blocked
   'cl_img_url'=>'https://images.craigslist.org',
];
