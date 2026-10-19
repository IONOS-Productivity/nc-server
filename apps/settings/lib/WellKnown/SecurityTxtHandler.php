<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Settings\WellKnown;

use OCP\AppFramework\Http\TextPlainResponse;
use OCP\Http\WellKnown\GenericResponse;
use OCP\Http\WellKnown\IHandler;
use OCP\Http\WellKnown\IRequestContext;
use OCP\Http\WellKnown\IResponse;

class SecurityTxtHandler implements IHandler {
	public function handle(string $service, IRequestContext $context, ?IResponse $previousResponse): ?IResponse {
		if ($service !== 'security.txt') {
			return $previousResponse;
		}

		$response = 'Contact: mailto:security@ionos.com
Expires: 2028-02-11T23:30:00.000Z
Encryption: https://keys.openpgp.org/search?q=security%40ionos.com
Preferred-Languages: en
Policy: https://www.ionos.com/it-security
Hiring: https://jobs.ionos.com/
';

		return new GenericResponse(new TextPlainResponse($response, 200));
	}
}
