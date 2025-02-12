<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;

class OpenApiFactory implements OpenApiFactoryInterface
{
    use OpenApiMetadataTrait;
    use RedocExtensionsTrait;

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

            if (\preg_match('/.*Dto/', $name)) {
                continue;
            }

            if (empty($schema['description'])) {
                continue;
            }

            $tags[] = [
                'name' => $name,
                'description' => $schema['description'],
            ];
        }

        return $tags;
    }

    private function getUpdatedPaths(OpenApi $openApi): Paths
    {
        $paths = new Paths();

        foreach ($openApi->getPaths()->getPaths() as $path => $pathItem) {
            $pathItem = $this->updatePathItemOperation($pathItem);

            $paths->addPath($path, $pathItem);
        }

        return $paths;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        $openApi = $openApi->withInfo($this->getInfoWithDescription($openApi));
        $openApi = $openApi->withTags($this->getComponentsSchemasTags($openApi));
        $openApi = $openApi->withPaths($this->getUpdatedPaths($openApi));
        $openApi = $openApi->withExtensionProperty('x-tagGroups', $this->getTagGroups($openApi));

        $securitySchemes = $openApi->getComponents()->getSecuritySchemes() ?: new \ArrayObject();
        $securitySchemes['access_token'] = new SecurityScheme(
            type: 'http',
            scheme: 'bearer',
        );

        return $openApi;
    }
}
