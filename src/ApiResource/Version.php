<?php

namespace App\ApiResource;

use ApiPlatform\Metadata as API;
use App\Filter\ResourceVersionResourceFilter;
use App\Filter\ResourceVersionResourceIdFilter;
use App\State\ResourceVersionStateProvider;
use Gedmo\Loggable\Entity\LogEntry;

/**
 * Some resources are versioned. This means v4 keeps track of the changes performed in subsets of specific properties within these resources.\
 * \
 * This allows us to keep track of the flow and the evolution of records in the platform.
 * Looking at the changes done between one version and the next one we can reconstruct how a resource was at a certain point in time.
 */
#[API\ApiFilter(ResourceVersionResourceFilter::class, properties: ['resource'])]
#[API\ApiFilter(ResourceVersionResourceIdFilter::class, properties: ['resourceId'])]
#[API\GetCollection(provider: ResourceVersionStateProvider::class)]
#[API\Get(provider: ResourceVersionStateProvider::class)]
class Version
{
    public function __construct(
        private readonly LogEntry $log,
        private readonly string $shortName,
    ) {}

    /**
     * The ID of the version record.
     */
    public function getId(): ?int
    {
        return $this->log->getId();
    }

    /**
     * The type of action that performed the recorded changes.
     */
    public function getAction(): ?string
    {
        return $this->log->getAction();
    }

    /**
     * The type of the recorded resource.
     */
    public function getResource(): string
    {
        return $this->shortName;
    }

    /**
     * The ID of the recorded resource.
     */
    public function getResourceId(): int
    {
        return $this->log->getObjectId();
    }

    /**
     * The version number for this specific resource.
     */
    public function getResourceVersion(): ?int
    {
        return $this->log->getVersion();
    }

    /**
     * The changed resource data, i.e the new values of the changed properties.
     */
    public function getResourceChanges()
    {
        return $this->log->getData();
    }

    /**
     * The date at which this version was created.
     */
    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->log->getLoggedAt();
    }
}
