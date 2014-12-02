<?php
namespace Swisscom\HttpForwardProxy\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package                          *
 * "Swisscom.HttpForwardProxy".                                           *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Http\Client\CurlEngine;
use TYPO3\Flow\Http\Request;
use TYPO3\Flow\Http\Uri;
use TYPO3\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class ForwardProxyCommandController extends CommandController {

	/**
	 * @Flow\Inject
	 * @var CurlEngine
	 */
	protected $curlEngine;

	/**
	 * Send a request to a URL and output the response.
	 * @param string $url URL
	 * @return void
	 */
	public function getUrlCommand($url = 'http://flow.typo3.org/home') {
		$uri = new Uri($url);
		$request = Request::create($uri);
		$response = $this->curlEngine->sendRequest($request);

		var_dump($response);
	}

}