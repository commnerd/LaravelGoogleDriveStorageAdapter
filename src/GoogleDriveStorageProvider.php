<?php

namespace GoogleDriveStorage;

use Illuminate\Support\ServiceProvider;

class GoogleDriveStorageProvider extends ServiceProvider
{
	public function boot()
	{
		new GoogleClient([
          'application_name' => '',

          // https://developers.google.com/console
          'client_id' => '',
          'client_secret' => '',
          'redirect_uri' => null,
          'state' => null,

          // Simple API access key, also from the API console. Ensure you get
          // a Server key, and not a Browser key.
          'developer_key' => '',

          // For use with Google Cloud Platform
          // fetch the ApplicationDefaultCredentials, if applicable
          // @see https://developers.google.com/identity/protocols/application-default-credentials
          'use_application_default_credentials' => false,
          'signing_key' => null,
          'signing_algorithm' => null,
          'subject' => null,

          // Other OAuth2 parameters.
          'hd' => '',
          'prompt' => '',
          'openid.realm' => '',
          'include_granted_scopes' => null,
          'login_hint' => '',
          'request_visible_actions' => '',
          'access_type' => 'online',
          'approval_prompt' => 'auto',

          // Task Runner retry configuration
          // @see Google_Task_Runner
          'retry' => array(),
          'retry_map' => null,

          // cache config for downstream auth caching
          'cache_config' => [],

          // function to be called when an access token is fetched
          // follows the signature function ($cacheKey, $accessToken)
          'token_callback' => null,

          // Service class used in Google_Client::verifyIdToken.
          // Explicitly pass this in to avoid setting JWT::$leeway
          'jwt' => null,

          // Setting api_format_v2 will return more detailed error messages
          // from certain APIs.
          'api_format_v2' => false
        ]);
	}
}
