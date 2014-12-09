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

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Client\CurlEngineException;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Response;
use TYPO3\Flow\Http\Client\CurlEngine;

/**
 * A Request Engine which uses cURL in order to send requests to external
 * HTTP servers.
 */
class CurlEngineProxy extends CurlEngine {

	/**
	 * @Flow\Inject(setting="proxy")
	 * @var array
	 */
	protected $settings;

	/**
	 * Sends the given HTTP request
	 *
	 * @param \TYPO3\Flow\Http\Request $request
	 * @return \TYPO3\Flow\Http\Response The response or FALSE
	 * @api
	 * @throws \TYPO3\Flow\Http\Exception
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
		}
	}

}
