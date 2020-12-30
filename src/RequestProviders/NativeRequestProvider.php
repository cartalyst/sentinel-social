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

namespace Cartalyst\Sentinel\Addons\Social\RequestProviders;

class NativeRequestProvider implements RequestProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOAuth1TemporaryCredentialsIdentifier()
    {
        return $_GET['oauth_token'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOAuth1Verifier()
    {
        return $_GET['oauth_verifier'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getOAuth2Code()
    {
        return $_GET['code'] ?? null;
    }
}
