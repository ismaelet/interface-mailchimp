<?php

namespace Ismaelet\Interface\Mailchimp;

class ApiClient {
	private const DEBUG = false;

	private static $apiKey = null;
	private static $serverPrefix = null;
	private static $audienceId = null;

	private static function initialize() {
		if (!self::$apiKey) self::$apiKey = vendorConfig('mailchimp-api-key');
		if (!self::$serverPrefix) self::$serverPrefix = vendorConfig('mailchimp-server-prefix');
		if (!self::$audienceId) self::$audienceId = vendorConfig('mailchimp-audience-id');
	}

	// subscribe an user
	public static function subscribe($email, $userData = [], $groupTag = null) {
		self::initialize();

		$client = new \MailchimpMarketing\ApiClient();
		$client->setConfig([
			'apiKey' => self::$apiKey,
			'server' => self::$serverPrefix,
		]);

		$hashedEmail = md5(strtolower(trim($email)));

		$parameters = [
			'email_address' => $email,
			'status_if_new' => 'subscribed',
			'merge_fields' => [
				'FNAME' => $userData['firstname'] ?? '',
				'LNAME' => $userData['lastname'] ?? '',
			]
		];

		if ($groupTag) $parameters['tags'] = [$groupTag];

		$response = $client->lists->setListMember(self::$audienceId, $hashedEmail, $parameters);

		if (self::DEBUG) debug($response);
	}

	// get a user data
	public static function get($email) {
		if (!self::$apiKey || !self::$serverPrefix) {
			self::initialize();
		}

		$client = new \MailchimpMarketing\ApiClient();
		$client->setConfig([
			'apiKey' => self::$apiKey,
			'server' => self::$serverPrefix,
		]);

		$hashedEmail = md5(strtolower(trim($email)));

		try {
			$response = $client->lists->getListMember(self::$audienceId, $hashedEmail);
		} catch (\GuzzleHttp\Exception\ClientException $e) {
			$response = null;
		}

		if (self::DEBUG) debug($response);

		return $response;
	}
}
