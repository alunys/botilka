<?php

declare(strict_types=1);

namespace Botilka\Infrastructure\Doctrine;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(readOnly=true)
 * @ORM\Table(
 *     name="event_store",
 *     indexes={
 *         @ORM\Index(columns={"domain"})
 *     }
 * )
 * @ApiResource(
 *     routePrefix="/event_store",
 *     collectionOperations={"get"},
 *     itemOperations={"get"}
 * )
 */
class Event
{
    /**
     * @var UuidInterface
     * @ORM\Id
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $playhead;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    private $payload;

    /**
     * @var ?array
     * @ORM\Column(type="json")
     */
    private $metadata;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $recordedOn;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $domain;

    public function __construct(string $id, int $playhead, string $type, array $payload, ?array $metadata, string $domain)
    {
        $this->id = Uuid::fromString($id);
        $this->playhead = $playhead;
        $this->type = $type;
        $this->payload = $payload;
        $this->metadata = $metadata;
        $this->recordedOn = new \DateTimeImmutable();
        $this->domain = $domain;
    }

    public function getId(): string
    {
        return $this->id->toString();
    }

    public function getPlayhead(): int
    {
        return $this->playhead;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function getRecordedOn(): \DateTimeImmutable
    {
        return $this->recordedOn;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }
}
