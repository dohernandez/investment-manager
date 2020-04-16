<?php

namespace App\Domain\Report;

use App\Domain\Report\Wallet\Content;
use Gedmo\Timestampable\Traits\Timestampable;

class WalletReport
{
    public const WALLET_REPORT_DAILY_TYPE = 'daily';

    use Timestampable;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $walletId;

    /**
     * @var Content
     */
    private $content;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function setContent(Content $content): self
    {
        $this->content = $content;

        $this->walletId = $content->getWallet()->getId();

        return $this;
    }
}
