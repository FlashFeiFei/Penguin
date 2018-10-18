<?php
/**
 * Created by PhpStorm.
 * User: admin123
 * Date: 2018/9/20
 * Time: 14:06
 */

namespace LyPenguin\Support;


class Arr
{

    /**
     * 通过key中的 . 来深度的存储 $value
     * 如果没有给key值存储value
     * @param $array
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * 通过key，深度的获取值
     * @param $array
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }
        //先判断是否是顶层的，是顶层的直接返回
        if (isset($array[$key])) {
            return $array[$key];
        }
        //非顶层的的处理
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                //没有遍历到最低的层的时候，中间发生了错误，返回默认值
                return $default;
            }
            //获取下一层
            $array = $array[$segment];
        }
        //返回最底层的那个值
        return $array;
    }

    /**
     * 批量删除某节点，（节点是树形的）
     *
     * @param array $array
     * @param array|string $keys
     */
    public static function forget(&$array, $keys)
    {
        $original = &$array;
        //深度清除一个值，树形结构
        foreach ((array)$keys as $key) {
            $parts = explode('.', $key);
            while (count($parts) > 1) {
                $part = array_shift($parts);
                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                }
            }
            unset($array[array_shift($parts)]);
            // clean up after each pass
            $array = &$original;
        }
    }

    /**
     * 获取所有给定数组，但指定的项数组除外。
     *
     * @param array $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function except($array, $keys)
    {
        return array_diff_key($array, array_flip((array)$keys));
    }
}