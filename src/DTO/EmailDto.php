<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class EmailDto
{
    public function __construct(
        #[Assert\NotBlank(message: "Idempotency key must be set")]
        public ?string $idempotencyKey = null,
        
        #[Assert\NotBlank(message: "Subject key must be set")]
        public ?string $subject = null,

        #[Assert\NotBlank(message: "Sender address must be set")]
        public ?string $from = null,
        
        #[Assert\NotBlank(message: "Recipient address must be set")]
        public ?string $to = null,
        
        #[Assert\NotBlank(message: "Template must be set")]
        public ?string $template = null,

        public ?array $variables = null,
        public ?string $cc = null,
        public ?string $bcc = null,
    ) {
    }

    public function getIdempotencyKey(): ?string
    {
        return $this->idempotencyKey;
    }

    public function setIdempotencyKey(?string $idempotencyKey): self
    {
        $this->idempotencyKey = $idempotencyKey;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getFrom(): ?string
    {
        return $this->from;
    }

    public function setFrom(?string $from): self
    {
        $this->from = $from;
        return $this;
    }

    public function getTo(): ?string
    {
        return $this->to;
    }

    public function setTo(?string $to): self
    {
        $this->to = $to;
        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): self
    {
        $this->template = $template;
        return $this;
    }

    public function getVariables(): ?array
    {
        return $this->variables;
    }

    public function setVariables(?array $variables): self
    {
        $this->variables = $variables;
        return $this;
    }

    public function getCc(): ?string
    {
        return $this->cc;
    }

    public function setCc(?string $cc): self
    {
        $this->cc = $cc;
        return $this;
    }

    public function getBcc(): ?string
    {
        return $this->bcc;
    }

    public function setBcc(?string $bcc): self
    {
        $this->bcc = $bcc;
        return $this;
    }
}