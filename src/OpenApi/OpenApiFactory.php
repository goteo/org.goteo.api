<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    use OpenApiMetadataTrait;

    public function __construct(private OpenApiFactoryInterface $decorated) {}

    private function getInfoWithDescription(OpenApi $openApi): Info
    {
        $description = \file_get_contents(sprintf(
            '%s%s%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            'OpenApiDescription.md'
        ));

        return $openApi
            ->getInfo()
            ->withDescription($description);
    }

    private function getComponentsSchemasTags(OpenApi $openApi): array
    {
        $tags = [];

        foreach ($openApi->getComponents()->getSchemas() as $name => $schema) {
            if (\preg_match('/.*\.patch/', $name)) {
                continue;
            }

            if (\preg_match('/.*\.jsonld/', $name)) {
                continue;
            }

            if (empty($schema['description'])) {
                continue;
            }

            if (\preg_match('/.*Dto/', $name)) {
                $name = preg_replace('/\..*Dto/', '', $name);
            }

            $tags[] = [
                'name' => $name,
                'description' => $schema['description'],
            ];
        }

        return $tags;
    }

    private function updatePathItemOperations(PathItem $pathItem): PathItem
    {
        if ($pathItem->getGet()) {
            $pathItem = $pathItem->withGet(
                $this->updateOperationMetadata($pathItem->getGet())
            );
        }

        if ($pathItem->getPost()) {
            $pathItem = $pathItem->withPost(
                $this->updateOperationMetadata($pathItem->getPost())
            );
        }

        if ($pathItem->getPut()) {
            $pathItem = $pathItem->withPut(
                $this->updateOperationMetadata($pathItem->getPut())
            );
        }

        if ($pathItem->getDelete()) {
            $pathItem = $pathItem->withDelete(
                $this->updateOperationMetadata($pathItem->getDelete())
            );
        }

        if ($pathItem->getPatch()) {
            $pathItem = $pathItem->withPatch(
                $this->updateOperationMetadata($pathItem->getPatch())
            );
        }

        return $pathItem;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        $openApi = $openApi->withInfo($this->getInfoWithDescription($openApi));
        $openApi = $openApi->withTags($this->getComponentsSchemasTags($openApi));

        $paths = new Paths();
        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            $pathItem = $this->updatePathItemOperations($pathItem);

            $paths->addPath($path, $pathItem);
        }

        $openApi = $openApi->withPaths($paths);

        $securitySchemes = $openApi->getComponents()->getSecuritySchemes() ?: new \ArrayObject();
        $securitySchemes['access_token'] = new SecurityScheme(
            type: 'http',
            scheme: 'bearer',
        );

        return $openApi;
    }
}
