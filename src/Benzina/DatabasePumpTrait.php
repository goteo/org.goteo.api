<?php

namespace App\Benzina;

use Goteo\Benzina\Pump\ContextAwareTrait;

trait DatabasePumpTrait
{
    use ContextAwareTrait;

    /**
     * @return array{
     *  scheme: string,
     *  user: string,
     *  pass: string,
     *  host: string,
     *  name: string
     * }
     */
    private function getDbData(array $context): array
    {
        $parsedUrl = parse_url($context['options']['database']);

        return [
            'name' => ltrim($parsedUrl['path'], '/'),
            ...$parsedUrl,
        ];
    }

    private function getDbConnection(array $context): \PDO
    {
        $dbdata = $this->getDbData($context);

        return new \PDO(
            dsn: sprintf('%s:host=%s;dbname=%s', $dbdata['scheme'], $dbdata['host'], $dbdata['name']),
            username: $dbdata['user'],
            password: $dbdata['pass'],
            options: [
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
    }
}
