<?php namespace Cartalyst\SentinelSocial\Controllers;
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

use App;
use Config;
use Exception;
use Illuminate\Routing\Controller;
use Input;
use Redirect;
use Sentinel;
use SentinelSocial;
use URL;
use View;

class OAuthController extends Controller {

	/**
	 * Lists all available services to authenticate with.
	 *
	 * @return Illuminate\View\View
	 */
	public function getIndex()
	{
		$connections = array_filter(SentinelSocial::getConnections(), function($connection)
		{
			return ($connection['identifier'] and $connection['secret']);
		});

		return View::make('cartalyst/sentinel-social::oauth.index', compact('connections'));
	}

	/**
	 * Shows a link to authenticate a service.
	 *
	 * @param  string  $slug
	 * @return string
	 */
	public function getAuthorize($slug)
	{
		$url = SentinelSocial::getAuthorizationUrl($slug, URL::to("oauth/callback/{$slug}"));

		return Redirect::to($url);
	}

	/**
	 * Handles authentication
	 *
	 * @param  string  $slug
	 * @return mixed
	 */
	public function getCallback($slug)
	{
		try
		{
			$user = SentinelSocial::authenticate($slug, URL::current(), function($link, $provider, $token, $slug)
			{
				// Callback after user is linked
			});

			return Redirect::to('oauth/authenticated');
		}
		catch (Exception $e)
		{
			return Redirect::to('oauth')->withErrors($e->getMessage());
		}
	}

	/**
	 * Returns the "authenticated" view which simply shows the
	 * authenticated user.
	 *
	 * @return mixed
	 */
	public function getAuthenticated()
	{
		if ( ! Sentinel::check())
		{
			return Redirect::to('oauth')->withErrors('Not authenticated yet.');
		}

		$user = Sentinel::getUser();

		return View::make('cartalyst/sentinel-social::oauth.authenticated', compact('user'));
	}

}
