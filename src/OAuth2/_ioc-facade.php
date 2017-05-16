<?php
namespace Module\OAuth2\Actions
{

    use Module\OAuth2\Actions\Recover\SigninChallengePage;
    use Module\OAuth2\Actions\Recover\SigninNewPassPage;
    use Module\OAuth2\Actions\Recover\SigninRecognizePage;
    use Module\OAuth2\Actions\User\LoginPage;
    use Module\OAuth2\Actions\User\LogoutPage;
    use Module\OAuth2\Actions\User\RegisterPage;
    use Module\OAuth2\Actions\User\RetrieveAuthenticatedUser;
    use Module\OAuth2\Actions\Validation\ValidatePage;
    use Module\OAuth2\Actions\Validation\ResendAuthCodeRequest;
    use Module\OAuth2\Actions\Validation\Validation;
    use Module\OAuth2\Model\Entity\UserEntity;


    /**
     * @property RegisterPage                   $RegisterPage
     * @property LoginPage                      $LoginPage
     * @property LogoutPage                     $LogoutPage
     * @property ValidatePage                   $ValidatePage
     * @property SigninRecognizePage            $SigninRecognizePage
     * @property SigninChallengePage            $SigninChallengePage
     * @property SigninNewPassPage              $SigninNewPassPage
     * @property ResendAuthCodeRequest          $ResendAuthCodeRequest
     * @property RetrieveAuthenticatedUser      $RetrieveAuthenticatedUser
     *
     * @method static UserEntity  RetrieveAuthenticatedUser()
     * @method static Validation  Validation()
     */
    class IOC extends \IOC
    { }
}


namespace Module\OAuth2\Services
{
    /**
     */
    class IOC extends \IOC
    { }
}

namespace Module\OAuth2\Services\Repository
{
    use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAuthCodes;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoClients;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshTokens;


    /**
     * @method static iRepoClients       Clients(array $options=null)
     * @method static iRepoUsers         Users(array $options=null)
     * @method static iRepoAccessTokens  AccessTokens(array $options=null)
     * @method static iRepoRefreshTokens RefreshTokens(array $options=null)
     * @method static iRepoAuthCodes     AuthCodes(array $options=null)
     */
    class IOC extends \IOC
    { }
}
