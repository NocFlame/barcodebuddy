<?php

/**
 * Barcode Buddy for Grocy
 *
 * PHP version 7
 *
 * LICENSE: This source file is subject to version 3.0 of the GNU General
 * Public License v3.0 that is attached to this project.
 *
 * @author     NocFlame
 * @copyright  2025 NocFlame
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html  GNU GPL v3.0
 * @since      File available since Release X.X
 */

require_once __DIR__ . "/../api.inc.php";
class ProviderCitygross extends LookupProvider {

    private $result;

    function __construct(string $apiKey = null) {
        parent::__construct($apiKey);
        $this->providerName      = "Citygross";
        $this->providerConfigKey = "LOOKUP_USE_CITYGROSS";
    }

    /**
     * Looks up a barcode
     * @param string $barcode The barcode to lookup
     * @return array|null Name of product, null if none found
     */
    public function lookupBarcode(string $barcode): ?array {
        if (!$this->isProviderEnabled())
            return null;

        global $CONFIG;

        $apiUrl = 'https://www.citygross.se/api/v1/Loop54/search/quick/?SearchQuery=';
        $url = $this->apiUrl . urlencode($barcode);

        $headers = [
            'Host: www.citygross.se',
            'Sec-Ch-Ua-Platform: "Linux"',
            'Accept-Language: en-US,en;q=0.9',
            'Accept: application/json',
            'Sec-Ch-Ua: "Chromium";v="133", "Not(A:Brand";v="99"',
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36',
            'Sec-Ch-Ua-Mobile: ?0',
            'Sec-Fetch-Site: same-origin',
            'Sec-Fetch-Mode: cors',
            'Sec-Fetch-Dest: empty',
            'Referer: https://www.citygross.se/',
            'Accept-Encoding: gzip, deflate, br',
            'Priority: u=1, i'
        ];

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate,br'); // Handle compressed response

        $response = curl_exec($ch);
        curl_close($ch);

        // Decode response
        $data = json_decode($response, true);
        if (!$data || empty($data['searchResults']['products'])) {
            return null; // No product found
        }

        // Extract product details
        $product = $data['searchResults']['products'][0];
        return [
            sanitizeString('name' => $product['name'] ?? 'Unknown')/*,
            'brand' => $product['brand'] ?? 'Unknown',
            'description' => strip_tags($product['description'] ?? ''),
            'category' => $product['category'] ?? 'Unknown',
            'origin' => $product['countryOfOrigin'] ?? 'Unknown',
            'size' => $product['descriptiveSize'] ?? 'Unknown',
            'image' => isset($product['images'][0]['url']) ? 'https://www.citygross.se/' . $product['images'][0]['url'] : null,
            'price' => $product['productStoreDetails']['prices']['currentPrice']['price'] ?? null,
            'price_unit' => $product['productStoreDetails']['prices']['currentPrice']['comparativePriceUnit'] ?? '',
            'nutrients' => $this->extractNutrients($product)*/
        ];
    }

    private function extractNutrients($product) {
            $nutrients = [];
            if (!empty($product['foodAndBeverageExtension']['nutrientInformations'][0]['nutrients'])) {
                foreach ($product['foodAndBeverageExtension']['nutrientInformations'][0]['nutrients'] as $nutrient) {
                    $nutrients[] = [
                        'type' => $nutrient['typeCode'],
                        'value' => $nutrient['value'],
                        'unit' => $this->getNutrientUnit($nutrient['unitOfMeasure'])
                    ];
                }
            }
            return $nutrients;
        }

        private function getNutrientUnit($unitCode) {
            $units = [
                0 => 'g', 1 => 'mg', 2 => 'μg', 3 => 'kcal', 4 => 'kJ', 5 => 'kcal'
            ];
            return $units[$unitCode] ?? '';
        }