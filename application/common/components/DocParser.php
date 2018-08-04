<?php
/**
 * Created by PhpStorm.
 * User: hh-pc
 * Date: 2017/5/11
 * Time: 11:35
 */

namespace app\common\components;

/**
 * 解析注释信息
 * Class DocParser
 */
class DocParser
{
    /**
     * 结果
     * @var array
     */
    private $params = [];

    /**
     * 解析
     * @param string $doc
     * @return array
     */
    public function parse($doc = '')
    {
        if ($doc == '') {
            return $this->params;
        }
        // Get the comment
        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment) === false) {
            return $this->params;
        }
        // Get all the lines and strip the * from the first character
        if (preg_match_all('#^\s*\*(.*)#m', trim($comment[1]), $lines) === false) {
            return $this->params;
        }
        $this->parseLines($lines[1]);
        return $this->params;
    }

    /**
     * 解析行
     * @param $lines
     */
    private function parseLines($lines)
    {
        foreach ($lines as $line) {
            $parsedLine = $this->parseLine($line); // Parse the line
            if ($parsedLine === false && !isset($this->params['description'])) {
                if (isset($desc)) {
                    // Store the first line in the short description
                    $this->params['description'] = implode(PHP_EOL, $desc);
                }
                $desc = array();
            } elseif ($parsedLine !== false) {
                $desc[] = $parsedLine; // Store the line in the long description
            }
        }
        $desc = implode(' ', $desc);
        if (!empty($desc)) {
            $this->params['long_description'] = $desc;
        }
    }

    /**
     * @param $line
     * @return bool|string
     */
    private function parseLine($line)
    {
        // trim the whitespace from the line
        $line = trim($line);
        if (empty($line)) {
            return false; // Empty line
        }
        if (strpos($line, '@') === 0) {
            if (strpos($line, ' ') > 0) {
                // Get the parameter name
                $param = substr($line, 1, strpos($line, ' ') - 1);
                $value = substr($line, strlen($param) + 2); // Get the value
            } else {
                $param = substr($line, 1);
                $value = '';
            }
            // Parse the line and return false if the parameter is valid
            if ($this->setParam($param, $value)) {
                return false;
            }
        }
        return $line;
    }

    /**
     * @param $param
     * @param $value
     * @return bool
     */
    private function setParam($param, $value)
    {
        if ($param == 'param' || $param == 'return') {
            $value = $this->formatParamOrReturn($value);
        }
        if ($param == 'class') {
            list($param, $value) = $this->formatClass($value);
        }
        if (empty($this->params[$param])) {
            $this->params[$param] = $value;
        } else if ($param == 'param') {
            $arr = array(
                $this->params[$param],
                $value
            );
            $this->params[$param] = $arr;
        } else {
            $this->params[$param] = $value + $this->params[$param];
        }
        return true;
    }

    /**
     * @param $value
     * @return array
     */
    private function formatClass($value)
    {
        $r = preg_split("[|]", $value);
        if (is_array($r)) {
            $param = $r[0];
            parse_str($r[1], $value);
            foreach ($value as $key => $val) {
                $val = explode(',', $val);
                if (count($val) > 1)
                    $value[$key] = $val;
            }
        } else {
            $param = 'Unknown';
        }
        return array(
            $param,
            $value
        );
    }

    /**
     *参数
     * @param $string
     * @return string
     */
    private function formatParamOrReturn($string)
    {
        $pos = strpos($string, ' ');
        $type = substr($string, 0, $pos);
        return '(' . $type . ')' . substr($string, $pos + 1);
    }

    /**
     * 重置
     */
    public function reset()
    {
        $this->params = [];
    }

    /**
     * @param $doc
     * @param $paramsName
     * @param string $default
     * @return mixed|string
     */
    public static function getParamsValue($doc, $paramsName, $default = '')
    {
        static $self;
        if (!$self) {
            $self = new DocParser();
        } else {
            $self->reset();
        }
        $params = $self->parse($doc);
        return isset($params[$paramsName]) ? $params[$paramsName] : $default;
    }
}