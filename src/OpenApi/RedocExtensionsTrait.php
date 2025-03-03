<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\OpenApi;

trait RedocExtensionsTrait
{
    private function getNonGroupTags(array $tags, array $groups)
    {
        $groupTags = \array_merge(...\array_map(fn($g) => $g['tags'], $groups));
        $nonGroupTags = \array_filter($tags, fn($t) => !\in_array($t['name'], $groupTags));

        return [...\array_map(fn($t) => $t['name'], $nonGroupTags)];
    }

    private function getTagGroups(OpenApi $openApi): array
    {
        $tags = $openApi->getTags();

        $groups = [
            [
                'name' => 'Users',
                'tags' => [
                    'User',
                    'UserToken',
                    'Person',
                    'Organization',
                ],
            ],
            [
                'name' => 'Projects',
                'tags' => [
                    'Project',
                    'ProjectReward',
                    'ProjectRewardClaim',
                    'ProjectBudgetItem',
                    'ProjectUpdate',
                ],
            ],
            [
                'name' => 'Gateways',
                'tags' => [
                    'Gateway',
                    'GatewayCharge',
                    'GatewayCheckout',
                ],
            ],
            [
                'name' => 'Accounting',
                'tags' => [
                    'Accounting',
                    'AccountingBalancePoint',
                    'AccountingTransaction',
                ],
            ],
        ];

        return [
            ...$groups,
            [
                'name' => 'Other',
                'tags' => $this->getNonGroupTags($tags, $groups),
            ],
        ];
    }
}
