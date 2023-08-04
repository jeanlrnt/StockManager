<?php

if (!function_exists('parse_address')) {
    /**
     * Paginate a collection.
     *
     * @param object $parameters
     * @return array
     */
    function parse_address(object $parameters): array
    {
        // Parse the address parameters from the request and put them in an array to be validated
        $address = [];
        foreach ($parameters as $key => $parameter) {
            if (preg_match('/^address\[(.*)]$/', $key, $matches)) {
                $address[$matches[1]] = $parameter;
                unset($parameters->$key);
            }
        }

        return $address;
    }
}
