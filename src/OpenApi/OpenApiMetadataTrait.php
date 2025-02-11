<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Model\Operation;

trait OpenApiMetadataTrait
{
    private function updateOperationMetadata(Operation $operation): Operation
    {
        $idParts = explode('_', $operation->getOperationId());
        $idResource = $operation->getTags()[0];
        $idOperation = array_slice($idParts, -1)[0];

        $operationDescription = $operation->getDescription();

        switch ($idOperation) {
            case 'collection':
                $operationId = sprintf('List all %ss', $idResource);
                break;
            case 'post':
                $operationId = sprintf('Create one %s', $idResource);
                $operationDescription = sprintf('Creates a new %s resource.', $idResource);
                break;
            case 'get':
                $operationId = sprintf('Retrieve one %s', $idResource);
                $operationDescription = sprintf('Retrieves one %s resource.', $idResource);
                break;
            case 'put':
                $operationId = sprintf('Update one %s', $idResource);
                break;
            case 'delete':
                $operationId = sprintf('Delete one %s', $idResource);
                break;
            case 'patch':
                $operationId = sprintf('Patch one %s', $idResource);
                break;
            default:
                $operationId = $operation->getOperationId();
        }

        return $operation
            ->withSummary($operationId)
            ->withOperationId($operationId)
            ->withDescription($operationDescription);
    }
}
