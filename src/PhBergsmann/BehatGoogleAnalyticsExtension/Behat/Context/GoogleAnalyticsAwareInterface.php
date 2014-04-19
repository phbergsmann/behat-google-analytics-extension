<?php
namespace PhBergsmann\BehatGoogleAnalyticsExtension\Behat\Context;

interface GoogleAnalyticsAwareInterface {
	public function setGoogleAnalyticsParameters(array $params);
	public function getGoogleAnalyticsParameters();
	public function setAnalyticsApiService(\Google_Service_Analytics $analyticsApiService);
	public function getAnalyticsApiService();
}
?>