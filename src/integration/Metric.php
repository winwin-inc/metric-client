<?php

/**
 * NOTE: This class is auto generated by Tars Generator (https://github.com/wenbinye/tars-generator).
 *
 * Do not edit the class manually.
 * Tars Generator version: 1.0-SNAPSHOT
 */

namespace winwin\metric\client\integration;

use wenbinye\tars\protocol\annotation\TarsProperty;

final class Metric
{
    /**
     * @TarsProperty(order = 0, required = true, type = "string")
     * @var string
     */
    public $scopeId;

    /**
     * @TarsProperty(order = 1, required = true, type = "string")
     * @var string
     */
    public $name;

    /**
     * @TarsProperty(order = 2, required = false, type = "map<string, string>")
     * @var array
     */
    public $tags;
}