<?php
namespace PhBergsmann\BehatGoogleAnalyticsExtension\Behat\Context\Initializer;

use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;
use PhBergsmann\BehatGoogleAnalyticsExtension\Behat\Context\GoogleAnalyticsAwareInterface;

class GoogleAnalyticsAwareInitializer implements InitializerInterface {
	/**
	 * @var array
	 */
	private $parameters;

	/**
	 * Initializes initializer.
	 *
	 * @param array $parameters
	 */
	public function __construct(array $parameters)
	{
		$this->parameters = $parameters;
	}

	/**
	 * Checks if initializer supports provided context.
	 *
	 * @param ContextInterface $context
	 *
	 * @return Boolean
	 */
	public function supports(ContextInterface $context) {
		return $context instanceof GoogleAnalyticsAwareInterface;
	}

	/**
	 * Initializes provided context.
	 *
	 * @param ContextInterface $context
	 */
	public function initialize(ContextInterface $context) {
		$client = new \Google_Client();
		$client->setApplicationName('BehatGoogleAnalyticsExtension');
		$client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
		$client->setClientId($this->parameters['client_id']);

		$key = file_get_contents($this->parameters['key_file_location']);
		$cred = new \Google_Auth_AssertionCredentials(
		    $this->parameters['service_account_name'],
		    array('https://www.googleapis.com/auth/analytics.readonly'),
		    $key
		);
		$client->setAssertionCredentials($cred);

		$service = new \Google_Service_Analytics($client);

		$context->setAnalyticsApiService($service);
		$context->setGoogleAnalyticsParameters($this->parameters);
	}
}
?>