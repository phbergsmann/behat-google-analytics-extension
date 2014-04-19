<?php
namespace PhBergsmann\BehatGoogleAnalyticsExtension;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
	Behat\Behat\Extension\Extension as BaseExtension;

use Behat\Behat\Extension\ExtensionInterface;

class Extension extends BaseExtension {
	/**
	 * Loads a specific configuration.
	 *
	 * @param array            $config    Extension configuration hash (from behat.yml)
	 * @param ContainerBuilder $container ContainerBuilder instance
	 */
	public function load(array $config, ContainerBuilder $container) {
		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/services'));
		$loader->load('core.xml');

		$container->setParameter('behat.googleanalytics.parameters', $config);
	}
}
?>