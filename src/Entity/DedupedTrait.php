<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use Doctrine\ORM\Mapping as ORM;

trait DedupedTrait
{
    /**
     * Entity came duplicated from the Goteo v3 platform.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column()]
    protected ?bool $deduped = false;

    /**
     * Previous IDs of the duplicated entities in the Goteo v3 platform.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\Column()]
    protected array $dedupedIds = [];

    public function isDeduped(): ?bool
    {
        return $this->deduped;
    }

    public function setDeduped(bool $deduped): static
    {
        $this->deduped = $deduped;

        return $this;
    }

    public function getDedupedIds(): ?array
    {
        return $this->dedupedIds;
    }

    public function addDedupedId(string $dedupedId): static
    {
        $this->dedupedIds = \array_unique([...$this->dedupedIds, $dedupedId]);

        return $this;
    }
}
