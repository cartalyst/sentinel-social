# Installation

The best and easiest way to install the Sentinel Social package is with [Composer](http://getcomposer.org).

## Preparation

Open your `composer.json` and add the following to the `require` array:

	"cartalyst/sentinel-social": "1.0.*"

Add the following lines after the `require` array on your `composer.json` file:

	"repositories": [
		{
			"type": "composer",
			"url": "https://packages.cartalyst.com"
		}
	],

> **Note:** This version is still in development, make sure that you set `min-stability` to `dev` on your `composer.json` file.

> **Note:** Make sure that after the required changes your `composer.json` file is valid by running `composer validate`.

## Install the dependencies

Run Composer to install or update the new requirement.

	php composer install

or

	php composer update

Now you are able to require the `vendor/autoload.php` file to autoload the package.
