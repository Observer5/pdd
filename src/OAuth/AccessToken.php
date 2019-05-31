<?php


namespace EasyPdd\OAuth;
use ArrayAccess;
use InvalidArgumentException;
use JsonSerializable;

class AccessToken implements AccessTokenInterface, ArrayAccess, JsonSerializable
{
    use HasAttributes;
    /**
     * AccessToken constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        if (empty($attributes['access_token'])) {
            throw new InvalidArgumentException('The key "access_token" could not be empty.');
        }
        $this->attributes = $attributes;
    }
    /**
     * Return the access token string.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->getAttribute('access_token');
    }
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return strval($this->getAttribute('access_token', ''));
    }
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getToken();
    }
}