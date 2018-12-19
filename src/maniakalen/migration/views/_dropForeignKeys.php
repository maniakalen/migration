<?php foreach ($foreignKeys as $column => $fkData): ?>
        // drops foreign key for table `<?= $fkData['relatedTable'] ?>`
        $this->dropForeignKey(
            '<?= $fkData['fk'] ?>',
            '<?= $table ?>'
        );
<?php endforeach;
