## Extending Sentinel Social

Sentinel Social was designed from the ground up with extendability in mind.

Extending is as simple as 2 steps

1. Creating your implementation class.
2. Adding the connection to Sentinel Social.

### Creating An Implementation Class

To create an implementation class, you firstly need to determine if you're dealing with OAuth 1 or OAuth 2.

#### OAuth 1

To create an OAuth 1 implementation class for Sentinel Social, simply create a class which extends `League\OAuth1\Client\Server\Server`.

Example:

```php
	use League\OAuth1\Client\Server\User;

	class MyOAuth1Provider extends \League\OAuth1\Client\Server\Server {

		/**
		 * The response type for data returned from API calls.
		 *
		 * @var string
		 */
		protected $responseType = 'json';

		/**
		 * Get the URL for retrieving temporary credentials.
		 *
		 * @return string
		 */
		public function urlTemporaryCredentials()
		{
			return 'https://api.myprovider.com/oauth/temporary_credentials';
		}

		/**
		 * Get the URL for redirecting the resource owner to authorize the client.
		 *
		 * @return string
		 */
		public function urlAuthorization()
		{
			return 'https://api.myprovider.com/oauth/authorize';
		}

		/**
		 * Get the URL retrieving token credentials.
		 *
		 * @return string
		 */
		public function urlTokenCredentials()
		{
			return 'https://api.myprovider.com/oauth/token_credentials';
		}

		/**
		 * Get the URL for retrieving user details.
		 *
		 * @return string
		 */
		public function urlUserDetails()
		{
			return 'https://api.myprovider/1.0/user.json';
		}

		/**
		 * Take the decoded data from the user details URL and convert
		 * it to a User object.
		 *
		 * @param  mixed  $data
		 * @param  TokenCredentials  $tokenCredentials
		 * @return User
		 */
		public function userDetails($data, TokenCredentials $tokenCredentials)
		{
			$user = new User;

			// Take the decoded data (determined by $this->responseType)
			// and fill out the user object by abstracting out the API
			// properties (this keeps our user object simple and adds
			// a layer of protection in-case the API response changes)

			$user->first_name = $data['user']['firstname'];
			$user->last_name  = $data['user']['lastname'];
			$user->email      = $data['emails']['primary'];
			// Etc..

			return $user;
		}

		/**
		 * Take the decoded data from the user details URL and extract
		 * the user's UID.
		 *
		 * @param  mixed  $data
		 * @param  TokenCredentials  $tokenCredentials
		 * @return string|int
		 */
		public function userUid($data, TokenCredentials $tokenCredentials)
		{
			return $data['unique_id'];
		}

		/**
		 * Take the decoded data from the user details URL and extract
		 * the user's email.
		 *
		 * @param  mixed  $data
		 * @param  TokenCredentials  $tokenCredentials
		 * @return string
		 */
		public function userEmail($data, TokenCredentials $tokenCredentials)
		{
			// Optional
			if (isset($data['email']))
			{
				return $data['email'];
			}
		}

		/**
		 * Take the decoded data from the user details URL and extract
		 * the user's screen name.
		 *
		 * @param  mixed  $data
		 * @param  TokenCredentials  $tokenCredentials
		 * @return User
		 */
		public function userScreenName($data, TokenCredentials $tokenCredentials)
		{
			// Optional
			if (isset($data['screen_name']))
			{
				return $data['screen_name'];
			}
		}
	}
```

#### OAuth 2

The underlying OAuth 2 package provides a generic provider which can be used for various services. An example connection looks as follows.

```
		'my_service' => [
			'name'       => 'My Service',
			'driver'     => 'GenericProvider',
			'identifier' => '',
			'secret'     => '',
			'scopes'     => [
				'user',
			],
			'additional_options' => [
				'urlAuthorize'            => 'https://api.my_service.com/authorize',
				'urlAccessToken'          => 'https://api.my_service.com/access_token',
				'urlResourceOwnerDetails' => 'https://api.my_service.com/user',
			],
		],

 ```
