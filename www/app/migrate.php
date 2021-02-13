<?php

$dbConfig = require_once(dirname(__FILE__) . '/config.php');

/**
 * 接続
 */
try {
    $db = new PDO("mysql:dbname={$dbConfig['database']};host={$dbConfig['host']}", $dbConfig['user'], $dbConfig['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // クエリ実行時のエラーを例外で補足

    /**
     * Migration
     */
    $sql = <<< SQL
create table if not exists trn_answers(
    id int AUTO_INCREMENT not null primary key,
    age int comment '年齢',
    company_scale_id int comment '在籍企業規模ID',
    industry_id int comment '業種ID',
    occupation_id int comment '職種ID',
    yearly_income int comment '現年収',
    average_overtime int comment '月間平均残業時間',
    average_holidays int comment '月間平均休日数'
);
SQL;
    $db->exec($sql);

    /**
     * Seeding: 2000データ
     * - age: 20～65歳
     * - company_scale_id: 1～10 (10個の在籍企業規模マスタデータを想定)
     * - industry_id: 1～30 (30個の業種マスタデータを想定)
     * - occupation_id: 1～30 (30個の職種マスタデータを想定)
     * - yearly_income: 5～20 * 50 (250, 300, ... 1000 万円を想定)
     * - average_overtime: 0～20 * 5 (0, 5, 10, ... 100 時間を想定)
     * - average_holidays: 0～20 (0, 1, 2, ... 20 日を想定)
     */
    for ($i = 0; $i < 2000; ++$i) {
        $age = rand(20, 65);
        $company_scale_id = rand(1, 10);
        $industry_id = rand(1, 30);
        $occupation_id = rand(1, 30);
        $yearly_income = rand(5, 20) * 50;
        $average_overtime = rand(0, 20) * 5;
        $average_holidays = rand(0, 20);
        $sql = "insert into trn_answers(age, company_scale_id, industry_id, occupation_id, yearly_income, average_overtime, average_holidays) values ($age, $company_scale_id, $industry_id, $occupation_id, $yearly_income, $average_overtime, $average_holidays)";
        echo ($i + 1) . ": $sql\n";
        $db->exec($sql);
    }
} catch (PDOException $e) {
    echo $e->getMessage() . "\n";
    exit();
}