# Introduction

Sentinel Social makes authenticating your users through social networks & third-party OAuth providers an absolute breeze.

The package follows the FIG standard PSR-4 to ensure a high level of interoperability between shared PHP code and is fully unit-tested.

The package requires PHP 5.4+.

Have a [read through the Installation Guide](#installation).

### Quick Example

#### Add Connections

```php
SentinelSocial::addConnection('facebook' => [
		'driver'     => 'Facebook',
		'identifier' => '',
		'secret'     => '',
		'scopes'     => ['email'],
	],
);
```

#### Authorize

```php
$callback = 'http://app.dev/callback.php';
$url      = SentinelSocial::getAuthorizationUrl('facebook', $callback);

header('Location: ' . $url);
exit;
```

#### Authenticate

```php
$callback = 'http://app.dev/callback.php';

try
{
	$user = SentinelSocial::authenticate('facebook', $callback, function(Cartalyst\SentinelSocial\Links\LinkInterface $link, $provider, $token, $slug)
	{
		// Retrieve the user in question for modificiation
		$user = $link->getUser();

		// You could add your custom data
		$data = $provider->getUserDetails($token);

		$user->foo = $data->foo;
		$user->save();
	});
}
catch (Cartalyst\SentinelSocial\AccessMissingException $e)
{
	var_dump($e); // You may save this to the session, redirect somewhere
	die();

	header('HTTP/1.0 404 Not Found');
}
```
