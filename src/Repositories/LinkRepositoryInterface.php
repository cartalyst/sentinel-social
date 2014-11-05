<?php namespace Cartalyst\Sentinel\Addons\Social\Repositories;
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
 * @version    0.2.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

interface LinkRepositoryInterface {

	/**
	 * Finds a link (or creates one) for the given provider slug and uid.
	 *
	 * @param  string  $slug
	 * @param  mixed   $uid
	 * @return \Cartalyst\Sentinel\Addons\Social\Models\LinkInterface
	 */
	public function findLink($slug, $uid);

}
