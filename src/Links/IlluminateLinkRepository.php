<?php namespace Cartalyst\SentinelSocial\Links;
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

use Cartalyst\SentinelSocial\Services\ServiceInterface;
use League\OAuth1\Client\Server\Server as OAuth1Server;
use League\OAuth2\Client\Provider\AbstractProvider as OAuth2Provider;

class IlluminateLinkRepository implements LinkRepositoryInterface {

	/**
	 * The Eloquent social model.
	 *
	 * @var string
	 */
	protected $model = 'Cartalyst\SentinelSocial\Links\EloquentLink';

	/**
	 * Create a new Eloquent Social Link provider.
	 *
	 * @param  string  $model
	 * @return void
	 */
	public function __construct($model = null)
	{
		if (isset($model))
		{
			$this->model = $model;
		}
	}

	/**
	 * Finds a link (or creates one) for the given provider slug and uid.
	 *
	 * @param  string  $slug
	 * @param  mixed   $uid
	 * @return \Cartalyst\SentinelSocial\Links\LinkInterface
	 */
	public function findLink($slug, $uid)
	{
		$link = $this
			->createModel()
			->newQuery()
			->with('user')
			->where('provider', '=', $slug)
			->where('uid', '=', $uid)
			->first();

		if ($link === null)
		{
			$link = $this->createModel();
			$link->fill(array(
				'provider' => $slug,
				'uid'      => $uid,
			));
			$link->save();
		}

		return $link;
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function createModel()
	{
		$class = '\\'.ltrim($this->model, '\\');

		return new $class;
	}

}
