<?php namespace Cartalyst\SentinelSocial\Repositories;
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

use Cartalyst\Support\Traits\RepositoryTrait;
use Cartalyst\SentinelSocial\Services\ServiceInterface;
use League\OAuth1\Client\Server\Server as OAuth1Server;
use League\OAuth2\Client\Provider\AbstractProvider as OAuth2Provider;

class LinkRepository implements LinkRepositoryInterface {

	use RepositoryTrait;

	/**
	 * The eloquent link model.
	 *
	 * @var string
	 */
	protected $model = 'Cartalyst\SentinelSocial\Models\Link';

	/**
	 * Finds a link (or creates one) for the given provider slug and uid.
	 *
	 * @param  string  $slug
	 * @param  mixed   $uid
	 * @return \Cartalyst\SentinelSocial\Socials\SocialInterface
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
			$link->fill([
				'provider' => $slug,
				'uid'      => $uid,
			]);
			$link->save();
		}

		return $link;
	}

}
