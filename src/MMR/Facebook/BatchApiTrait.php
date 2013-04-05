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
    protected $files = [];
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

    public function attachFile($path)
    {
        $key = 'file'.(count($this->files) + 1);

        $this->files[$key] = '@'.realpath($path);
        $this->setFileUploadSupport(true);

        return $key;
    }

    /**
     * Process awaiting calls and resolve the temporary response given earlier
     *
     * @return void
     */
    public function processBatch($options = [])
    {
        if (count($this->batch) == 0)
            return;

        $queries = [];

        foreach ($this->batch as $key => &$value)
            $queries[$key] = $value['params'];

        $params = array_merge([
            'batch' => $queries,
        ], $this->files, $options);

        $batchResponse = $this->api('/', 'POST', $params);

        foreach ($this->batch as $key => &$value)
            $value['result'] = json_decode($batchResponse[$key]['body'], true);

        $this->batch = [];
        $this->files = [];
    }
}
