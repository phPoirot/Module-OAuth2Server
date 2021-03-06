<?php
namespace Module\OAuth2\Actions\User;


use Module\Authorization\Actions\AuthenticatorAction;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Model\Entity\UserEntity;
use Module\OAuth2\Module;


class RetrieveAuthenticatedUser
    extends aAction
{
    /**
     * Retrieve Authenticated User
     *
     */
    function __invoke()
    {
        /** @var AuthenticatorAction $authenticator */
        $authenticator = \Module\Authorization\Actions::Authenticator();
        if (!$identifier = $authenticator->authenticator(Module::REALM)->hasAuthenticated())
            return false;


        $identity = $identifier->withIdentity();

        $user = new UserEntity($identity);
        if ($user->getUid() === null)
            throw new \Exception(sprintf(
                'Identifier (%s) With Identity (%s) not fulfilled OAuth Entity User on "identifier" property.'
                , get_class($identifier), get_class($identity)
            ));

        return $user;
    }
}
