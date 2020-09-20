<?php
declare(strict_types=1);

namespace Wumvi\ReCallRequest;

use Wumvi\Curl\Curl;
use Wumvi\Curl\Pipe\PostMethodPipe;

class ReCallRequestService
{
    private ReCallRequestDao $reCallRequestDao;

    public function __construct(ReCallRequestDao $reCallRequestDao)
    {
        $this->reCallRequestDao = $reCallRequestDao;
    }

    public function add(string $url, string $method = 'GET', string $data = '')
    {
        $this->reCallRequestDao->add($url, $method, $data);
    }

    public function reCall()
    {
        $list = $this->reCallRequestDao->getAll();

        $postPipe = new PostMethodPipe();
        $curlGet = new Curl();
        $curlGet->setTimeout(4);
        $curlPost = new Curl();
        $curlPost->setTimeout(4);

        foreach ($list as $item) {
            $url = $item['url'];

            if ($item['method'] === 'GET') {
                $curlGet->setUrl($url);
                $code = $curlGet->exec()->getHttpCode();
            } else {
                $postPipe->setData($item['data']);
                $curlPost->setUrl($url);
                $curlPost->applyPipe($postPipe);
                $code = $curlPost->exec()->getHttpCode();
            }

            if ($code === 200) {
                $this->reCallRequestDao->removeById($item['id']);
            } else {
                $this->reCallRequestDao->incrementTry($item['id']);
            }
        }

        $curlGet->close();
        $curlPost->close();
    }
}