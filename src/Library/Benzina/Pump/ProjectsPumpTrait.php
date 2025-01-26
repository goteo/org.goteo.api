<?php

namespace App\Library\Benzina\Pump;

trait ProjectsPumpTrait
{
    /**
     * Cleans the project location field to obtain better and more cacheable search queries.
     * Based on analysis of the Goteo v3 `project.project_location` values.
     */
    public static function cleanProjectLocation(string $location): ?string
    {
        // Skip web addresses
        if (
            \str_starts_with($location, 'www.')
            || \str_starts_with($location, 'http://')
            || \str_starts_with($location, 'https://')
        ) {
            return '';
        }

        // Remove secondary conjoined places from locations
        // e.g: "España y el mundo" -> "España"
        if (\str_contains($location, ' y ')) {
            $location = \explode(' y ', $location)[0];
        }

        // Normalize parenthesis
        if (\str_contains($location, '(') || \str_contains($location, ')')) {
            $location = \str_replace('(', ',', $location);
            $location = \str_replace(')', '', $location);
        }

        $location = \explode(',', $location);
        $location = \array_map(fn($l) => trim($l), $location);

        // Clean non desired location pieces
        $location = \array_filter($location, function ($l) {
            if (empty($l)) return false;
            if (is_numeric($l)) return false;
            if (\str_contains($l, 'º')) return false;

            return true;
        });

        // Up to 3 levels of location specifity
        $location = \array_slice($location, -3);

        return \mb_strtoupper(\trim(\join(', ', $location), '.'));
    }

    private const PROJECT_KEYS = [
        'id',
        'name',
        'subtitle',
        'lang',
        'currency',
        'currency_rate',
        'status',
        'translate',
        'progress',
        'owner',
        'node',
        'amount',
        'mincost',
        'maxcost',
        'days',
        'num_investors',
        'popularity',
        'num_messengers',
        'num_posts',
        'created',
        'updated',
        'published',
        'success',
        'closed',
        'passed',
        'contract_name',
        'contract_nif',
        'phone',
        'contract_email',
        'address',
        'zipcode',
        'location',
        'country',
        'image',
        'description',
        'motivation',
        'video',
        'video_usubs',
        'about',
        'goal',
        'related',
        'spread',
        'reward',
        'category',
        'keywords',
        'media',
        'media_usubs',
        'currently',
        'project_location',
        'scope',
        'resource',
        'comment',
        'contract_entity',
        'contract_birthdate',
        'entity_office',
        'entity_name',
        'entity_cif',
        'post_address',
        'secondary_address',
        'post_zipcode',
        'post_location',
        'post_country',
        'amount_users',
        'amount_call',
        'maxproj',
        'analytics_id',
        'facebook_pixel',
        'social_commitment',
        'social_commitment_description',
        'execution_plan',
        'sustainability_model',
        'execution_plan_url',
        'sustainability_model_url',
        'sign_url',
        'sign_url_action',
    ];
}
