<?php
/**
 * 模型操作函数
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */
namespace XiaoPHP\System;

use XiaoPHP\System\Tools\App\MysqlTools;

class Model
{
    protected $db;
    protected $table = null;

    /**
     * 构造方法：初始化数据库工具
     */
    public function __construct()
    {
        $this->db = new MysqlTools();
    }

    /**
     * 根据主键查找一条记录
     * @param int|string $id
     * @return array|false 返回关联数组，找不到返回 false
     */
    public function find($id)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->first();
    }

    /**
     * 获取所有记录（可带简单条件）
     * @param array $where 如 ['status' => 1]
     * @return array
     */
    public function all($where = [])
    {
        $query = $this->db->table($this->table);
        foreach ($where as $key => $value) {
            $query->where($key, $value);
        }
        return $query->get();
    }

    /**
     * 插入一条新记录
     * @param array $data 字段 => 值
     * @return int|false 返回插入的ID，失败返回 false
     */
    public function create($data)
    {
        return $this->db->table($this->table)->insert($data);
    }

    /**
     * 根据主键更新记录
     * @param int|string $id
     * @param array $data 要更新的字段
     * @return int 影响的行数
     */
    public function update($id, $data)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update($data);
    }

    /**
     * 根据主键删除记录（硬删除）
     * @param int|string $id
     * @return int 影响的行数
     */
    public function delete($id)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->delete();
    }

    /**
     * 通用保存方法：自动判断是插入还是更新
     * @param array $data 数据
     * @param int|null $id 若提供则更新，否则插入
     * @return int|false 插入返回ID，更新返回影响行数
     */
    public function save($data, $id = null)
    {
        if ($id === null) {

            return $this->create($data);
        } else {
            $exists = $this->find($id);
            if (!$exists) {
                return false;
            }
            return $this->update($id, $data);
        }
    }

    /**
     * 获取分页数据（简单分页）
     * @param int $page 当前页码
     * @param int $size 每页条数
     * @param array $where 条件
     * @return array ['data' => [], 'total' => 总数]
     */
    public function paginate($page = 1, $size = 10, $where = [])
    {
        $offset = ($page - 1) * $size;
        $query = $this->db->table($this->table);
        foreach ($where as $key => $value) {
            $query->where($key, $value);
        }
        $total = $query->count();
        $data = $this->db->table($this->table);
        foreach ($where as $key => $value) {
            $data->where($key, $value);
        }
        $data = $data->limit($size)->offset($offset)->get();
        return [
            'data'  => $data,
            'total' => $total,
            'page'  => $page,
            'size'  => $size,
        ];
    }

    /**
     * 返回查询构造器实例，用于复杂查询（链式）
     */
    public function db()
    {
        return $this->db->table($this->table);
    }


    /**
     * 获取表名
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * 开启事务
     */

    public function beginTransaction()
    {
        $this->db->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->db->pdo->commit();
    }

    public function rollBack()
    {
        $this->db->pdo->rollBack();
    }
}