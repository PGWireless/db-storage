<?php

namespace DBStorage\Codec;

use DateTime;
use DateTimeZone;

/**
 * 阿里云 KMS 服务
 * 功能：获取凭据
 */
class AliyunKMSService implements SecretKeyGetterInterface
{
    // 请求地址：{@link https://help.aliyun.com/document_detail/69006.html}

    // 新加坡
    const HOST_SINGAPORE     = 'kms.ap-southeast-1.aliyuncs.com';
    const HOST_SINGAPORE_VPC = 'kms-vpc.ap-southeast-1.aliyuncs.com';
    // 华东1（杭州）
    const HOST_HANGZHOU      = 'kms.cn-hangzhou.aliyuncs.com';
    const HOST_HANGZHOU_VPC  = 'kms-vpc.cn-hangzhou.aliyuncs.com';


    public $accessKeyId;

    private $host;
    private $accessKeySecret;

    /** @var SecretKeyCacheInterface */
    private $_cache;

    private $_commonParams = [
        'Version' => '2016-01-20',
        'AccessKeyId' => '',
        'SignatureMethod' => 'HMAC-SHA1',
        'Timestamp' => '', // 2013-01-10T12:00:00Z
        'SignatureVersion' => '1.0',
    ];

    /**
     * __construct
     *
     * @param string $host 请求主机 见上面定义的常量 self::HOST_*
     * @param string $accessKeyId 服务授权账号ID，不存在时将从环境变量 `KMS_ACCESS_ID` 获取
     * @param string $accessKeySecret 服务授权账号密钥，不存在时将从环境变量 `KMS_ACCESS_SECRET` 获取
     * @param SecretKeyCacheInterface|null $cache
     */
    public function __construct($host, $accessKeyId = '', $accessKeySecret = '', SecretKeyCacheInterface $cache = null)
    {
        $this->host = $host;

        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        if ($cache === null) {
            $cache = SecretKeyMemCache::instance();
        }

        if (!$this->accessKeyId && isset($_SERVER['KMS_ACCESS_ID'])) {
            $this->accessKeyId = $_SERVER['KMS_ACCESS_ID'];
        }
        if (!$this->accessKeySecret && isset($_SERVER['KMS_ACCESS_SECRET'])) {
            $this->accessKeySecret = $_SERVER['KMS_ACCESS_SECRET'];
        }

        $this->_cache = $cache;
    }

    private $_arrayCache = [];

    /** @inheritDoc */
    public function getSecretKey($name)
    {
        if (isset($this->_arrayCache[$name])) {
            return $this->_arrayCache[$name];
        }

        if ($this->_cache) {
            $value = $this->_cache->get($name);
            if ($value !== false) {
                $this->_arrayCache[$name] = $value;
                return $value;
            }
        }

        $value = false;
        $maxRetry = 3;
        for ($i = 0; $i < $maxRetry; $i++) {
            list($status, $header, $data) = $this->getSecretValue($name);
            if ($status != 200) {
                sleep(($i + 1) * 1);
            } else {
                $value = $data['SecretData'];
                break;
            }
        }

        if ($value) {
            $this->_arrayCache[$name] = $value;
            if ($this->_cache) {
                $this->_cache->set($name, $value);
            }
        }

        return $value;
    }

    /**
     * 调用阿里云接口
     *
     * @return array [http status, header, body]
     */
    public function getSecretValue($secretName)
    {
        $ts = new DateTime('now', new DateTimeZone('UTC'));

        $params = [
            'Action'      => 'GetSecretValue',
            'SecretName'  => $secretName,
            'AccessKeyId' => $this->accessKeyId,
            'Timestamp'   => $ts->format('Y-m-d\TH:i:s\Z'),
        ];

        $params = array_merge($this->_commonParams, $params);
        $sign = static::signature('GET', $params, $this->accessKeySecret);
        $params['Signature'] = $sign;

        return static::_curlRequest('https://' . $this->host, 'GET', $params);
    }

    /**
     * 阿里云请求签名
     *
     * @param string $httpMethod
     * @param array $params
     * @param string $accessKeySecret
     * @return string
     */
    public static function signature($httpMethod, array $params, $accessKeySecret)
    {
        unset($params['Signature']);
        ksort($params);

        $s = http_build_query($params);
        $s = sprintf('%s&%%2F&%s', $httpMethod, urlencode($s));

        return base64_encode(hash_hmac('sha1', $s, $accessKeySecret . '&', true));
    }

    private static function _curlRequest($url, $method = 'GET', $data = null, $headers = [])
    {
        $isPost = strcasecmp('POST', $method) === 0;
        $headers = array_map('strtolower', $headers);
        if (isset($headers['content-type']) && stripos($headers['content-type'], 'application/json') !== false) {
            if ($data && is_array($data)) {
                $data = json_encode($data);
            }
        } elseif ($data) {
            $data = http_build_query($data);
            if (!$isPost) { // GET
                $url .= '?' . $data;
            }
        }

        $ch = curl_init();
        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => 6,
        ];

        if ($isPost) {
            $opts[CURLOPT_POST] = 1;
        }
        if ($data && $isPost) {
            $opts[CURLOPT_POSTFIELDS] = $data;
            $headers['content-length'] = strlen($data);
        }
        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);

        if (0 != curl_errno($ch)) {
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        list($respHeader, $body) = explode("\r\n\r\n", $response, 2);
        $arrHeaders = explode("\r\n", $respHeader);
        $proto = explode(' ', $arrHeaders[0], 3);

        $contentType = '';
        $respHeader  = [];
        for ($i = 1, $len = count($arrHeaders); $i < $len; $i++) {
            list($key, $val) = explode(':', $arrHeaders[$i], 2);
            $respHeader[$key] = trim($val);
            if (!$contentType) {
                if (strcasecmp($key, 'content-type') === 0) {
                    $contentType = $val;
                }
            }
        }

        if (stripos($contentType, 'application/json') !== false) {
            $body = json_decode($body, true);
        }

        return [intval($proto[1]), $respHeader, $body];
    }
}
