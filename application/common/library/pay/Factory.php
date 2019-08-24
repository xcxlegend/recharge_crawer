<?php

namespace app\common\library\pay;

/**
 * Pay支付类生成工厂
 * Class Factory
 * @package app\common\library\pay
 */
class Factory
{
    static public function create($code, InitParam $params): ?IPay {
        $classname = 'app\\common\\library\\pay\\' . ucfirst($code) . 'Pay';
        if (class_exists($classname)) {
            $class = new $classname($params);
            if ($class instanceof IPay) {
                return $class;
            }
        }
        return null;
    }


}