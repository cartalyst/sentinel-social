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
use Cartalyst\SentinelSocial\Repositories\LinkRepository;
use PHPUnit_Framework_TestCase;

class EloquentLinkProviderTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	public function testFindingExistingLink()
	{
		$linkRepository = m::mock('Cartalyst\SentinelSocial\Repositories\LinkRepository[createModel]');
		$linkRepository->shouldReceive('createModel')->once()->andReturn($query = m::mock('StdClass'));

		$query->shouldReceive('newQuery')->once()->andReturn($query);
		$query->shouldReceive('with')->with('user')->once()->andReturn($query);
		$query->shouldReceive('where')->with('provider', '=', 'slug')->once()->andReturn($query);
		$query->shouldReceive('where')->with('uid', '=', 789)->once()->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn('success');

		$this->assertEquals('success', $linkRepository->findLink('slug', 789));
	}

	public function testFindingNonExistentLink()
	{
		$linkRepository = m::mock('Cartalyst\SentinelSocial\Repositories\LinkRepository[createModel]');

		$linkRepository->shouldReceive('createModel')->ordered()->once()->andReturn($query = m::mock('StdClass'));
		$query->shouldReceive('newQuery')->once()->andReturn($query);
		$query->shouldReceive('with')->with('user')->once()->andReturn($query);
		$query->shouldReceive('where')->with('provider', '=', 'slug')->once()->andReturn($query);
		$query->shouldReceive('where')->with('uid', '=', 789)->once()->andReturn($query);
		$query->shouldReceive('first')->once()->andReturn(null);

		$linkRepository->shouldReceive('createModel')->ordered()->once()->andReturn($model = m::mock('StdClass')); // Can't mock model, get "BadMethodCallException: Method Cartalyst\SentinelSocial\Links\EloquentLink::hasGetMutator() does not exist on this mock object"
		$model->shouldReceive('fill')->with(array(
			'provider' => 'slug',
			'uid'      => 789,
		))->once();
		$model->shouldReceive('save')->once();

		$this->assertEquals($model, $linkRepository->findLink('slug', 789));
	}

	public function testCreateModel()
	{
		$provider = new LinkRepository;
		$model = $provider->createModel();

		$this->assertInstanceOf('Cartalyst\SentinelSocial\Models\Link', $model);
	}

}
