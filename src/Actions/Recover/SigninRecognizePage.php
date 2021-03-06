<?php
namespace Module\OAuth2\Actions\Recover;

use Module\HttpFoundation\Actions\Url;
use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\HttpFoundation\Response\ResponseRedirect;
use Module\OAuth2\Model\Entity\User\MobileObject;
use Poirot\Http\HttpMessage\Request\Plugin;
use Module\Authorization\Actions\AuthenticatorAction;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;


class SigninRecognizePage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'SigninRecognizePage';

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Constructor.
     *
     * @param iRepoUsers   $users       @IoC /module/oauth2/services/repository/
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iHttpRequest $httpRequest)
    {
        parent::__construct($httpRequest);

        $this->repoUsers = $users;
    }


    function __invoke()
    {
        $request = $this->request;

        if (Plugin\MethodType::_($request)->isPost())
            return $this->_handleRecognizeIdentifier($request);


        $_query = Plugin\ParseRequestData::_($request)->parseQueryParams();
        if (! isset($_query['u']) )
            // User not recognized, Just Render Input Page ...
            return [
                ListenerDispatch::RESULT_DISPATCH => []
            ];


        # Retrieve Recognized User:

        $u = $_query['u'];
        /** @var iOAuthUser $user */
        $user = $this->repoUsers->findOneByUID($u);
        if (false == $user)
            throw new exRouteNotMatch;


        # Check Whether Attained User Is Same As Current Logged in User?!

        /** @var AuthenticatorAction $authenticator */
        $authenticator = \Module\Authorization\Actions::Authenticator();
        $identifier    = $authenticator->authenticator(\Module\OAuth2\Module::REALM)
            ->hasAuthenticated();

        if (false !== $identifier) {
            // Some user is logged in
            if ( $identifier->withIdentity()->getUid() == $user->getUid() ) {
                // The Same User is found
                $continue = (string) \Module\HttpFoundation\Actions::url('main/oauth/login');
                return [
                    ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect($continue)
                ];
            }
        }


        # Build View:

        return [
            // Tell Template View To Display Recognition.
            // TODO faction build user output
            'user' => [
                'uid'      => $user->getUid(),
                'fullname' => $user->getFullName(),
                #'avatar' => $userAvatarUrl
            ]
        ];
    }

    /**
     * Handle Recognize User From Identifier That Given
     *
     * @param iHttpRequest $request
     *
     * @return array|ResponseRedirect
     */
    protected function _handleRecognizeIdentifier(iHttpRequest $request)
    {
        /** @var Url $url */
        $url = \Module\HttpFoundation\Actions::url(null, [], Url::DEFAULT_INSTRUCT|Url::APPEND_CURRENT_REQUEST_QUERY);

        $_post = Plugin\ParseRequestData::_($request)->parseBody();
        if (! isset($_post['identifier']) )
        {
            \Module\HttpFoundation\Actions::flashMessage(self::FLASH_MESSAGE_ID)
                ->error('پارامتر های مورد نیاز ارسال نشده است.');
            ;

            return [
                ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect((string) $url)
            ];
        }


        $identifier = trim($_post['identifier']);
        // Check whether the given input is mobile number?
        $matches = [];
        if (\Module\OAuth2\isValidMobileNum($identifier, $matches))
        {
            if (isset($matches['country_code']))
                if ($matches['country_code'] == "0")
                    $matches['country_code'] = '+98';

            $identifier = new MobileObject([
                'country_code' => $matches['country_code'],
                'number'       => $matches['number'],
            ]);
        }

        if (false === $u = $this->repoUsers->findOneHasIdentifierWithValue($identifier))
        {
            \Module\HttpFoundation\Actions::flashMessage(self::FLASH_MESSAGE_ID)
                ->error('کاربر با این مشخصه پیدا نشد.');
            ;

            return [
                ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect((string) $url)
            ];
        }


        $url = $url->uri();
        $url = \Poirot\Psr7\modifyUri(
            $url
            , ['query' => \Poirot\Psr7\buildQuery(['u' => $u->getUid()]) ]
        );

        return [
            ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect((string) $url)
        ];
    }
}
