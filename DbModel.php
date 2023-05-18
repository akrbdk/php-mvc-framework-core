<?php

namespace App\Core;

abstract class DbModel extends Model
{
    public function save()
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $params = array_map(fn($attribute) => ":$attribute", $attributes);
        $statement = static::prepare("INSERT INTO $tableName (" . implode(',', $attributes) . ")
            VALUES(" . implode(',', $params) . ")");

        foreach ($attributes as $attribute) {
            $statement->bindValue(":$attribute", $this->{$attribute});
        }

        $statement->execute();
        return true;
    }

    abstract public function tableName(): string;

    abstract public function attributes(): array;

    public static function prepare($sql)
    {
        return Application::$app->db->pdo->prepare($sql);
    }

    abstract public function primaryKey(): string;

    public function findOne($where)
    {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode('AND ', array_map(fn($attr) => "$attr = :$attr", $attributes));
        $statement = static::prepare("SELECT * FROM $tableName WHERE $sql");
        foreach ($where as $key => $value) {
            $statement->bindValue(":$key", $value);
        }
        $statement->execute();
        return $statement->fetchObject(static::class);
    }
}
