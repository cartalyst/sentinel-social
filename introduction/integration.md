# Integration

## Laravel 5

The Sentinel Social package has optional support for Laravel 5 and it comes bundled with a
Service Provider and a Facade for easier integration.

After you have installed the package, just follow the instructions.

Open your Laravel config file `config/app.php` and add the following lines.

In the `$providers` array add the following service provider for this package.

	'Cartalyst\Sentinel\Addons\Social\Laravel\SocialServiceProvider',

In the `$aliases` array add the following facade for this package.

	'Social' => 'Cartalyst\Sentinel\Addons\Social\Laravel\Facades\Social',

### Assets

Run the following commands to publish the migration and config files.

- Sentinel

`php artisan vendor:publish --provider="Cartalyst\Sentinel\Laravel\SentinelServiceProvider"`

- Sentinel Social

`php artisan vendor:publish --provider="Cartalyst\Sentinel\Addons\Social\Laravel\SocialServiceProvider"`

#### Migrations

Run the following command to migrate Sentinel after publishing the assets.

`php artisan migrate`

#### Configuration

After publishing, the sentinel social config file can be found under `config/cartalyst.sentinel-addons.social.php` where you can modify the package configuration.

## Native

After you have installed the package, just follow the instructions.

### Setup your database

#### Sentinel schema

	`vendor/cartalyst/sentinel/schema/mysql.sql`

#### Sentinel Social schema

	`vendor/cartalyst/sentinel-social/schema/mysql.sql`

### Configuration

#### Instantiate Sentinel Social

```php
// Include the composer autoload file
require_once 'vendor/autoload.php';

// Import the necessary classes
use Cartalyst\Sentinel\Addons\Social\Manager;

$manager = new Manager($instanceOfSentinel);

$manager->addConnection('facebook' => [
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
$url      = $manager->getAuthorizationUrl('facebook', $callback);

header('Location: ' . $url);
exit;
```

#### Authenticate

```php
$callback = 'http://app.dev/callback.php';

try
{
	$user = $manager->authenticate('facebook', $callback, function(Cartalyst\Sentinel\Addons\Social\Models\LinkInterface $link, $provider, $token, $slug)
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
	var_dump($e); // You may save this to the session, redirect somewhere
	die();

	header('HTTP/1.0 404 Not Found');
}
```
