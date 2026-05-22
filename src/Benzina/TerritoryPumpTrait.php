<?php

namespace App\Benzina;

trait TerritoryPumpTrait
{
    /**
     * Normalizes flexible address strings to obtain highly cacheable and improved search queries.
     * Based on analysis of the Goteo v3 `project.project_location` values.
     *
     * @param int $detailLevel Desired number of remaining components in output address
     */
    public static function cleanLocation(string $location, int $detailLevel = 3): string
    {
        $location = trim($location);

        // 1. Skip URLs
        if (
            str_starts_with($location, 'http://')
            || str_starts_with($location, 'https://')
            || str_starts_with($location, 'www.')
        ) {
            return '';
        }

        // 2. Normalize separators ONLY when clearly semantic (not street hyphens)
        $location = preg_replace('/\s+(\/|\||&|\by\b|\bi\b|and)\s+/iu', ',', $location);

        // 3. Normalize parentheses -> commas
        $location = str_replace(['(', ')'], [',', ''], $location);

        // 4. Remove leading labels like "University X:"
        $location = preg_replace('/^[^:]{1,80}:\s*/u', '', $location);

        // 5. Tokenize
        $parts = array_map(
            fn($p) => trim($p),
            explode(',', $location)
        );

        // 6. Clean tokens
        $parts = array_values(array_filter($parts, function ($p) {
            if ($p === '') {
                return false;
            }

            // Remove pure noise like empty numbers or coordinates
            if (preg_match('/^\s*[-+]?\d+(\.\d+)?\s*$/', $p)) {
                return false;
            }

            return true;
        }));

        // 7. Normalize tokens
        $parts = array_map(function ($p) {
            $p = trim($p);

            // remove leading house numbers but keep street names
            $p = preg_replace('/^\d+\s*/u', '', $p);

            $upper = mb_strtoupper($p, 'UTF-8');
            foreach (self::COMMON_VARIATIONS as $standard => $variants) {
                if (in_array($upper, $variants, true)) {
                    return $standard;
                }
            }

            return $p;
        }, $parts);

        // 8. Keep only last N components (most specific context)
        if (count($parts) > $detailLevel) {
            $parts = array_slice($parts, -$detailLevel);
        }

        // 9. Join deterministically
        $result = implode(', ', $parts);

        // 10. Final cleanup
        $result = preg_replace('/\s+/u', ' ', $result);
        $result = trim($result, " \t\n\r\0\x0B,.-;");

        return mb_strtoupper($result, 'UTF-8');
    }

    /**
     * @var array<string, array> The standard preferred name and a list of possible variations and misspellings
     */
    private const COMMON_VARIATIONS = [
        'PERÚ' => ['PÉROU'],
        'ECUADOR' => ['EQUADOR'],
        'MÉXICO' => ['MEXICO', 'MESSICO', 'MX'],
        'CIUDAD DE MÉXICO' => ['CDMX'],
        'EUROPA' => ['EUROPEAN UNION', 'EUROPE'],
        'FRANCIA' => ['FRANCE'],
        'ITALIA' => ['ITALY'],
        'ALEMANIA' => ['GERMANY', 'DEUTSCHLAND'],
        'FINLANDIA' => ['FINLAND'],
        'SUECIA' => ['SWEDEN', 'SVERIGE'],
        'ESPAÑA' => ['ESPANYA', 'ESPANHA', 'ESPAGNE', 'SPAGNA', 'SPANIEN', 'SPAIN', 'ESTADO ESPAÑOL', 'ESPAINIA'],
        'ANDALUCÍA' => ['ANDALUCIA', 'ANDALUSIA'],
        'CÁDIZ' => ['CADIZ'],
        'CÓRDOBA' => ['CORDOBA'],
        'COMUNIDAD VALENCIANA' => ['COMUNITAT VALENCIANA', 'PAÍS VALENCIÀ'],
        'VALENCIA' => ['VALÈNCIA', 'PROVINCIA DE VALENCIA'],
        'ALICANTE' => ['ALACANT'],
        'CASTELLÓN DE LA PLANA' => ['CASTELLÓ DE LA PLANA', 'CASTELLON DE LA PLANA'],
        'LAS PALMAS DE GRAN CANARIA' => ['LAS PALMAS'],
        'CÁCERES' => ['CACERES'],
        'MÉRIDA' => ['MERIDA'],
        'LEÓN' => ['LEON'],
        'LEGANÉS' => ['LEGANES'],
        'BALEARES' => ['BALEAREN', 'BALEARS', 'ISLAS BALEARES', 'ILLES BALEARS', 'BALEARIC ISLANDS'],
        'GALICIA' => ['GALIZA'],
        'A CORUÑA' => ['LA CORUÑA', 'CORUÑA'],
        'OURENSE' => ['ORENSE'],
        'CATALUÑA' => ['CATALUNYA', 'PAÏSOS CATALANS', 'CATALUNA', 'CATALONIA'],
        'LLEIDA' => ['LÉRIDA', 'LERIDA'],
        'TARRASA' => ['TERRASA'],
        'BARCELONA' => ['PROVINCIA DE BARCELONA', 'BARCELONE', 'A BARCELONA'],
        'HOSPITALET DE LLOBREGAT' => ["L'HOSPITALET DE LLOBREGAT"],
        'SANT ADRIÀ DE BESÒS' => ['SANT ADRIÀ DEL BESÒS'],
        'MURCIA' => ['REGIÓN DE MURCIA'],
        'PAÍS VASCO' => ['EUSKADI', 'EUSKAL HERRIA'],
        'BILBAO' => ['BILBO'],
        'SAN SEBASTIÁN' => ['DONOSTIA', 'DONOSTIA-SAN SEBASTIÁN'],
        'VIZCAYA' => ['BIZKAIA'],
        'IRÚN' => ['IRUN'],
        'NAVARRA' => ['NAVARRE', 'NAFARROA'],
        'PAMPLONA' => ['IRUÑEA'],
        'CANARIAS' => ['ISLAS CANARIAS', 'CANARY ISLANDS'],
    ];
}
