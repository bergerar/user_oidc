<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace OCA\UserOIDC\Controller;

use OCA\UserOIDC\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IRequest;

class BaseOidcController extends Controller {

	/**
	 * @var IConfig
	 */
	private $config;

	public function __construct(IRequest $request,
		IConfig $config) {
		parent::__construct(Application::APP_ID, $request);
		$this->config = $config;
	}

	/**
	 * @return bool
	 */
	protected function isDebugModeEnabled(): bool {
		return $this->config->getSystemValueBool('debug', false);
	}

	/**
	 * @param string $message
	 * @param int $statusCode
	 * @param array $throttleMetadata
	 * @param bool|null $throttle
	 * @return TemplateResponse
	 */
	protected function buildErrorTemplateResponse(string $message, int $statusCode, array $throttleMetadata = [], ?bool $throttle = null): TemplateResponse {
		$params = [
			'errors' => [
				['error' => $message],
			],
		];
		return $this->buildFailureTemplateResponse('', 'error', $params, $statusCode, $throttleMetadata, $throttle);
	}

	/**
	 * @param string $message
	 * @param int $statusCode
	 * @param array $throttleMetadata
	 * @param bool|null $throttle
	 * @return TemplateResponse
	 */
	protected function build403TemplateResponse(string $message, int $statusCode, array $throttleMetadata = [], ?bool $throttle = null): TemplateResponse {
		$params = ['message' => $message];
		return $this->buildFailureTemplateResponse('core', '403', $params, $statusCode, $throttleMetadata, $throttle);
	}

	/**
	 * @param string $appName
	 * @param string $templateName
	 * @param array $params
	 * @param int $statusCode
	 * @param array $throttleMetadata
	 * @param bool|null $throttle
	 * @return TemplateResponse
	 */
	protected function buildFailureTemplateResponse(string $appName, string $templateName, array $params, int $statusCode,
		array $throttleMetadata = [], ?bool $throttle = null): TemplateResponse {
		$response = new TemplateResponse(
			$appName,
			$templateName,
			$params,
			TemplateResponse::RENDER_AS_ERROR
		);
		$response->setStatus($statusCode);
		// if not specified, throttle if debug mode is off
		if (($throttle === null && !$this->isDebugModeEnabled()) || $throttle) {
			$response->throttle($throttleMetadata);
		}
		return $response;
	}
}
