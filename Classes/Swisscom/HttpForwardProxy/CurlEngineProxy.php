<?php
namespace Swisscom\HttpForwardProxy;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\CurlEngineException;
use Neos\Flow\Http\Request;
use Neos\Flow\Http\Response;
use Neos\Flow\Http\Client\CurlEngine;

/**
 * A Request Engine which uses cURL in order to send requests to external
 * HTTP servers.
 */
class CurlEngineProxy extends CurlEngine {

	/**
	 * @Flow\InjectConfiguration("proxy")
	 * @var array
	 */
	protected $settings;

	/**
	 * Sends the given HTTP request
	 *
	 * @param \Neos\Flow\Http\Request $request
	 * @return \Neos\Flow\Http\Response The response or FALSE
	 * @api
	 * @throws \Neos\Flow\Http\Exception
	 * @throws CurlEngineException
	 */
	public function sendRequest(Request $request) {
		$backupOptions = $this->options;

		if ($this->settings['enable']) {
			$host = $request->getUri()->getHost();
			$this->setProxy();
		}

		$proxyResponse = parent::sendRequest($request);

		// unwrap proxy response if applicable
		$lines = explode(chr(10), $proxyResponse->getContent());
		$firstLine = array_shift($lines);
		if (substr($firstLine, 0, 5) === 'HTTP/') {
			$response = Response::createFromRaw($proxyResponse->getContent());
		} else {
			$response = $proxyResponse;
		}

		$this->options = $backupOptions;

		return $response;
	}

	/**
	 * Set proxy settings
	 */
	protected function setProxy() {
		if ($this->settings['host']) {
			$this->setOption(CURLOPT_PROXY, $this->settings['host']);
			if ($this->settings['port']) {
				$this->setOption(CURLOPT_PROXYPORT, $this->settings['port']);
			}

			if ($this->settings['username'] and $this->settings['password']) {
				$userLogin = $this->settings['username'] . ':' . $this->settings['password'];
				$this->setOption(CURLOPT_PROXYUSERPWD, $userLogin);
			}
			if ($this->settings['proxyTunnel']) {
				$this->setOption(CURLOPT_HTTPPROXYTUNNEL, TRUE);
			}

			if ($this->settings['curlOptions']) {
				$parsedCurlOptions = self::parseCurlConfig($this->settings['curlOptions']);

				foreach ($parsedCurlOptions as $key => $value) {
					$this->setOption($key, $value);
				}
			}

		}
	}

	/**
	 * Parse the config and replace curl.* configurators into the constant based values so it can be used elsewhere
	 * Credits: Guzzle https://github.com/guzzle/guzzle
	 *
	 * @param array|Collection $config The configuration we want to parse
	 *
	 * @return array
	 */
	public static function parseCurlConfig($config) {
		$curlOptions = array();
		foreach ($config as $key => $value) {
			if (is_string($key) && defined($key)) {
				// Convert constants represented as string to constant int values
				$key = constant($key);
			}
			if (is_string($value) && defined($value)) {
				$value = constant($value);
			}
			$curlOptions[$key] = $value;
		}

		return $curlOptions;
	}

}
