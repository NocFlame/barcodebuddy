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
        $url = $apiUrl . $barcode;

        $data = $this->execute($url, METHOD_GET);

        if (!$data || ($data['searchResults']['totalCount'] == 0)) {
            return null; // No product found
        }

        // Extract product details
        $product = $data['searchResults']['products'][0];

        $productName = sanitizeString($product['name']);
        $genericName = null;

        if ($this->useGenericName) {
            if (isset($product['name'])) {
                $genericName = sanitizeString($product['name']);
            }
        }

        return self::createReturnArray($this->returnNameOrGenericName($productName, $genericName));
    }
}
