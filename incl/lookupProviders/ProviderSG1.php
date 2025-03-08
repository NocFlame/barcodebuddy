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

class ProviderSG1 extends LookupProvider {

    function __construct(string $apiKey = null) {
        parent::__construct($apiKey);
        $this->providerName      = "SG1";
        $this->providerConfigKey = "LOOKUP_USE_SG1";
    }

    /**
     * Looks up a barcode
     * @param string $barcode The barcode to lookup
     * @return array|null Name of product, null if none found
     */
    public function lookupBarcode(string $barcode): ?array {
        if (!$this->isProviderEnabled())
            return null;

        $url = 'https://productsearch.gs1.se/foodservice/tradeItem/search';

        // Define the data to be sent in the body
        $data = json_encode([
            'query' => $barcode,
            'sortby' => 0,
            'sortDirection' => 1,
        ]);

        $response = $this->execute($url, METHOD_POST, null, null, null, true, $data);

        if (!$response || ($response['count'] == 0)) {
            return null; // No product found
        }

        // Extract product details
        $product = $response['results'][0];

        $productName = sanitizeString($product['descriptionShort']);
        $genericName = null;

        if ($this->useGenericName) {
            if (isset($product['descriptionShort'])) {
                $genericName = sanitizeString($product['descriptionShort']);
            }
        }

        return self::createReturnArray($this->returnNameOrGenericName($productName, $genericName));
    }
}