<?php
declare(strict_types=1);

namespace Wumvi\ReCallRequest;

use Wumvi\Curl\ContentType;
use Wumvi\Curl\Curl;
use Wumvi\Curl\Exception\CurlConnectionTimeoutException;
use Wumvi\Curl\Exception\CurlTimeoutException;
use Wumvi\Curl\Pipe\HeaderPipe;
use Wumvi\Curl\Pipe\PostMethodPipe;

class ReCallRequestService
{
    private ReCallRequestDao $reCallRequestDao;

    public function __construct(ReCallRequestDao $reCallRequestDao)
    {
        $this->reCallRequestDao = $reCallRequestDao;
    }

    public function addRecord(
        string $name,
        string $url,
        string $method = 'GET',
        string $data = '',
        array $headers = []
    )
    {
        $headers = json_encode($headers);
        $this->reCallRequestDao->addRecord($name, $url, $method, $data, $headers);
    }

    public function httpSend(
        string $name,
        string $url,
        string $method = 'GET',
        string $postData = '',
        array $headers = []
    )
    {
        try {
            $curl = new Curl();
            $headerPipe = new HeaderPipe($headers);
            if ($method === 'POST') {
                $postPipe = new PostMethodPipe();
                $postPipe->setData($postData);
                $curl->applyPipe($postPipe);
            }
            $curl->applyPipe($headerPipe);
            $curl->setUrl($url);
            $response = $curl->exec();
            $code = $response->getHttpCode();
            if (200 <= $code && $code <= 299) {
                $this->addRecord($name, $url, $method, $postData, $headers);
            }
        } catch (\Exception $ex) {
            $this->addRecord($name, $url, $method, $postData, $headers);
        }
    }

    public function reCall()
    {
        $list = $this->reCallRequestDao->getRecords();

        $postPipe = new PostMethodPipe();
        $curlGet = new Curl();
        $curlGet->setTimeout(4);
        $curlPost = new Curl();
        $curlPost->setTimeout(4);
        $headerPipe = new HeaderPipe([]);

        foreach ($list as $item) {
            $url = $item['url'];
            $recordId = (int)$item['id'];
            try {
                if ($item['method'] === 'GET') {
                    $curlGet->setUrl($url);
                    $code = $curlGet->exec()->getHttpCode();
                } else {
                    $headers = json_decode($item['headers'], true, 4, JSON_THROW_ON_ERROR);
                    $contentType = $headers[ContentType::CONTENT_TYPE] ?? '';
                    $postPipe->setData($item['data'], $contentType . '');
                    $curlPost->setUrl($url);
                    $curlPost->applyPipe($postPipe);
                    if (!empty($headers)) {
                        $headerPipe->setHeader($headers);
                        $curlPost->applyPipe($headerPipe);
                    }
                    $code = $curlPost->exec()->getHttpCode();
                }
            } catch (CurlTimeoutException | CurlConnectionTimeoutException $ex) {
                $type = $ex instanceof CurlTimeoutException ? 'read' : 'connect';
                $this->reCallRequestDao->setErrorToRecord($recordId, $type . ' timeout');
                continue;
            } catch (\Throwable $ex) {
                $this->reCallRequestDao->setErrorToRecord($recordId, $ex->getMessage());
                continue;
            }

            if (200 <= $code && $code <= 299) {
                $this->reCallRequestDao->removeRecord($recordId);
            } else {
                $error = 'Code status ' . $code;
                $this->reCallRequestDao->setErrorToRecord($recordId, $error);
            }
        }

        $curlGet->close();
        $curlPost->close();
    }
}
