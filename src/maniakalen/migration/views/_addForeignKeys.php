<?php foreach ($foreignKeys as $column => $fkData): ?>

        // add foreign key for table `<?= $fkData['relatedTable'] ?>`
        $this->addForeignKey(
            '<?= $fkData['fk'] ?>',
            '<?= $table ?>',
            '<?= $column ?>',
            '<?= $fkData['relatedTable'] ?>',
            '<?= $fkData['relatedColumn'] ?>',
            'CASCADE'
        );
<?php endforeach;
