# Behat Google Analytics Extension

## Prerequisites

##### Google Analytics Realtime API beta access

Just fill out this form and wait some time: https://docs.google.com/forms/d/1qfRFysCikpgCMGqgF3yXdUyQW4xAlLyjKuOoOEFN2Uw/viewform

##### A Google API Service Account

Google gives some help on that: https://developers.google.com/console/help/#service_accounts

##### A Google Analytics Account

Add a property for your test-domain, authorize your service Account with "Read & Analyze" permissions.

## Installation and configuration

### via composer add the extension

Add a dependency to
```bash
"phbergsmann/behat-google-analytics-extension": "*"
```

### extend your behat.yml

```yml
default:
    extension:
        PhBergsmann\BehatGoogleAnalyticsExtension\Extension:
            service_account_name: 1234567890@developer.gserviceaccount.com
            key_file_location: /PATH/TO/YOUR/PRIVATE/KEY
            client_id: 1234567890.apps.googleusercontent.com
            view: 123456789
```

### Include the context-class

In your custom feature-context add the new context in the constructor:

```php
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
		  $this->useContext('googleanalytics', new PhBergsmann\BehatGoogleAnalyticsExtension\Behat\Context\GoogleAnalyticsContext());
    }
```

If you want to use the campaign-identifier to allow concurrent testing (*recommended*) add these lines to your feature-context:

```php
	/**
	* Opens specified page.
	*
	* @Override Given /^(?:|I )am on "(?P<page>[^"]+)"$/
	* @Override When /^(?:|I )go to "(?P<page>[^"]+)"$/
	*/
	public function visit($page)
	{
		$this->getSubcontext('test')->visit($page);
	}
```

## Start testing

#### Identifying the test-run

Google Analytics doesn't have the possibility to identify users (by giving them some retrievable ID). To bypass this limitation the extension adds the possibility to "tag" a testrun by injecting campaing-tracking parameters. Tests run perfectly fine without user-tagging but when you run the same test twice within 5 minutes there is no guarantee, that the second run does not deliver a false positive because the test was working in the first run. To enable campaing tagging add the following tag to your scenario:

```gherkin
@GoogleAnalyticsIdentifyByCampaign
```

#### The extension adds the following step-definitions:

##### Check if the given URL has been tracked
```gherkin
Google Analytics tracks a pageview on "/the/url/i/want/to/be/tracked/"
```

##### Check if the given event was triggered
```gherkin
Google Analytics tracks an event with category "<<EVENT-CATEGORY>>", action "<<EVENT-ACTION>>" and label "<<EVENT-LABEL>>"
```