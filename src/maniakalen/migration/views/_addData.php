<?php
/**
 * Created by PhpStorm.
 * User: peter.georgiev
 * Date: 19/12/2018
 * Time: 10:56
 */

$columns = [];
foreach (array_keys(reset($data)) as $cols) {
    $columns[] = "'$cols'";
}
?>
        $this->execute('SET FOREIGN_KEY_CHECKS = 0');
        $this->batchInsert(
        '<?=$table?>',
        [<?=implode(',', $columns)?>],
        [
        <?php foreach ($data as $row) {
            echo "\t\t" . '[' . implode(',', array_map(function($value) {
                    if (is_string($value)) {
                        return \Yii::$app->db->quoteValue($value);
                    } elseif ($value === null) {
                        return 'NULL';
                    } elseif ((!is_object($value) && !is_resource($value)) || $value instanceof \yii\db\Expression) {
                        return $value;
                    }

                    return '';
                }, $row)) . '],' . "\n";
        } ?>
        ]);
        $this->execute('SET FOREIGN_KEY_CHECKS = 1');
