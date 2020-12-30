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
 * @version    6.1.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2020, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Sentinel\Addons\Social\Repositories;

use Cartalyst\Support\Traits\RepositoryTrait;

class LinkRepository implements LinkRepositoryInterface
{
    use RepositoryTrait;

    /**
     * The eloquent link model.
     *
     * @var string
     */
    protected $model = 'Cartalyst\Sentinel\Addons\Social\Models\Link';

    /**
     * Constructor.
     *
     * @param string|null $model
     *
     * @return void
     */
    public function __construct($model = null)
    {
        if ($model !== null) {
            $this->model = $model;
        }
    }

    /**
     * Finds a link (or creates one) for the given provider slug and uid.
     *
     * @param string $slug
     * @param mixed  $uid
     *
     * @return \Cartalyst\Sentinel\Addons\Social\Socials\SocialInterface
     */
    public function findLink($slug, $uid)
    {
        $link = $this
            ->createModel()
            ->newQuery()
            ->with('user')
            ->where('provider', '=', $slug)
            ->where('uid', '=', $uid)
            ->first()
        ;

        if ($link === null) {
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
