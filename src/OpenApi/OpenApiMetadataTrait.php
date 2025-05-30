<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;

trait OpenApiMetadataTrait
{
    private function getOperationType(Operation $operation): string
    {
        $idParts = explode('_', $operation->getOperationId());

        return array_slice($idParts, -1)[0];
    }

    private function updateOperationMetadata(Operation $operation): Operation
    {
        $resource = $operation->getTags()[0];

        $operationType = $this->getOperationType($operation);
        $operationDescription = $operation->getDescription();

        switch ($operationType) {
            case 'collection':
                break;
            case 'post':
                $operationDescription = sprintf('Creates a new %s resource.', $resource);
                break;
            case 'get':
                $operationDescription = sprintf('Retrieves one %s resource.', $resource);
                break;
            case 'put':
                break;
            case 'delete':
                break;
            case 'patch':
                break;
        }

        return $operation
            ->withDescription($operationDescription);
    }

    private function updatePathItemOperation(PathItem $pathItem)
    {
        $operation = [...\array_filter([
            $pathItem->getGet(),
            $pathItem->getPost(),
            $pathItem->getPut(),
            $pathItem->getPatch(),
            $pathItem->getDelete(),
        ])][0];

        $operationType = $this->getOperationType($operation);

        if ($operationType === 'collection') {
            $operationType = 'get';
        }

        $withOperation = \sprintf('with%s', \ucfirst($operationType));

        return $pathItem->$withOperation($this->updateOperationMetadata($operation));
    }
}
