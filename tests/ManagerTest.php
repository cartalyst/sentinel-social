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
use Cartalyst\SentinelSocial\Manager;
use InvalidProvider;
use Illuminate\Events\Dispatcher;
use PHPUnit_Framework_TestCase;

class ManagerTest extends PHPUnit_Framework_TestCase {

	protected $manager;

	protected $sentinel;

	protected $requestProvider;

	protected $session;

	protected $dispatcher;

	protected $linkProvider;

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass()
	{
		require_once __DIR__.'/stubs/InvalidProvider.php';
		require_once __DIR__.'/stubs/ValidOAuth1Provider.php';
		require_once __DIR__.'/stubs/ValidOAuth2Provider.php';
	}

	/**
	 * Setup resources and dependencies.
	 *
	 * @return void
	 */
	public function setUp()
	{
		$this->manager = new Manager(
			$this->sentinel        = m::mock('Cartalyst\Sentinel\Sentinel'),
			$this->linkProvider    = m::mock('Cartalyst\SentinelSocial\Links\LinkRepositoryInterface'),
			$this->requestProvider = m::mock('Cartalyst\SentinelSocial\RequestProviders\RequestProviderInterface'),
			$this->session         = m::mock('Cartalyst\Sentinel\Sessions\SessionInterface'),
			$this->dispatcher      = new Dispatcher
		);
	}

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testAddingConnection()
	{
		$this->manager->addConnection('foo', array('bar' => 'baz'));
		$this->assertCount(1, $this->manager->getConnections());
		$this->assertEquals(array('bar' => 'baz'), $this->manager->getConnection('foo'));
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testGettingNonExistentConnection()
	{
		$this->manager->getConnection('foo');
	}

	/**
	 * @expectedException RuntimeException
	 */
	public function testMakeNonExistentConnection()
	{
		$this->manager->make('foo', 'http://example.com/callback');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage Class matching driver is required
	 */
	public function testMakeConnectionWithMissingDriver()
	{
		$this->manager->addConnection('foo', array());
		$this->manager->make('foo', 'http://example.com/callback');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage App identifier and secret are required
	 */
	public function testMakeConnectionWithMissingIdentifier()
	{
		$this->manager->addConnection('foo', array(
			'driver' => 'Foo',
		));
		$this->manager->make('foo', 'http://example.com/callback');
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage App identifier and secret are required
	 */
	public function testMakeConnectionWithMissingSecret()
	{
		$this->manager->addConnection('foo', array(
			'driver' => 'Foo',
			'identifier' => 'bar',
		));
		$this->manager->make('foo', 'http://example.com/callback');
	}

	public function testMakeBuiltInOAuth1Connection()
	{
		$this->manager->addConnection('twitter', array(
			'driver'     => 'Twitter',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('twitter', 'http://example.com/callback');
		$this->assertInstanceOf('League\OAuth1\Client\Server\Twitter', $provider);
		$this->assertEquals('appid', $provider->getClientCredentials()->getIdentifier());
		$this->assertEquals('appsecret', $provider->getClientCredentials()->getSecret());
	}

	public function testMakeBuiltInOAuth2Connection()
	{
		$this->manager->addConnection('facebook', array(
			'driver'     => 'Facebook',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('facebook', 'http://example.com/callback');
		$this->assertInstanceOf('League\OAuth2\Client\Provider\Facebook', $provider);
		$this->assertEquals('appid', $provider->clientId);
		$this->assertEquals('appsecret', $provider->clientSecret);
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage does not inherit from a compatible OAuth provider class
	 */
	public function testMakingCustomInvalidConnection()
	{
		$this->manager->addConnection('foo', array(
			'driver'     => 'InvalidProvider',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('foo', 'http://example.com/callback');
	}

	public function testMakingValidOAuth1Provider()
	{
		$this->manager->addConnection('foo', array(
			'driver'     => 'ValidOAuth1Provider',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('foo', 'http://example.com/callback');
		$this->assertInstanceOf('ValidOAuth1Provider', $provider);
		$this->assertEquals('appid', $provider->getClientCredentials()->getIdentifier());
		$this->assertEquals('appsecret', $provider->getClientCredentials()->getSecret());
	}

	public function testMakingValidOAuth2Provider()
	{
		$this->manager->addConnection('foo', array(
			'driver'     => 'ValidOAuth2Provider',
			'identifier' => 'appid',
			'secret'     => 'appsecret',
		));

		$provider = $this->manager->make('foo', 'http://example.com/callback');
		$this->assertInstanceOf('ValidOAuth2Provider', $provider);
		$this->assertEquals('appid', $provider->clientId);
		$this->assertEquals('appsecret', $provider->clientSecret);
	}

	public function testGettingOAuth1AuthorizationUrl()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make,oauthVersion]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		$provider->shouldReceive('getTemporaryCredentials')->once()->andReturn('credentials');
		$this->session->shouldReceive('put')->with('credentials')->once();

		$provider->shouldReceive('getAuthorizationUrl')->once()->andReturn('uri');
		$this->assertEquals('uri', $manager->getAuthorizationUrl('foo', 'http://example.com/callback'));
	}

	public function testGettingOAuth2AuthorizationUrl()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make,oauthVersion]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\AbstractProvider'));

		$provider->shouldReceive('getAuthorizationUrl')->once()->andReturn('uri');
		$this->assertEquals('uri', $manager->getAuthorizationUrl('foo', 'http://example.com/callback'));
	}

	/**
	 * @expectedException Cartalyst\SentinelSocial\AccessMissingException
	 * @expectedExceptionMessage Missing [oauth_token] parameter
	 */
	public function testAuthenticatingOAuth1WithMissingTemporaryIdentifier()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn(null);

		$user = $manager->authenticate('foo', 'http://example.com/callback');
	}

	/**
	 * @expectedException Cartalyst\SentinelSocial\AccessMissingException
	 * @expectedExceptionMessage Missing [verifier] parameter
	 */
	public function testAuthenticatingOAuth1WithMissingVerifier()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('1az');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn(null);

		$user = $manager->authenticate('foo', 'http://example.com/callback');
	}

	public function testAuthenticatingOAuth1WithLinkedUser()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('identifier');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn('verifier');

		// Mock retrieving credentials from the underlying package
		$this->session->shouldReceive('get')->andReturn($temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials'));
		$provider->shouldReceive('getTokenCredentials')->with($temporaryCredentials, 'identifier', 'verifier')->once()->andReturn($tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentinelSocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($tokenCredentials)->once();

		// Logged in user
		$this->sentinel->shouldReceive('getUser')->once()->andReturn(null);

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->andReturn($user = m::mock('Cartalyst\Sentinel\Users\UserInterface'));

		// And finally, logging a user in
		$this->sentinel->shouldReceive('authenticate')->with($user, true)->once();

		$manager->existing(function($link, $provider, $token, $slug)
		{
			$_SERVER['__sentinel_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentinel_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentinel_social_existing']));
		unset($_SERVER['__sentinel_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentinel_social_linking']));
		$eventArgs = $_SERVER['__sentinel_social_linking'];
		unset($_SERVER['__sentinel_social_linking']);

		$this->assertCount(4, $eventArgs);
		list($_link, $_provider, $_tokenCredentials, $_slug) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($tokenCredentials, $_tokenCredentials);
		$this->assertEquals('foo', $_slug);
	}

	public function testAuthenticatingOAuth1WithUnlinkedExistingUser()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('identifier');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn('verifier');

		// Mock retrieving credentials from the underlying package
		$this->session->shouldReceive('get')->andReturn($temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials'));
		$provider->shouldReceive('getTokenCredentials')->with($temporaryCredentials, 'identifier', 'verifier')->once()->andReturn($tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentinelSocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($tokenCredentials)->once();

		// Logged in user
		$this->sentinel->shouldReceive('getUser')->once()->andReturn(null);

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->andReturn($user = m::mock('Cartalyst\Sentinel\Users\UserInterface'));

		// And finally, logging a user in
		$this->sentinel->shouldReceive('authenticate')->with($user, true)->once();

		$manager->existing(function($link, $provider, $token, $slug)
		{
			$_SERVER['__sentinel_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentinel_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentinel_social_existing']));
		unset($_SERVER['__sentinel_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentinel_social_linking']));
		$eventArgs = $_SERVER['__sentinel_social_linking'];
		unset($_SERVER['__sentinel_social_linking']);

		$this->assertCount(4, $eventArgs);
		list($_link, $_provider, $_tokenCredentials, $_slug) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($tokenCredentials, $_tokenCredentials);
		$this->assertEquals('foo', $_slug);
	}

	public function testAuthenticatingOAuth1WithUnlinkedNonExistentUser()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$user = m::mock('Cartalyst\Sentinel\Users\UserInterface');

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('identifier');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn('verifier');

		// Mock retrieving credentials from the underlying package
		$this->session->shouldReceive('get')->andReturn($temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials'));
		$provider->shouldReceive('getTokenCredentials')->with($temporaryCredentials, 'identifier', 'verifier')->once()->andReturn($tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentinelSocial\Links\LinkInterface'));

		$link->shouldReceive('storeToken')->with($tokenCredentials)->once();

		$this->sentinel->shouldReceive('getUser')->once();

		$link->shouldReceive('getUser')->once();

		$link->shouldReceive('getUser')->once()->andReturn($user);

		$link->shouldReceive('setUser')->with($user)->once();

		$provider->shouldReceive('getUserEmail')->once()->andReturn('foo@bar.com');

		$this->sentinel->shouldReceive('findByCredentials')->with(['login'=>'foo@bar.com'])->once();

		$this->sentinel->shouldReceive('getUserRepository')->once()->andReturn($users = m::mock('Cartalyst\Sentinel\Users\UserRepositoryInterface'));

		$users->shouldReceive('createModel')->once()->andReturn($user);

		$provider->shouldReceive('getUserScreenName')->once()->andReturn(['Ben', 'Corlett']);

		$this->sentinel->shouldReceive('registerAndActivate')->once()->andReturn($user);

		$this->sentinel->shouldReceive('authenticate')->with($user, true)->once()->andReturn($user);

		$manager->registering(function($link, $provider, $token, $slug)
		{
			$_SERVER['__sentinel_social_registering'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentinel_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentinel_social_registering']));
		unset($_SERVER['__sentinel_social_registering']);

		$this->assertTrue(isset($_SERVER['__sentinel_social_linking']));
		$eventArgs = $_SERVER['__sentinel_social_linking'];
		unset($_SERVER['__sentinel_social_linking']);

		$this->assertCount(4, $eventArgs);
		list($_link, $_provider, $_tokenCredentials, $_slug) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($tokenCredentials, $_tokenCredentials);
		$this->assertEquals('foo', $_slug);
	}

	public function testAuthenticatingOAuth1LoggedInUser()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth1\Client\Server\Server'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth1TemporaryCredentialsIdentifier')->once()->andReturn('identifier');
		$this->requestProvider->shouldReceive('getOAuth1Verifier')->once()->andReturn('verifier');

		// Mock retrieving credentials from the underlying package
		$this->session->shouldReceive('get')->andReturn($temporaryCredentials = m::mock('League\OAuth1\Client\Credentials\TemporaryCredentials'));
		$provider->shouldReceive('getTokenCredentials')->with($temporaryCredentials, 'identifier', 'verifier')->once()->andReturn($tokenCredentials = m::mock('League\OAuth1\Client\Credentials\TokenCredentials'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentinelSocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($tokenCredentials)->once();

		// Logged in user
		$this->sentinel->shouldReceive('getUser')->once()->andReturn($user = m::mock('Cartalyst\Sentinel\Users\UserInterface'));
		$link->shouldReceive('setUser')->with($user)->once();

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->andReturn($user);

		$manager->existing(function($link, $provider, $token, $slug)
		{
			$_SERVER['__sentinel_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentinel_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentinel_social_existing']));
		unset($_SERVER['__sentinel_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentinel_social_linking']));
		$eventArgs = $_SERVER['__sentinel_social_linking'];
		unset($_SERVER['__sentinel_social_linking']);

		$this->assertCount(4, $eventArgs);
		list($_link, $_provider, $_tokenCredentials, $_slug) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($tokenCredentials, $_tokenCredentials);
		$this->assertEquals('foo', $_slug);
	}

	/**
	 * @expectedException Cartalyst\SentinelSocial\AccessMissingException
	 * @expectedExceptionMessage Missing [code] parameter
	 */
	public function testAuthenticatingOAuth2WithMissingCode()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\AbstractProvider'));

		$this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn(null);

		$user = $manager->authenticate('foo', 'http://example.com/callback');
	}

	public function testAuthenticatingOAuth2WithLinkedUser()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\AbstractProvider'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn('code');

		// Mock retrieving credentials from the underlying package
		$provider->shouldReceive('getAccessToken')->with('authorization_code', array('code' => 'code'))->once()->andReturn($accessToken = m::mock('League\OAuth2\Client\Token\AccessToken'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentinelSocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($accessToken)->once();

		$user = m::mock('Cartalyst\Sentinel\Users\UserInterface');

		$this->sentinel->shouldReceive('getUser')
					->once();

		$link->shouldReceive('getUser')
			->once()
			->andReturn($user);

		$link->shouldReceive('getUser')->ordered()->once()->andReturn($user);

		// And finally, logging a user in
		$this->sentinel->shouldReceive('authenticate')->with($user, true)->once();

		$manager->existing(function($link, $provider, $token, $slug)
		{
			$_SERVER['__sentinel_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentinel_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentinel_social_existing']));
		unset($_SERVER['__sentinel_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentinel_social_linking']));
		$eventArgs = $_SERVER['__sentinel_social_linking'];
		unset($_SERVER['__sentinel_social_linking']);

		$this->assertCount(4, $eventArgs);
		list($_link, $_provider, $_accessToken, $_slug) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($accessToken, $_accessToken);
		$this->assertEquals('foo', $_slug);
	}

	public function testAuthenticatingOAuth2WithUnlinkedExistingUser()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\AbstractProvider'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn('code');

		// Mock retrieving credentials from the underlying package
		$provider->shouldReceive('getAccessToken')->with('authorization_code', array('code' => 'code'))->once()->andReturn($accessToken = m::mock('League\OAuth2\Client\Token\AccessToken'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentinelSocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($accessToken)->once();

		$user = m::mock('Cartalyst\Sentinel\Users\UserInterface');

		$this->sentinel->shouldReceive('getUser')
					->once();

		$link->shouldReceive('getUser')
			->once()
			->andReturn($user);

		$link->shouldReceive('getUser')->ordered()->once()->andReturn($user);

		// And finally, logging a user in
		$this->sentinel->shouldReceive('authenticate')->with($user, true)->once();

		$manager->existing(function($link, $provider, $token, $slug)
		{
			$_SERVER['__sentinel_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentinel_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentinel_social_existing']));
		unset($_SERVER['__sentinel_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentinel_social_linking']));
		$eventArgs = $_SERVER['__sentinel_social_linking'];
		unset($_SERVER['__sentinel_social_linking']);

		$this->assertCount(4, $eventArgs);
		list($_link, $_provider, $_accessToken, $_slug) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($accessToken, $_accessToken);
		$this->assertEquals('foo', $_slug);
	}

	public function testAuthenticatingOAuth2WithUnlinkedNonExistentUser()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
		$manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

		$user = m::mock('Cartalyst\Sentinel\Users\UserInterface');

		$manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\AbstractProvider'));

		// Request proxy
		$this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn('code');

		// Mock retrieving credentials from the underlying package
		$provider->shouldReceive('getAccessToken')->with('authorization_code', array('code' => 'code'))->once()->andReturn($accessToken = m::mock('League\OAuth2\Client\Token\AccessToken'));

		// Unique ID
		$provider->shouldReceive('getUserUid')->once()->andReturn(789);

		// Finding an appropriate link
		$this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentinelSocial\Links\LinkInterface'));
		$link->shouldReceive('storeToken')->with($accessToken)->once();

		$this->sentinel->shouldReceive('getUser')->once();

		$link->shouldReceive('getUser')->once();

		$link->shouldReceive('getUser')->once()->andReturn($user);

		$link->shouldReceive('setUser')->with($user)->once();

		$provider->shouldReceive('getUserEmail')->once()->andReturn('foo@bar.com');

		$this->sentinel->shouldReceive('findByCredentials')->with(['login'=>'foo@bar.com'])->once();

		$this->sentinel->shouldReceive('getUserRepository')->once()->andReturn($users = m::mock('Cartalyst\Sentinel\Users\UserRepositoryInterface'));

		$users->shouldReceive('createModel')->once()->andReturn($user);

		$provider->shouldReceive('getUserScreenName')->once()->andReturn(['Ben', 'Corlett']);

		$this->sentinel->shouldReceive('registerAndActivate')->once()->andReturn($user);

		$this->sentinel->shouldReceive('authenticate')->with($user, true)->once()->andReturn($user);

		$manager->registering(function($link, $provider, $token, $slug)
		{
			$_SERVER['__sentinel_social_registering'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentinel_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentinel_social_registering']));
		unset($_SERVER['__sentinel_social_registering']);

		$this->assertTrue(isset($_SERVER['__sentinel_social_linking']));
		$eventArgs = $_SERVER['__sentinel_social_linking'];
		unset($_SERVER['__sentinel_social_linking']);

		$this->assertCount(4, $eventArgs);
		list($_link, $_provider, $_accessToken, $_slug) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($accessToken, $_accessToken);
		$this->assertEquals('foo', $_slug);
	}

	public function testAuthenticatingOAuth2LoggedInUser()
	{
		$manager = m::mock('Cartalyst\SentinelSocial\Manager[make]');
        $manager->__construct($this->sentinel, $this->linkProvider, $this->requestProvider, $this->session, $this->dispatcher);

        $manager->shouldReceive('make')->with('foo', 'http://example.com/callback')->once()->andReturn($provider = m::mock('League\OAuth2\Client\Provider\AbstractProvider'));

        // Request proxy
        $this->requestProvider->shouldReceive('getOAuth2Code')->once()->andReturn('code');

        // Mock retrieving credentials from the underlying package
        $provider->shouldReceive('getAccessToken')->with('authorization_code', array('code' => 'code'))->once()->andReturn($accessToken = m::mock('League\OAuth2\Client\Token\AccessToken'));

        // Unique ID
        $provider->shouldReceive('getUserUid')->once()->andReturn(789);

        // Finding an appropriate link
        $this->linkProvider->shouldReceive('findLink')->with('foo', 789)->once()->andReturn($link = m::mock('Cartalyst\SentinelSocial\Links\LinkInterface'));
        $link->shouldReceive('storeToken')->with($accessToken)->once();

		// Logged in user
		$this->sentinel->shouldReceive('getUser')->once()->andReturn($user = m::mock('Cartalyst\Sentinel\Users\UserInterface'));
		$link->shouldReceive('setUser')->with($user)->once();

		// Retrieving a user from the link
		$link->shouldReceive('getUser')->andReturn($user);

		$manager->existing(function($link, $provider, $token, $slug)
		{
			$_SERVER['__sentinel_social_existing'] = true;
		});

		$user = $manager->authenticate('foo', 'http://example.com/callback', function()
		{
			$_SERVER['__sentinel_social_linking'] = func_get_args();
		}, true);

		$this->assertTrue(isset($_SERVER['__sentinel_social_existing']));
		unset($_SERVER['__sentinel_social_existing']);

		$this->assertTrue(isset($_SERVER['__sentinel_social_linking']));
		$eventArgs = $_SERVER['__sentinel_social_linking'];
		unset($_SERVER['__sentinel_social_linking']);

		$this->assertCount(4, $eventArgs);
		list($_link, $_provider, $_accessToken, $_slug) = $eventArgs;
		$this->assertEquals($link, $_link);
		$this->assertEquals($provider, $_provider);
		$this->assertEquals($accessToken, $_accessToken);
		$this->assertEquals('foo', $_slug);
	}

}
