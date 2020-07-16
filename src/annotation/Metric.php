<?php

namespace winwin\metric\client\annotation;

use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Metric
{
    /**
     * @var string
     * @Required()
     */
    public $name;

    /**
     * @var array
     */
    public $tags;
}
