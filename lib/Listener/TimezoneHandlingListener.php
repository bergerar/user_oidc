<?php
/**
 * SPDX-FileCopyrightText: 2023 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\UserOIDC\Listener;

use OCA\Files\Event\LoadAdditionalScriptsEvent;
use OCA\UserOIDC\AppInfo\Application;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\ISession;
use OCP\IUserSession;
use OCP\Util;

/**
 * @implements IEventListener<LoadAdditionalScriptsEvent|Event>
 */
class TimezoneHandlingListener implements IEventListener {

	/** @var IUserSession */
	private $userSession;
	/** @var ISession */
	private $session;
	/** @var IConfig */
	private $config;

	public function __construct(
		IUserSession $userSession,
		ISession $session,
		IConfig $config,
	) {
		$this->userSession = $userSession;
		$this->session = $session;
		$this->config = $config;
	}

	public function handle(Event $event): void {
		if (!$event instanceof LoadAdditionalScriptsEvent) {
			return;
		}

		if (!$this->userSession->isLoggedIn()) {
			return;
		}

		$user = $this->userSession->getUser();
		$timezoneDB = $this->config->getUserValue($user->getUID(), 'core', 'timezone');

		if ($timezoneDB === '' || !$this->session->exists('timezone')) {
			Util::addScript(Application::APP_ID, Application::APP_ID . '-timezone');
		}
	}
}
