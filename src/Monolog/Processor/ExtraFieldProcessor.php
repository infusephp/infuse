<?php

namespace Monolog\Processor;

/**
 * Injects fields from the current web request ($_SERVER) in all records. Based
 * off of the WebProcessor that comes out of the box with Monolog.
 *
 * @author Jared King <j@jaredtking.com>
 */
class ExtraFieldProcessor
{
    /**
     * @var array|\ArrayAccess
     */
    protected $serverData;

    /**
     * @var array
     */
    protected $extraFields = array();

    /**
     * @param array|\ArrayAccess $serverData  Array or object w/ ArrayAccess that provides access to the $_SERVER data
     * @param array              $extraFields Extra field names to be added mapping to $serverData
     */
    public function __construct($serverData = null, array $extraFields)
    {
        if (null === $serverData) {
            $this->serverData =& $_SERVER;
        } elseif (is_array($serverData) || $serverData instanceof \ArrayAccess) {
            $this->serverData = $serverData;
        } else {
            throw new \UnexpectedValueException('$serverData must be an array or object implementing ArrayAccess.');
        }

        if (is_array($extraFields)) {
            $this->extraFields = $extraFields;
        } else {
            throw new \UnexpectedValueException('$extraFields must be an array.');
        }
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // skip processing if for some reason request data
        // is not present (CLI or wonky SAPIs)
        if (!isset($this->serverData['REQUEST_URI'])) {
            return $record;
        }

        $record['extra'] = $this->appendExtraFields($record['extra']);

        return $record;
    }

    /**
     * @param  array $extra
     * @return array
     */
    private function appendExtraFields(array $extra)
    {
        foreach ($this->extraFields as $extraName => $serverName) {
            $extra[$extraName] = isset($this->serverData[$serverName]) ? $this->serverData[$serverName] : null;
        }

        if (isset($this->serverData['UNIQUE_ID'])) {
            $extra['unique_id'] = $this->serverData['UNIQUE_ID'];
        }

        return $extra;
    }
}
