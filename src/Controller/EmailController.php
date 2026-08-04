<?php

namespace App\Controller;

use App\DTO\EmailDto;
use App\Entity\EmailSentLog;
use App\Message\SendEmailMessage;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;

class EmailController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment $twig,
        private readonly MessageBusInterface $messageBus,
        private readonly LoggerInterface $logger,
    ){
    }

    #[Route('/api/send', methods: ['POST'])]
    public function send(#[MapRequestPayload] EmailDto $email): JsonResponse
    {
        try {
            $body = $this->twig->render("emails/{$email->getTemplate()}.html.twig", $email->getVariables());
        }
        catch (LoaderError $e) {
            $this->logger->error("Non-existent template requested: '{$email->getTemplate()}'.", ['exception' => $e]);
            return $this->json([
                'status' => 'error',
                'message' => 'Cannot find twig template ' . $email->getTemplate()
            ], 404);
        }
        catch (Throwable $e) {
            $this->logger->error("Error rendering template '{$email->getTemplate()}'.", ['exception' => $e]);
            return $this->json([
                'status' => 'error',
                'message' => 'Error rendering template: ' . $e->getMessage()
            ], 500);
        }

        try {
            $emailSentLog = (new EmailSentLog())
                ->setIdempotencyKey($email->getIdempotencyKey())
                ->setTemplate($email->getTemplate())
                ->setRecipient($email->getTo());

            $this->entityManager->persist($emailSentLog);
            $this->entityManager->flush();
        }
        catch (UniqueConstraintViolationException) {
            return $this->json(['status' => 'success']);
        }

        $this->messageBus->dispatch((new SendEmailMessage(
            idempotencyKey: $email->getIdempotencyKey(),
            subject: $email->getSubject(),
            from: $email->getFrom(),
            to: $email->getTo(),
            body: $body,
            cc: $email->getCc(),
            bcc: $email->getBcc(),
        )));

        return $this->json(['status' => 'success']);
    }
}