<?php

/*
 * Part of the Sentinel Social package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Sentinel Social
 * @version    7.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2022, Cartalyst LLC
 * @link       https://cartalyst.com
 */

use Mockery as m;
use Cartalyst\Sentinel\Addons\Social\RequestProviders\NativeRequestProvider as Provider;

class NativeRequestProviderTest extends PHPUnit\Framework\TestCase
{
    /**
     * Close mockery.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        m::close();
    }

    /** @test */
    public function it_can_retrieve_oauth1_temporary_credentials_identifier()
    {
        $provider = new Provider();

        $_GET['oauth_token'] = 'oauth_token_value';

        $this->assertSame('oauth_token_value', $provider->getOAuth1TemporaryCredentialsIdentifier());
    }

    /** @test */
    public function it_can_retrieve_oauth1_verifier()
    {
        $provider = new Provider();

        $_GET['oauth_verifier'] = 'verifier_value';

        $this->assertSame('verifier_value', $provider->getOAuth1Verifier());
    }

    /** @test */
    public function it_can_retrieve_oauth2_code()
    {
        $provider = new Provider();

        $_GET['code'] = 'code_value';

        $this->assertSame('code_value', $provider->getOAuth2Code());
    }
}
