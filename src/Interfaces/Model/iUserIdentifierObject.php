<?php
namespace Module\OAuth2\Interfaces\Model;

use Poirot\Std\Interfaces\Struct\iDataOptions;

/*
{
  "type": "mobile",
  "value": [
    "+98",
    "9355497674"
  ],
  "validated": true
}
*/

interface iUserIdentifierObject
    extends iDataOptions // let it to hydrate to array
{
    /**
     * Set Medium Type
     * @param string $type
     * @return $this
     */
    function setType($type);

    /**
     * Get Auth Code Medium Type
     * exp. mobile | email | ...
     *
     * @return string
     */
    function getType();

    /**
     * Set Value
     * @param mixed $value
     * @return $this
     */
    function setValue($value);

    /**
     * Get Value
     * @return mixed|null
     */
    function getValue();

    /**
     * Set Validated
     * @param bool $validated
     * @return $this
     */
    function setValidated($validated = true);

    /**
     * Is Validated?
     * !! default false
     * @return boolean|null
     */
    function isValidated();
}
