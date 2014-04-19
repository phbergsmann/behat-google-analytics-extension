<?php
namespace PhBergsmann\BehatGoogleAnalyticsExtension\Behat\Context;

use Behat\MinkExtension\Context\RawMinkContext;

class GoogleAnalyticsContext extends RawMinkContext implements GoogleAnalyticsAwareInterface {
	/**
	 * @var \Google_Service_Analytics
	 */
	protected $analyticsApiService;

	/**
	 * @var array
	 */
	protected $googleAnalyticsParameters = array();

	/**
	* Opens specified page and adds the campaing tracking code.
	*
	* @Override Given /^(?:|I )am on "(?P<page>[^"]+)"$/
	* @Override When /^(?:|I )go to "(?P<page>[^"]+)"$/
	*/
	public function visit($page)
	{
		if (($appendToUrl = $this->getGoogleAnalyticsParameter('appendToUrl')) !== false) {
			$separator = (parse_url($page, PHP_URL_QUERY) == NULL) ? '?' : '&';
			$page .= $separator . $appendToUrl;
		}
		$this->getSession()->visit($this->locatePath($page));
	}

	/**
	* @Then /^Google Analytics tracks a pageview on "([^"]*)"$/
	*/
	public function googleAnalyticsTracksAPageviewOn($arg1)
	{
		$try = 1;

		while ($try < 15) {
			$query = $this->queryGoogleAnalytics('rt:pagePath');

			if(is_array($query->getRows()) === TRUE) {
				foreach ($query->getRows() as $row) {
					if ($row[0] == $arg1) {
						return true;
					}
				}
			}

			$try++;
			sleep(5);
		}

		throw new \Exception('Pageview not tracked');
	}


	/**
	 * @Then /^Google Analytics tracks an event with category "([^"]*)", action "([^"]*)" and label "([^"]*)"$/
	 */
	public function googleAnalyticsTracksAnEventWithCategoryDownloadActionAndLabel($arg1, $arg2, $arg3)
	{
		$try = 1;

		while ($try < 15) {
			$query = $this->queryGoogleAnalytics('rt:eventAction,rt:eventCategory,rt:eventLabel');

			if(is_array($query->getRows()) === TRUE) {
				foreach ($query->getRows() as $row) {
					if ($row[0] == $arg2 && $row[1] == $arg1 && $row[2] == $arg3) {
						return true;
					}
				}
			}

			$try++;
			sleep(5);
		}

		throw new \Exception('Event not tracked');
	}

	/**
	 * @param array $params
	 */
	public function setGoogleAnalyticsParameters(array $params) {
		$this->googleAnalyticsParameters = $params;
	}

	/**
	 * @return array
	 */
	public function getGoogleAnalyticsParameters() {
		return $this->googleAnalyticsParameters;
	}

	/**
	 * @param \Google_Service_Analytics $analyticsApiService
	 */
	public function setAnalyticsApiService(\Google_Service_Analytics $analyticsApiService) {
		$this->analyticsApiService = $analyticsApiService;
	}

	/**
	 * string $parameter
	 * mixed $value
	 */
	public function setGoogleAnalyticsParameter($parameter, $value) {
		$this->googleAnalyticsParameters[$parameter] = $value;
	}

	/**
	 * @param string $parameter
	 * @return mixed
	 */
	public function getGoogleAnalyticsParameter($parameter) {
		if (array_key_exists($parameter, $this->googleAnalyticsParameters) === false) {
			return false;
		}
		return $this->googleAnalyticsParameters[$parameter];
	}

	/**
	 * @return \Google_Service_Analytics
	 */
	public function getAnalyticsApiService() {
		return $this->analyticsApiService;
	}


	/**
	 * @BeforeScenario @GoogleAnalyticsIdentifyByCampaign
	 */
	public function generateIdentificationUriParam()
	{
		$this->generateIdentificationHash();
	    $this->setGoogleAnalyticsParameter('appendToUrl', 'utm_source=behat&utm_medium=test&utm_campaign=' . $this->getGoogleAnalyticsParameter('identificationHash'));
		$this->setGoogleAnalyticsParameter('identification', 'campaign');
	}

	protected function generateIdentificationHash() {
		if (($hash = $this->getGoogleAnalyticsParameter('identificationHash')) === false) {
			$hash = sha1('behat' . time() . rand(0,999));
			$this->setGoogleAnalyticsParameter('identificationHash', $hash);
		}
	}

	/**
	 * @param string $dimensions
	 * @param string $filters
	 * @return \Google_Service_Analytics_RealtimeData
	 */
	protected function queryGoogleAnalytics($dimensions = '', $filters = '') {
		$queryOptions = array(
			'dimensions' => $dimensions
		);

		if ($this->getGoogleAnalyticsParameter('identification') === 'campaign') {
			$queryOptions['filters'] = 'rt:campaign==' . $this->getGoogleAnalyticsParameter('identificationHash');
		}

		return $this->analyticsApiService->data_realtime->get('ga:' . $this->getGoogleAnalyticsParameter('view'), 'rt:activeUsers', $queryOptions);
	}
}
?>