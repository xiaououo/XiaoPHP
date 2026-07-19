<?php
/**
 * 数据库ORM操作类
 * Date: 2026-07-18
 * Author: 小新
 * SystemName: XiaoPHP
 */

namespace XiaoPHP\System\Tools\App;

use XiaoPHP\System\Config\Conf;

class MysqlTools
{
    private $pdo;

    function __construct()
    {
        try {
            $conf = Conf::get("Mysql");
            $dsn =
                "mysql:host=" . $conf["host"] . ";port=" . $conf["port"] . ";dbname=" . $conf["dbname"];
            $this->pdo = new \PDO($dsn, $conf["user"], $conf["password"]);
            
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
        } catch (\PDOException $e) {
            $this->error($e->getMessage());
        }
    }

    private function sanitizeTableName(string $table): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    }

    private function sanitizeColumnName(string $column): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    private $table = "";
    private $limit = "";
    private $offset = "";
    private $orderBy = "";
    private $where = [];
    private $whereLike = [];

    function table($table)
    {
        $this->table = $this->sanitizeTableName($table);
        $this->where = [];
        $this->whereLike = [];
        $this->limit = "";
        $this->offset = "";
        $this->orderBy = "";
        return $this;
    }

    function limit($n)
    {
        $this->limit = (int) $n;
        return $this;
    }

    function offset($n)
    {
        $this->offset = (int) $n;
        return $this;
    }

    function where($column, $value)
    {
        $column = $this->sanitizeColumnName($column);
        if (is_array($value)) {
            $in = [];
            foreach ($value as $k => $v) {
                $key = ":" . $column . "_in_" . $k;
                $in[] = $key;
                $this->where["bind"][$key] = $v;
            }
            $this->where[] = "`$column` IN (" . implode(",", $in) . ")";
        } else {
            if (preg_match('/^(>=|<=|>|<|!=)\s*(.+)$/s', $value, $matches)) {
                $op = $matches[1];
                $val = $matches[2];
            } else {
                $op = "=";
                $val = $value;
            }
            $key = ":" . $column;
            $this->where["bind"][$key] = $val;
            $this->where[] = "`$column` $op $key";
        }
        return $this;
    }

    function whereLike($column, $value, $position = "both")
    {
        $column = $this->sanitizeColumnName($column);
        if ($position == "left") {
            $likeValue = "%" . $value;
        } elseif ($position == "right") {
            $likeValue = $value . "%";
        } else {
            $likeValue = "%" . $value . "%";
        }
        $key = ":" . $column . "_like";
        $this->where["bind"][$key] = $likeValue;
        $this->where[] = "`$column` LIKE $key";
        return $this;
    }

    function whereMultiLike($columns, $value)
    {
        $parts = [];
        foreach ($columns as $i => $col) {
            $col = $this->sanitizeColumnName($col);
            $key = ":multi_like_" . $i;
            $this->where["bind"][$key] = "%" . $value . "%";
            $parts[] = "`$col` LIKE $key";
        }
        if (!empty($parts)) {
            $this->where[] = "(" . implode(" OR ", $parts) . ")";
        }
        return $this;
    }

    function whereFullLike($value)
    {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM `{$this->table}`");
        $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $parts = [];
        foreach ($columns as $i => $col) {
            $col = $this->sanitizeColumnName($col);
            $key = ":full_like_" . $i;
            $this->where["bind"][$key] = "%" . $value . "%";
            $parts[] = "`$col` LIKE $key";
        }
        if (!empty($parts)) {
            $this->where[] = "(" . implode(" OR ", $parts) . ")";
        }
        return $this;
    }

    function order($column, $direction = 'ASC')
    {
        $column = $this->sanitizeColumnName($column);
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy = "`$column` $direction";
        return $this;
    }

    function getMinMaxId()
    {
        $sql = "SELECT MIN(id) AS min_id,MAX(id) AS max_id FROM `{$this->table}`";
        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [
            "min_id" => (int) ($row["min_id"] ?? 0),
            "max_id" => (int) ($row["max_id"] ?? 0),
        ];
    }

    function count(): int
    {
        $where = "";
        $binds = [];
        if (!empty($this->where)) {
            $binds = $this->where["bind"] ?? [];
            unset($this->where["bind"]);
            $where = " WHERE " . implode(" AND ", $this->where);
        }
        $sql = "SELECT COUNT(*) AS total FROM `{$this->table}`{$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) ($row["total"] ?? 0);
    }

    function sum(string $column): float
    {
        $column = $this->sanitizeColumnName($column);
        $where = "";
        $binds = [];
        if (!empty($this->where)) {
            $binds = $this->where["bind"] ?? [];
            unset($this->where["bind"]);
            $where = " WHERE " . implode(" AND ", $this->where);
        }
        $sql = "SELECT SUM(`$column`) AS total FROM `{$this->table}`{$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (float) ($row["total"] ?? 0);
    }

    function get()
    {
        $where = "";
        $binds = [];
        if (!empty($this->where)) {
            $binds = $this->where["bind"] ?? [];
            unset($this->where["bind"]);
            $where = " WHERE " . implode(" AND ", $this->where);
        }
        $sql = "SELECT * FROM `{$this->table}`{$where}";
        if ($this->orderBy !== "") {
            $sql .= " ORDER BY " . $this->orderBy;
        }
        if ($this->limit !== "") {
            $sql .= " LIMIT " . $this->limit;
            if ($this->offset !== "") {
                $sql .= " OFFSET " . $this->offset;
            }
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $this->where = [];
        $this->limit = "";
        $this->offset = "";
        $this->orderBy = "";
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    function first()
    {
        $where = "";
        $binds = [];
        if (!empty($this->where)) {
            $binds = $this->where["bind"] ?? [];
            unset($this->where["bind"]);
            $where = " WHERE " . implode(" AND ", $this->where);
        }
        $sql = "SELECT * FROM `{$this->table}`{$where}";
        if ($this->orderBy !== "") {
            $sql .= " ORDER BY " . $this->orderBy;
        }
        $sql .= " LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $this->where = [];
        $this->orderBy = "";
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    function delete()
    {
        if (empty($this->where)) {
            $this->error("DELETE 操作必须带 WHERE 条件");
        }
        $binds = $this->where["bind"] ?? [];
        unset($this->where["bind"]);
        $where = " WHERE " . implode(" AND ", $this->where);
        $sql = "DELETE FROM `{$this->table}`{$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $this->where = [];
        return $stmt->rowCount();
    }

    function update($data)
    {
        if (empty($this->where)) {
            $this->error("UPDATE 操作必须带 WHERE 条件");
        }
        $set = [];
        $binds = [];
        foreach ($data as $col => $val) {
            $col = $this->sanitizeColumnName($col);
            $key = ":set_" . $col;
            $set[] = "`$col`=$key";
            $binds[$key] = $val;
        }
        $whereBinds = $this->where["bind"] ?? [];
        unset($this->where["bind"]);
        $where = " WHERE " . implode(" AND ", $this->where);
        $binds = array_merge($binds, $whereBinds);
        $sql = "UPDATE `{$this->table}` SET " . implode(",", $set) . $where;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $this->where = [];
        return $stmt->rowCount();
    }

    function insert($data)
    {
        $columns = [];
        $placeholders = [];
        $binds = [];
        foreach ($data as $col => $val) {
            $col = $this->sanitizeColumnName($col);
            $columns[] = "`$col`";
            $key = ":ins_" . $col;
            $placeholders[] = $key;
            $binds[$key] = $val;
        }
        $sql =
            "INSERT INTO `{$this->table}` (" .
            implode(",", $columns) .
            ") VALUES (" .
            implode(",", $placeholders) .
            ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        return $this->pdo->lastInsertId();
    }

    function close()
    {
        $this->pdo = null;
    }
    function error($msg)
    {
        $info = [
            "code" => 500,
            "msg" => "数据库错误",
            "data" => ["msg" => $msg],
        ];
        header("Content-Type: application/json");
        echo json_encode($info);
        exit();
    }
}