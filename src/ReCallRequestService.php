<?php
declare(strict_types=1);

namespace Wumvi\ReCallRequest;

use Wumvi\Curl\Curl;
use Wumvi\Curl\Exception\CurlConnectionTimeoutException;
use Wumvi\Curl\Exception\CurlTimeoutException;
use Wumvi\Curl\Pipe\PostMethodPipe;

class ReCallRequestService
{
    private ReCallRequestDao $reCallRequestDao;

    public function __construct(ReCallRequestDao $reCallRequestDao)
    {
        $this->reCallRequestDao = $reCallRequestDao;
    }

    public function addRecord(string $name, string $url, string $method = 'GET', string $data = '')
    {
        $this->reCallRequestDao->addRecord($name, $url, $method, $data);
    }

    public function reCall()
    {
        $list = $this->reCallRequestDao->getRecords();

        $postPipe = new PostMethodPipe();
        $curlGet = new Curl();
        $curlGet->setTimeout(4);
        $curlPost = new Curl();
        $curlPost->setTimeout(4);

        foreach ($list as $item) {
            $url = $item['url'];
            $recordId = (int)$item['id'];
            try {
                if ($item['method'] === 'GET') {
                    $curlGet->setUrl($url);
                    $code = $curlGet->exec()->getHttpCode();
                } else {
                    $postPipe->setData($item['data']);
                    $curlPost->setUrl($url);
                    $curlPost->applyPipe($postPipe);
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

            if ($code === 200) {
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
