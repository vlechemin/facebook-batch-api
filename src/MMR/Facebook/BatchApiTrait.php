<?php

/**
 * Make batch calls transparently via the Facebook class
 *
 * @author vlechemin
 */
namespace MMR\Facebook;

trait BatchApiTrait
{
    protected $batch = [];
    static protected $MAX_BATCH = 50;

    /**
     * Execute a call to the api via batch
     * 
     * @param string  $url
     * @param string  $method
     * @param array   $data
     * @return &array A temporary response that will be resolved later
     */
    public function &batchApi($url, $method = 'GET', $data = [])
    {
        if (count($this->batch) == self::$MAX_BATCH)
            $this->processBatch();

        $query = [
            'params' => [
                'relative_url' => urlencode($url),
                'method' => $method,
                'body' => urlencode(http_build_query($data)),
            ],
            'result' => [],
        ];
        
        $this->batch[] = &$query;

        return $query['result'];
    }

    /**
     * Process awaiting calls and resolve the temporary response given earlier
     * 
     * @return void
     */
    public function processBatch()
    {
        if (count($this->batch) == 0)
            return;

        $queries = [];

        foreach ($this->batch as $key => &$value)
            $queries[$key] = $value['params'];

        $batchResponse = $this->api('?batch='.json_encode($queries), 'POST');

        foreach ($this->batch as $key => &$value)
            $value['result'] = json_decode($batchResponse[$key]['body'], true);

        $this->batch = [];
    }
}
