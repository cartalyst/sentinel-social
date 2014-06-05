<?php namespace Cartalyst\SentinelSocial\Tests;
/**
 * Part of the Sentinel Social package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Sentinel Social
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Mockery as m;
use Cartalyst\SentinelSocial\RequestProviders\IlluminateProvider as Provider;
use PHPUnit_Framework_TestCase;

class IlluminateRequestProviderTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testOAuth1TemporaryCredentialsIdentifier()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('oauth_token')->once()->andReturn('oauth_token_value');
		$this->assertEquals('oauth_token_value', $provider->getOAuth1TemporaryCredentialsIdentifier());
	}

	public function testOAuth1Verifier()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('oauth_verifier')->once()->andReturn('verifier_value');
		$this->assertEquals('verifier_value', $provider->getOAuth1Verifier());
	}

	public function testOAuth2Code()
	{
		$provider = new Provider($request = m::mock('Illuminate\Http\Request'));
		$request->shouldReceive('input')->with('code')->once()->andReturn('code_value');
		$this->assertEquals('code_value', $provider->getOAuth2Code());
	}

}
