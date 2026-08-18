<?php

namespace Test\Mail;

use OCP\Defaults;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Mail\IAttachment;
use OCP\Mail\IEMailTemplate;
use OCP\Mail\IMessage;
use Psr\Log\LoggerInterface;

/**
 * Class FakeMailer
 */
class FakeMailer implements \OCP\Mail\IMailer {

	public function __construct(
		private IConfig          $config,
		private LoggerInterface  $logger,
		private Defaults         $defaults,
		private IURLGenerator    $urlGenerator,
		private IL10N            $l10n,
		private IEventDispatcher $dispatcher,
		private IFactory         $l10nFactory,
	) {
	}
	public function createMessage(): IMessage {
	}

	public function createAttachment($data = null, $filename = null, $contentType = null): IAttachment {
	}

	public function createAttachmentFromPath(string $path, $contentType = null): IAttachment {
	}

	public function createEMailTemplate(string $emailId, array $data = []): IEMailTemplate {
	}

	public function send(IMessage $message): array {
	}

	public function validateMailAddress(string $email): bool {
	}
}
