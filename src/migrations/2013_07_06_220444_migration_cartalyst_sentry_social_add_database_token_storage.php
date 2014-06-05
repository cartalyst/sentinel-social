<?php
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

use Illuminate\Database\Migrations\Migration;

class MigrationCartalystSentinelSocialAddDatabaseTokenStorage extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('social', function($table)
		{
			// Common
			$table->string('access_token')->nullable();
			$table->integer('end_of_life')->nullable();

			// OAuth2
			$table->string('refresh_token')->nullable();

			// OAuth1
			$table->string('request_token')->nullable();
			$table->string('request_token_secret')->nullable();

			// Misc
			$table->text('extra_params')->nullable();

			$table->unique(array('service', 'access_token'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('social', function($table)
		{
			$table->dropUnique('social_service_access_token_unique');
		});

		Schema::table('social', function($table)
		{
			$table->dropColumn(array(
				'access_token',
				'refresh_token',
				'request_token',
				'request_token_secret',
				'extra_params',
				'end_of_life',
			));
		});
	}

}
