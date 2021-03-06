<?php
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Router\Route\RouteSegment;
use \Module\OAuth2\Actions;

return [
    'oauth'  => [
        'routes' => [
            ## OAuth2 Specific EndPoints -----------------------------------------------------------------\
            'authorize' => [
                'route' => RouteSegment::class,
                'options' => [
                    'criteria' => '/auth',
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        Actions\AuthorizePage::class,
                    ],
                ],
            ],
            'token' => [
                'route' => RouteSegment::class,
                'options' => [
                    'criteria' => '/auth/token',
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        Actions\RespondToTokenRequest::class,
                    ],
                ],
            ],

            ## Register User / Login ---------------------------------------------------------------------\
            'register' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/register',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        '/module/oauth2/actions/RegisterPage',
                    ],
                ],
            ],
            'login' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/login',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        '/module/oauth2/actions/LoginPage',
                    ],
                ],
            ],
            'logout' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/logout',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        '/module/oauth2/actions/LogoutPage',
                    ],
                ],
            ],

            ## User Area ---------------------------------------------------------------------------------\
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
                            ListenerDispatch::ACTIONS => [ function() { return []; }, ]
                        ],
                    ],

                ],
            ],


            ## Members Validation / Login Challenge ------------------------------------------------------\
            'recover' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/recover',
                    'match_whole' => false,
                ],
                'routes' => [
                    'validate' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/validate/:validation_code~\w+~',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/oauth2/actions/ValidatePage',
                            ],
                        ],
                    ],
                    'validate_resend' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/validate/resend/:validation_code~\w+~/:identifier_type~\w+~',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/oauth2/actions/ResendAuthCodeRequest',
                            ],
                        ],
                    ],

                    'signin_recognize' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/recognize',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/oauth2/actions/SigninRecognizePage',
                            ],
                        ],
                    ],

                    'signin_challenge' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/challenge/:uid~\w+~</:identifier~\w+~>',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/oauth2/actions/SigninChallengePage',
                            ],
                        ],
                    ],

                    'pick_new_password' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/newpass/:validation_code~\w+~/:token~\w+~',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                '/module/oauth2/actions/SigninNewPassPage',
                            ],
                        ],
                    ],
                ],
            ],


            ## API ---------------------------------------------------------------------------------------\
            // TODO default renderer strategy for this routes
            'api' => include __DIR__.'/routes/api-routes.php',


        ], // end oauth routes
    ],
];
