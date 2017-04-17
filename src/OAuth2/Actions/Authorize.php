<?php
namespace Module\OAuth2\Actions;

use Module\OAuth2\Interfaces\Server\Repository\iRepoUsersApprovedClients;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\PhpServer;
use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;
use Poirot\Http\Psr\ResponseBridgeInPsr;
use Poirot\Http\Psr\ServerRequestBridgeInPsr;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthClient;
use Poirot\OAuth2\Server\Exception\exOAuthServer;
use Poirot\OAuth2\Server\Grant\aGrant;
use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;


class Authorize extends aAction
{
    /** @var GrantAggregateGrants */
    protected $grantResponder;
    /** @var iRepoUsersApprovedClients */
    protected $repoApprovedClients;


    /**
     * Authorize constructor.
     *
     * @param GrantAggregateGrants      $grantResponder      @IoC /module/oauth2/services/
     * @param iRepoUsersApprovedClients $repoApprovedClients @IoC /module/oauth2/services/repository/Users.ApprovedClients
     */
    function __construct(GrantAggregateGrants $grantResponder, iRepoUsersApprovedClients $repoApprovedClients)
    {
        $this->grantResponder = $grantResponder;
        $this->repoApprovedClients = $repoApprovedClients;
    }

    function __invoke(HttpRequest $request = null, HttpResponse $response = null)
    {
        $aggregateGrant = $this->grantResponder;

        $requestPsr = new ServerRequestBridgeInPsr($request);

        /** @var aGrant $grant */
        if (!$grant = $aggregateGrant->canRespondToRequest($requestPsr))
            throw exOAuthServer::unsupportedGrantType();


        $_post = PhpServer::_($request)->getPost();

        /** @var iOAuthClient $client */
        $client = $grant->assertClient(false);
        list($scopeRequested, $scopes) = $grant->assertScopes($client->getScope());


        ##

        // check whether to display approve page or not?
        if (!$approveNotRequire = $client->isResidentClient()) {
            $RepoApprovedClients = $this->repoApprovedClients;
            $User = $this->RetrieveAuthenticatedUser();

            //// also maybe client approve the client in the past
            $approveNotRequire = $RepoApprovedClients->isUserApprovedClient($User, $client);
        }

        if (false == $approveNotRequire)
        {
            if (MethodType::_($request)->isPost() && $_post->get('deny_access', null) !== null) {
                // Get Deny Result Back To The Client
                $responsePsr = new ResponseBridgeInPsr($response);
                $exception   = exOAuthServer::accessDenied($grant->newGrantResponse());
                $responsePsr = $exception->buildResponse($responsePsr);
                $responsePsr = \Poirot\Http\parseResponseFromPsr($responsePsr);
                $response    = new HttpResponse($responsePsr);
                return $response;

            } elseif (MethodType::_($request)->isPost() && $_post->get('allow_access', null) !== null) {
                // Allow Access The Client
                $RepoApprovedClients = $this->repoApprovedClients;
                $User = $this->RetrieveAuthenticatedUser();
                $RepoApprovedClients->approveClient($User, $client);
            } else {
                ## display approve page
                return array(
                    'client' => array(
                        'name'        => $client->getName(),
                        'description' => $client->getDescription(),
                        'image_url'   => $client->getImage(),
                    ),
                    'scopes' => $scopes,
                );
            }
        }

        // Client is resident or approved by user
        // !! Call Neighbor Namespace Action
        return $this->RespondToRequest($request, $response);
    }
}
