<?php
namespace Module\OAuth2\Actions\User;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\HttpFoundation\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoClients;


class LogoutPage
    extends aAction
{
    protected $repoClients;


    /**
     * ValidatePage constructor.
     *
     * @param iRepoClients $clientRepo  @IoC /module/oauth2/services/repository/Clients
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoClients $clientRepo, iHttpRequest $httpRequest)
    {
        parent::__construct($httpRequest);

        $this->repoClients = $clientRepo;
    }


    function __invoke()
    {
        if ($this->_authenticator()->hasAuthenticated())
            $this->_authenticator()->identifier()->signOut();


        $redirectUri = \Module\HttpFoundation\Actions::url('main/home');


        // Allow 3rd Party OAuth To Logout and Redirect User Directly

        $request = $this->request;

        $query = ParseRequestData::_($request)->parseQueryParams();
        if (isset($query['client_id']) && isset($query['redirect_uri']))
        {
            $c      = $query['client_id'];
            $client = $this->repoClients->findByIdentifier($c);

            $r      = rtrim($query['redirect_uri'], '/');

            $match = false;
            foreach ($client->getRedirectUri() as $registeredRedirect) {
                $registeredRedirect = rtrim($registeredRedirect, '/');
                if ($r == $registeredRedirect) {
                    $match = true;
                    break;
                }
            }

            if ( $match )
                $redirectUri = $r;

        }


        return [
            ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect( $redirectUri )
        ];
    }
}
