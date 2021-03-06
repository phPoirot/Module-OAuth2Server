<?php
namespace Module\OAuth2\Actions\Recover\SigninChallenge;

use Module\HttpFoundation\Response\ResponseRedirect;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Http\Interfaces\iHttpResponse;
use Poirot\View\Interfaces\iViewModel;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;
use Psr\Http\Message\UriInterface;


abstract class aChallenge
{
    const CHALLENGE_TYPE = VOID;


    /** @var ViewModelTemplate|iViewModelPermutation */
    protected $viewModel;
    /** @var UriInterface */
    protected $nextChallengeUrl;
    /** @var iOAuthUser */
    protected $user;


    /**
     * Constructor.
     * @param iViewModel $viewModel @IoC /ViewModel
     */
    function __construct(iViewModel $viewModel)
    {
        $this->viewModel = $viewModel;
    }


    /**
     * @param iOAuthUser  $user
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate|iHttpResponse
     */
    function __invoke(iOAuthUser $user = null, iHttpRequest $request = null)
    {
        $this->user = $user;

        if ($redirectResponse = $this->_assertUser($user))
            // Check Whether Attained User Is Same As Current Logged in User?!
            // if so redirect to login page
            return $redirectResponse;


        return $this->doInvoke($request);
    }

    /**
     * @param iHttpRequest $request
     *
     * @return iViewModelPermutation|ViewModelTemplate
     */
    abstract function doInvoke(iHttpRequest $request);


    /**
     * Check Whether User That want to recover Account is not currently Logged in,
     * if so redirect it to dashboard
     *
     * @param iOAuthUser $user
     *
     * @return ResponseRedirect
     */
    protected function _assertUser($user)
    {
        $authorization = \Module\Authorization\Actions::Authenticator();
        $identifier    = $authorization->authenticator(\Module\OAuth2\Module::REALM)
            ->hasAuthenticated();

        if (false !== $identifier) {
            // Some user is logged in
            if ( $identifier->withIdentity()->getUid() == $user->getUid() ) {
                // The Same User is found
                $continue = (string) \Module\HttpFoundation\Actions::url('main/oauth/login');
                return new ResponseRedirect($continue);
            }
        }

        return null;
    }

    /**
     * Get Current Challenge Identifier Object
     *
     * @return IdentifierObject
     * @throws \Exception
     */
    protected function _getChallengeIdentifierObject()
    {
        $user = $this->user;

        /** @var IdentifierObject $idnt */
        foreach ($user->getIdentifiers() as $idnt) {
            if ($idnt->getType() === static::CHALLENGE_TYPE) {
                $find = $idnt;
                break;
            }
        }

        if (!isset($find))
            throw new \Exception(sprintf(
                'Identifier Object For Challenge (%s) not found.'
                , static::CHALLENGE_TYPE
            ));

        return $find;
    }


    // Options

    /**
     * Set Next User Challenge Url
     *
     * @param UriInterface $url
     *
     * @return $this
     */
    function setNextUserChallengeUrl(UriInterface $url)
    {
        $this->nextChallengeUrl = $url;
        return $this;
    }

    /**
     * Get Next User Challenge Url
     *
     * @return UriInterface|null
     */
    function getNextUserChallengeUrl()
    {
        return $this->nextChallengeUrl;
    }
}
