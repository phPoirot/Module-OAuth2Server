<?php
namespace Module\OAuth2\Interfaces\Model;

/*
{
  "type": "password",
  "value": "e10adc3949ba59abbe56e057f20f883e"
}
*/

interface iUserGrantObject
{
    /**
     * Set Type
     * @param string $type
     * @return $this
     */
    function setType($type);

    /**
     * Get Type
     * @return string
     */
    function getType();

    /**
     * Set Value
     * @param string $value
     * @return $this
     */
    function setValue($value);

    /**
     * Get Value
     * @return string
     */
    function getValue();

    /**
     * Set Options
     * @param array $options
     * @return $this
     */
    function setOptions($options);

    /**
     * get Options
     * @return array
     */
    function getOptions();

    /**
     * Insert an Option into Options array
     * @param string $option
     * @param string $value
     * @return $this
     */
    function addOption($option, $value);

}
