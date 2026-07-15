<?php
namespace app\tools;

use XiaoPHP\systools\Config\Conf;

class MysqlTools
{
    var $pdo = "";

    function __construct()
    {
        try {
            $conf = Conf::get("Mysql");
            $dsn =
                "mysql:host=" . $conf["host"] . ";port=" . $conf["port"] . ";dbname=" . $conf["dbname"];
            $this->pdo = new PDO($dsn, $conf["user"], $conf["password"]);
        } catch (PDOException $e) {
            $this->error($e->getMessage());
        }
    }
    var $table = "";
    var $limit = "";
    var $offset = "";
    function table($table)
    {
        $this->table = $table;
        $this->where = [];
        $this->whereLike = [];
        $this->limit = "";
        $this->offset = "";
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
    var $where = [];
    function where($column, $value)
    {
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
    var $whereLike = [];
    function whereLike($column, $value, $position = "both")
    {
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
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $parts = [];
        foreach ($columns as $i => $col) {
            $key = ":full_like_" . $i;
            $this->where["bind"][$key] = "%" . $value . "%";
            $parts[] = "`$col` LIKE $key";
        }
        if (!empty($parts)) {
            $this->where[] = "(" . implode(" OR ", $parts) . ")";
        }
        return $this;
    }
    function getMinMaxId()
    {
        $sql = "SELECT MIN(id) AS min_id,MAX(id) AS max_id FROM `{$this->table}`";
        $stmt = $this->pdo->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            "min_id" => (int) ($row["min_id"] ?? 0),
            "max_id" => (int) ($row["max_id"] ?? 0),
        ];
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $sql = "SELECT * FROM `{$this->table}`{$where} LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($binds);
        $this->where = [];
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

function db()
{
    return new MysqlTools();
}