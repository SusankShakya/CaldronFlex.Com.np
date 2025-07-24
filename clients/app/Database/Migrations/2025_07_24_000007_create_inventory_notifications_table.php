<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInventoryNotificationsTable extends Migration
{
    public function up()
    {
        // Table for tracking inventory notification history
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'inventory_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'warehouse_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'notification_type' => [
                'type' => 'ENUM',
                'constraint' => ['low_stock', 'out_of_stock', 'inventory_report'],
                'default' => 'low_stock',
            ],
            'alert_level' => [
                'type' => 'ENUM',
                'constraint' => ['warning', 'critical', 'out_of_stock'],
                'null' => true,
            ],
            'recipient_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'recipient_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'channel' => [
                'type' => 'ENUM',
                'constraint' => ['email', 'in_app', 'both'],
                'default' => 'email',
            ],
            'stock_level_at_notification' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'threshold_at_notification' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ],
            'notification_data' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'JSON data with additional notification details',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'sent', 'failed'],
                'default' => 'pending',
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'deleted' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('inventory_id');
        $this->forge->addKey('warehouse_id');
        $this->forge->addKey('recipient_id');
        $this->forge->addKey('notification_type');
        $this->forge->addKey('sent_at');
        $this->forge->addKey('status');
        $this->forge->addKey(['inventory_id', 'warehouse_id', 'notification_type', 'alert_level'], false, false, 'idx_notification_lookup');
        $this->forge->addKey('deleted');
        
        $this->forge->createTable('inventory_notifications');

        // Add foreign key constraints
        $this->db->query('ALTER TABLE `inventory_notifications` ADD CONSTRAINT `fk_inventory_notifications_inventory` FOREIGN KEY (`inventory_id`) REFERENCES `inventory`(`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `inventory_notifications` ADD CONSTRAINT `fk_inventory_notifications_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `inventory_notifications` ADD CONSTRAINT `fk_inventory_notifications_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `inventory_notifications` ADD CONSTRAINT `fk_inventory_notifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // Drop foreign key constraints first
        $this->db->query('ALTER TABLE `inventory_notifications` DROP FOREIGN KEY `fk_inventory_notifications_inventory`');
        $this->db->query('ALTER TABLE `inventory_notifications` DROP FOREIGN KEY `fk_inventory_notifications_warehouse`');
        $this->db->query('ALTER TABLE `inventory_notifications` DROP FOREIGN KEY `fk_inventory_notifications_recipient`');
        $this->db->query('ALTER TABLE `inventory_notifications` DROP FOREIGN KEY `fk_inventory_notifications_created_by`');
        
        // Drop the table
        $this->forge->dropTable('inventory_notifications');
    }
}