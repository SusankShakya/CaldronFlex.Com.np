<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVariantTables extends Migration
{
    public function up()
    {
        // Create item_variants table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'variant_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'comment' => 'size, material, finish, quality',
            ],
            'variant_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'variant_value' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'price_modifier_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'fixed',
                'comment' => 'fixed or percentage',
            ],
            'price_modifier' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
            ],
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addKey('variant_type');
        $this->forge->createTable('item_variants');

        // Create item_variant_combinations table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'variant_combination' => [
                'type' => 'TEXT',
                'comment' => 'JSON array of variant IDs',
            ],
            'sku' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'stock_quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'additional_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'comment' => 'Total price modifier for this combination',
            ],
            'status' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addKey('sku');
        $this->forge->createTable('item_variant_combinations');

        // Create variant_price_history table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'variant_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'For variant price changes',
            ],
            'combination_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'For combination price changes',
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'old_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'new_price' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'change_reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'changed_at' => [
                'type' => 'DATETIME',
            ],
            'changed_by' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('variant_id');
        $this->forge->addKey('combination_id');
        $this->forge->addKey('item_id');
        $this->forge->addKey('changed_at');
        $this->forge->createTable('variant_price_history');
    }

    public function down()
    {
        $this->forge->dropTable('variant_price_history', true);
        $this->forge->dropTable('item_variant_combinations', true);
        $this->forge->dropTable('item_variants', true);
    }
}