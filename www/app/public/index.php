<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>データ集計テスト</title>
</head>
<body>

<?php
$dbConfig = require_once(dirname(__FILE__) . '/../config.php');

/**
 * 接続
 */
try {
    $db = new PDO("mysql:dbname={$dbConfig['database']};host={$dbConfig['host']}", $dbConfig['user'], $dbConfig['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // クエリ実行時のエラーを例外で補足
} catch (PDOException $e) {
    echo $e->getMessage() . "\n";
    exit();
}

/**
 * 指定カラムの平均値・最高値・最低値を集計
 * @param PDO $db
 * @param string $column
 * @return array ['avg' => float, 'max' => float, 'min' => float]
 */
function aggregateColumn($db, $column) {
    $query = $db->prepare("select avg($column) as avg, max($column) as max, min($column) as min from trn_answers");
    $query->execute();
    return $query->fetch(PDO::FETCH_ASSOC);
}

/**
 * 年代別の指定カラム平均値・最高値・最低値・件数を集計
 * @param PDO $db
 * @param int $age: 20, 30, 40, 50, 60
 * @param string $column
 * @return array ['avg' => float, 'max' => float, 'min' => float, 'count' => int]
 */
function aggregateAgeColumn($db, $age, $column) {
    $query = $db->prepare("select avg($column) as avg, max($column) as max, min($column) as min, count($column) as count from trn_answers where ? <= age and age < ?");
    $query->execute([$age, $age + 10]);
    return $query->fetch(PDO::FETCH_ASSOC);
}
?>

<?php foreach ([
    ['column' => 'yearly_income', 'text' => '年収情報'],
    ['column' => 'average_overtime', 'text' => '残業時間'],
    ['column' => 'average_holidays', 'text' => '休日数']
] as $data) : ?>
<?php $start = microtime(true) // 処理速度測定 ?>
<dl>
    <dt><?= $data['text'] ?></dt>
    <dd>
        <?php $agg = aggregateColumn($db, $data['column']) ?>
        <dl>
            <dt>平均値</dt>
            <dd><?= $agg['avg'] ?></dd>
            <dt>年代別</dt>
            <dd>
                <?php for ($age = 20; $age <= 60; $age += 10) : ?>
                <dl>
                    <?php $ageAgg = aggregateAgeColumn($db, $age, $data['column']) ?>
                    <dt><?= $age ?>代</dt>
                    <dd>平均値: <?= $ageAgg['avg'] ?></dd>
                    <dd>最高値: <?= $ageAgg['max'] ?></dd>
                    <dd>最低値: <?= $ageAgg['min'] ?></dd>
                    <dd>人数: <?= $ageAgg['count'] ?></dd>
                </dl>
                <?php endfor ?>
            </dd>
        </dl>
        <dl>
            <dt>最高値</dt>
            <dd><?= $agg['max'] ?></dd>
        </dl>
        <dl>
            <dt>最低値</dt>
            <dd><?= $agg['min'] ?></dd>
        </dl>
    </dd>
</dl>
<dl style="font-weight: bold;">
    <dt>処理速度<dt>
    <dd><?= (microtime(true) - $start) * 1000 ?> ミリ秒</dd>
    <dt>計算回数</dt>
    <dd>6ループ (1ループ: 2000件データ)</dd>
    <dt>サーバスペック</dt>
    <dd>AWS EC2 t3.large 相当</dd>
</dl>
<hr />
<?php endforeach ?>

</body>
</html>