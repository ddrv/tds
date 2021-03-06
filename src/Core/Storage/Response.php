<?php

namespace Cpa\TDS\Core\Storage;

use Exception;

class Response
{

    /**
     * @var string
     */
    protected $dir;

    /**
     * @var string
     */
    protected $tmp;

    /**
     * @param string $dir
     * @param string $tmp
     */
    public function __construct($dir, $tmp)
    {
        $this->dir = $dir;
        $this->tmp = $tmp;
    }

    /**
     * @param string $key
     * @return void
     */
    public function delete($key)
    {
        $file = $this->getFileName($key);
        if (!file_exists($file)) return;
        unlink($file);
    }

    /**
     * @param mixed $response
     * @return bool
     * @throws Exception
     */
    public function save($response)
    {
        $tmp = $this->tmp.DIRECTORY_SEPARATOR.uniqid();
        if (is_array($response) || is_object($response)) {
            $data = (array)$response;
        } else {
            if (file_exists($response)) {
                $response = file_get_contents($response);
            }
            $data = json_decode($response, true);
        }
        $this->check($data);
        $key = $data['key'];
        $file = $this->getFileName($key);

        $parameters = isset($data['extends'])?($this->getParameters($data['extends'], 2)):'array()';

        $className = 'Response'.mb_strtoupper(md5($key));
        $content = '<?php'.PHP_EOL.PHP_EOL;
        $content .= 'namespace Cpa\TDS\Binary\Response;'.PHP_EOL.PHP_EOL;
        $content .= 'use Cpa\TDS\Core\Response;'.PHP_EOL.PHP_EOL;
        $content .= '/**'.PHP_EOL;
        $content .= ' * Class '.$className.PHP_EOL;
        $content .= ' *'.PHP_EOL;
        $content .= ' * Response '.$key.'. This file automatically generated by '.__CLASS__.PHP_EOL;
        $content .= ' *'.PHP_EOL;
        $content .= ' * @see https://packagist.org/packages/cpa/tds'.PHP_EOL;
        $content .= ' * @see https://github.com/ddrv/cpa-tds'.PHP_EOL;
        $content .= ' */'.PHP_EOL;
        $content .= 'class '.$className.' extends Response'.PHP_EOL;
        $content .= '{'.PHP_EOL.PHP_EOL;
        $content .= '    /**'.PHP_EOL;
        $content .= '     * @var string'.PHP_EOL;
        $content .= '     */'.PHP_EOL;
        $content .= '    protected $key = \''.(string)$key.'\';'.PHP_EOL.PHP_EOL;
        $content .= '    /**'.PHP_EOL;
        $content .= '     * @var int'.PHP_EOL;
        $content .= '     */'.PHP_EOL;
        $content .= '    protected $status = '.(int)$data['status'].';'.PHP_EOL.PHP_EOL;
        $content .= '    /**'.PHP_EOL;
        $content .= '     * @var string[]'.PHP_EOL;
        $content .= '     */'.PHP_EOL;
        $content .= '    protected $headers = array('.PHP_EOL;
        foreach ($data['headers'] as $header) {
            $content .= '        \''.addslashes((string)$header).'\',' . PHP_EOL;
        }
        $content .= '    );'.PHP_EOL.PHP_EOL;
        if (!empty($data['cookies'])) {
            $content .= '    /**' . PHP_EOL;
            $content .= '     * @var string[]' . PHP_EOL;
            $content .= '     */' . PHP_EOL;
            $content .= '    protected $cookies = array(' . PHP_EOL;
            foreach ($data['cookies'] as $cookie) {
                $content .= '        \'' . addslashes((string)$cookie['name']) . '\' => array(' . PHP_EOL;
                $content .= '            \'value\' => \'' . addslashes((string)$cookie['value']) . '\',' . PHP_EOL;
                $content .= '            \'domain\' => \'' . (empty($cookie['domain'])?'':addslashes((string)$cookie['domain'])) . '\',' . PHP_EOL;
                $content .= '            \'path\' => \'' . (empty($cookie['path'])?'/':addslashes((string)$cookie['path'])) . '\',' . PHP_EOL;
                $content .= '            \'secure\' => ' . (empty($cookie['secure'])?'false':'true') . ',' . PHP_EOL;
                $content .= '            \'httpOnly\' => ' . (empty($cookie['httpOnly'])?'false':'true') . ',' . PHP_EOL;
                $content .= '            \'hours\' => ' . (empty($cookie['hours'])?'0':(int)$cookie['hours']) . ',' . PHP_EOL;
                $content .= '        ),' . PHP_EOL;
            }
            $content .= '    );' . PHP_EOL . PHP_EOL;
        }
        $content .= '    /**'.PHP_EOL;
        $content .= '     * @var array'.PHP_EOL;
        $content .= '     */'.PHP_EOL;
        $content .= '    protected $parameters = '.$parameters.';'.PHP_EOL.PHP_EOL;
        $content .= '    public function __construct()'.PHP_EOL;
        $content .= '    {'.PHP_EOL;
        $content .= '        $this->body = base64_decode(\''.base64_encode($data['body']).'\');'.PHP_EOL;
        $content .= '        parent::_construct();'.PHP_EOL;
        $content .= '    }'.PHP_EOL;
        $content .= '}'.PHP_EOL;
        file_put_contents($tmp, $content);
        rename($tmp, $file);
        return true;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getFileName($key)
    {
        return $this->dir.DIRECTORY_SEPARATOR.'response-'.$key.'.php';
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public static function check($data)
    {
        $data = (array)$data;
        $e = 'response error: ';
        if (!$data) throw new \Exception($e.'empty data');
        if (!array_key_exists('key', $data)) throw new \Exception($e.'property key is a required');
        if (!array_key_exists('status', $data)) throw new \Exception($e.'property status is a required');
        if (!array_key_exists('headers', $data)) throw new \Exception($e.'property headers is a required');
        if (!is_array($data['headers'])) throw new \Exception($e.'property headers must be an array');
        if (array_key_exists('cookies', $data) && !is_array($data['cookies'])) throw new \Exception($e.'property cookies must be an array');
        if (!array_key_exists('body', $data)) throw new \Exception($e.'property body is a required');
        if (!preg_match('/^[a-z0-9\-\._]+$/ui', $data['key'])) throw new \Exception($e.'incorrect key');
        if ($data['status'] != (int)$data['status']) throw new \Exception($e.'property status must be an integer');
        if ($data['body'] != (string)$data['body']) throw new \Exception($e.'property body must be a string');
        foreach ($data['headers'] as $num => $header) {
            if ($header != (string)$header) throw new \Exception($e.'property headers.'.$num.' must be a string');
        }
        if (!empty($data['cookies'])) {
            foreach ($data['cookies'] as $num => $cookie) {
                if (!array_key_exists('name', $cookie)) throw new \Exception($e . 'property cookies.' . $num . '.name is a required');
                if (!array_key_exists('value', $cookie)) throw new \Exception($e . 'property cookies.' . $num . '.value is a required');
                if ($cookie['name'] != (string)$cookie['name']) throw new \Exception($e . 'property cookies.' . $num . '.name must be a string');
                if ($cookie['value'] != (string)$cookie['value']) throw new \Exception($e . 'property cookies.' . $num . '.value must be a string');
                if (array_key_exists('hours', $cookie) && $cookie['hours'] != (int)$cookie['hours']) throw new \Exception($e . 'property cookies.' . $num . '.hours must be an integer');
            }
        }
    }

    /**
     * @param array $array
     * @param int $tab
     * @return string
     */
    protected function getParameters($array, $tab=0)
    {
        $result = 'array(';
        $array = (array)$array;
        foreach ($array as $key=>$value) {
            $result .= PHP_EOL.str_repeat('    ', $tab).'\''.addslashes($key).'\' => '.(is_array($value)?$this->getParameters($value, $tab+1):'\''.addslashes($value).'\',');
        }
        return $result.PHP_EOL.str_repeat('    ', $tab-1).')';
    }
}