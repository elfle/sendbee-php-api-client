<?php


namespace Sendbee\Api\Transport;


use Exception;
use Sendbee\Api\Support\DataException;

class Response
{
    /**
     * @var bool is a 2xx HTTP response
     */
    protected $success = false;
    /**
     * @var int HTTP status
     */
    protected $httpStatus = 0;
    protected $data;
    protected $meta;
    protected $links;
    protected $warning;
    protected $error;

    public function __construct($httpStatus, $responseBody = '', $dataModelClass = null)
    {
        $this->httpStatus = $httpStatus;
        $this->success = ($httpStatus >= 200) && ($httpStatus < 300);

        try
        {
            $parsed = json_decode($responseBody, true);

            foreach (['meta', 'links', 'warning', 'error'] as $key)
            {
                if(array_key_exists($key, $parsed))
                {
                    $this->$key = $parsed[$key];
                }
            }


            if(array_key_exists('data', $parsed))
            {
                $data = $parsed['data'];

                // creates models if a class is specified and it exists
                if($dataModelClass && class_exists($dataModelClass))
                {

                    if($this->isPaginatedResponse())
                    {
                        // if we have pagination data, assume we received a collection of models back
                        $this->data = [];

                        foreach($data as $d)
                        {
                            $this->data[] = new $dataModelClass($d);
                        }
                    }
                    else
                    {
                        $this->data = new $dataModelClass($data);
                    }
                }
                else
                {
                    $this->data = $data;
                }
            }
        }
        catch (DataException $ex)
        {
            throw $ex;
        }

    }

    protected function isPaginatedResponse()
    {
        $meta = $this->getMeta();
        return (!empty($meta) && array_key_exists('total', $meta));
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @return int
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return mixed
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return mixed
     */
    public function getWarning()
    {
        return $this->warning;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }


}