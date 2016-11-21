<?php
namespace Module\OAuth2\Interfaces\Model;

interface iEntityValidationCode
{
    /**
     * Set User Identifier That Validation Belong To
     *
     * @param string $identifier
     *
     * @return $this
     */
    function setUserIdentifier($identifier);

    /**
     * Get User Identifier That Validation Is Belong To
     *
     * @return string
     */
    function getUserIdentifier();

    /**
     * Set Validation Code
     *
     * @param string $code
     *
     * @return $this
     */
    function setValidationCode($code);

    /**
     * Validation Code
     *
     * @return string
     */
    function getValidationCode();

    /**
     * Set Authorization codes
     *
     * !! empty argument [] will clear all given
     *    authorization codes
     *
     * $authCodes: [
     *  iEntityValidationCodeAuthObject,
     *
     * @param array|\Traversable $authCodes
     *
     * @return $this
     */
    function setAuthCodes($authCodes);

    /**
     * Add Authorization Code
     *
     * @param iEntityValidationCodeAuthObject $authCode
     *
     * @return $this
     */
    function addAuthCode(iEntityValidationCodeAuthObject $authCode);

    /**
     * Get Authorization Codes
     *
     * @return []iEntityValidationCodeAuthObject
     */
    function getAuthCodes();

    /**
     * Set Expiration DateTime
     *
     * @param \DateTime $dateTime
     *
     * @return $this
     */
    function setDateTimeExpiration(\DateTime $dateTime);

    /**
     * Expiration DateTime
     *
     * @return \DateTime
     */
    function getDateTimeExpiration();
}
