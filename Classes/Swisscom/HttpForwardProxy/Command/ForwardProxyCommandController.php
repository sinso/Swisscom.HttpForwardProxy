<?php
namespace Swisscom\HttpForwardProxy\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package                          *
 * "Swisscom.HttpForwardProxy".                                           *
 *                                                                        *
 *                                                                        */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Request;
use Neos\Flow\Http\Uri;
use Neos\Flow\Cli\CommandController;

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
	public function getUrlCommand($url = 'https://flow.neos.io/') {
		$uri = new Uri($url);
		$request = Request::create($uri);
		$response = $this->curlEngine->sendRequest($request);

		var_dump($response);
	}

}