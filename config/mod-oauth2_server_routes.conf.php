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
                // TODO preserve url query params
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
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/AssertAuthToken'
                ],
                'routes' => [
                    'members' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/members',
                            'match_whole' => false,
                        ],
                        'routes' => [
                            // When POST something
                            'post' => [
                                'route'   => 'RouteMethod',
                                'options' => [
                                    'method' => 'POST',
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        '/module/foundation/actions/ParseRequestData',
                                        function($request_data) {
                                           kd($request_data);
                                        },
                                        '/module/oauth2/actions/Register',
                                    ],
                                ],
                            ],
                            'get' => [
                                'route'   => 'RouteMethod',
                                'options' => [
                                    'method' => 'GET',
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => function() {
                                        kd('This is magic of poirot/.');
                                    },
                                ],
                            ],
                        ],
                    ]
                ],
            ],

            ##
            'register' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/register',
                    'match_whole' => false,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Register',
                ],
            ],
            'login' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/login',
                    'match_whole' => false,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Login',
                ],
            ],
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
