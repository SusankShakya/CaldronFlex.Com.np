<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrintingSpecificationsTable extends Migration
{
    public function up()
    {
        // Printing specifications table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'order_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'estimate_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'print_method' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'color_mode' => [
                'type' => 'ENUM',
                'constraint' => ['CMYK', 'RGB', 'Pantone', 'Grayscale'],
                'default' => 'CMYK',
            ],
            'resolution_dpi' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 300,
            ],
            'bleed_size' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.125,
                'comment' => 'Bleed size in inches',
            ],
            'safe_zone' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.25,
                'comment' => 'Safe zone in inches',
            ],
            'pantone_colors' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of Pantone color codes',
            ],
            'special_instructions' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'proof_required' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'proof_type' => [
                'type' => 'ENUM',
                'constraint' => ['digital', 'physical', 'both'],
                'default' => 'digital',
            ],
            'turnaround_time' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Turnaround time in hours',
            ],
            'rush_order' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'file_format' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ],
            'color_profile' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'finishing_options' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON array of finishing options',
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('order_id');
        $this->forge->addKey('estimate_id');
        $this->forge->addKey('item_id');
        $this->forge->createTable('printing_specifications');
    }

    public function down()
    {
        $this->forge->dropTable('printing_specifications');
    }
}