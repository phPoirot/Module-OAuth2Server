<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\Actions\Helper\UrlAction;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Actions\Users\SigninChallenge\aChallenge;
use Module\OAuth2\Actions\Users\SigninChallenge\ChallengeEmail;
use Module\OAuth2\Actions\Users\SigninChallenge\ChallengeFine;
use Module\OAuth2\Actions\Users\SigninChallenge\ChallengeMobile;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\UserIdentifierObject;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Ioc\instance;
use Poirot\View\Interfaces\iViewModelPermutation;
use Poirot\View\ViewModelTemplate;


class SigninChallengePage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'SigninChallengePage';

    /** @var iRepoUsers */
    protected $repoUsers;
    /** @var ViewModelTemplate|iViewModelPermutation */
    protected $viewModel;


    /**
     * Constructor.
     * @param iRepoUsers            $users     @IoC /module/oauth2/services/repository/
     * @param iViewModelPermutation $viewModel @IoC /
     */
    function __construct(iRepoUsers $users, iViewModelPermutation $viewModel)
    {
        $this->repoUsers = $users;
        $this->viewModel = $viewModel;
    }

    /**
     * @param string       $uid        User UID
     * @param string       $identifier Identifier type; exp. "email"
     * @param iHttpRequest $request
     *
     * @return ResponseRedirect|iViewModelPermutation
     */
    function __invoke($uid = null, $identifier = null, iHttpRequest $request = null)
    {
        /** @var iEntityUser $user */
        $user = $this->repoUsers->findOneByUID($uid);
        if (!$user)
            throw new exRouteNotMatch;


        if ($identifier === null)
            // Identifier not given try to pick one !!
            return $this->_pickAChallengeForUser($user);


        # Issue Challenge
        $challenge = $this->_newChallenge($identifier, $user);
        if (!$challenge)
            // Given Challenge is not valid pick another!
            return $this->_pickAChallengeForUser($user);


        return call_user_func($challenge, $user, $request);
    }


    // ..

    /**
     * Pick a Challenge for user from user`s identifiers
     *
     * @param iEntityUser $user
     *
     * @return ResponseRedirect
     */
    protected function _pickAChallengeForUser($user)
    {
        $userIdentifiers = $user->getIdentifiers();

        $challengeType = 'fine';

        /** @var UserIdentifierObject $idnt */
        foreach ($userIdentifiers as $idnt) {
            if ($this->_canHandleChallengeForIdentifier($idnt->getType())) {
                $challengeType = $idnt->getType();
                break;
            }
        }


        # build redirect uri point to challenge
        $redirect = $this->withModule('foundation')->url(
            'main/oauth/members/signin_challenge'
            , ['uid' => $user->getUID(), 'identifier' => $challengeType]
            , true
        );

        return new ResponseRedirect($redirect);
    }

    /**
     * @param string      $identifier_type
     * @param iEntityUser $user
     *
     * @return callable
     * @throws \Exception
     */
    protected function _newChallenge($identifier_type, $user)
    {
        switch ($identifier_type) {
            case 'fine':
                $challenge = \Poirot\Ioc\newInitIns(new instance(ChallengeFine::class));
                break;
            case 'email':
                $challenge = \Poirot\Ioc\newInitIns(new instance(ChallengeEmail::class));
                break;
            case 'mobile':
                $challenge = \Poirot\Ioc\newInitIns(new instance(ChallengeMobile::class));
                break;

            default: throw new \Exception(sprintf(
                'Challenge (%s) is not specified.'
                , $identifier_type
            ));
        }

        if (!$challenge instanceof aChallenge)
            throw new \Exception(sprintf(
                'Challenge (%s) is requested but (%s) is instanced.'
                , $identifier_type, \Poirot\Std\flatten($challenge)
            ));

        // Generate next challenge link and inject to challenge abstract

        // attain next identifier and create link to challenge it!
        /** @var UserIdentifierObject $idnt */
        $nextChallengeType = 'fine';
        $userIdentifiers = $user->getIdentifiers();
        do {
            /** @var UserIdentifierObject $currIdentifier */
            $currIdentifier = current($userIdentifiers);
            if ($currIdentifier->getType() === $identifier_type) {
                // achieve self challenge try next
                $tryNext = true;
                continue;
            }

            if ( isset($tryNext) && $this->_canHandleChallengeForIdentifier($currIdentifier->getType()) ) {
                $nextChallengeType = $currIdentifier->getType();
                break;
            }

        } while( next($userIdentifiers) );


        /** @var UrlAction $nextUrl */
        $foundation = $this->withModule('foundation');
        $uid = $user->getUID();
        $nextUrl = $foundation->url(
            'main/oauth/members/signin_challenge'
            , ['uid' => $uid, 'identifier' => $nextChallengeType]
            , true
        );

        /** @var aChallenge $challenge */
        $challenge->setNextUserChallengeUrl( $nextUrl->uri() );
        return $challenge;
    }

    protected function _canHandleChallengeForIdentifier($type)
    {
        return in_array($type, ['email', 'mobile']);
    }
}