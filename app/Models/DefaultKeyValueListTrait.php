<?php
/**
 * Created by PhpStorm.
 * Staff: JCY
 * Date: 10/31/2019
 * Time: 4:44 PM
 */

namespace App\Models;

use Illuminate\Support\Facades\Cache;

/**
 *
 * Trait DefaultKeyValueListTrait
 * @package App\Models\Traits
 */
trait DefaultKeyValueListTrait
{
    static public function getColumnNameOfListValue(){
        return 'name';
    }

    static public function getColumnNameOfListKey(){
        return 'id';
    }

    static public function getColumnNameOfListWhere(){
        return 'type';
    }

    static public function getColumnNameOfListOrderBy(){
        return 'sort';
    }

    static public function getColumnNameOfListCacheKey(){
        return self::class.'KeyValueList';
    }

    static public function getKeyValueList($where=null, $pluck=false, $cache=true){
        $orderBy = self::getColumnNameOfListOrderBy();
        $label = self::getColumnNameOfListValue();
        $key = self::getColumnNameOfListKey();
        $whereColumn = self::getColumnNameOfListWhere();

        if ($cache) {
            return Cache::rememberForever(self::getColumnNameOfListCacheKey() . $where. $pluck, function () use ($orderBy, $key, $label, $whereColumn, $where, $pluck) {
                $list = self::select($key, $label);

                if (isset($where)) {
                    $list = $list->where($whereColumn, $where);
                }

                if (isset($orderBy)) {
                    $list = $list->orderBy($orderBy);
                }

                if ($pluck) {
                    $list = $list->pluck($label,$key);
                }

                return $list->get();
            });
        }
        else {
            $list = self::select($key, $label);

            if (isset($where)) {
                $list = $list->where($whereColumn, $where);
            }

            if (isset($orderBy)) {
                $list = $list->orderBy($orderBy);
            }

            if ($pluck) {
                $list = $list->pluck($label,$key);
            }

            return $list->get();
        }

    }
}
