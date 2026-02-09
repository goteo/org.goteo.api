<?php

namespace App\Service\Scout;

interface ScoutProcessorInterface
{
    /**
     * Decide if the result is processable.
     */
    public function supports(ScoutResult $result): bool;

    /**
     * Process a ScoutResult.
     *
     * @param ScoutResult $result The input result
     *
     * @return ScoutResult The output result
     */
    public function process(ScoutResult $result): ScoutResult;
}
