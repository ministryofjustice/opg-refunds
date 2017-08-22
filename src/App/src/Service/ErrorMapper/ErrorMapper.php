<?php
namespace App\Service\ErrorMapper;

use Zend\Stdlib\ArrayUtils;

class ErrorMapper
{

    /**
     * Error mappings across the whole site.
     *
     * @var array
     */
    private $globalMap = array();

    /**
     * Error mappings across the whole site for a specific field.
     *
     * @var array
     */
    private $globalFieldMap = array();

    /**
     * Error mappings for local (page level) messages.
     *
     * @var array
     */
    private $localMap = array();

    /**
     * Error mappings for local (page level) messages, for a specific field.
     *
     * @var array
     */
    private $localFieldMap = array();


    public function addLocalMap(array $map)
    {
        $this->localMap = ArrayUtils::merge($this->localMap, $map);
    }

    public function addFieldMap(array $map)
    {
        $this->fieldMap = ArrayUtils::merge($this->fieldMap, $map);
    }

    public function getHumanMessage($field, $slug, $locale = 'en-GB')
    {
        $map = $this->globalMap;

        if (isset($this->globalFieldMap[$field])) {
            $map = ArrayUtils::merge($map, $this->globalFieldMap[$field]);
        }

        $map = ArrayUtils::merge($map, $this->localMap);

        if (isset($this->localFieldMap[$field])) {
            $map = ArrayUtils::merge($map, $this->localFieldMap[$field]);
        }

        return ($map[$slug]) ?? $slug;
    }
}
