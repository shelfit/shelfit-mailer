<?php

namespace App\MessageHandler;

use App\Message\SendEmailMessage;
use App\Repository\EmailSentLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Throwable;

#[AsMessageHandler]
readonly class SendEmailMessageHandler
{
    public function __construct(
        private MailerInterface $mailer,
        private EmailSentLogRepository $emailSentLogRepository,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SendEmailMessage $message): void
    {
        $log = $this->emailSentLogRepository->findOneBy(['idempotencyKey' => $message->getIdempotencyKey()]);

        if ($log !== null && $log->getSentAt() !== null) {
            return;
        }

        $log->setSentAt(new DateTimeImmutable());
        $this->entityManager->persist($log);
        $this->entityManager->flush();

        $email = (new Email())
            ->subject($message->getSubject())
            ->html($message->getBody())
            ->to($message->getTo())
            ->from($message->getFrom());

        if ($message->getCc()) {
            $email->cc($message->getCc());
        }

        if ($message->getBcc()) {
            $email->bcc($message->getBcc());
        }

        try {
            $this->mailer->send($email);
        } catch (Throwable $e) {
            $this->logger->error("Error sending email", [
                'exception' => $e->getMessage(),
                'idempotencyKey' => $message->getIdempotencyKey(),
            ]);

            $log->setSentAt(null);
            $this->entityManager->persist($log);
            $this->entityManager->flush();

            throw $e;
        }
    }
}