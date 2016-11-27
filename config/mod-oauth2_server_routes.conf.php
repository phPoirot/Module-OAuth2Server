<?php
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;

return [
    /// Override Home Page Route
    //- Redirect to login page
    'home'  => array(
        'route'    => 'RouteSegment',
        'options' => array(
            'criteria'    => '/',
            'match_whole' => true,
        ),
        'params'  => array(
            ListenerDispatch::CONF_KEY => function($response) {
                // TODO preserve url query params with redirect
                // TODO redirect plugin or ResponseRedirect
                /** @var \Poirot\Http\HttpResponse $response */
                $response->setStatusCode(302);
                $response->headers()->insert(
                    \Poirot\Http\Header\FactoryHttpHeader::of([
                        'location' => (string) \Module\Foundation\Actions\IOC::url('main/oauth/login')
                    ])
                );

                return $response;
            },
        ),
    ),

    'oauth'  => [
        'routes' => [

            ## API
            'api' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/api/v1',
                    'match_whole' => false,
                ],
                'params'  => [
                    // This Action Run First In Chains and Assert Validate Token
                    ListenerDispatch::CONF_KEY => function ($request = null) {
                        $token = \Module\OAuth2\assertAuthToken($request);
                        return ['token' => $token];
                    }
                ],
                'routes' => [
                    'me' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/me',
                            'match_whole' => false,
                        ],
                        'routes' => [
                            'profile' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/profile',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/users/GetUserInfo',
                                ],
                            ],
                        ],
                    ],
                    'members' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/members',
                            'match_whole' => false,
                        ],
                        'routes' => [
                            'profile' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/profile/:uid{\w+}',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/users/GetUserInfo',
                                ],
                            ],
                            // When POST something
                            'post' => [
                                'route'   => 'RouteMethod',
                                'options' => [
                                    'method' => 'POST',
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        '/module/oauth2/actions/users/Register',
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
            ],

            'me' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/me',
                    'match_whole' => false,
                ],
                'routes' => [
                    'profile' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => function() { return []; },
                        ],
                    ],
                    'logout' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/logout',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/LogoutPage',
                        ],
                    ]
                ],
            ],

            ##
            'register' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/register',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/RegisterPage',
                ],
            ],
            'login' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/login',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/LoginPage',
                ],
            ],
            'validate' => [
                'route' => 'RouteSegment',
                'options' => [
                    // also "validation_code" exists in params and pass through actions as argument
                    'criteria'    => '/members/validate/:validation_code{\w+}',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/ValidatePage',
                ],
            ],

            ##
            'authorize' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/auth',
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Authorize',
                ],
            ],
            'token' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/auth/token',
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/RespondToRequest',
                ],
            ],

        ],
    ],
];
