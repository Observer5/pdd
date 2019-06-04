<?php
namespace EasyPdd\Tests\OAuth;

use EasyPdd\OAuth\OAuth;
use EasyPdd\Tests\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuthTest extends TestCase
{
    public function getOAuth()
    {
        $oauth = new OAuth('client_id', 'client_secret');
        return $oauth;
    }
    
    public function testRedirect()
    {
        $oauth = $this->getOAuth();

        $this->assertInstanceOf(RedirectResponse::class, $oauth->redirect());
    }
}