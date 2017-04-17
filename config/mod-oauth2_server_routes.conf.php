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
            ListenerDispatch::CONF_KEY => function() {
                return new \Module\Foundation\HttpSapi\Response\ResponseRedirect(
                    \Module\Foundation\Actions\IOC::url('main/oauth/login')
                );
            },
        ),
    ),

    'oauth'  => [
        'routes' => [

            ## OAuth2 Specific EndPoints -----------------------------------------------------------------\
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

            ## Register User / Login ---------------------------------------------------------------------\
            'register' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/register',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => [
                        \Module\OAuth2\Actions\IOC::bareService()->RegisterPage,
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
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/LoginPage',
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
                            ListenerDispatch::CONF_KEY => function() { return []; },
                        ],
                    ],

                ],
            ],


            ## Members Validation / Login Challenge ------------------------------------------------------\
            'members' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/members',
                    'match_whole' => false,
                ],
                'routes' => [
                    'validate' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/validate/:validation_code{{\w+}}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/ValidatePage',
                        ],
                    ],
                    'validate_resend' => [
                        // TODO force render strategy by router; when error happen the ajax requests also must response in ?json
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/validate/resend/:validation_code{{\w+}}/:identifier_type{{\w+}}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/ValidationResendAuthCode',
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
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/SigninRecognizePage',
                        ],
                    ],

                    'signin_challenge' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/challenge/:uid{{\w+}}[/:identifier{{\w+}}]',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/SigninChallengePage',
                        ],
                    ],

                    'pick_new_password' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/newpass/:validation_code{{\w+}}/:token{{\w+}}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/SigninNewPassPage',
                        ],
                    ],
                ],
            ],


            ## API ---------------------------------------------------------------------------------------\
            'api' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/api/v1',
                    'match_whole' => false,
                ],
                'params'  => [
                    // This Action Run First In Chains and Assert Validate Token
                    //! define array allow actions on matched routes chained after this action
                    /*
                     * [
                     *    [0] => Callable Defined HERE
                     *    [1] => routes defined callable
                     *     ...
                     */
                    ListenerDispatch::CONF_KEY => [
                        function ($request = null) {
                            $token = \Module\OAuth2\assertAuthToken($request);
                            return ['token' => $token];
                        }
                    ],
                ],
                'routes' => [
                    ## me
                    'me' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/me',
                            'match_whole' => false,
                        ],
                        // TODO only tokens that has owner identifier
                        'routes' => [
                            ##Profile:
                            'profile' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/profile',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        \Module\OAuth2\Actions\Users\GetUserInfo::functorParseUidFromToken(),
                                        function() { return ['checkIsValidID' => true];}, //
                                        '/module/oauth2/actions/users/GetUserInfo'
                                    ],
                                ],
                            ],
                            ## Identifiers:
                            'grants' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/grants',
                                    'match_whole' => false,
                                ],
                                'routes' => [
                                    // Change Password:
                                    'password' => [
                                        'route' => 'RouteSegment',
                                        'options' => [
                                            'criteria'    => '/password',
                                            'match_whole' => true,
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                \Module\OAuth2\Actions\Users\ChangePassword::functorGetParsedUIDFromToken(),
                                                \Module\OAuth2\Actions\Users\ChangePassword::functorGetParsedRequestData(),
                                                '/module/oauth2/actions/users/ChangePassword',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            ## Identifiers:
                            'identifiers' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/identifiers',
                                    'match_whole' => false,
                                ],
                                'routes' => [
                                    // Identifiers:
                                    // Change Identity (email, mobile, ..):
                                    'change' => [
                                        'route' => 'RouteSegment',
                                        'options' => [
                                            'criteria'    => '/change',
                                            'match_whole' => true,
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                \Module\OAuth2\Actions\Users\ChangeIdentity::functorGetParsedRequestData(),
                                                \Module\OAuth2\Actions\Users\ChangeIdentity::functorGetParsedUIDFromToken(),
                                                '/module/oauth2/actions/users/ChangeIdentity',
                                            ]
                                        ],
                                    ],
                                    // Confirm Identity Validation:
                                    'confirm' => [
                                        'route' => 'RouteSegment',
                                        'options' => [
                                            'criteria'    => '/change/confirm/:validation_code{\w+}',
                                            'match_whole' => true,
                                        ],
                                        'params'  => [
                                            // TODO separate Page with API func.
                                            ListenerDispatch::CONF_KEY => [
                                                '/module/oauth2/actions/Users/ValidatePage',
                                                \Module\OAuth2\Actions\Users\ValidatePage::prepareApiResultClosure(),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],

                    ## members
                    'members' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/members',
                            'match_whole' => false,
                        ],
                        'routes' => [
                            'exists' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/exists',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        \Module\OAuth2\Actions\Users\isExistsUserWithIdentifier::functorGetParsedRequestData(),
                                        '/module/oauth2/actions/users/isExistsUserWithIdentifier',
                                    ],
                                ],
                            ],
                            'whois' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/whois',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/users/WhoisRequest',
                                ],
                            ],
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
                            // Register New User Request By POST
                            'post' => [
                                'route'   => 'RouteMethod',
                                'options' => [
                                    'method' => 'POST',
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        '/module/oauth2/actions/users/RegisterRequest',
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
            ],


        ], // end oauth routes
    ],
];
