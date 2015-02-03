# Usage

## OAuth Flow

While OAuth1 and OAuth2 are incompatible protocols, they (for the most part) follow the same process:

1. A secure connection is established between a your app and a provider.
2. A user is redirected to the provider where they may login and approve (or reject) your app to have access.
3. Your app receives a token from the service so your app may act on behalf of the person who authenticated. You never find out their password and they have the option to revoke your access at any point.

Sentinel Social abstracts all the differences between OAuth 1 and OAuth 2, so that you can focus on the more interesting parts of your app.

## Manager

	$manager = new Cartalyst\Sentinel\Addons\Social\Manager($instanceOfSentinel);

> `Social` is the Laravel alias for the manager and can be directly used without instantiation.

## Connections

Single connection

```php
Social::addConnection('facebook', [
		'driver'     => 'Facebook',
		'identifier' => '',
		'secret'     => '',
		'scopes'     => ['email'],
	],
);
```

Multiple connections

```php
$connections = [

	'facebook' => [
		'driver'     => 'Facebook',
		'identifier' => '',
		'secret'     => '',
		'scopes'     => ['email'],
	],

	'github' => [
		'driver'     => 'GitHub',
		'identifier' => '',
		'secret'     => '',
		'scopes'     => ['user'],
	],

);

Social::addConnections($connections);
```

> Connections on Laravel are stored in `app/config/packages/cartalyst/sentinel-social/config.php`

## Authorization

Authorizing a user (redirecting them to the provider's login/approval screen) is extremely easy.

Once you've configured a provider with Sentinel Social, you simply need to redirect the user to the authorization URL.

```php
Route::get('oauth/authorize', function()
{
	$callback = URL::to('oauth/callback');
	$url = Social::getAuthorizationUrl('facebook', $callback);
	return Redirect::to($url);
});
```

## Authentication

Once a user has finished authorizing (or rejecting) your application, they're redirected to the callback URL which you specified.

To handle the authentication process, you will need to respond to the response from the provider on that callback URL.

```php
Route::get('oauth/callback', function()
{
	// Callback is required for providers such as Facebook and a few others (it's required
	// by the spec, but some providers omit this).
	$callback = URL::current();

	try
	{
		$user = Social::authenticate('facebook', URL::current(), function(Cartalyst\Sentinel\Addons\Social\Models\LinkInterface $link, $provider, $token, $slug)
		{
			// Retrieve the user in question for modificiation
			$user = $link->getUser();

			// You could add your custom data
			$data = $provider->getUserDetails($token);

			$user->foo = $data->foo;
			$user->save();
		});
	}
	catch (Cartalyst\Sentinel\Addons\Social\AccessMissingException $e)
	{
		// Missing OAuth parameters were missing from the query string.
		// Either the person rejected the app, or the URL has been manually
		// accesed.
		if ($error = Input::get('error'))
		{
			return Redirect::to('oauth')->withErrors($error);
		}

		App::abort(404);
	}
});
```

> **Note:** If you attempt to authenticate a provider when a Sentinel user is already logged in, the authenticated provider account will be linked with that User. For you as a developer, this allows your users to link multiple social accounts easily. If you don't want to allow other accounts to be linked, either don't show the social login links and/or log the user out at the start of the authorization process (in your controller).

## Hooks

In addition to providing a hook (callback) for when a user is being linked (the second parameter passed to `authenticate()`), we also provide ways to hook into new user registrations as well as only existing user linking.

For example, this may be useful to send welcome emails when new users are being registered:

```php
Social::registering(function(Cartalyst\Sentinel\Addons\Social\Models\LinkInterface $link, $provider, $token, $slug)
{
	$user = $link->getUser();

	Mail::later($user->email, 'welcome', compact('user', 'slug'));
});

Social::existing(function(Cartalyst\Sentinel\Addons\Social\Models\LinkInterface $link, $provider, $token, $slug)
{
	// Callback for existing users
});

// Finally, after hooks are registered, you may authenticate away
$user = Social::authenticate($params);
```
