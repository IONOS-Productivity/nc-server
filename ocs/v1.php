<?php
/**
 * SPDX-FileCopyrightText: 2016-2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-FileCopyrightText: 2016 ownCloud, Inc.
 * SPDX-License-Identifier: AGPL-3.0-only
 */
require_once __DIR__ . '/../lib/versioncheck.php';
require_once __DIR__ . '/../lib/base.php';

if (\OCP\Util::needUpgrade()
	|| \OC::$server->getConfig()->getSystemValueBool('maintenance')) {
	// since the behavior of apps or remotes are unpredictable during
	// an upgrade, return a 503 directly
	http_response_code(503);
	header('X-Nextcloud-Maintenance-Mode: 1');
	$response = new \OC\OCS\Result(null, 503, 'Service unavailable');
	OC_API::respond($response, OC_API::requestedFormat());
	exit;
}

use OCP\Security\Bruteforce\MaxDelayReached;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/*
 * Try the appframework routes
 */
try {
	OC_App::loadApps(['session']);
	OC_App::loadApps(['authentication']);
	OC_App::loadApps(['extended_authentication']);

	// load all apps to get all api routes properly setup
	// FIXME: this should ideally appear after handleLogin but will cause
	// side effects in existing apps
	OC_App::loadApps();

	if (!\OC::$server->getUserSession()->isLoggedIn()) {
		OC::handleLogin(\OC::$server->getRequest());
	}

	OC::$server->get(\OC\Route\Router::class)->match('/ocsapp'.\OC::$server->getRequest()->getRawPathInfo());
} catch (MaxDelayReached $ex) {
	$format = \OC::$server->getRequest()->getParam('format', 'xml');
	OC_API::respond(new \OC\OCS\Result(null, OCP\AppFramework\Http::STATUS_TOO_MANY_REQUESTS, $ex->getMessage()), $format);
} catch (ResourceNotFoundException $e) {
	OC_API::setContentType();

	$format = \OC::$server->getRequest()->getParam('format', 'xml');
	$txt = 'Invalid query, please check the syntax. API specifications are here:'
		.' http://www.freedesktop.org/wiki/Specifications/open-collaboration-services.'."\n";
	OC_API::respond(new \OC\OCS\Result(null, \OCP\AppFramework\OCSController::RESPOND_NOT_FOUND, $txt), $format);
} catch (MethodNotAllowedException $e) {
	OC_API::setContentType();
	http_response_code(405);
} catch (\OC\OCS\Exception $ex) {
	OC_API::respond($ex->getResult(), OC_API::requestedFormat());
} catch (\OC\User\LoginException $e) {
	OC_API::respond(new \OC\OCS\Result(null, \OCP\AppFramework\OCSController::RESPOND_UNAUTHORISED, 'Unauthorised'));
} catch (OC\Authentication\Exceptions\UserAgentForbidden $ex) {
	OC_API::respond(new \OC\OCS\Result(null, 403, $ex->getMessage()));
} catch (\Exception $e) {
	\OCP\Server::get(LoggerInterface::class)->error($e->getMessage(), ['exception' => $e]);
	OC_API::setContentType();

	$format = \OC::$server->getRequest()->getParam('format', 'xml');
	$txt = 'Internal Server Error'."\n";
	try {
		if (\OC::$server->getSystemConfig()->getValue('debug', false)) {
			$txt .= $e->getMessage();
		}
	} catch (\Throwable $e) {
		// Just to be save
	}
	OC_API::respond(new \OC\OCS\Result(null, \OCP\AppFramework\OCSController::RESPOND_SERVER_ERROR, $txt), $format);
}
