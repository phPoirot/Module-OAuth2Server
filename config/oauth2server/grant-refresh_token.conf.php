<?php
return [
    'ttl_refresh_token' => new \DateInterval('P1M'),
    'ttl_access_token' => new \DateInterval('PT1H'),
    /*
    'repo_access_token' => new \Poirot\Ioc\instance(
        '/Module/OAuth2/Services/Repository/AccessTokens'
    ),
    */
];