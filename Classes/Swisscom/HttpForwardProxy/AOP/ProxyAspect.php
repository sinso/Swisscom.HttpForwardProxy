<?php
namespace Swisscom\HttpForwardProxy\AOP;
/*                                                                        *
 * This script belongs to the TYPO3 Flow package                          *
 * "Swisscom.HttpForwardProxy".                                           *
 *                                                                        *
 *                                                                        */

use Swisscom\HttpForwardProxy\CurlEngineProxy;
use TYPO3\Flow\Annotations as Flow;

/**
 * Class LanguageDetectionAspect
 *
 * @Flow\Aspect
 * @Flow\Scope("singleton")
 */
class ProxyAspect {

	/**
	 * @Flow\Inject
	 * @var CurlEngineProxy
	 */
	protected $curlEngineProxy;

	/**
	 * Initializes the object after all dependencies have been injected
	 */
	public function initializeObject() {
	}

	/**
	 * @Flow\Around("method(TYPO3\Flow\Http\Client|CurlEngine->sendRequest())")
	 * @return void
	 */
	public function addProxyConfigurationAdvice(\TYPO3\Flow\AOP\JoinPointInterface $joinPoint) {
		$request = $joinPoint->getMethodArgument('request');

		//$result = $joinPoint->getAdviceChain()->proceed($joinPoint);
		$result = $this->curlEngineProxy->sendRequest($request);

		return $result;
	}
}
