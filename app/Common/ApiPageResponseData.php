<?php


namespace App\Common;

use App\Common\BaseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class ApiResponseData
 * @package App\Common\Api
 */
class ApiPageResponseData extends BaseType
{
    public $status;

    public $message;

    public $total;

    public $result;

    public $requested;

    /**
     * ApiResponseData constructor.
     * @param array $requested
     */
    public function __construct(Request $request)
    {
        if(env("APP_DEBUG") == true){
            $this->setRequested($request->toArray());
        }

        $info = $request->url().' '.$request->getClientIp()." ".$request->getHost(). " ".json_encode($request->input());

        Log::debug($info);
    }


    public function getAttributes(): array
    {
        // TODO: Implement getAttributes() method.
        return ["message", "status", "total", "result", "requested"];
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * @param array result
     */
    public function setResult(array $result)
    {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getRequested(): array
    {
        return $this->requested;
    }

    /**
     * @param array $requested
     */
    public function setRequested(array $requested)
    {
        $this->requested = $requested;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total)
    {
        $this->total = $total;
    }
}
