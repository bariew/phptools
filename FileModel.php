<?php
/**
 * FileModel class file.
 * @copyright (c) 2014, bariew
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\phptools;

/**
 * FileModel - for extracting and saving data to file.
 * @author Pavel Bariev <bariew@yandex.ru>
 */

class FileModel
{
    /**
     * @var string path to read content from.
     */
    private $readPath;

    /**
     * @var string path to write content to.
     */
    private $writePath;

    /**
     * @var int file type. See TYPE constants.
     */
    private $fileType = 0;

    /**
     * @var mixed file data.
     */
    public $data;

    const TYPE_PHP = 0;
    const TYPE_JSON = 1;

    /**
     * Setting model options. Read path is required.
     * @param string $readPath path to file.
     * @param array $options
     */
    public function __construct($readPath, $options = [])
    {
        $this->readPath = $readPath;
        foreach ($options as $attribute => $value) {
            $this->$attribute = $value;
        }
        $this->data = $this->readData();
        $this->writePath = $this->writePath ? $this->writePath : $this->readPath;
    }

    /**
     * Reading file data.
     * @return mixed
     */
    private function readData()
    {
        switch ($this->fileType) {
            case self::TYPE_JSON :
                $data = json_decode(file_get_contents($this->readPath), true);
                break;
            default : $data = require $this->readPath;
        }
        return $data;
    }

    /**
     * Putting data to file.
     * You may put into depth of multidimensional array
     * when your key is array.
     * e.g. $this->set(['a', 'b'], 1) will set data to ['a' => ['b' => 1 ... ] ...].
     * @param array|string $key
     * @param mixed $value
     * @return int
     */
    public function set($key, $value)
    {
        $config = $this->data;
        if (!$key) {
            $config = array_merge($config, $value);
        }
        $key = is_array($key) ? $key : [$key];

        $data = &$config;
        while ($key) {
            $k = array_shift($key);
            $config[$k] = isset($config[$k]) ? $config[$k] : [];
            $config[$k] = $key
                ? $config[$k]
                : (is_array($value)
                    ? array_merge($config[$k], $value)
                    : $value);
            $config = &$config[$k];
        }
        $this->data = $data;
        return $this->save();
    }

    /**
     * Gets data sub value.
     * @see \self::set() For multidimensional
     * @param $key
     * @return array|mixed
     */
    public function get($key)
    {
        $key = is_array($key) ? $key : [$key];
        $config = $this->data;
        while ($key) {
            $k = array_shift($key);
            $config = isset($config[$k]) ? $config[$k] : [];
        }
        return $config;
    }

    /**
     * Removes key from data.
     * @see \self::set() For multidimensional
     * @param $key
     * @return int
     */
    public function remove($key)
    {
        $key = is_array($key) ? $key : [$key];
        $config = $this->data;
        $data = &$config;
        while ($key) {
            $k = array_shift($key);
            if (!$key) {
                unset($config[$k]);
                break;
            }
            $config[$k] = isset($config[$k]) ? $config[$k] : [];
            $config = &$config[$k];
        }
        $this->data = $data;
        return $this->save();
    }

    /**
     * Puts complete data into file.
     * @param $data
     * @return int
     * @throws \Exception
     */
    public function put($data)
    {
        $this->data = array_merge($this->data, $data);
        return $this->save();
    }

    /**
     * Saves $this->data to file.
     * @return int
     */
    public function save()
    {
        switch ($this->fileType) {
            case self::TYPE_JSON :
                $content = json_encode($this->data);
                break;
            default :  $content = '<?php return '. var_export($this->data, true) . ';';
        }
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        return file_put_contents($this->writePath, $content);
    }
} 