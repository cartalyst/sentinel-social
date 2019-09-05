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
 * @version    4.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       https://cartalyst.com
 */

use Mockery as m;
use Cartalyst\Sentinel\Addons\Social\RequestProviders\IlluminateRequestProvider;

class IlluminateRequestProviderTest extends PHPUnit\Framework\TestCase
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

    public function testOAuth1TemporaryCredentialsIdentifier()
    {
        $provider = new IlluminateRequestProvider($request = m::mock('Illuminate\Http\Request'));
        $request->shouldReceive('input')->with('oauth_token')->once()->andReturn('oauth_token_value');
        $this->assertSame('oauth_token_value', $provider->getOAuth1TemporaryCredentialsIdentifier());
    }

    public function testOAuth1Verifier()
    {
        $provider = new IlluminateRequestProvider($request = m::mock('Illuminate\Http\Request'));
        $request->shouldReceive('input')->with('oauth_verifier')->once()->andReturn('verifier_value');
        $this->assertSame('verifier_value', $provider->getOAuth1Verifier());
    }

    public function testOAuth2Code()
    {
        $provider = new IlluminateRequestProvider($request = m::mock('Illuminate\Http\Request'));
        $request->shouldReceive('input')->with('code')->once()->andReturn('code_value');
        $this->assertSame('code_value', $provider->getOAuth2Code());
    }
}
